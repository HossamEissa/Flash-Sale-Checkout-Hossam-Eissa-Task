<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HoldItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'hold_id',
        'product_id',
        'quantity',
        'price_per_unit',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price_per_unit' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the hold that owns this hold item.
     */
    public function hold(): BelongsTo
    {
        return $this->belongsTo(Hold::class);
    }

    /**
     * Get the product for this hold item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the total price for this hold item.
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->quantity * $this->price_per_unit;
    }

    /**
     * Scope to get hold items for active holds only.
     */
    public function scopeActiveHolds($query)
    {
        return $query->whereHas('hold', function ($q) {
            $q->active();
        });
    }

    /**
     * Scope to get hold items for a specific product.
     */
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }
}