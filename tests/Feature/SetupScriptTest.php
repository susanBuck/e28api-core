<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\File;

use App\Actions\LoadResources;
use App\Actions\BuildApi;
use Str;

class SetupScriptTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     *
     */
    private function runAndGetOutput($cmd = "cd ../; bash setup")
    {
        exec($cmd, $output);
        return implode($output);
    }

    /**
     *
     */
    public function testSetupRunsSuccessfully()
    {
        $output = $this->runAndGetOutput();
        
        $this->assertTrue(Str::contains($output, "Resources created:* product* favoriteSeeds run:* product (10 rows added)* favorite (2 rows added)* user (2 rows added)"));
        $this->assertNotTrue(Str::contains($output, 'error'));
    }

    /**
     *
     */
    public function testSetupDetectsWhenItsRunFromTheWrongLocation()
    {
        $output = $this->runAndGetOutput("bash ../setup");

        $this->assertTrue(Str::contains($output, "This script must be run from within the e28api directory."));
    }
}