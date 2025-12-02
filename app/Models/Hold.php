<?php

namespace App\Models;

use App\Enums\HoldStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Hold extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'expires_at',
    ];

    protected $casts = [
        'status' => HoldStatus::class,
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the hold items for this hold.
     */
    public function holdItems(): HasMany
    {
        return $this->hasMany(HoldItem::class);
    }

    /**
     * Check if this hold has expired.
     */
    public function isExpired(): bool
    {
        return $this->status === HoldStatus::EXPIRED || 
               ($this->status === HoldStatus::ACTIVE && Carbon::now()->isAfter($this->expires_at));
    }

    /**
     * Check if this hold is active.
     */
    public function isActive(): bool
    {
        return $this->status === HoldStatus::ACTIVE && Carbon::now()->isBefore($this->expires_at);
    }

    /**
     * Get the total value of this hold.
     */
    public function getTotalValueAttribute(): float
    {
        return $this->holdItems->sum(function ($item) {
            return $item->quantity * $item->price_per_unit;
        });
    }

    /**
     * Get the total quantity of items in this hold.
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->holdItems->sum('quantity');
    }

    /**
     * Scope to get only active holds.
     */
    public function scopeActive($query)
    {
        return $query->where('status', HoldStatus::ACTIVE)
                    ->where('expires_at', '>', Carbon::now());
    }

    /**
     * Scope to get expired holds.
     */
    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('status', HoldStatus::EXPIRED)
              ->orWhere(function ($subQuery) {
                  $subQuery->where('status', HoldStatus::ACTIVE)
                           ->where('expires_at', '<=', Carbon::now());
              });
        });
    }
}