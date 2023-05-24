<?php

class AppController
{
    /**
     * Cipher algorithm used to encrypt the password
     */
    const CIPHER_ALGO = "aes-256-cbc";

    /**
     * Default restrictions
     */
    const DEFAULT_RESTRICTIONS = ["ppw" => "", "alf" => ""];

    /**
     * processed $_GET array
     */
    protected array $get;

    /**
     * processed $_POST array
     */
    protected array $post;

    public function __construct()
    {
        $this->get = $this->prepare($_GET);
        $this->post = $this->prepare($_POST);
    }

    /**
     * Launching the application
     */
    public function run()
    {
        if (array_key_exists("pdf", $this->get)) {
            $this->getFile();
        } else {
            $json_restrictions = $this->getRestrictions();
            $array_restrictions = json_decode($json_restrictions, true);
            $restrictions = $this->encryptRestrictions($array_restrictions);
            $this->render("view.php", compact("restrictions"));
        }
    }

    /**
     * Handle get the PDF file logic
     */
    protected function getFile()
    {
        header("Cache-Control: public");
        header("Content-Type: application/pdf");
        header("Content-Transfer-Encoding: Binary");
        $file = $this->curl("http://localhost/pdf-js-demo-2/pdf.php");
        echo $file;
    }

    /**
     * Handle get restrictions logic
     * 
     * @return string $json_restrictions
     */
    protected function getRestrictions()
    {
        header("Content-Type: application/pdf");
        $json_restrictions = $this->curl("http://localhost/pdf-js-demo-2/index.php");
        if (!$json_restrictions) $json_restrictions = json_encode(self::DEFAULT_RESTRICTIONS);
        return $json_restrictions;
    }

    /**
     * Encrypt Restrictions
     * 
     * @param array $encrypted_restrictions
     */
    protected function encryptRestrictions(array $restrictions)
    {
        $plain_password = $restrictions["ppw"];
        $encrypted_data = $this->encrypt($plain_password);
        $encrypted_restrictions = json_encode(["ppw" => $encrypted_data['encrypted'], "iv" => $encrypted_data['iv'], "alf" => $restrictions["alf"]]);
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
            $output = ["encrypted" => "", "iv" => ""];
        } else {
            $cipher_algo = self::CIPHER_ALGO;
            $iv = $this->getInitializationVector();
            $passphrase = session_id();
            $option = 0;
            $encrypted = openssl_encrypt($plain_string, $cipher_algo, $passphrase, $option, $iv);
            $output = ["encrypted" => $encrypted, "iv" => $iv];
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
        $iv = strtoupper(implode("-", [$iv1, $iv2, $iv3]));
        return $iv;
    }

    /**
     * Connect and communicate to API servers.
     */
    protected function curl($path)
    {
        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $path);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $content = curl_exec($curl);
            curl_close($curl);
            return $content;
        } catch (\Throwable $th) {
            return null;
        }
    }

    /**
     * Serverside rendering
     */
    protected function render(string $path, array $variables = [])
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
    protected function prepare(array $vars)
    {
        $output = [];
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
