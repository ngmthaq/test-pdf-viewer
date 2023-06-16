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
     * @return mixed $file
     */
    public function getFile()
    {
        header("Cache-Control: public");
        header("Content-Type: application/pdf");
        header("Content-Transfer-Encoding: Binary");
        return curl("http://localhost/pdf-js-demo-2/pdf.php");
    }

    /**
     * Handle get restrictions logic
     * 
     * @return array
     */
    public function getRestrictions()
    {
        header("Content-Type: application/json");
        return curl("http://localhost/pdf-js-demo-2/index.php");
    }
}
