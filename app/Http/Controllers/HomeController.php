<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use File;
use Str;
use Artisan;
use DB;
use Illuminate\Support\Facades\Schema;
use App\Actions\LoadDatabaseTables;
use App\Actions\LoadResources;

class HomeController extends Controller
{
    public $resources;
    
    /**
     *
     */
    public function __construct()
    {
        $resourcePath = base_path('../resources.json');
        if (File::exists($resourcePath)) {
            $resourcesJson = File::get($resourcePath);
        } else {
            dd("Resource file not found at " . $resourcePath);
        }
        
        $loadResources = new LoadResources($resourcesJson);
        $this->resources = $loadResources->resources;
    }
    
    /**
     * GET /
     */
    public function index()
    {
        $loadDatabaseTables = new LoadDatabaseTables();

        return view('index')->with([
            'database' => $loadDatabaseTables->results,
            'resources' => $this->resources,
            'allowedOrigins' => config('cors.allowed_origins'),
            'statefulDomains' => config('sanctum.stateful'),
            'sessionDomain' => config('session.domain')
        ]);
    }
}