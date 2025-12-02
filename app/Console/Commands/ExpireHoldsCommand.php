<?php

namespace App\Console\Commands;

use App\Models\Hold;
use App\Enums\HoldStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpireHoldsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holds:expire {--force : Force expiry without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire all holds that have passed their expiry time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting hold expiry process...');

        // Find all active holds that have passed their expiry time
        $expiredHolds = Hold::where('status', HoldStatus::ACTIVE)
            ->where('expires_at', '<=', Carbon::now())
            ->get();

        if ($expiredHolds->isEmpty()) {
            $this->info('No expired holds found.');
            return 0;
        }

        $count = $expiredHolds->count();
        $this->info("Found {$count} expired holds.");

        if (!$this->option('force') && !$this->confirm("Do you want to expire {$count} holds?")) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $expired = 0;
        $failed = 0;

        foreach ($expiredHolds as $hold) {
            try {
                DB::transaction(function () use ($hold) {
                    // Lock the hold for update
                    $lockedHold = Hold::where('id', $hold->id)->lockForUpdate()->first();
                    
                    if ($lockedHold && $lockedHold->status === HoldStatus::ACTIVE) {
                        $lockedHold->update([
                            'status' => HoldStatus::EXPIRED,
                            'updated_at' => Carbon::now(),
                        ]);
                    }
                });
                
                $expired++;
                $this->line("✓ Expired hold ID: {$hold->id}");
            } catch (\Exception $e) {
                $failed++;
                $this->error("✗ Failed to expire hold ID: {$hold->id} - {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Expiry process completed:");
        $this->info("- Successfully expired: {$expired} holds");
        
        if ($failed > 0) {
            $this->warn("- Failed to expire: {$failed} holds");
        }

        return 0;
    }
}
