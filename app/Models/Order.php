<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_hold',
        'status',
        'payment_status',
        'total',
        'expires_at',
        'consumed_at',
    ];

    protected $casts = [
        'is_hold' => 'boolean',
        'status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'total' => 'decimal:2',
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
    ];

####################################### Relations ###################################################
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
####################################### End Relations ###################################################

################################ Accessors and Mutators #############################################
    public function isActive(): bool
    {
        if ($this->is_hold) {
            return $this->expires_at === null || $this->expires_at->isFuture();
        }

        return in_array($this->status->value, ['confirmed', 'in_progress', 'shipped', 'out_for_delivery']);
    }

    public function scopeActiveHolds($query)
    {
        return $query->where('is_hold', true)
                    ->where('status', OrderStatus::Pending)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }


    public function scopeConfirmedOrders($query)
    {
        return $query->where('is_hold', false)
                    ->whereIn('status', [
                        OrderStatus::Confirmed,
                        OrderStatus::InProgress,
                        OrderStatus::Shipped,
                        OrderStatus::OutForDelivery
                    ]);
    }
################################ End Accessors and Mutators #############################################
}
