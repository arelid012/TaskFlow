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

        // Get user's projects - FIXED VERSION
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
            // Regular users see projects they OWN OR are MEMBERS of
            $projects = Project::where(function($query) use ($user) {
                // Projects they created
                $query->where('created_by', $user->id)
                    // OR projects they're members of in project_user table
                    ->orWhereHas('members', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
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
         // ===== OPTION B: Get user's assigned tasks (Active + Recent Completed) =====
        // Get active (non-done) tasks - INCLUDING TASKS YOU CREATED
        $userActiveTasks = Task::where(function($query) use ($user) {
            $query->where('assigned_to', $user->id)
                ->orWhere('created_by', $user->id); // ADD THIS LINE
        })
        ->with(['project', 'assignee'])
        ->where('status', '!=', 'done')
        ->latest()
        ->take(6) // Show 6 active tasks
        ->get();

        // Get recently completed tasks (last 2 completed) - INCLUDING TASKS YOU CREATED
        $userRecentCompleted = Task::where(function($query) use ($user) {
            $query->where('assigned_to', $user->id)
                ->orWhere('created_by', $user->id); // ADD THIS LINE
        })
        ->with(['project', 'assignee'])
        ->where('status', 'done')
        ->latest()
        ->take(2) // Show 2 recently completed
        ->get();

        // Merge both collections
        $userTasks = $userActiveTasks->merge($userRecentCompleted);
        
        // DEBUG: More detailed logging
        Log::info('User ID: ' . $user->id);
        Log::info('Found ' . $userActiveTasks->count() . ' active tasks'); // FIXED
        Log::info('Found ' . $userRecentCompleted->count() . ' recently completed tasks'); // FIXED
        Log::info('Total tasks to display: ' . $userTasks->count()); // ADDED
        
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

        // Get overdue tasks - include tasks you created
        $overdueTasks = Task::where(function($query) use ($user) {
            $query->where('assigned_to', $user->id)
                ->orWhere('created_by', $user->id); // ADD THIS
        })
        ->whereDate('due_date', '<', Carbon::today())
        ->where('status', '!=', 'done')
        ->with('project')
        ->get();

        // Get due soon tasks - include tasks you created
        $dueSoonTasks = Task::where(function($query) use ($user) {
            $query->where('assigned_to', $user->id)
                ->orWhere('created_by', $user->id); // ADD THIS
        })
        ->whereDate('due_date', '>=', Carbon::today())
        ->whereDate('due_date', '<=', Carbon::today()->addDays(2))
        ->where('status', '!=', 'done')
        ->with('project')
        ->get();

        // Group tasks by project - only ACTIVE tasks for this view
        $tasksByProject = collect();
        if ($userActiveTasks->count() > 0) {
            $tasksByProject = Task::where(function($query) use ($user) {
                $query->where('assigned_to', $user->id)
                    ->orWhere('created_by', $user->id); // ADD THIS
            })
            ->with(['project', 'assignee'])
            ->where('status', '!=', 'done')
            ->get()
            ->groupBy(function ($task) {
                return $task->project ? $task->project->name : 'Uncategorized';
            });
        }

        // Calculate stats - include tasks you created
        $totalTasksAssigned = Task::where(function($query) use ($user) {
            $query->where('assigned_to', $user->id)
                ->orWhere('created_by', $user->id); // ADD THIS
        })->count();

        $completedTasks = Task::where(function($query) use ($user) {
            $query->where('assigned_to', $user->id)
                ->orWhere('created_by', $user->id); // ADD THIS
        })
        ->where('status', 'done')
        ->count();
            
        $stats = [
            'total_projects' => $projects->count(),
            'pending_tasks' => $userActiveTasks->count(), // Only count active for "pending"
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
            'stats',
            'userActiveTasks', // Pass this separately for accurate counts
            'userRecentCompleted' // Pass this separately if needed
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