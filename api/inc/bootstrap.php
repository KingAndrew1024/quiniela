<?php
define("PROJECT_ROOT_PATH", substr(__DIR__, 0, strrpos(__DIR__, DIRECTORY_SEPARATOR)));

// include main configuration file
require_once PROJECT_ROOT_PATH . DIRECTORY_SEPARATOR . "inc" . DIRECTORY_SEPARATOR . "config.php";

// DB connection
require_once PROJECT_ROOT_PATH . DIRECTORY_SEPARATOR . "inc" . DIRECTORY_SEPARATOR . "Connection.php";

// include the base controller file
require_once PROJECT_ROOT_PATH . DIRECTORY_SEPARATOR . "controllers" . DIRECTORY_SEPARATOR . "BaseController.php";
 
// include the use model file
//require_once PROJECT_ROOT_PATH . "/Model/UserModel.php";
