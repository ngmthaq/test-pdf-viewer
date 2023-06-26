<?php

class PDFController
{
    /**
     * REMOTE_ADDR_PARAM_KEY
     */
    const REMOTE_ADDR_PARAM_KEY = "ipa";

    /**
     * HTTP_REFERER_PARAM_KEY
     */
    const HTTP_REFERER_PARAM_KEY = "ref";

    /**
     * QUERY_STRING_PARAM_KEY
     */
    const QUERY_STRING_PARAM_KEY = "qst";

    /**
     * REL_PARAM_KEY
     */
    const REL_PARAM_KEY = "rel";

    /**
     * SESSION_ID_PARAM_KEY
     */
    const SESSION_ID_PARAM_KEY = "ssid";

    /**
     * PRIVATE_KEY_PARAM_KEY
     */
    const PRIVATE_KEY_PARAM_KEY = "key";

    /**
     * IV_PARAM_KEY
     */
    const IV_PARAM_KEY = "iv";

    /**
     * Request type param key
     */
    const REQUEST_TYPE_PARAM_KEY = "rqt";

    /**
     * Request type (info)
     */
    const REQUEST_TYPE_INFO = "info";

    /**
     * Request type (dlg)
     */
    const REQUEST_TYPE_DLG = "dlg";

    /**
     * Request type (log)
     */
    const REQUEST_TYPE_LOG = "log";

    /**
     * Request type (error)
     */
    const REQUEST_TYPE_ERROR = "error";

    /**
     * Request type (PDF)
     */
    const REQUEST_TYPE_PDF = "pdf";

    /**
     * Environment-specific base url (development)
     */
    const DEV_BASEURL = 'http://192.168.20.133/mol_pdf_protect/';

    /**
     * Environment-specific base url (test)
     */
    const TEST_BASEURL = 'http://192.168.217.44:13080/mol_pdf_protect/';

    /**
     * Environment-specific base url (production)
     */
    const PROD_BASEURL = 'http://192.168.217.116/mol_pdf_protect/';

    /**
     * Environment-specific base url (mock)
     */
    const MOCK_BASEURL = 'http://192.168.1.199/pdf-js-demo-2/';

    /**
     * PDF API Endpoint
     */
    const PDF_PATH = 'pdf/index.php';

    /**
     * Error Endpoint
     */
    const ERROR_PATH = 'err/index';

    /**
     * Log Endpoint
     */
    const LOG_PATH = 'cli_log/download_log';

    /**
     * DLG Endpoint
     */
    const DLG_PATH = 'dlg/index';

    /**
     * Cipher algorithm used to encrypt the password
     */
    const CIPHER_ALGO = "aes-256-cbc";

    /**
     * Default restrictions
     */
    const DEFAULT_RESTRICTIONS = array("ppw" => "", "alf" => "");

    /**
     * QUERY_STRING
     */
    const QUERY_STRING = "QUERY_STRING";

    /**
     * REMOTE_ADDR
     */
    const REMOTE_ADDR = "REMOTE_ADDR";

    /**
     * HTTP_REFERER
     */
    const HTTP_REFERER = "HTTP_REFERER";

    /**
     * HTTP_USER_AGENT
     */
    const HTTP_USER_AGENT = "HTTP_USER_AGENT";

    /**
     * HTTP_HOST
     */
    const HTTP_HOST = "HTTP_HOST";

    /**
     * SCRIPT_NAME
     */
    const SCRIPT_NAME = "SCRIPT_NAME";

    /**
     * HTTPS
     */
    const HTTPS = "HTTPS";

    /**
     * Alf separator
     */
    const ALF_SEPARATOR = "|";

    /**
     * processed $_GET array
     */
    protected $get;

    /**
     * processed $_POST array
     */
    protected $post;

    public function __construct($get, $post)
    {
        $this->get = $get;
        $this->post = $post;
    }

    /**
     * Handle get the PDF file logic
     * 
     * @return mixed $file
     */
    public function getFile()
    {
        header("Cache-Control: public");
        header("Content-Type: application/pdf");
        header("Content-Transfer-Encoding: Binary");
        $params = $this->getQueryParameters();
        $rqt = $this->getRequestType($params);
        $rqt = $rqt === null ? self::REQUEST_TYPE_PDF : $rqt;
        $path = $this->getFullPath($rqt, $params);
        $response = $this->curl($path);
        return $response;
    }

    /**
     * Handle get restrictions logic
     * 
     * @return array
     */
    public function getRestrictions()
    {
        $params = $this->getQueryParameters();
        $rqt = $this->getRequestType($params);
        $rqt = $rqt === null ? self::REQUEST_TYPE_INFO : $rqt;
        $path = $this->getFullPath($rqt, $params);
        header("Content-Type: application/json");
        $response = $this->curl($path);
        if ($response["code"] === 200) {
            $response["data"] = $this->handleEncryptRestrictions($response["data"], $params[self::IV_PARAM_KEY]);
        }
        return $response;
    }

