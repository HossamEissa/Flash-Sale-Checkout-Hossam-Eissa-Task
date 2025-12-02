<?php

use App\Models\Payment;
use App\Models\Order;
use App\Enums\PaymentStatusEnum;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;

test('can create a payment with correct attributes', function () {
    $order = Order::factory()->withOnlinePayment()->create(['total' => 100.00]);
    
    $payment = Payment::factory()->create([
        'idempotency_key' => 'test_key_123',
        'order_id' => $order->id,
        'amount' => 100.00,
    ]);

    expect($payment->idempotency_key)->toBe('test_key_123');
    expect($payment->order_id)->toBe($order->id);
    expect($payment->status)->toBe(PaymentStatusEnum::Pending);
    expect($payment->amount)->toBe('100.00');
    expect($payment->currency)->toBe('EGP');
});

test('payment belongs to an order', function () {
    $order = Order::factory()->withOnlinePayment()->create();
    $payment = Payment::factory()->create(['order_id' => $order->id]);

    expect($payment->order)->toBeInstanceOf(Order::class);
    expect($payment->order->id)->toBe($order->id);
});

test('can mark payment as processing', function () {
    $payment = Payment::factory()->create();

    $payment->markAsProcessing();
    
    expect($payment->fresh()->status)->toBe(PaymentStatusEnum::Processing);
});

test('can mark payment as completed', function () {
    $payment = Payment::factory()->create();

    $payment->markAsCompleted();
    
    expect($payment->fresh()->status)->toBe(PaymentStatusEnum::Completed);
});

test('can mark payment as failed with error message', function () {
    $payment = Payment::factory()->create();

    $payment->markAsFailed('Test error message');
    
    expect($payment->fresh()->status)->toBe(PaymentStatusEnum::Failed);
    expect($payment->fresh()->error_message)->toBe('Test error message');
});

test('has correct status check methods', function () {
    $payment = Payment::factory()->create();

    expect($payment->isPending())->toBeTrue();
    expect($payment->isProcessing())->toBeFalse();
    expect($payment->isCompleted())->toBeFalse();
    expect($payment->isFailed())->toBeFalse();

    $payment->update(['status' => PaymentStatusEnum::Completed]);

    expect($payment->isPending())->toBeFalse();
    expect($payment->isProcessing())->toBeFalse();
    expect($payment->isCompleted())->toBeTrue();
    expect($payment->isFailed())->toBeFalse();
});

test('can increment retry count', function () {
    $payment = Payment::factory()->create();

    expect($payment->retry_count)->toBe(0);
    
    $payment->incrementRetryCount();
    
    expect($payment->fresh()->retry_count)->toBe(1);
});

test('payment factory creates valid payment', function () {
    $payment = Payment::factory()->create();
    
    expect($payment->idempotency_key)->toBeString();
    expect($payment->status)->toBe(PaymentStatusEnum::Pending);
    expect($payment->currency)->toBe('EGP');
    expect($payment->retry_count)->toBe(0);
});

test('can create completed payment using factory', function () {
    $payment = Payment::factory()->completed()->create();
    
    expect($payment->status)->toBe(PaymentStatusEnum::Completed);
});

test('can create failed payment using factory', function () {
    $payment = Payment::factory()->failed()->create();
    
    expect($payment->status)->toBe(PaymentStatusEnum::Failed);
    expect($payment->error_message)->toBeString();
});