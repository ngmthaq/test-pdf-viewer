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
        $encrypted_restrictions = json_encode(array("ppw" => $encrypted_data['encrypted'], "iv" => $encrypted_data['iv'], "alf" => $restrictions["alf"]));
        return $encrypted_restrictions;
    }

    /**
     * Encrypt string
     * 
     * @param array $output
     */
    protected function encrypt($plain_string)
    {
        if ($plain_string === "") {
            $output = array("encrypted" => "", "iv" => "");
        } else {
            $cipher_algo = self::CIPHER_ALGO;
            $iv = $this->getInitializationVector();
            $passphrase = session_id();
            $option = 0;
            $encrypted = openssl_encrypt($plain_string, $cipher_algo, $passphrase, $option, $iv);
            $output = array("encrypted" => $encrypted, "iv" => $iv);
        }
        return $output;
    }

    /**
     * Get a random initialization vector
     * 
     * @return string $iv
     */
    protected function getInitializationVector()
    {
        $iv1 = bin2hex(openssl_random_pseudo_bytes(4));
        $iv2 = bin2hex(openssl_random_pseudo_bytes(2));
        $iv3 = bin2hex(openssl_random_pseudo_bytes(1));
        $iv = strtoupper(implode("-", array($iv1, $iv2, $iv3)));
        return $iv;
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
