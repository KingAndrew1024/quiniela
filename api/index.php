<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . "inc" . DIRECTORY_SEPARATOR . "bootstrap.php";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = substr($uri, strrpos($uri, 'php/'));
$uri = explode('/', $uri);

$controllers = array("teams", "matches", "users", "forecasts");

if (isset($uri[1]) && in_array($uri[1], $controllers) && isset($uri[2])) {
  $controllerName = substr_replace($uri[1], strtoupper(substr($uri[1], 0, 1)), 0, 1) . "Controller";
  require_once PROJECT_ROOT_PATH . DIRECTORY_SEPARATOR . "controllers" . DIRECTORY_SEPARATOR . $controllerName . ".php";

  $objFeedController = new $controllerName();
  $strMethodName = $uri[2] . 'Action';
  $objFeedController->{$strMethodName}();
} else {
  header("HTTP/1.1 404 Not Found");
  exit();
}
