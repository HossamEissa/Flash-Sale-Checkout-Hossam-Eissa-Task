<?php

namespace App\Jobs;

use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExpireOrderHoldJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The order ID to expire.
     */
    protected int $orderId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::beginTransaction();
        try {
            // Lock the order for update to prevent race conditions
            $order = Order::where('id', $this->orderId)
                ->lockForUpdate()
                ->first();

            if (!$order) {
                Log::warning("Order with ID {$this->orderId} not found for expiry");
                DB::rollBack();
                return;
            }

            // Only expire if it's a hold, still pending, and past expiry time
            if ($order->is_hold && 
                $order->status === OrderStatus::Pending && 
                $order->expires_at &&
                Carbon::now()->isAfter($order->expires_at)) {
                
                $order->update([
                    'status' => OrderStatus::Cancelled,
                    'updated_at' => Carbon::now(),
                ]);

                DB::commit();
                Log::info("Order hold {$this->orderId} expired successfully");
            } else {
                DB::rollBack();
                Log::info("Order {$this->orderId} not expired - is_hold: {$order->is_hold}, status: {$order->status->value}, expires_at: {$order->expires_at}");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to expire order hold {$this->orderId}: " . $e->getMessage());
            
            // Re-throw to trigger job retry mechanism
            throw $e;
        }
    }

    /**
     * The job failed to process.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ExpireOrderHoldJob failed for order {$this->orderId}: " . $exception->getMessage());
    }
}