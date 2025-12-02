<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}