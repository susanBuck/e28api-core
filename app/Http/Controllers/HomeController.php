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
        $loadResources = new LoadResources();
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