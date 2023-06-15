<?php

function assets(string $path)
{
    echo "./vendors/$path?t=" . time();
}

function dump($data)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    echo "<br/>";
}
