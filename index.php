<?php

if (!session_id()) {
    $session_name = "PHPSESSID";
    if (!array_key_exists($session_name, $_COOKIE)) {
        $session_uuid = strtoupper(vsprintf('%s%s-%s-%s-%s-%s%s', str_split(bin2hex(random_bytes(16)), 4)));
        session_id($session_uuid);
    }
    session_name($session_name);
    session_start();
}

/**
 * Define constants
 */
define("ROOT_DIR", __DIR__);
define("CONTROLLER_DIR", ROOT_DIR . DIRECTORY_SEPARATOR . "controllers");
define("MODEL_DIR", ROOT_DIR . DIRECTORY_SEPARATOR . "models");
define("VIEW_DIR", ROOT_DIR . DIRECTORY_SEPARATOR . "views");
define("VENDORS_DIR", ROOT_DIR . DIRECTORY_SEPARATOR . "vendors");
define("HELPERS_DIR", ROOT_DIR . DIRECTORY_SEPARATOR . "helpers");

/**
 * Import needed files
 */
require_once "./helpers/helpers.php";
require_once "./models/pdf.model.php";
require_once "./controllers/app.controller.php";

/**
 * Launching application
 */
$app = new AppController();
$app->run();
