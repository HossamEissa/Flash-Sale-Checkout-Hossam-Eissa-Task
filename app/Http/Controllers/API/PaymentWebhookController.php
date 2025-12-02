<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentWebhookRequest;
use App\Services\PaymentWebhookService;
use App\Traits\ApiResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    use ApiResponder;

    public function __construct(
        private PaymentWebhookService $paymentWebhookService
    ) {}

    /**
     * Handle payment webhook
     */
    public function handle(PaymentWebhookRequest $request): JsonResponse
    {
        $validated = $request->validated();

        Log::info('Payment webhook received', ['idempotency_key' => $validated['idempotency_key'], 'order_id' => $validated['order_id'], 'event_type' => $validated['event_type'],]);

        $result = $this->paymentWebhookService->processWebhook($validated);

        // Check for actual processing errors (not just failed payments)
        if (!$result['success'] && isset($result['already_processed'])) {
            return $this->errorWrongArgs($result['message']);
        }

        return $this->respondWithItem($result, 'Webhook processed successfully');
    }
}
