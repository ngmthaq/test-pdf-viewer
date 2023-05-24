<?php

/**
 * Start a dev server
 * 
 * @example php dev.php {port}
 */

$host = $argv[1] ?? 8081;
exec("php -S 127.0.0.1:$host");
