<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateReservationRequest extends FormRequest
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
            'property_code' => 'required|string|exists:Property,property_code',
            'customerEmail' => 'nullable|email|exists:User,email',
            'checkInDate' => 'required|date_format:Y-m-d',
            'checkOutDate' => 'required|date_format:Y-m-d|after:checkInDate',
            'totalGuest' => 'required|integer|min:1',
            'rooms' => 'required|array|min:1',
            'rooms.*.room_code' => 'required|string|exists:Room,room_code',
            'facilities' => 'nullable|array',
            'facilities.*.facility_code' => 'nullable|string|exists:Facility,facility_code',
            'customerData' => 'required|array|min:1',
            'customerData.*.fullname' => 'required|string',
            'customerData.*.email' => 'nullable|email',
            'customerData.*.nik' => 'nullable|string',
            'customerData.*.birth_date' => 'nullable|date_format:Y-m-d',
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
