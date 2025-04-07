<?php

namespace App\Http\Requests\Property;

use App\Http\Requests\TypeRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePropertyRequest extends FormRequest
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
        $typeRules = (new TypeRequest())->rules();

        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'types' => 'required|array|min:1',
            // 'types.*.property_id' => $typeRules['property_id'],
            'types.*.name' => 'required|string|distinct|max:255',
            'types.*.price_per_night' => $typeRules['price_per_night'],
        ];
    }

    public function attributes()
    {
        return [
            'total_rooms' => 'total rooms',
            // 'types.*.property_id' => 'type property id',
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
