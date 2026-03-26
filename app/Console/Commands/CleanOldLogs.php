<?php

namespace App\Console\Commands;

use App\Models\SystemLog;
use Illuminate\Console\Command;

class CleanOldLogs extends Command
{
    protected $signature   = 'logs:clean';
    protected $description = 'Delete system logs older than 90 days';

    public function handle(): void
    {
        $count = SystemLog::where('created_at', '<', now()->subDays(90))->delete();
        $this->info("Deleted {$count} old log entries.");
    }
}
