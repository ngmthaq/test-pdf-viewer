<?php

function assets(string $path)
{
    echo "/vendors/$path?v=" . time();
}

function dump($data)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}
