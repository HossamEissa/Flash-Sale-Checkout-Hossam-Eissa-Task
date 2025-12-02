<?php

use App\Models\Order;
use App\Models\Payment;
use App\Enums\PaymentStatusEnum;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\Cache;

test('processes successful payment webhook', function () {
    Cache::flush();
    
    $order = Order::factory()->withOnlinePayment()->create();
    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'idempotency_key' => 'webhook_test_123',
        'status' => PaymentStatusEnum::Pending,
    ]);

    $response = $this->postJson('/api/webhooks/payment', [
        'idempotency_key' => 'webhook_test_123',
        'order_id' => $order->id,
        'event_type' => 'success',
        'external_transaction_id' => 'txn_abc123',
        'amount' => 100.00,
        'currency' => 'EGP',
        'payload' => ['gateway' => 'test'],
    ]);

    $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'success',
                    'message',
                    'payment_status',
                    'order_status',
                ]
            ]);

    expect($response->json('data.success'))->toBeTrue();
    expect($response->json('data.payment_status'))->toBe('completed');
    expect($response->json('data.order_status'))->toBe('confirmed');

    // Verify database changes
    $payment->refresh();
    $order->refresh();
    
    expect($payment->status)->toBe(PaymentStatusEnum::Completed);
    expect($payment->external_transaction_id)->toBe('txn_abc123');
    expect($order->status)->toBe(OrderStatus::Confirmed);
});

test('processes failed payment webhook', function () {
    Cache::flush();
    
    $order = Order::factory()->withOnlinePayment()->create();
    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'idempotency_key' => 'webhook_fail_123',
        'status' => PaymentStatusEnum::Pending,
    ]);

    $response = $this->postJson('/api/webhooks/payment', [
        'idempotency_key' => 'webhook_fail_123',
        'order_id' => $order->id,
        'event_type' => 'failed',
        'payload' => ['error' => 'insufficient_funds'],
    ]);

    $response->assertStatus(200);

    expect($response->json('data.success'))->toBeFalse();
    expect($response->json('data.payment_status'))->toBe('failed');

    // Verify database changes
    $payment->refresh();
    expect($payment->status)->toBe(PaymentStatusEnum::Failed);
});

test('returns cached result for duplicate webhook', function () {
    Cache::flush();
    
    $order = Order::factory()->withOnlinePayment()->create();
    $payment = Payment::factory()->completed()->create([
        'order_id' => $order->id,
        'idempotency_key' => 'webhook_dup_123',
    ]);

    // Cache the result
    Cache::put('webhook_dup_123', [
        'success' => true,
        'message' => 'Payment processed successfully',
        'payment_status' => 'completed',
        'order_status' => 'confirmed',
    ]);

    $response = $this->postJson('/api/webhooks/payment', [
        'idempotency_key' => 'webhook_dup_123',
        'order_id' => $order->id,
        'event_type' => 'success',
    ]);

    $response->assertStatus(200);
    expect($response->json('data.success'))->toBeTrue();
    expect($response->json('data.message'))->toBe('Payment processed successfully');
});

test('rejects webhook for non-pending payment', function () {
    Cache::flush();
    
    $order = Order::factory()->withOnlinePayment()->create();
    $payment = Payment::factory()->completed()->create([
        'order_id' => $order->id,
        'idempotency_key' => 'webhook_completed_123',
    ]);

    $response = $this->postJson('/api/webhooks/payment', [
        'idempotency_key' => 'webhook_completed_123',
        'order_id' => $order->id,
        'event_type' => 'success',
    ]);

    $response->assertStatus(400);
    expect($response->json('status'))->toBeFalse();
    expect($response->json('message'))->toBe('Payment is not in pending state');
});

test('validates required webhook fields', function () {
    $response = $this->postJson('/api/webhooks/payment', []);

    $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'idempotency_key',
                'order_id',
                'event_type'
            ]);
});

test('validates webhook event_type values', function () {
    $response = $this->postJson('/api/webhooks/payment', [
        'idempotency_key' => 'test_123',
        'order_id' => 1,
        'event_type' => 'invalid_event',
    ]);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['event_type']);
});

test('validates order_id exists', function () {
    $response = $this->postJson('/api/webhooks/payment', [
        'idempotency_key' => 'test_123',
        'order_id' => 999999,
        'event_type' => 'success',
    ]);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id']);
});

test('handles webhook for non-existent payment gracefully', function () {
    Cache::flush();
    
    $order = Order::factory()->create();

    $response = $this->postJson('/api/webhooks/payment', [
        'idempotency_key' => 'nonexistent_payment',
        'order_id' => $order->id,
        'event_type' => 'success',
    ]);

    $response->assertStatus(400);
    expect($response->json('status'))->toBeFalse();
    expect($response->json('message'))->toBe('Payment not found or processing error');
});

test('webhook stores payload correctly', function () {
    Cache::flush();
    
    $order = Order::factory()->withOnlinePayment()->create();
    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'idempotency_key' => 'webhook_payload_123',
        'status' => PaymentStatusEnum::Pending,
    ]);

    $payload = [
        'gateway_response' => 'approved',
        'reference_number' => 'REF12345',
        'merchant_data' => 'custom_data',
    ];

    $response = $this->postJson('/api/webhooks/payment', [
        'idempotency_key' => 'webhook_payload_123',
        'order_id' => $order->id,
        'event_type' => 'success',
        'payload' => $payload,
    ]);

    $response->assertStatus(200);

    $payment->refresh();
    expect($payment->payload)->toBe($payload);
});