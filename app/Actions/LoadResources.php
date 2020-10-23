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
    public $errors = [];
    public $resources;
 
    /**
     *
     */
    public function __construct()
    {
        # Initialize resources as an empty object
        $this->resources = new stdClass();

        # Load resources.json file
        $resourcesJson = File::get(base_path('../resources.json'));
        if (!$resourcesJson) {
            $this->errors[] = "Resource file not found at " . base_path('../resources.json');
            return;
        }

        # Load JSON from file
        $resources = json_decode($resourcesJson);
        if (!$resources) {
            $this->errors[] = "Resource file $resourcePath is not valid JSON";
            return;
        }

        foreach ($resources as $resourceName => $fields) {
            $resourceErrors = [];

            if (!ctype_alpha($resourceName)) {
                $resourceErrors[] = "Resource name `$resourceName` is invalid; must only contain letters";
            }

            foreach ($fields as $field => $value) {
                if (!property_exists($value, 'type')) {
                    $resourceErrors[] = "Resource `$resourceName`, field `$field` missing *type*";
                }

                if (!property_exists($value, 'validators')) {
                    $resourceErrors[] = "Resource `$resourceName`, field `$field` missing *validators*";
                }
            }
            
            if ($resourceErrors) {
                $this->errors = array_merge($this->errors, $resourceErrors);
            } else {
                $this->resources->$resourceName = $fields;
            }
        }
    }
}