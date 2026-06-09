<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueueWorkCronCommand extends Command
{
    protected $signature   = 'queue:cron';
    protected $description = 'Cron-safe queue worker — processes pending jobs and logs activity';

    public function handle(): int
    {
        $pending = DB::table('jobs')->where('available_at', '<=', now()->timestamp)->count();

        if ($pending === 0) {
            Log::info('[QueueCron] Fired — no jobs ready, skipping.');
            return self::SUCCESS;
        }

        Log::info("[QueueCron] Started — {$pending} job(s) ready to process.");

        $this->call('queue:work', [
            'connection'        => 'database',
            '--stop-when-empty' => true,
            '--tries'           => 3,
            '--timeout'         => 55,
        ]);

        $remaining = DB::table('jobs')->count();
        Log::info("[QueueCron] Finished — {$remaining} job(s) still pending (future-scheduled).");

        return self::SUCCESS;
    }
}
