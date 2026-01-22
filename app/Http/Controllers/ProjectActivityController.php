<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Models\User;

class ProjectActivityController extends Controller
{

    // 🖼 Render Blade page
    public function page(Project $project, Request $request)
    {
        $this->authorize('view', $project);

        $users = User::select('id', 'name')->get();
        
        // Get the task ID to highlight
        $highlightTaskId = $request->input('highlight');
        $initialPage = 1;
        
        // If we have a task to highlight, find which page it's on
        if ($highlightTaskId) {
            // Find all activity logs for this task, ordered by latest first
            $taskLogPosition = ActivityLog::where('project_id', $project->id)
                ->where('task_id', $highlightTaskId)
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($taskLogPosition) {
                // Count how many logs are newer than this one
                $newerLogsCount = ActivityLog::where('project_id', $project->id)
                    ->where('created_at', '>', $taskLogPosition->created_at)
                    ->count();
                
                // Calculate page (20 logs per page, page 1 = newest)
                $initialPage = floor($newerLogsCount / 20) + 1;
            }
        }

        return view('projects.activity', compact('project', 'users', 'highlightTaskId', 'initialPage'));
    }


    public function index(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $query = ActivityLog::with([
            'user:id,name',
            'task:id,title,status,assigned_to,due_date,created_by,project_id',
            'task.project:id,created_by'
        ])->where('project_id', $project->id);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('task_id')) {
            $query->where('task_id', $request->task_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $activities = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $latestTaskLogs = ActivityLog::where('project_id', $project->id)
            ->whereNotNull('task_id')
            ->latest('created_at')
            ->get()
            ->unique('task_id')
            ->pluck('id')
            ->toArray();

        $latestIdsOnPage = array_intersect(
            $activities->pluck('id')->toArray(),
            $latestTaskLogs
        );

        $activities->getCollection()->transform(function ($log) use ($latestIdsOnPage) {
            $log->is_latest = in_array($log->id, $latestIdsOnPage);
            return $log;
        });

        return ActivityLogResource::collection($activities);
    }
}

