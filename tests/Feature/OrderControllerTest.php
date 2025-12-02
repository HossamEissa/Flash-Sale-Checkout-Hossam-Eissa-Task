<?php

use App\Models\Order;
use App\Models\Payment;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentStatusEnum;

test('can create order from hold with online payment', function () {
    $hold = Order::factory()->asHold()->create([
        'total' => 100.00,
        'status' => OrderStatus::Pending,
        'payment_status' => PaymentStatus::Pending,
    ]);

    $response = $this->postJson('/api/orders', [
        'hold_id' => $hold->id,
        'payment_status' => 'online_payment',
    ]);

    $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'total',
                    'status',
                    'payment_status',
                    'is_hold',
                    'consumed_at',
                ]
            ]);

    // Check database changes
    $hold->refresh();
    expect($hold->is_hold)->toBeFalse();
    expect($hold->status)->toBe(OrderStatus::Confirmed);
    expect($hold->payment_status)->toBe(PaymentStatus::OnlinePayment);
    expect($hold->consumed_at)->not->toBeNull();

    // Check payment was created
    $payment = Payment::where('order_id', $hold->id)->first();
    expect($payment)->not->toBeNull();
    expect($payment->status)->toBe(PaymentStatusEnum::Pending);
    expect($payment->amount)->toBe('100.00');
});

test('can create order from hold with cash on delivery', function () {
    $hold = Order::factory()->asHold()->create([
        'total' => 75.00,
    ]);

    $response = $this->postJson('/api/orders', [
        'hold_id' => $hold->id,
        'payment_status' => 'cash_on_delivery',
    ]);

    $response->assertStatus(201);

    // Check no payment record was created for COD
    $payment = Payment::where('order_id', $hold->id)->first();
    expect($payment)->toBeNull();
});

test('cannot create order from non-existent hold', function () {
    $response = $this->postJson('/api/orders', [
        'hold_id' => 999999,
        'payment_status' => 'online_payment',
    ]);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['hold_id']);
});

test('cannot create order from expired hold', function () {
    $expiredHold = Order::factory()->asHold()->create([
        'expires_at' => now()->subHour(),
    ]);

    $response = $this->postJson('/api/orders', [
        'hold_id' => $expiredHold->id,
        'payment_status' => 'online_payment',
    ]);

    $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'message' => 'Hold not found or expired',
            ]);
});

test('can retrieve order details', function () {
    $order = Order::factory()->withOnlinePayment()->create([
        'is_hold' => false,
        'status' => OrderStatus::Confirmed,
    ]);

    $response = $this->getJson("/api/orders/{$order->id}");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'total',
                    'status',
                    'payment_status',
                    'is_hold',
                ]
            ]);

    expect($response->json('data.id'))->toBe($order->id);
    expect($response->json('data.is_hold'))->toBeFalse();
});

test('cannot retrieve non-existent order', function () {
    $response = $this->getJson('/api/orders/999999');

    $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'message' => 'Order not found',
            ]);
});

test('cannot retrieve hold as order', function () {
    $hold = Order::factory()->asHold()->create();

    $response = $this->getJson("/api/orders/{$hold->id}");

    $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'message' => 'Order not found',
            ]);
});

test('validates required fields when creating order', function () {
    $response = $this->postJson('/api/orders', []);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['hold_id', 'payment_status']);
});

test('validates payment_status enum values', function () {
    $hold = Order::factory()->asHold()->create();

    $response = $this->postJson('/api/orders', [
        'hold_id' => $hold->id,
        'payment_status' => 'invalid_status',
    ]);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_status']);
});