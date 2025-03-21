<?php
/**
 * The configs and constant definitions of the PHP app.
 */

  $config = array(
    "db" => array(
      "dbname" => $_SERVER["DB_DATABASE"],
      "username" => $_SERVER["DB_USERNAME"],
      "password" => $_SERVER["DB_PASSWORD"],
      "host" => "localhost"
    ),
    "urls" => array(
      "baseUrl" => "/~pta/signify-server"
    ),
    "settings" => array(
      "duration" => 10000
    ),
    "version" => "0.82"
  );

  define("PROJECT_ROOT", dirname(__DIR__) . "/");
  define("HELPERS_DIR", PROJECT_ROOT . "src/helpers/");
  define("MODEL_DIR", PROJECT_ROOT . "src/model/");
  define("CONTROLLER_DIR", PROJECT_ROOT . "src/controller/");
  define("ENDPOINTS_DIR", PROJECT_ROOT . "src/endpoints/");
  define("BASEURL", $config['urls']['baseUrl']);
  define("SCENE_DEFAULT_DURATION", $config['settings']['duration']);
  define("VERSION", $config['version']);

?>