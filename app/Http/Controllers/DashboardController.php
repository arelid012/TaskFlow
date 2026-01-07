<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $tasksByProject = Task::with(['project', 'assignee'])
            ->where('assigned_to', auth()->id())
            ->get()
            ->groupBy(fn ($task) => $task->project->name);

        // Get overdue tasks count
        $overdueTasks = Task::where('assigned_to', auth()->id())
            ->where('due_date', '<', Carbon::today())
            ->where('status', '!=', 'done')
            ->count();

        // Get due soon tasks (within 2 days)
        $dueSoonTasks = Task::where('assigned_to', auth()->id())
            ->where('due_date', '>=', Carbon::today())
            ->where('due_date', '<=', Carbon::today()->addDays(2))
            ->where('status', '!=', 'done')
            ->count();

        return view('dashboard', compact('tasksByProject', 'overdueTasks', 'dueSoonTasks'));
    }
}