<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpireOrderHoldsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire-holds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire all order-based holds that have passed their expiry time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting order hold expiry process...');

        // Find all pending order holds that have passed their expiry time
        $expiredOrders = Order::where('is_hold', true)
            ->where('status', OrderStatus::Pending)
            ->where('expires_at', '<=', Carbon::now())
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('No expired order holds found.');
            return 0;
        }

        $count = $expiredOrders->count();
        $this->info("Found {$count} expired order holds.");

        if (!$this->option('force') && !$this->confirm("Do you want to expire {$count} order holds?")) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $expired = 0;
        $failed = 0;

        foreach ($expiredOrders as $order) {
            try {
                DB::transaction(function () use ($order) {
                    // Lock the order for update
                    $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();
                    
                    if ($lockedOrder && 
                        $lockedOrder->is_hold && 
                        $lockedOrder->status === OrderStatus::Pending) {
                        
                        $lockedOrder->update([
                            'status' => OrderStatus::Cancelled,
                            'updated_at' => Carbon::now(),
                        ]);
                    }
                });
                
                $expired++;
                $this->line("Expired order hold ID: {$order->id}");
            } catch (\Exception $e) {
                $failed++;
                $this->error("Failed to expire order hold ID: {$order->id} - {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Expiry process completed:");
        $this->info("Successfully expired: {$expired} order holds");
        
        if ($failed > 0) {
            $this->warn("Failed to expire: {$failed} order holds");
        }

        return 0;
    }
}
