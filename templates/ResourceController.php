<?php

namespace App\Http\Controllers\GeneratedControllers;

use App\Models\GeneratedModels\Resource;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\GeneratedRequests\ResourceRequest;

class ResourceController extends \App\Http\Controllers\Controller
{
    private $fields = [];

    /**
     * GET /resource
     */
    public function index(Request $request)
    {
        $resource = new Resource();
        $query = $resource->query();

        # Limit query to only resources beloning to the currently logged in user
        if ($resource->userRestricted) {
            $query = $query->where('user_id', $request->user()->id);
        }

        return response([
            'success' => true,
            'errors' => null,
            'resource' => $query->get(),
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

        if ($resource->userRestricted and $resource->user_id != $request->user()->id) {
            return response([
                'success' => false,
                'errors' => ['Data access denied'],
                'test' => 'data-access-denied'
            ], 200);
        }
        
        return response([
            'success' => true,
            'errors' => null,
            'resource' => $resource->toArray()
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

        if ($resource->userRestricted) {
            $resource->user_id = $request->user()->id;
        }
        
        $resource->save();

        return response([
            'success' => true,
            'errors' => null,
            'test' => 'resource-created',
            'resource' => $resource
            ], 201); # 201 Resource created
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

        if ($resource->userRestricted and $resource->user_id != $request->user()->id) {
            return response([
                'success' => false,
                'errors' => ['Data access denied'],
                'test' => 'data-access-denied'
            ], 200);
        }

        $resource->delete();

        return response([
            'success' => true,
            'errors' => null,
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
            'test' => 'update-completed',
            'message' => 'Updated resource ' . $id,
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
        
        if ($resource->userRestricted) {
            $query = $query->where('user_id', $request->user()->id);
        }
        
        foreach ($queries as $key => $value) {
            $query = $query->where($key, $value);
        }

        $results = $query->get()->toArray();

        $query = DB::getQueryLog();

        return response([
            'results' => $results
        ], 200); # 200 Ok
    }
}