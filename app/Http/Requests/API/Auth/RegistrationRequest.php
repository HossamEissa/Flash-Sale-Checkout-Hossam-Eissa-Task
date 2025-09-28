<?php

namespace App\Http\Requests\API\Auth;

use App\Http\Requests\API\Auth\ProfileTypes\DriverRequest;
use App\Http\Requests\API\Auth\ProfileTypes\ProfileTypeRequest;
use App\Http\Requests\API\Auth\ProfileTypes\CompanyRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegistrationRequest extends FormRequest
{
    protected ProfileTypeRequest $profileTypeRequest;

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
        return array_merge($this->profileTypeRequest->rules(), [
            'avatar' => ['nullable', 'image'],
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'country_code' => ['required_with:phone_number', 'string'],
            'country_calling_code' => ['required_with:phone_number', 'string'],
            'phone_number' => ['nullable', 'string', 'unique:users,phone_number', "phone:{$this->input('country_code')}"],
            'profile_type' => ['required', 'string'],
            'status' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', 'min:8', Password::min(8)->letters()->symbols()->numbers()->mixedCase()->uncompromised()],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function prepareForValidation()
    {
        $this->profileTypeRequest = match ($this->input('profile_type')) {
            'company' => new CompanyRequest(),
            default => throw new \Exception('Invalid profile type'),
        };


        $this->merge([
            'profile_type' => $this->profileTypeRequest->profileClass,
            'status' => 'active',
        ]);
    }
}
