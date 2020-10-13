<?php

namespace App\Http\Controllers\GeneratedControllers;

use App\Models\GeneratedModels\Resource;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResourceController extends \App\Http\Controllers\Controller
{
    private $fields = [];

    /**
     * GET /api/resource
     */
    public function index()
    {
        return Resource::all();
    }

    /**
    * GET /api/resource/{id}
    */
    public function show($id)
    {
        $resource = Resource::where('id', $id)->first();

        if (!$resource) {
            return response([
            'error' => ['Resource not found']
        ], 404);
        } else {
            return response($resource->toArray(), 200); # 200 Ok
        }
    }

    /**
    * POST /api/resource
    */
    public function store(Request $request)
    {
        $data = $request->only(array_keys($this->fields));

        $validator = Validator::make($request->all(), $this->fields);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()], 400); # Bad request
        }

        $resource = new Resource();
        foreach ($this->fields as $fieldName => $rule) {
            $resource->$fieldName = $data[$fieldName];
        }
        $resource->save();

        return response($data, 201); # 201 Resource created
    }

    /**
    * DELETE /api/resource/{id}
    */
    public function destroy(Request $request, $id)
    {
        $resource = Resource::find($id);
        
        if (!$resource) {
            return response(['error' => 'Resource ' . $id . ' not found'], 400);
        }
 
        $resource->delete();

        return response(['success' => 'Deleted resource '.$id], 200); # 200 Ok
    }

    /**
    * PUT /api/resource/{id}
    */
    public function update(Request $request, $id)
    {
        $id = $request->id;
        $resource = Resource::find($id);
        
        if (!$resource) {
            return response(['error' => 'Resource ' . $id . ' not found'], 400);
        }

        $data = $request->only(array_keys($this->fields));
        $validator = Validator::make($request->all(), $this->fields);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()], 400); # Bad request
        }

        $resource = new Resource();
        foreach ($this->fields as $fieldName => $rule) {
            $resource->$fieldName = $data[$fieldName];
        }
        $resource->save();

        return response([
            'success' => 'Updated resource ' . $id,
            'resource' => $resource->toArray()
        ], 200); # 200 Ok
    }

    /**
    * GET /api/resource/query?key=value
    */
    public function query(Request $request)
    {
        DB::enableQueryLog();

        $queries = $request->all();

        $query = Resource::query();

        foreach ($queries as $key => $value) {
            $query = $query->where($key, $value);
        }

        $results = $query->get()->toArray();

        $query = DB::getQueryLog();

        return response([
            'query' => $query,
            'results' => $results
        ], 200); # 200 Ok
    }
}