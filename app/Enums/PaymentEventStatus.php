<?php

namespace App\Enums;

enum PaymentEventStatus: string
{
    case Success = 'success';
    case Failure = 'failure';

    public function translated(): string
    {
        return __('enums.payment_event_status.' . $this->value);
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
