<?php
/**
 * The endpoint definitions which are accessible only with token.
 * 
 * This script contains all API endpoints which can be requested
 * only with token.  
 * 
 * @author Pekka Tapio Aalto
 */

// Load all the needed functions.
require_once MODEL_DIR . "device.php";
require_once MODEL_DIR . "list.php";
require_once HELPERS_DIR . "functions.php";

/**
 * Gets the active content of the given list.
 * 
 * This function retrieves all active content for
 * given list. If list contains sublists, then
 * this function is called recursively to collect 
 * all the content of the sublist and merges returned
 * values.
 * 
 * @author Pekka Tapio Aalto
 * 
 * @param int $listid    Id of the list.
 * @param int $duration  Default duration of the content which
 *                       is used when duration is not defined for content. 
 *                       If not defined, use whole applications
 *                       default value.
 * @param array $checked Array containing all the list processed
 *                       in current branch. This is used to prevent
 *                       infinite loops where list is used again on it's
 *                       sublists.
 * @return array         Returns array containing results latest 
 *                       modification time and result content in an
 *                       array.
 * 
 */
function processListrows($listid, $duration = SCENE_DEFAULT_DURATION, $checked = array()) {

  // Initialize the result array.
  $result = array();

  // A variable to store the last modification time.
  $lastUpdate = "1970-01-01 00:00:00";
  
  // Check if the list has already been processed above in this branch.
  if (!in_array($listid, $checked)) {

    // The list has not yet been processed before, so add it to 
    // the list of processed. This prevents endless list loops, 
    // where the same list would reappear in one of its sub-lists.
    array_push($checked, $listid);      

    // Get the list rows.
    $listrows = getListRows($listid);

    // Go through the rows one by one. 
    foreach ($listrows as $listrow) {
      // Check if there is a newer edit time on the list row. 
      // If so, update it to the latest edit time.
      if ($listrow["updated"] > $lastUpdate) {
        $lastUpdate = $listrow["updated"];
      }     

      // If the type is (c)ontent, then add to the array 
      // a new item. Use the first of the following list with 
      // a specified value as the duration of the content.
      //  1. The duration defined for the content (contentduration).
      //  2. Default value, which is the default duration for the 
      //     whole list or the default duration defined for the 
      //     whole application.
      if ($listrow["rowtype"] == "c") {
        $rowduration = $listrow["contentduration"] ? $listrow["contentduration"] : $duration; 
        array_push($result, array(
          "id"=>$listrow["contentid"],
          "type"=>$listrow["contenttype"], 
          "data"=>json_decode($listrow["contentdata"]), 
          "duration"=>intval($rowduration)
        ));
      }

      // If the type is (l)ist and it is active, then this function 
      // is called again with the id of the list.
      // In the function call, the default duration of the content 
      // is the first defined value on following list:
      //  1. The duration defined for the list (listduration).
      //  2. Default value, which is the default duration for the 
      //     whole application.
      if ($listrow["rowtype"] == "l" && $listrow["active"] == "1") {
        // Find out the default list duration.        
        $listduration = $listrow["listduration"] ? $listrow["listduration"] : $duration;
        // Process the sublist.
        $processResult = processListrows($listrow["listid"], $listduration, $checked);
        // Store newer update time if sublists last update time is newer. 
        if ($processResult["updated"] > $lastUpdate) {
          $lastUpdate = $processResult["updated"];
        }
        // Merge sublists result with main list.
        $result = array_merge($result, $processResult["result"]);
      }
    }   
  }

  // Returns aa array with:
  // - the last modification time of the list (and sublists)
  // - contents of the list (and sublists)
  return array("updated" => $lastUpdate, "result" => $result);
}

// Initialize endpoints array.
$endpoints_auth = array();

/**
 * Checks if the token is valid, and prevents the execution of
 * the requested endpoint.
 *
 * @param array $requestData Contains the parameters sent in the request,
 *                           for this endpoint is required an item with
 *                           key "token" that contains the token
 *                           received to authenticate and authorize
 *                           the request.
 * @return void
 */
