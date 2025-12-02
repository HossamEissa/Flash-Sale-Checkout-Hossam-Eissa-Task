<?php

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use App\Traits\DynamicPagination;
use App\Traits\Filterable;
use App\Traits\Searchable;
use App\Traits\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Payment extends Model
{
    use HasFactory, DynamicPagination, Filterable, Searchable, Sortable;

    protected $fillable = [
        'idempotency_key',
        'order_id',
        'event_type',
        'status',
        'payload',
        'external_transaction_id',
        'amount',
        'currency',
        'error_message',
        'retry_count',
    ];

    protected $casts = [
        'status' => PaymentStatusEnum::class,
        'payload' => 'array',
        'amount' => 'decimal:2',
        'retry_count' => 'integer',
    ];

####################################### Relations ###################################################

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

####################################### End Relations ###############################################

################################ Accessors and Mutators #############################################


    public function markAsProcessing(): void
    {
        $this->update(['status' => PaymentStatusEnum::Processing]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => PaymentStatusEnum::Completed,
            'processed_at' => Carbon::now(),
        ]);
    }

    public function markAsFailed(string $errorMessage = null): void
    {
        $this->update([
            'status' => PaymentStatusEnum::Failed,
            'error_message' => $errorMessage,
            'processed_at' => Carbon::now(),
        ]);
    }

    public function incrementRetryCount(): void
    {
        $this->increment('retry_count');
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatusEnum::Pending;
    }

    public function isProcessing(): bool
    {
        return $this->status === PaymentStatusEnum::Processing;
    }


    public function isCompleted(): bool
    {
        return $this->status === PaymentStatusEnum::Completed;
    }

    public function isFailed(): bool
    {
        return $this->status === PaymentStatusEnum::Failed;
    }

################################ End Accessors and Mutators #########################################
}
