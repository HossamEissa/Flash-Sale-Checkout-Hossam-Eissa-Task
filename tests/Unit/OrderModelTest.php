<?php

use App\Models\Order;
use App\Models\Payment;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentStatusEnum;

test('can create order with correct attributes', function () {
    $order = Order::factory()->create([
        'total' => 150.50,
        'status' => OrderStatus::Pending,
        'payment_status' => PaymentStatus::OnlinePayment,
    ]);

    expect($order->total)->toBe('150.50');
    expect($order->status)->toBe(OrderStatus::Pending);
    expect($order->payment_status)->toBe(PaymentStatus::OnlinePayment);
});

test('can create hold with expiration', function () {
    $order = Order::factory()->asHold()->create();

    expect($order->is_hold)->toBeTrue();
    expect($order->expires_at)->not->toBeNull();
    expect($order->expires_at->isFuture())->toBeTrue();
});

test('has many order items relationship', function () {
    $order = Order::factory()->create();

    expect($order->orderItems())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('can convert hold to confirmed order', function () {
    $order = Order::factory()->asHold()->create([
        'status' => OrderStatus::Pending,
        'payment_status' => PaymentStatus::Pending,
    ]);

    $order->update([
        'is_hold' => false,
        'status' => OrderStatus::Confirmed,
        'payment_status' => PaymentStatus::OnlinePayment,
        'expires_at' => null,
        'consumed_at' => now(),
    ]);

    expect($order->is_hold)->toBeFalse();
    expect($order->status)->toBe(OrderStatus::Confirmed);
    expect($order->payment_status)->toBe(PaymentStatus::OnlinePayment);
    expect($order->expires_at)->toBeNull();
    expect($order->consumed_at)->not->toBeNull();
});

test('can have payments', function () {
    $order = Order::factory()->withOnlinePayment()->create();
    $payment = Payment::factory()->create(['order_id' => $order->id]);

    $order->load('payments');

    expect($order->payments)->toHaveCount(1);
    expect($order->payments->first())->toBeInstanceOf(Payment::class);
    expect($order->payments->first()->id)->toBe($payment->id);
});

test('order factory creates different statuses correctly', function () {
    $pendingOrder = Order::factory()->create();
    expect($pendingOrder->status)->toBe(OrderStatus::Pending);

    $confirmedOrder = Order::factory()->confirmed()->create();
    expect($confirmedOrder->status)->toBe(OrderStatus::Confirmed);
    expect($confirmedOrder->consumed_at)->not->toBeNull();
});

test('order factory creates different payment statuses correctly', function () {
    $pendingPaymentOrder = Order::factory()->create();
    expect($pendingPaymentOrder->payment_status)->toBe(PaymentStatus::Pending);

    $onlinePaymentOrder = Order::factory()->withOnlinePayment()->create();
    expect($onlinePaymentOrder->payment_status)->toBe(PaymentStatus::OnlinePayment);
});

test('order total is cast to decimal correctly', function () {
    $order = Order::factory()->create(['total' => 99.99]);

    expect($order->total)->toBe('99.99');
    expect(is_string($order->total))->toBeTrue();
});

test('order boolean fields work correctly', function () {
    $regularOrder = Order::factory()->create(['is_hold' => false]);
    $holdOrder = Order::factory()->create(['is_hold' => true]);

    expect($regularOrder->is_hold)->toBeFalse();
    expect($holdOrder->is_hold)->toBeTrue();
});