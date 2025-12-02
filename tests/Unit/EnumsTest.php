<?php

use App\Enums\PaymentStatusEnum;
use App\Enums\PaymentStatus;
use App\Enums\OrderStatus;

test('PaymentStatusEnum has correct values', function () {
    expect(PaymentStatusEnum::Pending->value)->toBe('pending');
    expect(PaymentStatusEnum::Processing->value)->toBe('processing');
    expect(PaymentStatusEnum::Completed->value)->toBe('completed');
    expect(PaymentStatusEnum::Failed->value)->toBe('failed');
});

test('PaymentStatusEnum values method returns all values', function () {
    $values = PaymentStatusEnum::values();
    
    expect($values)->toBeArray();
    expect($values)->toContain('pending', 'processing', 'completed', 'failed');
    expect($values)->toHaveCount(4);
});

test('PaymentStatus enum has correct values', function () {
    expect(PaymentStatus::Pending->value)->toBe('pending');
    expect(PaymentStatus::OnlinePayment->value)->toBe('online_payment');
    expect(PaymentStatus::CashOnDelivery->value)->toBe('cash_on_delivery');
    expect(PaymentStatus::Failed->value)->toBe('failed');
    expect(PaymentStatus::Refunded->value)->toBe('refunded');
});

test('PaymentStatus values method returns all values', function () {
    $values = PaymentStatus::values();
    
    expect($values)->toBeArray();
    expect($values)->toContain('pending', 'online_payment', 'cash_on_delivery', 'failed', 'refunded');
    expect($values)->toHaveCount(5);
});

test('OrderStatus enum has correct values', function () {
    expect(OrderStatus::Pending->value)->toBe('pending');
    expect(OrderStatus::Confirmed->value)->toBe('confirmed');
    expect(OrderStatus::Processing->value)->toBe('processing');
    expect(OrderStatus::Shipped->value)->toBe('shipped');
    expect(OrderStatus::Delivered->value)->toBe('delivered');
    expect(OrderStatus::Cancelled->value)->toBe('cancelled');
    expect(OrderStatus::Expired->value)->toBe('expired');
});

test('OrderStatus values method returns all values', function () {
    $values = OrderStatus::values();
    
    expect($values)->toBeArray();
    expect($values)->toContain('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'expired');
    expect($values)->toHaveCount(7);
});

test('enums can be compared correctly', function () {
    expect(PaymentStatusEnum::Pending)->toBe(PaymentStatusEnum::Pending);
    expect(PaymentStatusEnum::Pending)->not->toBe(PaymentStatusEnum::Completed);
    
    expect(PaymentStatus::OnlinePayment)->toBe(PaymentStatus::OnlinePayment);
    expect(PaymentStatus::OnlinePayment)->not->toBe(PaymentStatus::CashOnDelivery);
    
    expect(OrderStatus::Pending)->toBe(OrderStatus::Pending);
    expect(OrderStatus::Pending)->not->toBe(OrderStatus::Confirmed);
});

test('enums can be instantiated from values', function () {
    expect(PaymentStatusEnum::from('pending'))->toBe(PaymentStatusEnum::Pending);
    expect(PaymentStatusEnum::from('completed'))->toBe(PaymentStatusEnum::Completed);
    
    expect(PaymentStatus::from('online_payment'))->toBe(PaymentStatus::OnlinePayment);
    expect(PaymentStatus::from('cash_on_delivery'))->toBe(PaymentStatus::CashOnDelivery);
    
    expect(OrderStatus::from('confirmed'))->toBe(OrderStatus::Confirmed);
    expect(OrderStatus::from('cancelled'))->toBe(OrderStatus::Cancelled);
    expect(OrderStatus::from('processing'))->toBe(OrderStatus::Processing);
});