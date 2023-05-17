<?php

session_start();

define("ROOT_DIR", __DIR__);
define("CONTROLLER_DIR", ROOT_DIR . DIRECTORY_SEPARATOR . "controllers");
define("VIEW_DIR", ROOT_DIR . DIRECTORY_SEPARATOR . "views");
define("LIB_DIR", ROOT_DIR . DIRECTORY_SEPARATOR . "libs");

require_once "./helpers.php";
require_once "./controllers/app.controller.php";
require_once "./controllers/pdf.controller.php";

$app = new App();
$app->run();
