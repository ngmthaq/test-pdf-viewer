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
        $this->get = prepare($_GET);
        $this->post = prepare($_POST);
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
                return sendBinary($response["data"]);
            } else {
                return sendBinary("", $response["code"]);
            }
        } else {
            $response = $this->pdf->getRestrictions();
            if ($response["code"] === 200) {
                $array_restrictions = json_decode($response["data"], true);
                $restrictions = $this->encryptRestrictions($array_restrictions);
                return renderView("pdf/viewer.php", compact("restrictions"));
            } else {
                return renderView("errors/index.php", array(), $response["code"]);
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
        $encrypted_data = rowFenceEncrypt($plain_password);
        $encrypted_restrictions = json_encode(array("ppw" => $encrypted_data['output'], "key" => $encrypted_data['key'], "alf" => $restrictions["alf"]));

        return $encrypted_restrictions;
    }
}
