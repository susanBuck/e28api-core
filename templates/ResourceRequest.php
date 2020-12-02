<?php

namespace App\Http\Requests\GeneratedRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\GeneratedModels\Resource;

use Illuminate\Validation\Rule;

class ResourceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $resource = new Resource();

        # If this resource is not user restricted, then everyone is authorized
        if (!$resource->userRestricted) {
            return true;
        }

        # If they're not logged in, they can't be authorized
        if (!$this->user()) {
            return false;
        }

        # If the request includes an id, we need to find that resource by id
        # and check that its user_id matches the id of the requesting user
        if (!is_null($this->route('id'))) {
            return $this->user()->id == $resource->find($this->route('id'))->user_id;
        }

        # If they passed all the above tests, we can assume they're authorized
        return true;

        # Note: If this function returns false, it will return a AccessDeniedHttpException which is
        # handled in /app/Exceptions/handler.php
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        # rules #
    }

    /**
     *
     */
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'success' => false,
            'test' => 'failed-validation',
            'errors' => $validator->errors()->messages(),
        ], 200);

        throw new ValidationException($validator, $response);
    }
}
