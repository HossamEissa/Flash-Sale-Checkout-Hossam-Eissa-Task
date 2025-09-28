<?php

namespace App\Http\Requests\API\Profile;

use App\Http\Requests\API\Auth\ProfileTypes\ProfileTypeRequest;
use App\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileSupplierRequest extends ProfileTypeRequest
{
    public string $profileClass = Supplier::class;

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
            'profile.supplier_category_id' => 'sometimes|exists:supplier_categories,id',
            'profile.address' => 'sometimes|string',
            'profile.cr_commercial_registration' => 'sometimes|mimes:jpg,jpeg,png,gif,svg,webp,heif,heic,bmp,tiff,pdf',
            'profile.vat_certificate' => 'sometimes|mimes:jpg,jpeg,png,gif,svg,webp,heif,heic,bmp,tiff,pdf',
            'profile.tax_id' => 'sometimes|string',
            'profile.sales_name' => 'sometimes|string',
            'profile.sales_email_address' => 'sometimes|email',
            'profile.sales_country_code' => 'sometimes|string',
            'profile.sales_country_calling_code' => 'sometimes|string',
            'profile.sales_phone_number' => 'sometimes|string',
            'profile.finance_name' => 'sometimes|string',
            'profile.finance_email_address' => 'sometimes|email',
            'profile.finance_country_code' => 'sometimes|string',
            'profile.finance_country_calling_code' => 'sometimes|string',
            'profile.finance_phone_number' => 'sometimes|string',
        ];
    }
}
