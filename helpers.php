<?php

function assets(string $path)
{
    echo "/public/$path?v=" . time();
}

function dump($data)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}