    /**
     * Handle encrypt restrictions
     * 
     * @param string $restrictions
     * @param string $iv Initialization Vector
     * @return string $encrypted_restrictions
     */
    public function handleEncryptRestrictions($restrictions, $iv)
    {
        $restrictions = json_decode($restrictions, true);
        $alf = strlen($restrictions["alf"]) > 0 ? explode(self::ALF_SEPARATOR, $restrictions["alf"]) : $restrictions["alf"];
        $encrypted_restrictions = array("ppw" => $restrictions["ppw"], "iv" => $iv, "alf" => $alf);
        return $encrypted_restrictions;
    }

    /**
     * Get query parameters
     * 
     * @return array
     */
    public function getQueryParameters()
    {
        $params = array();
        $query_string = $this->getServerVariable(self::QUERY_STRING);
        parse_str($query_string, $params);
        $params[self::REL_PARAM_KEY] = 1;
        $params[self::SESSION_ID_PARAM_KEY] = session_id();
        $params[self::REMOTE_ADDR_PARAM_KEY] = $this->getServerVariable(self::REMOTE_ADDR);
        $params[self::HTTP_REFERER_PARAM_KEY] = rawurlencode($this->getServerVariable(self::HTTP_REFERER));
        $params[self::QUERY_STRING_PARAM_KEY] = rawurlencode($this->getServerVariable(self::QUERY_STRING));
        $params[self::PRIVATE_KEY_PARAM_KEY] = session_id();
        $params[self::IV_PARAM_KEY] = $this->generateInitializationVector();
        return $params;
    }

    /**
     * Get request type ($rqt)
     * 
     * @param array $params Parameters handle by getQueryParameters method
     * @return string|null
     */
    public function getRequestType($params)
    {
        return isset($params[self::REQUEST_TYPE_PARAM_KEY]) ? $params[self::REQUEST_TYPE_PARAM_KEY] : null;
    }

    /**
     * Get server variable
     * 
     * @param string $key The key of the variable to get
     * @return string
     */
    public function getServerVariable($key)
    {
        if (is_null(filter_input(INPUT_SERVER, $key))) {
            return isset($_SERVER[$key]) ? $_SERVER[$key] : '';
        } else {
            return filter_input(INPUT_SERVER, $key);
        }
    }

    /**
     * Get full path
     * 
     * @param string $rqt
     * @param array $params
     * @return string
     */
    public function getFullPath($rqt, $params)
    {
        $query_string = http_build_query($params);
        $query_string = strlen($query_string) > 0 ? "?" . $query_string : "";
        $url = (empty($_SERVER[self::HTTPS]) ? "http://" : "https://") . $_SERVER[self::HTTP_HOST] . $_SERVER[self::SCRIPT_NAME];

        if (preg_match('/(mol-develop\.net|192\.168\.20\.133\/mol_pdf_protect-relay\/)/', $url)) {
            $base_url = self::DEV_BASEURL;
        } elseif (preg_match('/(test(ing)?|meteo-inc\.jp)/', $url)) {
            $base_url = self::TEST_BASEURL;
        } elseif (preg_match('/192\.168\.1\.199/', $url)) {
            $base_url = self::MOCK_BASEURL;
        } else {
            $base_url = self::PROD_BASEURL;
        }

        switch ($rqt) {
            case self::REQUEST_TYPE_DLG:
                return $base_url . self::DLG_PATH . $query_string;
            case self::REQUEST_TYPE_LOG:
                return $base_url . self::LOG_PATH . $query_string;
            case self::REQUEST_TYPE_ERROR:
                return $base_url . self::ERROR_PATH . $query_string;
            default:
                return $base_url . self::PDF_PATH . $query_string;
        }
    }

    /**
     * Connect and communicate to API servers.
     * 
     * @param string $path
     * @return array
     */
    public function curl($path)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $path);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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
     * Encrypt string (Row fence cipher)
     * 
     * @param string $input
     * @param int $key
     * @param string $padding
     * @return array
     */
    public function rowFenceEncrypt($input, $key = 0, $padding = "=")
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
     * Decrypt string (Row fence cipher)
     * 
     * @param string $input
     * @param int $key
     * @param string $padding
     * @return string
     */
    public function rowFenceDecrypt($input, $key, $padding = "=")
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
     * Encrypt string
     * 
     * @param array $output
     */
    public function aesEncrypt($plain_string)
    {
        if ($plain_string === "") {
            $output = array("encrypted" => "", "iv" => "");
        } else {
            $cipher_algo = self::CIPHER_ALGO;
            $iv = $this->generateInitializationVector();
            $passphrase = session_id();
            $option = 0;
            $encrypted = openssl_encrypt($plain_string, $cipher_algo, $passphrase, $option, $iv);
            $output = array("encrypted" => $encrypted, "iv" => $iv);
        }
        return $output;
    }

    /**
     * Generate Initialization Vector
     * @return string
     */
    public function generateInitializationVector()
    {
        $iv1 = bin2hex(openssl_random_pseudo_bytes(4));
        $iv2 = bin2hex(openssl_random_pseudo_bytes(2));
        $iv3 = bin2hex(openssl_random_pseudo_bytes(1));
        $iv = strtoupper(implode("-", array($iv1, $iv2, $iv3)));
        return $iv;
    }
}
