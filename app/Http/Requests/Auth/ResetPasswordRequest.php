<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
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
            'email' => 'required_without:phone_number|email|exists:users,email',
            'phone_number' => 'required_without:email|string',
            'code' => 'required|digits:4',
            'password' => ['required', 'string', 'confirmed', 'min:8' , Password::min(8)->letters()->symbols()->numbers()->mixedCase()],
        ];
    }
}
