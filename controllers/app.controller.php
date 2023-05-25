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
        if (array_key_exists("pdf", $this->get)) {
            echo $this->pdf->getFile();
        } else {
            $json_restrictions = $this->pdf->getRestrictions();
            $array_restrictions = json_decode($json_restrictions, true);
            $restrictions = $this->encryptRestrictions($array_restrictions);
            $this->render("pdf/viewer.php", compact("restrictions"));
        }
    }

    /**
     * Encrypt Restrictions
     * 
     * @param array $encrypted_restrictions
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
     */
    protected function render($path,  $variables = array())
    {
        header('Content-Type: text/html; charset=utf-8');
        extract($variables);
        include VIEW_DIR . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * Prepare request params
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
