<?php

function assets(string $path)
{
    echo "/public/$path?v=" . time();
}
