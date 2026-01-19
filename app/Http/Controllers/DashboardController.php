<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        Log::info('Dashboard loaded for user: ' . $user->id . ' - ' . $user->name . ' (Role: ' . $user->role . ')');

        // FIX 1: Get user's projects - SIMPLIFIED VERSION
        if ($user->role === 'admin' || $user->role === 'manager') {
        // Admins/Managers see all projects
        $projects = Project::withCount([
            'tasks' => function ($query) {
                $query->where('status', '!=', 'done');
            }
        ])
        ->latest()
        ->take(5)
        ->get();
    } else {
        // Regular users see projects they OWN (created_by)
        $projects = Project::where('created_by', $user->id)
            ->withCount([
                'tasks' => function ($query) {
                    $query->where('status', '!=', 'done');
                }
            ])
            ->latest()
            ->take(5)
            ->get();
    }

        // FIX 2: Get user's assigned tasks
        $userTasks = Task::where('assigned_to', $user->id)
            ->with(['project', 'assignee'])
            ->where('status', '!=', 'done')
            ->latest()
            ->take(8)
            ->get();
        
        // DEBUG: More detailed logging
        Log::info('User ID: ' . $user->id);
        Log::info('Found ' . $userTasks->count() . ' pending tasks');
        
        if ($userTasks->isEmpty()) {
            // Check what tasks exist in the database
            $allTasks = Task::all();
            Log::info('Total tasks in database: ' . $allTasks->count());
            
            $tasksWithAssignee = Task::whereNotNull('assigned_to')->count();
            Log::info('Tasks with assignee: ' . $tasksWithAssignee);
            
            $myAllTasks = Task::where('assigned_to', $user->id)->get();
            Log::info('All tasks assigned to me: ' . $myAllTasks->count());
            
            foreach ($myAllTasks as $task) {
                Log::info('Task ' . $task->id . ': ' . $task->title . ' - Status: ' . $task->status);
            }
        }

        // Get overdue tasks
        $overdueTasks = Task::where('assigned_to', $user->id)
            ->whereDate('due_date', '<', Carbon::today())
            ->where('status', '!=', 'done')
            ->with('project')
            ->get();

        // Get due soon tasks
        $dueSoonTasks = Task::where('assigned_to', $user->id)
            ->whereDate('due_date', '>=', Carbon::today())
            ->whereDate('due_date', '<=', Carbon::today()->addDays(2))
            ->where('status', '!=', 'done')
            ->with('project')
            ->get();

        // Group tasks by project - only for display if we have tasks
        $tasksByProject = collect();
        if ($userTasks->count() > 0) {
            $tasksByProject = Task::with(['project', 'assignee'])
                ->where('assigned_to', $user->id)
                ->where('status', '!=', 'done')
                ->get()
                ->groupBy(function ($task) {
                    return $task->project ? $task->project->name : 'Uncategorized';
                });
        }

        // Calculate stats
        $totalTasksAssigned = Task::where('assigned_to', $user->id)->count();
        $completedTasks = Task::where('assigned_to', $user->id)
            ->where('status', 'done')
            ->count();
            
        $stats = [
            'total_projects' => $projects->count(),
            'pending_tasks' => $userTasks->count(),
            'overdue_tasks' => $overdueTasks->count(),
            'due_soon_tasks' => $dueSoonTasks->count(),
            'total_tasks' => $totalTasksAssigned,
            'completed_tasks' => $completedTasks,
        ];
        
        Log::info('Dashboard stats: ' . json_encode($stats));

        return view('dashboard', compact(
            'projects',
            'userTasks', 
            'overdueTasks',
            'dueSoonTasks',
            'tasksByProject',
            'stats'
        ));
    }
    
    // Add this debug method to help troubleshoot
    public function testTasks()
    {
        $user = auth()->user();
        
        // Create a test task if none exist
        $testProject = Project::first();
        
        if ($testProject) {
            // Check if test task already exists
            $existingTestTask = Task::where('title', 'Test Task for ' . $user->name)->first();
            
            if (!$existingTestTask) {
                $task = Task::create([
                    'title' => 'Test Task for ' . $user->name,
                    'project_id' => $testProject->id,
                    'assigned_to' => $user->id,
                    'status' => 'todo',
                    'due_date' => Carbon::tomorrow(),
                    'created_by' => $user->id,
                ]);
                
                return response()->json([
                    'message' => 'Test task created!',
                    'task' => $task,
                    'user_id' => $user->id,
                    'assigned_to_check' => Task::where('assigned_to', $user->id)->count()
                ]);
            }
        }
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role
            ],
            'tasks_assigned_to_me' => Task::where('assigned_to', $user->id)->get()->map(function($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status,
                    'project' => $task->project ? $task->project->name : 'No project'
                ];
            }),
            'total_tasks_in_db' => Task::count(),
            'projects_i_own' => $user->projects()->pluck('name')
        ]);
    }
}