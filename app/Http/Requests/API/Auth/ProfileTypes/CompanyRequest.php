<?php

namespace App\Http\Requests\API\Auth\ProfileTypes;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends ProfileTypeRequest
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
            'profile.supplier_category_id' => 'required|exists:supplier_categories,id',
            'profile.address' => 'required|string',
            'profile.cr_commercial_registration' => 'required|mimes:jpg,jpeg,png,gif,svg,webp,heif,heic,bmp,tiff,pdf',
            'profile.vat_certificate' => 'required|mimes:jpg,jpeg,png,gif,svg,webp,heif,heic,bmp,tiff,pdf',
            'profile.tax_id' => 'required|string',
            'profile.sales_name' => 'required|string',
            'profile.sales_email_address' => 'required|email',
            'profile.sales_country_code' => 'required|string',
            'profile.sales_country_calling_code' => 'required|string',
            'profile.sales_phone_number' => 'required|string',
            'profile.finance_name' => 'required|string',
            'profile.finance_email_address' => 'required|email',
            'profile.finance_country_code' => 'required|string',
            'profile.finance_country_calling_code' => 'required|string',
            'profile.finance_phone_number' => 'required|string',
        ];
    }
}
