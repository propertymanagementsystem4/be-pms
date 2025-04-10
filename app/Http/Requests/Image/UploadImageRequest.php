<?php

namespace App\Http\Requests\Image;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadImageRequest extends FormRequest
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
            'images' => 'required|array|min:1',
            'images.*' => 'required|mimes:jpeg,png,jpg,webp|max:2048',
            'property_id' => 'nullable|uuid|exists:Property,id_property',
            'room_id' => 'nullable|uuid|exists:Room,id_room',
            'reservation_id' => 'nullable|uuid|exists:Reservation,id_reservation',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {
            if (
                !$this->filled('property_id') &&
                !$this->filled('room_id') &&
                !$this->filled('reservation_id')
            ) {
                $validator->errors()->add(
                    'relation',
                    'At least one of property_id, room_id, or reservation_id is required.'
                );
            }
        });
    }

    public function attributes()
    {
        return [
            'images.*' => 'images',
            'property_id' => 'property ID',
            'room_id' => 'room ID',
            'reservation_id' => 'reservation ID',
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
