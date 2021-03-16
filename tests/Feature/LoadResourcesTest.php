<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\File;

use App\Actions\LoadResources;
use App\Actions\BuildApi;
use Str;

class LoadResourcesTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    private $resourcesDir;

    /**
    *
    */
    public function setUp() :void
    {
        parent::setUp();

        # Where we’ll write our test JSON resources files
        $this->resourcesDir = base_path('storage/tests').'/';

        # Clear out any of the test json files we wrote in previous tests
        # (Do this in setUp instead of tearDown so we can investigate files after failed tests)
        File::delete(File::allFiles($this->resourcesDir));
    }

    /**
    * Helper function to write our test JSON resource files
    */
    private function writeJson($fileName, $content)
    {
        File::put($this->resourcesDir.$fileName.'.json', $content);
    }

    /**
     * Test the default resource files that come with the app
     */
    public function testLoadDefaultResources()
    {
        $resources = new LoadResources();
        $this->assertTrue($resources->errors == []);
        $this->assertTrue($resources->resources->product !== null);
        $this->assertTrue($resources->resources->favorite !== null);
    }

    /**
     *
     */
    public function testInvalidJson()
    {
        $invalidJson = '{
            "permission_level": 0
            "fields": {}
        }';

        $this->writeJson('product', $invalidJson);
        
        $resources = new LoadResources($this->resourcesDir);

        $this->assertTrue($resources->errors[0] == "Resource file `product.json` is not valid JSON");
    }

    /**
     *
     */
    public function testInvalidResourceName()
    {
        $json = '{
            "permission_level": 0,
            "fields": {
                "name": {
                    "type": "string",
                    "validators": [
                        "required",
                        "min:3",
                        "max:100"
                    ]
                }
            }
        }';

        $this->writeJson('product1', $json);
        
        $resources = new LoadResources($this->resourcesDir);
        
        $this->assertTrue($resources->errors[0] == "Resource name `product1` (file `product1.json`) is invalid; must only contain letters");
    }
    
    /**
     *
     */
    public function testMissingDataType()
    {
        $json = '{
            "permission_level": 0,
            "fields": {
                "name": {
                    "validators": [
                        "required",
                        "min:3",
                        "max:100"
                    ]
                }
            }
        }';

        $this->writeJson('product', $json);

        $resources = new LoadResources($this->resourcesDir);
        
        $this->assertTrue($resources->errors[0] == "Resource `product.json`, field `name` missing *type*");
    }

    /**
     *
     */
    public function testMissingValidators()
    {
        $json = '{
            "permission_level": 0,
            "fields": {
                "name": {
                    "type": "string"
                }
            }
        }';

        $this->writeJson('product', $json);

        $resources = new LoadResources($this->resourcesDir);

        $this->assertTrue(Str::contains($resources->errors[0], "Resource `product.json`, field `name` missing *validators* property."));
    }

    /**
     *
     */
    public function testUnrecognizedValidator()
    {
        $json = '{
            "permission_level": 0,
            "fields": {
                "name": {
                    "type": "string",
                    "validators": ["important"]
                }
            }
        }';

        $this->writeJson('product', $json);

        $resources = new LoadResources($this->resourcesDir);

        $this->assertTrue($resources->errors[0] == "Resource `product.json`, field `name` is using an unrecognized validator: `important`");
    }

    /**
     *
     */
    public function testValidatorMissingValue()
    {
        $json = '{
            "permission_level": 0,
            "fields": {
                "name": {
                    "type": "string",
                    "validators": ["min"]
                }
            }
        }';

        $this->writeJson('product', $json);

        $resources = new LoadResources($this->resourcesDir);

        $this->assertTrue($resources->errors[0] == "Resource `product.json`, field `name` is using the validator `min` with no value. Expecting something like `min:5`.");
    }
}