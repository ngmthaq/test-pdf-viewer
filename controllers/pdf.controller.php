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

    public function getContent(string $path)
    {
        header("Cache-Control: public");
        header("Content-Type: application/pdf");
        header("Content-Transfer-Encoding: Binary");
        $file = $this->getFileContent($path);
        return $file;
    }

    public function getRestrictions(string $path)
    {
        header('Content-Type: application/json; charset=utf-8');
        $file = $this->getFileContent($path);
        return $file;
    }

    public function getFileContent($path)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $path);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($curl);
        curl_close($curl);
        return $content;
    }
}
