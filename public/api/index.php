<?php
/**
 * The main script of the API interface.
 * 
 * This script handles all API interface requests.
 * Checks the validity of token if the request contains 
 * the X-API-KEY header.
 * 
 * This script is based on idea described in the linked article.
 * 
 * @author Pekka Tapio Aalto
 * @link https://medium.com/winkhosting/create-a-basic-php-api-with-token-authentication-96111eada51
 */

  // Run the init sequence.
  require_once '../../src/init.php';

  // Define header attributes to handle JSON-based POST requests.
  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Methods: GET, POST");
  header("Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, x-API-key");
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Max-Age: 3600');

  // Define the content type of the response.
  header("Content-Type: application/json; charset=UTF-8");

  // Load the endpoints definitions.
  require_once ENDPOINTS_DIR . "endpoints.php";
  require_once ENDPOINTS_DIR . "endpoints_auth.php";

  // Initialize the array of the request data.
  $requestData = array();

  // Clean up the path from the beginning of the url and 
  // any parameters from the end of the url.
  // After the cleanup, the address /~signify/api/register 
  // is shortened to /register.
  $parsedURI = parse_url($_SERVER["REQUEST_URI"]);
  $endpoint = str_replace($config['urls']['baseUrl'], "", $parsedURI["path"]);
  $endpoint = str_replace("/api", "", $endpoint);
  if (empty($endpoint)) {
      $endpoint = "/";
  }

  // Check whether the data has been sent in JSON format. 
  // If so, it is decoded and stored in the $_POST table.
  // Next code is from page https://sqkhor.com/blog/php-reading-json-from-javascript-fetch-api-and-axios.
  if (!empty($_SERVER['CONTENT_TYPE']) && preg_match("/application\/json/i", $_SERVER['CONTENT_TYPE'])) {
    if ($php_input = json_decode(trim(file_get_contents("php://input")), true)) {
      $_POST = array_merge_recursive($_POST, $php_input);
    }
  }

  // Collect the parameters given in the request and export 
  // them to the array.
  switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
      $requestData = $_POST;
      break;
    case 'GET':
      $requestData = $_GET;
      break;
    default:
      break;
  }

  // Check whether the token is output in the X-API-KEY header. 
  // If so, it is stored in the array.
  if (isset($_SERVER["HTTP_X_API_KEY"])) {
    $requestData["token"] = $_SERVER["HTTP_X_API_KEY"];
  }

  // Perform the action corresponding to the endpoint request. 
  // If the action is not found, the error text is returned.
  if (isset($endpoints[$endpoint])) {     
    $endpoints[$endpoint]($requestData);
  } else if (isset($endpoints_auth[$endpoint])) {
    // Validate the token.
    $endpoints_auth["checktoken"]($requestData);
    // The token is valid, execute the action.
    $endpoints_auth[$endpoint]($requestData);
  } else {
    // The action is not found, return error.
    $endpoints["404"](array("endpoint" => $endpoint));
  }

?>