<?php

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
 * Connect and communicate to API servers.
 * 
 * @param string $path
 * @return array
 */
function curl($path)
{
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $path);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For HTTPS
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // For HTTPS
        $response = curl_exec($ch);
        $response_info = curl_getinfo($ch);
        $response_code = (int)$response_info["http_code"];
        curl_close($ch);
        if ($response_code === 0) {
            $response_array = array("code" => 500, "message" => "Cannot connect with $path!", "info" => $response_info, "data" => $response);
            console($response_array["message"], "error");
            return $response_array;
        }
        return array("code" => $response_code, "message" => null, "info" => $response_info, "data" => $response);
    } catch (\Throwable $th) {
        console($th->getMessage(), "error");
        return array("code" => 500, "message" => $th->getMessage(), "info" => array(), "data" => null);
    }
}

/**
 * Encrypt string
 * 
 * @param string $input
 * @param int $key
 * @param string $padding
 * @return array
 */
function encrypt($input, $key = 0, $padding = "=")
{
    if ($input === "") return array("output" => "", "key" => $key);

    $text_length = strlen($input);
    $key = $key === 0 ? rand(2, $text_length) : $key;
    $array_text = str_split($input);
    $rows = array();

    for ($i = 0; $i < $key; $i++) {
        $rows[$i] = array();
    }

    for ($i = 0; $i < $key; $i++) {
        for ($j = 0; $j < ceil($text_length / $key); $j++) {
            $pos = ($key * $j) + $i;
            $rows[$i][] = isset($array_text[$pos]) ? $array_text[$pos] : $padding;
        }
    }

    $ouput = implode("", array_map(function ($row) {
        return implode("", $row);
    }, $rows));

    return array("output" => $ouput, "key" => $key);
}

/**
 * Decrypt string
 * 
 * @param string $input
 * @param int $key
 * @param string $padding
 * @return string
 */
function decrypt($input, $key, $padding = "=")
{
    $text_length = strlen($input);
    $array_text = str_split($input);
    $columns = round($text_length / $key);
    $rows = array();
    $plain_rows = array();

    for ($i = 0; $i < $key; $i++) {
        for ($j = 0; $j < $columns; $j++) {
            $pos = $i * $columns + $j;
            $rows[$i][] = $array_text[$pos];
        }
    }

    for ($p = 0; $p < $columns; $p++) {
        $plain_rows[$p] = array_map(function ($row) use ($p) {
            return $row[$p];
        }, $rows);
    }

    $ouput = implode("", array_map(function ($row) {
        return implode("", $row);
    }, $plain_rows));

    return str_replace($padding, "", $ouput);
}

/**
 * Serverside rendering
 * 
 * @param string $path path to view
 * @param array $variables variables pass to view
 * @param int $status
 */
function renderView($path,  $variables = array(), $status = 200)
{
    header('Content-Type: text/html; charset=utf-8');
    http_response_code($status);
    extract($variables);
    ob_start();
    include VIEW_DIR . DIRECTORY_SEPARATOR . $path;
    $contents = ob_get_contents();
    ob_clean();
    echo $contents;
    exit;
}

/**
 * Send binary back to client
 * 
 * @param mixed $binary
 * @param int $status
 */
function sendBinary($binary, $status = 200)
{
    header('Content-Transfer-Encoding: binary');
    header('Content-type: application/pdf');
    header('Content-Disposition: attachment; filename=molfile.pdf');
    header('Content-Length: ' . strlen($binary));
    http_response_code($status);
    echo $binary;
    exit;
}

/**
 * Prepare request params avoid XSS
 * 
 * @return array $output
 */
function prepare($vars)
{
    $output = array();

    foreach ($vars as $key => $value) {
        if (gettype($value) === "array") {
            $output[$key] = prepare($value);
        } elseif (gettype($value) === "string") {
            $output[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        } else {
            $output[$key] = $value;
        }
    }

    return $output;
}
