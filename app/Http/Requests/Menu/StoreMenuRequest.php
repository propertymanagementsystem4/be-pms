<?php

namespace App\Http\Requests\Menu;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreMenuRequest extends FormRequest
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
            'role_id' => 'required|exists:Role,id_role',
            'name' => 'required|string|max:255|unique:Menu,name',
            'icon' => 'nullable|string|max:50',
            // 'order' => 'nullable|integer|min:0',
            'submenus' => 'nullable|array',
            'submenus.*.name' => 'required_with:submenus|string|max:255',
            // 'submenus.*.order' => 'nullable|integer|min:0',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'menu name',
            'submenus' => 'submenus',
            'submenus.*.name' => 'submenu name'
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
