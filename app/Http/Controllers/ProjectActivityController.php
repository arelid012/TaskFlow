<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ActivityLog;

class ProjectActivityController extends Controller
{
    public function index(Project $project)
    {
        // 🔐 Authorization
        $this->authorize('view', $project);

        $activities = ActivityLog::with([
                'user:id,name',
                'task:id,title'
            ])
            ->where('project_id', $project->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'project_id' => $project->id,
            'data' => $activities->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'description' => $log->description,
                    'user' => [
                        'id' => $log->user->id,
                        'name' => $log->user->name,
                    ],
                    'task' => $log->task ? [
                        'id' => $log->task->id,
                        'title' => $log->task->title,
                    ] : null,
                    'created_at' => $log->created_at->toDateTimeString(),
                ];
            }),
            'meta' => [
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
            ]
        ]);
    }
}

