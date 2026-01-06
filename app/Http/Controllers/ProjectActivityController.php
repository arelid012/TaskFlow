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
    public function page(Project $project)
    {
        $this->authorize('view', $project);

        $users = User::select('id', 'name')->get();

        return view('projects.activity', compact('project', 'users'));
    }


    public function index(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $query = ActivityLog::with([
            'user:id,name',
            'task:id,title,status'
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

