<?php

namespace App\Jobs;

use App\Models\Hold;
use App\Enums\HoldStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExpireHoldJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The hold ID to expire.
     */
    protected int $holdId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $holdId)
    {
        $this->holdId = $holdId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::transaction(function () {
                // Lock the hold for update to prevent race conditions
                $hold = Hold::where('id', $this->holdId)
                    ->lockForUpdate()
                    ->first();

                if (!$hold) {
                    Log::warning("Hold with ID {$this->holdId} not found for expiry");
                    return;
                }

                // Only expire if still active and past expiry time
                if ($hold->status === HoldStatus::ACTIVE && 
                    Carbon::now()->isAfter($hold->expires_at)) {
                    
                    $hold->update([
                        'status' => HoldStatus::EXPIRED,
                        'updated_at' => Carbon::now(),
                    ]);

                    Log::info("Hold {$this->holdId} expired successfully");
                } else {
                    Log::info("Hold {$this->holdId} not expired - status: {$hold->status->value}, expires_at: {$hold->expires_at}");
                }
            });
        } catch (\Exception $e) {
            Log::error("Failed to expire hold {$this->holdId}: " . $e->getMessage());
            
            // Re-throw to trigger job retry mechanism
            throw $e;
        }
    }

    /**
     * The job failed to process.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ExpireHoldJob failed for hold {$this->holdId}: " . $exception->getMessage());
    }
}