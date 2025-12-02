<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case InProgress = 'in_progress';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case OutForDelivery = 'out_for_delivery';
    case Failed = 'failed';

    public function translated(): string
    {
        return __('enums.order_status.' . $this->value);
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
