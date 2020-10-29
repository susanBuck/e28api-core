<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * When there's a Laravel exception the output is super long
     * This method is used just to dump the first chunk of the error
     */
    public function shortDump($r)
    {
        var_dump(substr($r->content(), 0, 500));
    }
}