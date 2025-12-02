<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentWebhookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Webhook authorization handled separately
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'idempotency_key' => 'required|string|max:255',
            'order_id' => 'required|integer|exists:orders,id',
            'event_type' => 'required|string|in:payment_success,payment_failed,payment_cancelled',
            'external_transaction_id' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'payload' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'idempotency_key.required' => 'Idempotency key is required for webhook safety.',
            'order_id.required' => 'Order ID is required.',
            'order_id.exists' => 'The specified order does not exist.',
            'event_type.required' => 'Event type is required.',
            'event_type.in' => 'Event type must be one of: payment_success, payment_failed, payment_cancelled.',
            'currency.size' => 'Currency must be a 3-character ISO code.',
        ];
    }
}
