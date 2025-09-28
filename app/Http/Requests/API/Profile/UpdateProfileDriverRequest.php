<?php

namespace App\Http\Requests\API\Profile;

use App\Http\Requests\API\Auth\ProfileTypes\ProfileTypeRequest;
use App\Models\Driver;
use App\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileDriverRequest extends ProfileTypeRequest
{
    public string $profileClass = Driver::class;

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
            'profile.car_number' => 'sometimes|string',
        ];
    }
}
