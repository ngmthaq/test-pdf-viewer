<?php

class PDFModel
{
    /**
     * Default restrictions
     */
    const DEFAULT_RESTRICTIONS = array("ppw" => "", "alf" => "");

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
     * @return binary $file
     */
    public function getFile()
    {
        header("Cache-Control: public");
        header("Content-Type: application/pdf");
        header("Content-Transfer-Encoding: Binary");
        $file = $this->curl("http://localhost/pdf-js-demo-2/pdf.php");
        return $file;
    }

    /**
     * Handle get restrictions logic
     * 
     * @return string $json_restrictions
     */
    public function getRestrictions()
    {
        header("Content-Type: application/pdf");
        $json_restrictions = $this->curl("http://localhost/pdf-js-demo-2/index.php");
        if (!$json_restrictions) $json_restrictions = json_encode(self::DEFAULT_RESTRICTIONS);
        return $json_restrictions;
    }

    /**
     * Connect and communicate to API servers.
     * 
     * @return mixed $output | null
     */
    protected function curl($path)
    {
        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $path);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($curl);
            curl_close($curl);
            return $output;
        } catch (\Throwable $th) {
            return null;
        }
    }
}
