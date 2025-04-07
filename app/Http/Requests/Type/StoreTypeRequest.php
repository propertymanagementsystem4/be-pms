<?php

namespace App\Http\Requests\Type;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreTypeRequest extends FormRequest
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
            'property_id' => 'required|uuid|exists:Property,id_property',
            'types' => 'required|array|min:1',
            'types.*.name' => 'required|string|distinct|unique:Type,name|max:255',
            'types.*.price_per_night' => 'required|numeric|min:0',
        ];
    }

    public function attributes()
    {
        return [
            'property_id' => 'Property ID',
            'types.*.name' => 'type name',
            'types.*.price_per_night' => 'type price per night',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'statusCode' => 422,
            'message' => 'Validation Error',
            'errors' => $validator->errors(),
        ], 422));
    }
}
