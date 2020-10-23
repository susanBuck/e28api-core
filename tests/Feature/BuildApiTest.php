<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\File;

use App\Actions\LoadResources;
use App\Actions\BuildApi;

class BuildApiTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    //private $favoriteResource;

    // public function setUp(): void
    // {
    //     $this->favoriteResource = file_get_contents('../resources/favorite-resource.json');
    //     parent::setUp();
    // }

    // public function tearDown(): void
    // {
    //     $favoriteResource = file_get_contents('../resources/favorite-resource.json');
    //     parent::setUp();
    // }

    /**
     *
     */
    public function testLoadResources()
    {
        $resources = new LoadResources();
        
        $this->assertTrue($resources->errors == []);
        $this->assertTrue($resources->resources->product !== null);
        $this->assertTrue($resources->resources->favorite !== null);

        unset($resources->resources->product->name->type);
    }

    // public function testBadResourceFile()
    // {
    // }

    // public function testNoResources()
    // {
    // }
}