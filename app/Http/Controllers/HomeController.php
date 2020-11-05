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
        # Gather and validate resource JSON file
        $resourcePath = base_path('../resources.json');

        # Load resources.json file
        $resourcesJson = File::get($resourcePath);

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
            'allowedOrigins' => config('cors.allowed_origins')
        ]);
    }
}