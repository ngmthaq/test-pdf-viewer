<?php

class PDF
{
    public function getContent(string $path)
    {
        header("Cache-Control: public");
        header("Content-Type: application/pdf");
        header("Content-Transfer-Encoding: Binary");
        $file = file_get_contents($path);
        return $file;
    }

    public function getRestrictions(string $path)
    {
        header('Content-Type: application/json; charset=utf-8');
        $file = file_get_contents($path);
        return $file;
    }
}
