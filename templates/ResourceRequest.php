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

        if (!$resource->userRestricted) {
            return true;
        }

        if (!$this->user) {
            return false;
        }

        return $this->user()->id == $resource->find($this->route("id"))->id;

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