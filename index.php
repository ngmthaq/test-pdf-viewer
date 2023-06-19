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
 * Import assets
 * 
 * @param string $path
 */
function assets(string $path)
{
    echo "./vendors/$path?t=" . time();
}

/**
 * Dump data
 * 
 * @param mixed $data
 */
function dump($data)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    echo "<br/>";
}

/**
 * Log data into console tab in devtool
 * 
 * @param mixed $output
 * @param string $type log|info|warn|error|table|trace
 * @param bool $with_script_tags
 */
function console($output, $type = "log", $with_script_tags = true)
{
    $js_code = 'console.' . $type . '(' . json_encode($output, JSON_HEX_TAG) . ');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
}

/**
 * Import needed files
 */
require_once "./controllers/pdf.controller.php";
require_once "./controllers/app.controller.php";

/**
 * Launching application
 */
$app = new AppController();
$app->run();
