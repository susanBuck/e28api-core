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
    /**
     * GET /
     */
    public function index()
    {
        $resourcePath = base_path('../resources.json');
        if (File::exists($resourcePath)) {
            $resourcesJson = File::get($resourcePath);
        } else {
            dd("Resource file not found at " . $resourcePath);
        }
        
        $loadResources = new LoadResources($resourcesJson);
        $resources = $loadResources->resources;

        $loadDatabaseTables = new LoadDatabaseTables();

        return view('index')->with([
            'database' => $loadDatabaseTables->results,
            'resources' => $resources,
            'allowedOrigins' => config('cors.allowed_origins'),
            'statefulDomains' => config('sanctum.stateful'),
            'sessionDomain' => config('session.domain'),
            'httpsCookie' => config('session.secure')
        ]);
    }

    /**
     * GET /refresh
     */
    public function refresh()
    {
        Artisan::call('e28-api:setup --refreshOnly=true');

        return response([
            'message' => 'Tables were cleared and re-seeded.',
            'success' => true
        ], 200);
    }
}