$endpoints_auth["checktoken"] = function ($requestData): void {
  if (!isset($requestData["token"])) {
    echo json_encode("No token was received to authorize the operation. Verify the information sent.");
    exit;
  }

  if (!isset($tokens[$requestData["token"]])) {
    $token = $requestData["token"];
    $result = checkDeviceToken($token);
    if (!$result) {
      echo json_encode("The token " . $requestData["token"] . " does not exists or is not authorized to perform this operation.");
      exit;
    }
  }
};

/**
 * Checks if the device is linked. 
 * 
 * This function checks device's status and stores new screen 
 * width and height if device's screen width or height has been 
 * changed.
 * 
 * Returns array containing state with following meaning:
 *  1 = Device is registered, but not yet linked.
 *  2 = Device is registered and linked.
 * 
 * @param array $requestData Contains the parameters sent in the request.
 * @return void
 */
$endpoints_auth["/check"] = function ($requestData): void {
  // Get the data from request.
  $token = $requestData["token"];
  $width = $requestData["width"];
  $height = $requestData["height"];

  // Get the data of device.
  $device = getDeviceWithToken($token);

  // Check if device's screen width or height is changed.
  // If so, update new values to device's data.
  if ($device["height"] != $height || $device["width"] != $width) {
    updateScreenSize($device["id_device"], $width, $iddevice);
  }

  // Check if device's name is defined. If so, device is linked. 
  // Otherwise it is only registered and waiting for linking.
  if ($device["name"]) {
    echo json_encode(array("state"=>"2", "name"=>$device["name"], "descr"=>$device["descr"], "width"=>$width, "height"=>$height));
    exit;
  } else {
    echo json_encode(array("state"=>"1"));
    exit; 
  }
  
};

/**
 * Get the the vulnerability RSS feed from the Kyberturvallisuuskeskus.
 *
 * Returns XML document containing latest vulnerability information.
 * This endpoint acts as an proxy server.
 *
 * @param array $requestData Contains the parameters sent in the request.
 * @return void
 */
$endpoints_auth["/scene-vulnerability"] = function ($requestData): void {
  $url = "https://www.kyberturvallisuuskeskus.fi/sites/default/files/rss/vulns.xml";
  if (($xml_data = file_get_contents($url))===false){
    echo json_encode("Error fetching vulnerability data");
  } else {
    header("Content-Type: application/xml");
    echo $xml_data;
  }
};

/**
 * Get the scenes of the device. 
 * 
 * Returns array containing scene data array, returned datas latest 
 * update time and calculated MD5 hash of the JSON encoded scene data.
 * 
 * @param array $requestData Contains the parameters sent in the request.
 * @return void
 */
$endpoints_auth["/scenes"] = function ($requestData): void {

  // Get the deviceid with token.  
  $token = $requestData["token"];
  $device = getDeviceWithToken($token);
  $deviceid = $device["id_device"];

  // Get the lists allocated to the device.
  $lists = getDeviceLists($deviceid);

  // Initialize the result array.
  $result = array();

  // Initialize a variable to store latest modification time.
  $lastUpdate = "1970-01-01 00:00:00";

  // Process the lists one by one.
  foreach ($lists as $list) {

    // Check if current list is active. If so, 
    // process it, otherwise skip it.
    if ($list["active"] == "1") {

      // Check if list's update time is newer than latest
      // modification time.  
      if ($list["updated"] > $lastUpdate) {
        $lastUpdate = $list["updated"];
      }

      // Check if default content duration is defined for
      // the list. In not, use applications default value.
      $duration = $list["duration"] ? $list["duration"] : SCENE_DEFAULT_DURATION;

      // Process list and merge results with final result list.
      // Update latest modification time if processed list had
      // newer update time.
      $listResult = processListrows($list["id_list"], $duration);
      $result = array_merge($result, $listResult["result"]);
      if ($listResult["updated"] > $lastUpdate) {
        $lastUpdate = $listResult["updated"];
      }
    }
  }

  // Encode result data to JSON string and calculate
  // MD5 hash.
  $encodedScenes = json_encode($result);
  $hash = md5($encodedScenes);

  // Return JSON encoded array containing hash, latest 
  // modification time and scene data.
  echo json_encode(array("hash"=>$hash, "updated"=>$lastUpdate, "scenes" => $result, "version" => VERSION));
  exit;
};