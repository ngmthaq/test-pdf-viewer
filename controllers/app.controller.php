<?php

class App
{
    protected PDF $pdf;
    protected array $get;
    protected array $post;

    public function __construct()
    {
        $this->get = $this->prepare($_GET);
        $this->post = $this->prepare($_POST);
        $this->pdf = new PDF($this->get, $this->post);
    }

    public function run()
    {
        if (array_key_exists("pdf", $this->get)) {
            echo $this->pdf->getContent("http://localhost:8082/pdf.php");
        } else {
            $default_restrictions = json_encode(array("ppw" => "", "alf" => ""));
            $restrictions = $this->pdf->getRestrictions("http://localhost:8082/") ?? $default_restrictions;
            header('Content-Type: text/html; charset=utf-8');
            $this->render("view.php", compact("restrictions"));
        }
    }

    protected function render(string $path, array $variables = [])
    {
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
