<?php

namespace App\Http\Controllers\GeneratedControllers;

use App\Models\GeneratedModels\Resource;

use Validator;
use Route;
use Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\GeneratedRequests\ResourceRequest;

class ResourceController extends \App\Http\Controllers\Controller
{
    private $fields = [];

    private $denied = [
            'success' => false,
            'errors' => ['access denied'],
            'test' => 'access-denied'
    ];

    /**
     * GET /resource
     */
    public function index(Request $request)
    {
        $resource = new Resource();
        $query = $resource->query();

        if (config('permissions.resource') >= 3 && !$request->user()) {
            return response($this->denied, 200);
        }

        # At permission level 5, resource is full private so only return rows for owner
        if (config('permissions.resource') == 5) {
            $query = $query->where('user_id', $request->user()->id);
        }

        $results = $query->get();

        return response([
            'success' => true,
            'errors' => null,
            'resource' => $results,
            'count' => $results->count()
        ], 200);
    }

    /**
    * GET /resource/{id}
    */
    public function show(Request $request, $id)
    {
        $resource = Resource::where('id', $id)->first();

        if (!$resource) {
            return response([
                'success' => false,
                'errors' => ['Resource not found'],
                'test' => 'resource-not-found'
            ], 200);
        }

        if (config('permissions.resource') >= 3 && !$request->user()) {
            return response($this->denied, 200);
        }

        # At permission level 5, resource is full private so only return rows for owner
        if (config('permissions.resource') == 5 && $resource->user_id != $request->user()->id) {
            return response($this->denied, 200);
        }

        return response([
            'success' => true,
            'errors' => null,
            'resource' => $resource->toArray()
        ], 200);
    }


    /**
    * GET /resource/query?key=value
    */
    public function query(Request $request)
    {
        DB::enableQueryLog();

        $queries = $request->all();

        $resource = new Resource();

        $query = $resource->query();

        if (config('permissions.resource') >= 3 && !$request->user()) {
            return response($this->denied, 200);
        }
        
        # At permission_level 5, resource is full private so user can only query for the resources they own
        if (config('permissions.resource') == 5) {
            $query = $query->where('user_id', $request->user()->id);
        }

        foreach ($queries as $key => $value) {
            $query = $query->where($key, $value);
        }

        $results = $query->get()->toArray();

        $query = DB::getQueryLog();

        return response([
            'success' => true,
            'errors' => [],
            'resource' => $results
        ], 200);
    }

    /**
    * POST /resource
    */
    public function store(ResourceRequest $request)
    {
        $resource = new Resource();

        foreach ($this->fields as $fieldName => $rule) {
            $resource->$fieldName = $request->$fieldName;
        }

        if (config('permissions.resource') >= 1 && !($request->user())) {
            return response($this->denied, 200);
        }

        # At permission_level 5, resource is full private so any new resources has to belong to logged in user
        if (config('permissions.resource') == 5) {
            $resource->user_id = $request->user()->id;
        }
        
        $resource->save();

        return response([
            'success' => true,
            'errors' => null,
            'test' => 'resource-created',
            'resource' => $resource
            ], 200);
    }

    /**
    * DELETE /resource/{id}
    */
    public function destroy(Request $request, $id)
    {
        $resource = Resource::find($id);

        if (!$resource) {
            return response([
                'success' => false,
                'test' => 'resource-not-found',
                'errors' => ['Resource ' . $id . ' not found']
            ], 200);
        }

        if (config('permissions.resource') >= 1 && !$request->user()) {
            return response($this->denied, 200);
        }

        # 2 - Resource is readable by all, but only owners can alter
        # 4 - Resource is only readable after login; only owner can alter
        # 5 - Resource is only readable/alterable by owner
        if (in_array(config('permissions.resource'), [2,4,5]) and $resource->user_id != $request->user()->id) {
            return response($this->denied, 200);
        }

        $resource->delete();

        return response([
            'success' => true,
            'errors' => null,
            'test' => 'resource-deleted'
        ], 200);
    }

    /**
    * PUT /resource/{id}
    */
    public function update(Request $request, $id)
    {
        $resource = Resource::find($id);

        if (!$resource) {
            return response([
                'success' => false,
                'test' => 'update-failed-because-resource-not-found',
                'errors' => ['Resource ' . $id . ' not found']
            ], 200);
        }

        if (config('permissions.resource') >= 1 && !$request->user()) {
            return response($this->denied, 200);
        }

        # 2 - Resource is readable by all, but only owners can alter
        # 4 - Resource is only readable after login; only owner can alter
        # 5 - Resource is only readable/alterable by owner
        if (in_array(config('permissions.resource'), [2,4,5]) and $resource->user_id != $request->user()->id) {
            return response($this->denied, 200);
        }

        # Executing Form Request validation manually so we can do the check above
        # to make sure the resource exists before validating
        # otherwise, it throws an error when checking unique fields
        app('App\Http\Requests\GeneratedRequests\ResourceRequest');

        # Do update
        foreach ($this->fields as $fieldName => $rule) {
            $resource->$fieldName = $request->$fieldName;
        }
        $resource->save();

        return response([
            'success' => true,
            'errors' => null,
            'test' => 'resource-updated',
            'message' => 'Updated resource ' . $id,
            'resource' => $resource->toArray()
        ], 200);
    }
}