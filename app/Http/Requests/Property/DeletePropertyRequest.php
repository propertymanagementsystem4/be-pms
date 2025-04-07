<?php

namespace App\Http\Requests\Property;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DeletePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'property_ids' => 'required|array|min:1',
            'property_ids.*' => [
                'required',
                'uuid',
                'exists:Property,id_property',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'property_ids' => 'Property IDs',
            'property_ids.*' => 'Property ID',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'statusCode' => 422,
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 422));
    }
}
