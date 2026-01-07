<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckOverdueTasks extends Command
{
    protected $signature = 'tasks:check-overdue';
    protected $description = 'Check and log overdue tasks';

    public function handle()
    {
        // Get a system user or admin user
        $systemUser = User::where('role', 'admin')->first();
        
        // If no admin, get any user (first user in system)
        if (!$systemUser) {
            $systemUser = User::first();
        }

        $overdueTasks = Task::where('due_date', '<', Carbon::today())
            ->where('status', '!=', 'done')
            ->whereDoesntHave('activityLogs', function ($query) {
                $query->where('action', 'task_overdue')
                    ->whereDate('created_at', Carbon::today());
            })
            ->get();

        foreach ($overdueTasks as $task) {
            ActivityLogger::log(
                $systemUser ? $systemUser->id : 0, // Use 0 if no user found
                'task_overdue',
                "Task became overdue",
                $task->project_id,
                $task->id
            );
        }

        $this->info("Checked {$overdueTasks->count()} overdue tasks.");
        
        return Command::SUCCESS;
    }
}