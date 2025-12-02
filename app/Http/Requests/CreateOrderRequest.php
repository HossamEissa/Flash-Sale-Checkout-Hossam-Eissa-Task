<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'hold_id' => 'required|integer|exists:orders,id',
            'payment_status' => 'required|string|in:cash_on_delivery,online_payment,pending',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'hold_id.required' => 'Hold ID is required.',
            'hold_id.exists' => 'The specified hold does not exist.',
            'payment_status.required' => 'Payment status is required.',
            'payment_status.in' => 'Payment status must be one of: cash_on_delivery, online_payment, pending.',
        ];
    }
}
