<?php

namespace App\Http\Requests\API\Profile;

use App\Http\Requests\API\Auth\ProfileTypes\ProfileTypeRequest;
use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileCompanyRequest extends ProfileTypeRequest
{
    public string $profileClass = Company::class;

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
            'profile.tax_card' => 'sometimes|mimes:jpg,jpeg,png,gif,svg,webp,heif,heic,bmp,tiff,pdf',
            'profile.tax_number' => 'sometimes|string',
        ];
    }
}
