<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get user's projects (based on your ProjectController logic)
        if ($user->role === 'admin' || $user->role === 'manager') {
            $projects = Project::withCount(['tasks' => function ($query) {
                $query->where('status', '!=', 'done');
            }])->latest()->take(5)->get();
        } else {
            $projects = collect()
                ->merge($user->projects ?? [])
                ->merge($user->assignedProjects ?? [])
                ->unique('id')
                ->values()
                ->take(5);
            
            // Load task counts for each project
            foreach ($projects as $project) {
                $project->pending_tasks_count = $project->tasks()
                    ->where('status', '!=', 'done')
                    ->count();
            }
        }

        // Get user's assigned tasks (pending only)
        $userTasks = Task::where('assigned_to', $user->id)
            ->with(['project', 'assignee'])
            ->where('status', '!=', 'done')
            ->latest()
            ->take(8)
            ->get();

        // Get overdue tasks (detailed for display)
        $overdueTasks = Task::where('assigned_to', $user->id)
            ->where('due_date', '<', Carbon::today())
            ->where('status', '!=', 'done')
            ->with('project')
            ->get();

        // Get due soon tasks (within 2 days)
        $dueSoonTasks = Task::where('assigned_to', $user->id)
            ->where('due_date', '>=', Carbon::today())
            ->where('due_date', '<=', Carbon::today()->addDays(2))
            ->where('status', '!=', 'done')
            ->with('project')
            ->get();

        // Group tasks by project (for your current view)
        $tasksByProject = Task::with(['project', 'assignee'])
            ->where('assigned_to', $user->id)
            ->get()
            ->groupBy(fn ($task) => $task->project->name);

        // Counts for stats cards
        $stats = [
            'total_projects' => $projects->count(),
            'pending_tasks' => $userTasks->count(),
            'overdue_tasks' => $overdueTasks->count(),
            'due_soon_tasks' => $dueSoonTasks->count(),
            'total_tasks' => Task::where('assigned_to', $user->id)->count(),
            'completed_tasks' => Task::where('assigned_to', $user->id)
                ->where('status', 'done')
                ->count(),
        ];

        return view('dashboard', compact(
            'projects',
            'userTasks', 
            'overdueTasks',
            'dueSoonTasks',
            'tasksByProject',
            'stats'
        ));
    }
}