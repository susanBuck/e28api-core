<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\File;

use App\Actions\LoadResources;
use App\Actions\BuildApi;
use Str;

class BuildApiTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     *
     */
    public function testLoadResources()
    {
        $resources = new LoadResources(File::get(base_path('../resources.json')));
        
        $this->assertTrue($resources->errors == []);
        $this->assertTrue($resources->resources->product !== null);
        $this->assertTrue($resources->resources->favorite !== null);
    }

    /**
     *
     */
    public function testInvalidResourceName()
    {
        $json = '{
            "product1": {
                "name": {
                    "type": "string",
                    "validators": []
                }
            }
        }';

        $resources = new LoadResources($json);
        
        $this->assertTrue($resources->errors[0] == "Resource name `product1` is invalid; must only contain letters");
    }
    
    /**
     *
     */
    public function testMissingDataType()
    {
        $json = '{
            "product": {
                "name": {
                    "validators": [
                        "required"
                    ]
                }
            }
        }';

        $resources = new LoadResources($json);

        $this->assertTrue($resources->errors[0] == "Resource `product`, field `name` missing *type*");
    }

    /**
     *
     */
    public function testMissingValidators()
    {
        $json = '{
            "product": {
                "name": {
                    "type": "string"
                }
            }
        }';

        $resources = new LoadResources($json);
        $this->assertTrue(Str::contains($resources->errors[0], "Resource `product`, field `name` missing *validators* property."));
    }

    /**
     *
     */
    public function testUnrecognizedValidator()
    {
        $json = '{
            "product": {
                "name": {
                    "type": "string",
                    "validators": [
                        "important"
                    ]
                }
            }
        }';

        $resources = new LoadResources($json);
        $this->assertTrue($resources->errors[0] == "Resource `product`, field `name` is using an unrecognized validator: `important`");
    }

    /**
     *
     */
    public function testValidatorMissingValue()
    {
        $json = '{
            "product": {
                "name": {
                    "type": "string",
                    "validators": [
                        "min"
                    ]
                }
            }
        }';

        $resources = new LoadResources($json);
        $this->assertTrue($resources->errors[0] == "Resource `product`, field `name` is using the validator `min` with no value. Expecting something like `min:5`.");
    }
}