<?php

class App
{
    protected PDF $pdf;
    protected array $get;
    protected array $post;

    public function __construct()
    {
        $this->pdf = new PDF();
        $this->get = $this->prepare($_GET);
        $this->post = $this->prepare($_POST);
    }

    public function run()
    {
        $this->render("pdf.php");
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
