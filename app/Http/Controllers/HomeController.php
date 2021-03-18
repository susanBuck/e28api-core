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
    public function index(Request $request)
    {
        $loadResources = new LoadResources();
        $resources = $loadResources->resources;

        $loadDatabaseTables = new LoadDatabaseTables();

        $permission_levels = [
            'Fully public: Resource is readable and alterable by all',
            'Resource is readable by all, but only users can alter',
            'Resource is readable by all, but only owners can alter',
            'Resource is only readable after login; users can alter',
            'Resource is only readable after login; only owner can alter',
            'Fully private: Resource is only readable/alterable by owner'
        ];

        return view('index')->with([
            'permission_levels' => $permission_levels,
            'database' => $loadDatabaseTables->results,
            'resources' => $resources,
            'allowedOrigins' => config('cors.allowed_origins'),
            'statefulDomains' => config('sanctum.stateful'),
        ]);
    }

    /**
     * GET /refresh
     */
    public function refresh()
    {
        Artisan::call('e28api:setup --refreshOnly=true');

        return response([
            'message' => 'Tables were cleared and re-seeded.',
            'success' => true
        ], 200);
    }
}