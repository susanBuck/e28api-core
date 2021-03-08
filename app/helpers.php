<?php

use Illuminate\Support\Str;

/*
Note: Without the app()->runningInConsole() check you'll get the following
error when running feature tests:

Argument 2 passed to Illuminate\Routing\UrlGenerator::__construct() must be an instance of Illuminate\Http\Request
*/

function isSecure()
{
    $url = app()->runningInConsole() ? '' : url()->current();
    return parse_url($url, PHP_URL_SCHEME) == 'https';
}

function getRootDomain()
{
    $url = app()->runningInConsole() ? '' : url()->current();
    return Str::after(parse_url($url, PHP_URL_HOST), '.');
}