<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupOldTasks extends Command
{
    protected $signature = 'tasks:cleanup';
    protected $description = 'Delete tasks older than 30 days';

    public function handle()
    {
        $cutoffDate = Carbon::now()->subDays(30);
        
        $oldTasks = Task::where('created_at', '<', $cutoffDate)->get();
        $count = $oldTasks->count();
        
        if ($count > 0) {
            $taskIds = $oldTasks->pluck('id')->toArray();
            Task::whereIn('id', $taskIds)->delete();
            
            Log::info("Deleted {$count} old tasks", ['task_ids' => $taskIds]);
            $this->info("Deleted {$count} tasks older than 30 days");
        } else {
            $this->info('No old tasks found to delete');
        }

        return 0;
    }
}