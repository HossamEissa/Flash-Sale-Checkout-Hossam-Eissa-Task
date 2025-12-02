<?php

namespace App\Models;

use App\Traits\DynamicPagination;
use App\Traits\Filterable;
use App\Traits\Searchable;
use App\Traits\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory, DynamicPagination, Filterable, Searchable, Sortable;
    
    protected $fillable = [
        'name',
        'slug',
        'image',
        'price',
        'stock',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    protected $appends = [
        'available_stock'
    ];

####################################### Relations ###################################################

    /**
     * Relationship with order items
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

####################################### End Relations ###############################################

################################ Accessors and Mutators #############################################

    /**
     * Calculate available stock (optimized for burst traffic)
     * Uses single query with joins for performance
     */
    public function getAvailableStockAttribute()
    {
        $reserved = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.product_id', $this->id)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('orders.is_hold', true)
                      ->where('orders.status', 'pending')
                      ->where(function ($sub) {
                          $sub->whereNull('orders.expires_at')
                              ->orWhere('orders.expires_at', '>', now());
                      });
                })
                ->orWhere(function ($q) {
                    $q->where('orders.is_hold', false)
                      ->whereIn('orders.status', ['confirmed', 'in_progress', 'shipped', 'out_for_delivery']);
                });
            })
            ->sum('order_items.qty');

        return max(0, $this->stock - ($reserved ?? 0));
    }

################################ End Accessors and Mutators #########################################
}
