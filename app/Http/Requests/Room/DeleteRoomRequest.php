<?php

namespace App\Http\Requests\Room;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DeleteRoomRequest extends FormRequest
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
            'room_ids' => 'required|array|min:1',
            'room_ids.*' => [
                'required',
                'uuid',
                'exists:rooms,id',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'room_ids' => 'Room IDs',
            'room_ids.*' => 'Room ID',
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
