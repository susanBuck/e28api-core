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
    private $validators = ['required','min', 'max', 'alpha','alpha_num','alpha_dash','numeric','email','boolean','unique'];
    private $validatorsWithArguments = ['min', 'max'];
 
    /**
     *
     */
    public function __construct($resourcesJson)
    {
        # Initialize resources as an empty object
        $this->resources = new stdClass();

        # Load JSON from file
        $resources = json_decode($resourcesJson);
        if (!$resources) {
            $this->errors[] = "resources.json does not contain valid JSON";
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
                    $resourceErrors[] = "Resource `$resourceName`, field `$field` missing *validators* property. If this field does not require validation, set `validators` to an empty array []";
                } else {
                    foreach ($value->validators as $validator) {
                        $argument = null;
                        if (strstr($validator, ':')) {
                            $validator = substr($validator, 0, strpos($validator, ':'));
                            $argument = substr($validator, strpos($validator, ':'));
                        }
                        
                        if (!in_array($validator, $this->validators)) {
                            $resourceErrors[] = "Resource `$resourceName`, field `$field` is using an unrecognized validator: `$validator`";
                        }

                        if (in_array($validator, $this->validatorsWithArguments) && $argument == null) {
                            $resourceErrors[] = "Resource `$resourceName`, field `$field` is using the validator `$validator` with no value. Expecting something like `$validator:5`.";
                        }
                    }
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