<?php

class AppController
{
    /**
     * Cipher algorithm used to encrypt the password
     */
    const CIPHER_ALGO = "aes-256-cbc";

    /**
     * processed $_GET array
     */
    protected $get;

    /**
     * processed $_POST array
     */
    protected $post;

    /**
     * PDF model instance
     */
    protected $pdf;

    public function __construct()
    {
        $this->get = $this->prepare($_GET);
        $this->post = $this->prepare($_POST);
        $this->pdf = new PDFModel($this->get, $this->post);
    }

    /**
     * Launching the application
     */
    public function run()
    {
        if ($this->isGetPdf()) {
            $response = $this->pdf->getFile();
            if ($response["code"] === 200) {
                $this->sendBinary($response["data"]);
            } else {
                $this->sendBinary("", $response["code"]);
            }
        } else {
            $response = $this->pdf->getRestrictions();
            if ($response["code"] === 200) {
                $array_restrictions = json_decode($response["data"], true);
                $restrictions = $this->encryptRestrictions($array_restrictions);
                $this->render("pdf/viewer.php", compact("restrictions"));
            } else {
                $this->render("errors/index.php", array(), $response["code"]);
            }
        }
    }

    /**
     * Check is get pdf file
     * 
     * @return bool
     */
    protected function isGetPdf()
    {
        return array_key_exists("pdf", $this->get);
    }

    /**
     * Encrypt Restrictions
     * 
     * @param array $restrictions
     * @return string $encrypted_restrictions - json string
     */
    protected function encryptRestrictions($restrictions)
    {
        $plain_password = $restrictions["ppw"];
        $encrypted_data = $this->encrypt($plain_password);
        $encrypted_restrictions = json_encode(array("ppw" => $encrypted_data['output'], "key" => $encrypted_data['key'], "alf" => $restrictions["alf"]));

        return $encrypted_restrictions;
    }

    /**
     * Encrypt string
     * 
     * @param string $input
     * @param int $key
     * @param string $padding
     * @return array
     */
    protected function encrypt($input, $key = 0, $padding = "=")
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
    protected function decrypt($input, $key, $padding = "=")
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
    protected function render($path,  $variables = array(), $status = 200)
    {
        header('Content-Type: text/html; charset=utf-8');
        http_response_code($status);
        extract($variables);
        ob_start();
        include VIEW_DIR . DIRECTORY_SEPARATOR . $path;
        $contents = ob_get_contents();
        ob_clean();
        $contents = trim(preg_replace('/(\s\s+)|(\n)|(\t)/', " ", $contents));
        echo $contents;
        exit;
    }

    /**
     * Send binary back to client
     * 
     * @param mixed $binary
     * @param int $status
     */
    protected function sendBinary($binary, $status = 200)
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
    protected function prepare($vars)
    {
        $output = array();

        foreach ($vars as $key => $value) {
            if (gettype($value) === "array") {
                $output[$key] = $this->prepare($value);
            } elseif (gettype($value) === "string") {
                $output[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } else {
                $output[$key] = $value;
            }
        }

        return $output;
    }
}
