<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case CashOnDelivery = 'cash_on_delivery';
    case OnlinePayment = 'online_payment';
    case Pending = 'pending';


    public function translated(): string
    {
        return __('enums.payment_status.' . $this->value);
    }

    public static function values(): array
    {
        return array_column(
            array_map(fn($c) => ['value' => $c->value], self::cases()),
            'value'
        );
    }

    public static function options(): array
    {
        $rows = array_map(fn($c) => ['value' => $c->value, 'label' => $c->translated()], self::cases());
        return array_column($rows, 'label', 'value');
    }
}