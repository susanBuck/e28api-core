<?php

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use Str;
use File;
use Artisan;
use stdClass;
use Hash;

class LoadSeeds
{
    public $errors = [];
    public $resources;
 
    /**
     *
     */
    public function __construct($resources)
    {
        $this->seeds = new stdClass();

        $seedFiles = File::allFiles(base_path('../seeds/'));

        foreach ($seedFiles as $seedFile) {
            $seedErrors = [];
            
            $seeds = json_decode(File::get($seedFile));
            $pathinfo = pathinfo($seedFile);
            $fileNameWithExtension = $pathinfo['basename'];
            $expectedResourceName = $pathinfo['filename'];

            if ($expectedResourceName != 'user' && !property_exists($resources, $expectedResourceName)) {
                $seedErrors[] = "Found seed file `$fileNameWithExtension` but no corresponding resource `$expectedResourceName`";
            }

            if (!$seeds) {
                $seedErrors[] = "Seed file `$fileNameWithExtension` is not valid JSON";
            }

            if (!property_exists($seeds, 'seeds')) {
                foreach ($seeds as $prop => $value) {
                    $firstProp = $prop;
                    break;
                }
                $seedErrors[] = "Seed file `$fileNameWithExtension` must have a single property called `seeds`; found `".$firstProp."` instead.";
            }

            if ($seedErrors) {
                $this->errors = array_merge($this->errors, $seedErrors);
            } else {
                $this->seeds->$expectedResourceName = $seeds;
            }
        }
    }
}