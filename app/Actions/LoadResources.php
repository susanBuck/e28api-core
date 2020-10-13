<?php

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use Str;
use File;
use Artisan;
use stdClass;
use Hash;

class LoadResources
{
    public $results;
    private $routes;
    private $resourceName;
    private $resourceNameLower;
    private $resourceNameStudly;

    /**
     *
     */
    public function __construct()
    {
        $resourceFiles = File::allFiles(base_path('../resources/'));

        $this->resources =  new \stdClass();

        foreach ($resourceFiles as $file) {
            $file = pathinfo($file);
            
            $resourcePath = $file['dirname'] . '/' . $file['basename'];
            
            $resourceText = File::get($resourcePath);
            
            $resourceObj = json_decode($resourceText);
            
            if ($resourceObj) {
                foreach ($resourceObj as $resourceName => $values) {
                    $this->resources->$resourceName = $values;
                    break;
                }
            }
        }
    }
}