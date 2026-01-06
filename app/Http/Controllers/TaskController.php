<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Notifications\TaskUpdated;
use App\Services\ActivityLogger;
use App\Models\ActivityLog;
use App\Models\User;

class TaskController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        return $project->tasks()->with('assignee')->get();
    }

    public function store(Request $request, Project $project)
    {

        $this->authorize('view', $project);

        $request->validate([
            'title' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'in:todo,doing,done',
        ]);

        $task = $project->tasks()->create([
            'title' => $request->title,
            'assigned_to' => $request->assigned_to,
            'status' => $request->status ?? 'todo',
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'project_id' => $project->id,
            'task_id' => $task->id,
            'action' => 'task_created',
            'description' => 'Task created',
        ]);

        return back()->with('success', 'Task created');
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate([
            'status' => 'required|in:todo,doing,done',
        ]);

        $oldStatus = $task->status;
        $newStatus = $request->status;

        // ✅ Validate BEFORE update
        if (!$this->validStatusTransition($oldStatus, $newStatus)) {
            abort(422, 'Invalid status transition');
        }

        // ✅ No-op protection
        if ($oldStatus === $newStatus) {
            return $task;
        }

        $task->update([
            'status' => $newStatus
        ]);

        // ✅ Notify only on completion
        if ($newStatus === 'done' && $task->assignee) {
            $task->assignee->notify(
                new TaskUpdated($task, 'completed')
            );
        }

        // ✅ Log ALL status changes
        ActivityLogger::log(
            auth()->id(),
            'task_status_changed',
            "Changed status from {$oldStatus} to {$newStatus}",
            $task->project_id,
            $task->id,
            [
                'from' => $oldStatus,
                'to'   => $newStatus
            ]
        );

        return $task;
    }


    public function assign(Request $request, Task $task)
    {
        $this->authorize('assign', $task);

        $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $oldAssignee = $task->assigned_to;

        // Convert empty string to null
        $assignedTo = $request->filled('assigned_to') ? $request->assigned_to : null;

        // ✅ FIX: Use $assignedTo variable (not $request->assigned_to)
        $task->update([
            'assigned_to' => $assignedTo,
        ]);

        $task->load('assignee');

        if ($oldAssignee && $request->assigned_to) {
            $oldName = User::find($oldAssignee)?->name;

            ActivityLogger::log(
                auth()->id(),
                'task_reassigned',
                "Task reassigned from {$oldName} to {$task->assignee->name}",
                $task->project_id,
                $task->id
            );
        } elseif ($request->assigned_to) {
            ActivityLogger::log(
                auth()->id(),
                'task_assigned',
                "Task assigned to {$task->assignee->name}",
                $task->project_id,
                $task->id
            );
        } elseif ($oldAssignee && !$request->assigned_to) {
            // Handle unassignment
            $oldName = User::find($oldAssignee)?->name;
            ActivityLogger::log(
                auth()->id(),
                'task_unassigned',
                "Task unassigned from {$oldName}",
                $task->project_id,
                $task->id
            );
        }

        return response()->json([
            'success' => true,
            'assigned_to' => $assignedTo, // ✅ Use $assignedTo here too
            'task_id' => $task->id,
        ]);
    }

    private function validStatusTransition(string $from, string $to): bool
    {
        return match ($from) {
            'todo'  => $to === 'doing',
            'doing' => $to === 'done',
            'done'  => false,
        };
    }

    public function kanban(Project $project)
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()
            ->with('assignee')
            ->get()
            ->groupBy('status');

        return [
            'todo'  => $tasks->get('todo', []),
            'doing' => $tasks->get('doing', []),
            'done'  => $tasks->get('done', []),
        ];
    }

}
