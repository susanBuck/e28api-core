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
    public function __construct($resourcesDirectory = null)
    {
        if (is_null($resourcesDirectory)) {
            $resourcesDirectory = base_path('../resources/');
        }

        $this->resources = new stdClass();

        $resourceFiles = File::allFiles($resourcesDirectory);

        foreach ($resourceFiles as $resourceFile) {
            $resourceErrors = [];
            
            $resource = json_decode(File::get($resourceFile));
            $pathinfo = pathinfo($resourceFile);
            $fileNameWithExtension = $pathinfo['basename'];
            $expectedResourceName = $pathinfo['filename'];
            
            if (is_null($resource)) {
                $resourceErrors[] = "Resource file `$fileNameWithExtension` is not valid JSON";
            } else {
                if (!ctype_alpha($expectedResourceName)) {
                    $resourceErrors[] = "Resource name `$expectedResourceName` (file `$fileNameWithExtension`) is invalid; must only contain letters";
                }

                if (!property_exists($resource, 'permission_level')) {
                    $resourceErrors[] = "Resource file `$fileNameWithExtension` must have a property called `permission_level`";
                }

                if (!property_exists($resource, 'fields')) {
                    $resourceErrors[] = "Resource file `$fileNameWithExtension` must have a property called `fields`";
                } else {
                    foreach ($resource->fields as $fieldName => $field) {
                        if (!property_exists($field, 'type')) {
                            $resourceErrors[] = "Resource `$fileNameWithExtension`, field `$fieldName` missing *type*";
                        }
                        
                        if (!property_exists($field, 'validators')) {
                            $resourceErrors[] = "Resource `$fileNameWithExtension`, field `$fieldName` missing *validators* property. If this field does not require validation, set `validators` to an empty array []";
                        } else {
                            foreach ($field->validators as $validator) {
                                $argument = null;
                                if (strstr($validator, ':')) {
                                    $validator = substr($validator, 0, strpos($validator, ':'));
                                    $argument = substr($validator, strpos($validator, ':'));
                                }
                            
                                if (!in_array($validator, $this->validators)) {
                                    $resourceErrors[] = "Resource `$fileNameWithExtension`, field `$fieldName` is using an unrecognized validator: `$validator`";
                                }

                                if (in_array($validator, $this->validatorsWithArguments) && $argument == null) {
                                    $resourceErrors[] = "Resource `$fileNameWithExtension`, field `$fieldName` is using the validator `$validator` with no value. Expecting something like `$validator:5`.";
                                }
                            }
                        }
                    }
                }
            }

            # Finish
            if ($resourceErrors) {
                $this->errors = array_merge($this->errors, $resourceErrors);
            } else {
                $this->resources->$expectedResourceName = $resource;
            }
        }
    }
}
