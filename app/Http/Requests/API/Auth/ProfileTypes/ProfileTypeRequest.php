<?php

namespace App\Http\Requests\API\Auth\ProfileTypes;

use Illuminate\Foundation\Http\FormRequest;

abstract class ProfileTypeRequest
{
    public string $profileClass;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    abstract public function rules(): array;
}
