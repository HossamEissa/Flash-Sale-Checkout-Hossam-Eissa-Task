<?php

use App\Services\PaymentWebhookService;
use App\Models\Payment;
use App\Models\Order;
use App\Enums\PaymentStatusEnum;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\Cache;

test('processes successful payment webhook correctly', function () {
    Cache::flush();
    
    $order = Order::factory()->withOnlinePayment()->create();
    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'idempotency_key' => 'webhook_123',
        'status' => PaymentStatusEnum::Pending,
    ]);

    $service = new PaymentWebhookService();
    $webhookData = [
        'idempotency_key' => 'webhook_123',
        'event_type' => 'success',
        'external_transaction_id' => 'txn_456',
        'payload' => ['gateway' => 'test'],
    ];

    $result = $service->processWebhook($webhookData);

    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('Payment processed successfully');
    expect($result['payment_status'])->toBe('completed');
    expect($result['order_status'])->toBe('confirmed');

    // Check database updates
    $payment->refresh();
    $order->refresh();
    
    expect($payment->status)->toBe(PaymentStatusEnum::Completed);
    expect($payment->external_transaction_id)->toBe('txn_456');
    expect($order->status)->toBe(OrderStatus::Confirmed);
});

test('processes failed payment webhook correctly', function () {
    Cache::flush();
    
    $order = Order::factory()->withOnlinePayment()->create();
    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'idempotency_key' => 'webhook_123',
        'status' => PaymentStatusEnum::Pending,
    ]);

    $service = new PaymentWebhookService();
    $webhookData = [
        'idempotency_key' => 'webhook_123',
        'event_type' => 'failed',
        'payload' => ['error' => 'insufficient_funds'],
    ];

    $result = $service->processWebhook($webhookData);

    expect($result['success'])->toBeFalse();
    expect($result['message'])->toBe('Payment failed, order cancelled');
    expect($result['payment_status'])->toBe('failed');

    // Check database updates
    $payment->refresh();
    
    expect($payment->status)->toBe(PaymentStatusEnum::Failed);
});

test('returns cached result for duplicate webhook', function () {
    Cache::flush();
    
    $order = Order::factory()->withOnlinePayment()->create();
    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'idempotency_key' => 'webhook_123',
        'status' => PaymentStatusEnum::Completed,
    ]);

    // Cache a result
    $cachedResult = [
        'success' => true,
        'message' => 'Payment processed successfully',
        'payment_status' => 'completed',
        'order_status' => 'confirmed',
    ];
    Cache::put('webhook_123', $cachedResult);

    $service = new PaymentWebhookService();
    $webhookData = [
        'idempotency_key' => 'webhook_123',
        'event_type' => 'success',
    ];

    $result = $service->processWebhook($webhookData);

    expect($result)->toBe($cachedResult);
});

test('rejects webhook for non-pending payment', function () {
    Cache::flush();
    
    $order = Order::factory()->withOnlinePayment()->create();
    $payment = Payment::factory()->completed()->create([
        'order_id' => $order->id,
        'idempotency_key' => 'webhook_123',
    ]);

    $service = new PaymentWebhookService();
    $webhookData = [
        'idempotency_key' => 'webhook_123',
        'event_type' => 'success',
    ];

    $result = $service->processWebhook($webhookData);

    expect($result['success'])->toBeFalse();
    expect($result['message'])->toBe('Payment is not in pending state');
    expect($result['already_processed'])->toBeTrue();
});

test('handles payment not found gracefully', function () {
    Cache::flush();
    
    $service = new PaymentWebhookService();
    $webhookData = [
        'idempotency_key' => 'nonexistent_webhook',
        'event_type' => 'success',
    ];

    $result = $service->processWebhook($webhookData);

    expect($result['success'])->toBeFalse();
    expect($result['message'])->toBe('Payment not found or processing error');
    expect($result['already_processed'])->toBeFalse();
});

test('caches successful webhook result', function () {
    Cache::flush();
    
    $order = Order::factory()->withOnlinePayment()->create();
    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'idempotency_key' => 'webhook_123',
        'status' => PaymentStatusEnum::Pending,
    ]);

    $service = new PaymentWebhookService();
    $webhookData = [
        'idempotency_key' => 'webhook_123',
        'event_type' => 'success',
    ];

    $service->processWebhook($webhookData);

    expect(Cache::has('webhook_123'))->toBeTrue();
    
    $cachedResult = Cache::get('webhook_123');
    expect($cachedResult['success'])->toBeTrue();
    expect($cachedResult['payment_status'])->toBe('completed');
});

test('updates payment with webhook payload', function () {
    Cache::flush();
    
    $order = Order::factory()->withOnlinePayment()->create();
    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'idempotency_key' => 'webhook_123',
        'status' => PaymentStatusEnum::Pending,
    ]);

    $service = new PaymentWebhookService();
    $webhookData = [
        'idempotency_key' => 'webhook_123',
        'event_type' => 'success',
        'external_transaction_id' => 'txn_789',
        'payload' => [
            'gateway_response' => 'approved',
            'reference_number' => 'REF123',
        ],
    ];

    $service->processWebhook($webhookData);

    $payment->refresh();
    
    expect($payment->external_transaction_id)->toBe('txn_789');
    expect($payment->payload)->toBe([
        'gateway_response' => 'approved',
        'reference_number' => 'REF123',
    ]);
});