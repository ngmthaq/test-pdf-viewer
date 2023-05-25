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
        $response = $this->curl("http://localhost/pdf-js-demo-2/pdf.php");
        if ($response["code"] === 200) return $response["data"];
        return "";
    }

    /**
     * Handle get restrictions logic
     * 
     * @return string $json_restrictions
     */
    public function getRestrictions()
    {
        header("Content-Type: application/json");
        $json_restrictions = json_encode(self::DEFAULT_RESTRICTIONS);
        $response = $this->curl("http://localhost/pdf-js-demo-2/index.php");
        if ($response["code"] === 200) $json_restrictions = $response["data"];
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
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $path);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For HTTPS
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // For HTTPS
            $response = curl_exec($ch);
            $response_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return array("code" => $response_code, "data" => $response);
        } catch (\Throwable $th) {
            return array("code" => 500, "data" => $th->getMessage());
        }
    }
}
