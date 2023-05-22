<?php

class PDF
{
    protected array $get;
    protected array $post;

    public function __construct(array $get, array $post)
    {
        $this->get = $get;
        $this->post = $post;
    }

    public function getContent()
    {
        header("Cache-Control: public");
        header("Content-Type: application/pdf");
        header("Content-Transfer-Encoding: Binary");
        $file = $this->getFileContent("http://localhost:8082/pdf.php");
        return $file;
    }

    public function getRestrictions()
    {
        header('Content-Type: application/json; charset=utf-8');
        $file = $this->getFileContent("http://localhost:8082/pdf.php");
        return $file;
    }

    public function getFileContent($path)
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
}
