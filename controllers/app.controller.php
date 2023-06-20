<?php

class AppController
{
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
        $this->pdf = new PDFController($this->get, $this->post);
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
                $restrictions = $this->pdf->encryptRestrictions($array_restrictions);
                $this->renderView("pdf/viewer.php", compact("restrictions"));
            } else {
                $this->renderView("errors/index.php", array(), $response["code"]);
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
        return array_key_exists("rqt", $this->get) && $this->get["rqt"] === "pdf";
    }

    /**
     * Serverside rendering
     * 
     * @param string $path path to view
     * @param array $variables variables pass to view
     * @param int $status
     */
    public function renderView($path,  $variables = array(), $status = 200)
    {
        header('Content-Type: text/html; charset=utf-8');
        http_response_code($status);
        extract($variables);
        include VIEW_DIR . DIRECTORY_SEPARATOR . $path;
        exit;
    }

    /**
     * Send binary back to client
     * 
     * @param mixed $binary
     * @param int $status
     */
    public function sendBinary($binary, $status = 200)
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
    public function prepare($vars)
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
