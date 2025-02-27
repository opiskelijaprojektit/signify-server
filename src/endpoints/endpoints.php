<?php
/**
 * The endpoint definitions which are accessible without token.
 * 
 * This script contains all API endpoints which can be requested
 * without token.  
 * 
 * @author Pekka Tapio Aalto
 */

// Load all the needed functions.
require_once MODEL_DIR . "device.php";
require_once HELPERS_DIR . "functions.php";

// Initialize empty endpoints array.
$endpoints = array();

/**
 * Prints a default message if the API base path is queried.
 * 
 * @param array $requestData Contains the parameters sent in the request, 
 *                           for this endpoint they are ignored.
 * @return void
 */
$endpoints["/"] = function (array $requestData): void {
  echo json_encode("API is working");
};

/**
 * Registers the device.
 * 
 * Generates tag and token for the device, stores them and clients 
 * IP address, version, screen width and height to database and 
 * returns JSON containing generated tag and token.
 * 
 * @param array $requestData Contains the devices screen width and height.
 * @return void
 */
$endpoints["/register"] = function (array $requestData): void {
  $tag = createHash(3) . "-" . createHash(3);
  $token = createAuthToken();
  $ip = getIPaddress();  
  $width = $requestData["width"];
  $height = $requestData["height"];
  $version = $requestData["version"];
  $deviceid = addDevice($tag, $token, $ip, $width, $height, $version);
  if ($deviceid) {
    echo json_encode(array("tag"=>$tag, "token"=>$token, "width"=>$width, "height"=>$height));
  }
};
  
/**
 * Prints a default message if the endpoint path does not exist.
 * 
 * @param array $requestData Contains the parameters sent in the request, 
 *                           for this endpoint they are ignored.
 * @return void
 */
$endpoints["404"] = function ($requestData): void {
  echo json_encode("Endpoint " . $requestData["endpoint"] . " not found.");
};
?>