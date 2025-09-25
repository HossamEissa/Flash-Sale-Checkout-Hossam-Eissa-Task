<?php

namespace App\Http\Requests\API\Profile;

use App\Http\Requests\API\Auth\ProfileTypes\ProfileTypeRequest;
use App\Http\Requests\API\SuperAdmin\Drivers\UpdateDriverRequest;
use App\Http\Requests\API\SuperAdmin\Suppliers\UpdateSupplierRequest;
use App\Models\Driver;
use App\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
            'avatar' => ['sometimes', 'image'],
            'name' => ['sometimes', 'string'],
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($this->user()->id)
            ],
            'country_code' => ['required_with:phone_number', 'string'],
            'country_calling_code' => ['required_with:phone_number', 'string'],
            'phone_number' => [
                'sometimes',
                'string',
                Rule::unique('users', 'phone_number')->ignore($this->user()->id),
                "phone:{$this->input('country_code')}"],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function prepareForValidation()
    {
        $this->profileTypeRequest = match ($this->user()->profile_type) {
            Company::class => new UpdateProfileSupplierRequest(),
            default => throw new \Exception('Invalid profile type'),
        };
    }
}
