<?php

class AppController
{
    const CIPHER_ALGO = "aes-256-cbc";
    const CIPHER_IV_BYTES = 16;

    protected PDFController $pdf;
    protected array $get;
    protected array $post;

    public function __construct()
    {
        $this->get = $this->prepare($_GET);
        $this->post = $this->prepare($_POST);
        $this->pdf = new PDFController($this->get, $this->post);
    }

    public function run()
    {
        if (array_key_exists("pdf", $this->get)) {
            echo $this->pdf->getContent();
        } else {
            $raw_restrictions = $this->pdf->getRestrictions();
            $array_restrictions = json_decode($raw_restrictions, true);
            $plain_password = $array_restrictions["ppw"];
            $encrypted_data = $this->decrypt($plain_password);
            $restrictions = json_encode(["ppw" => $encrypted_data['encrypted'], "iv" => $encrypted_data['iv'], "alf" => ""]);
            $this->render("view.php", compact("restrictions"));
        }
    }

    protected function decrypt($plain_string)
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

    protected function getInitializationVector()
    {
        $iv1 = bin2hex(openssl_random_pseudo_bytes(4));
        $iv2 = bin2hex(openssl_random_pseudo_bytes(2));
        $iv3 = bin2hex(openssl_random_pseudo_bytes(1));
        return strtoupper(implode("-", [$iv1, $iv2, $iv3]));
    }

    protected function render(string $path, array $variables = [])
    {
        header('Content-Type: text/html; charset=utf-8');
        extract($variables);
        include VIEW_DIR . DIRECTORY_SEPARATOR . $path;
    }

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
