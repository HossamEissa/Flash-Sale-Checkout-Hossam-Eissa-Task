<?php

namespace App\Http\Requests\API\Auth\ProfileTypes;

use App\Models\Company;
use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;

class MemberRequest extends ProfileTypeRequest
{
    public string $profileClass = Member::class;

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

        ];
    }
}
