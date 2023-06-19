<?php

class PDFController
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
        return $this->curl("http://localhost/pdf-js-demo-2/pdf.php");
    }

    /**
     * Handle get restrictions logic
     * 
     * @return array
     */
    public function getRestrictions()
    {
        header("Content-Type: application/json");
        return $this->curl("http://localhost/pdf-js-demo-2/index.php");
    }

    /**
     * Encrypt Restrictions
     * 
     * @param array $restrictions
     * @return string $encrypted_restrictions - json string
     */
    public function encryptRestrictions($restrictions)
    {
        $plain_password = $restrictions["ppw"];
        $encrypted_data = $this->rowFenceEncrypt($plain_password);
        $encrypted_restrictions = json_encode(array("ppw" => $encrypted_data['output'], "key" => $encrypted_data['key'], "alf" => $restrictions["alf"]));

        return $encrypted_restrictions;
    }

    /**
     * Connect and communicate to API servers.
     * 
     * @param string $path
     * @return array
     */
    public function curl($path)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $path);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For HTTPS
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // For HTTPS
            $response = curl_exec($ch);
            $response_info = curl_getinfo($ch);
            $response_code = (int)$response_info["http_code"];
            curl_close($ch);
            if ($response_code === 0) {
                $response_array = array("code" => 500, "message" => "Cannot connect with $path!", "info" => $response_info, "data" => $response);
                console($response_array["message"], "error");
                return $response_array;
            }
            return array("code" => $response_code, "message" => null, "info" => $response_info, "data" => $response);
        } catch (\Throwable $th) {
            console($th->getMessage(), "error");
            return array("code" => 500, "message" => $th->getMessage(), "info" => array(), "data" => null);
        }
    }

    /**
     * Encrypt string (Row fence cipher)
     * 
     * @param string $input
     * @param int $key
     * @param string $padding
     * @return array
     */
    public function rowFenceEncrypt($input, $key = 0, $padding = "=")
    {
        if ($input === "") return array("output" => "", "key" => $key);

        $text_length = strlen($input);
        $key = $key === 0 ? rand(2, $text_length) : $key;
        $array_text = str_split($input);
        $rows = array();

        for ($i = 0; $i < $key; $i++) {
            $rows[$i] = array();
        }

        for ($i = 0; $i < $key; $i++) {
            for ($j = 0; $j < ceil($text_length / $key); $j++) {
                $pos = ($key * $j) + $i;
                $rows[$i][] = isset($array_text[$pos]) ? $array_text[$pos] : $padding;
            }
        }

        $ouput = implode("", array_map(function ($row) {
            return implode("", $row);
        }, $rows));

        return array("output" => $ouput, "key" => $key);
    }

    /**
     * Decrypt string (Row fence cipher)
     * 
     * @param string $input
     * @param int $key
     * @param string $padding
     * @return string
     */
    public function rowFenceDecrypt($input, $key, $padding = "=")
    {
        $text_length = strlen($input);
        $array_text = str_split($input);
        $columns = round($text_length / $key);
        $rows = array();
        $plain_rows = array();

        for ($i = 0; $i < $key; $i++) {
            for ($j = 0; $j < $columns; $j++) {
                $pos = $i * $columns + $j;
                $rows[$i][] = $array_text[$pos];
            }
        }

        for ($p = 0; $p < $columns; $p++) {
            $plain_rows[$p] = array_map(function ($row) use ($p) {
                return $row[$p];
            }, $rows);
        }

        $ouput = implode("", array_map(function ($row) {
            return implode("", $row);
        }, $plain_rows));

        return str_replace($padding, "", $ouput);
    }
}
