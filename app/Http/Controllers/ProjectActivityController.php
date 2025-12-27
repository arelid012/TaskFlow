<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;

class ProjectActivityController extends Controller
{

    // 🖼 Render Blade page
    public function page(Project $project)
    {
        $this->authorize('view', $project);

        return view('projects.activity', compact('project'));
    }
   


    public function index(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $query = ActivityLog::with([
            'user:id,name',
            'task:id,title'
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

        return response()->json(
            ActivityLogResource::collection($activities)
                ->additional([
                    'project_id' => $project->id,
                    'filters' => $request->only([
                        'action',
                        'user_id',
                        'task_id',
                        'from',
                        'to',
                    ]),
                ])
        );
    }
}

