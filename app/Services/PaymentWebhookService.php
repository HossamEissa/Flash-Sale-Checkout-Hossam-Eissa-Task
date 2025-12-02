<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentStatusEnum;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentWebhookService
{
    public function processWebhook(array $data): array
    {
        DB::beginTransaction();
        try {
            $payment = Payment::where('idempotency_key', $data['idempotency_key'])
                ->lockForUpdate()
                ->firstOrFail();

            if (Cache::has($data['idempotency_key'])) {
                return Cache::get($data['idempotency_key']);
            }

            if ($payment->status !== PaymentStatusEnum::Pending) {
                return [
                    'success' => false,
                    'message' => 'Payment is not in pending state',
                    'already_processed' => true,
                ];
            }

            $isSuccess = $data['event_type'] === 'success';
            $newStatus = $isSuccess ? PaymentStatusEnum::Completed : PaymentStatusEnum::Failed;

            $payment->update([
                'idempotency_key' => $data['idempotency_key'],
                'status' => $newStatus,
                'external_transaction_id' => $data['external_transaction_id'] ?? null,
                'payload' => $data['payload'] ?? [],
            ]);

            $order = $payment->order;
            if ($isSuccess) {
                $order->update(['status' => OrderStatus::Confirmed]);
            }

            $result = [
                'success' => $isSuccess,
                'message' => $isSuccess ? 'Payment processed successfully' : 'Payment failed, order cancelled',
                'payment_status' => $payment->status->value,
                'order_status' => $order->status->value,
            ];

            Cache::put($data['idempotency_key'], $result);

            DB::commit();
            return $result;

        } catch (Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Payment not found or processing error',
                'already_processed' => false,
            ];
        }
    }

}
