<?php

namespace Mruganshi\CodeGenerator\Console\Commands;

use Illuminate\Console\Command;
use Mruganshi\CodeGenerator\Models\CodeGeneratorFileLog;

class ClearLogs extends Command
{
    protected $signature = 'code-generator:clear-logs';
    protected $description = 'Deletes log entries older than configured retention days';

    public function handle(): void
    {
        $days = config('code_generator.log_retention_days');

        //   Delete log entries older than the configured retention period
        $deleted = CodeGeneratorFileLog::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("Deleted {$deleted} log entries older than {$days} days.");
    }
}
