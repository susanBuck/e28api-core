<?php

use Illuminate\Support\Str;

function isSecure()
{
    $url = url()->current();
    return parse_url($url, PHP_URL_SCHEME) == 'https';
}

function getRootDomain()
{
    $url = url()->current();
    return Str::after(parse_url($url, PHP_URL_HOST), '.');
}