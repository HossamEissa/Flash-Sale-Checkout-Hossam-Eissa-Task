<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case OnlinePayment = 'online_payment';
    case CashOnDelivery = 'cash_on_delivery';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}