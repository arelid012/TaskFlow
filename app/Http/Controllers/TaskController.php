<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Notifications\TaskUpdated;
use App\Services\ActivityLogger;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        return $project->tasks()->with('assignee')->get();
    }

    public function store(Request $request, Project $project)
    {
        // Use the new policy method
        $this->authorize('createTask', $project);

        $request->validate([
            'title' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'in:todo,doing,done',
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        // Additional validation: assignee must be project member
        if ($request->filled('assigned_to')) {
            $isAssigneeMember = $project->members()
                ->where('user_id', $request->assigned_to)
                ->exists();
                
            if (!$isAssigneeMember) {
                return back()->with('error', 'Cannot assign task to non-project member.');
            }
        }

        $task = $project->tasks()->create([
            'title' => $request->title,
            'assigned_to' => $request->assigned_to,
            'status' => $request->status ?? 'todo',
            'due_date' => $request->due_date,
            'created_by' => auth()->id(), // Important!
        ]);

        // Send notification
        if ($task->assignee && $task->assignee->id != auth()->id()) {
            $task->assignee->notify(new TaskUpdated($task, 'assigned'));
        }

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
            'due_date' => 'nullable|date', // Add validation for update
        ]);

        $oldStatus = $task->status;
        $newStatus = $request->status;
        $oldDueDate = $task->due_date;

        // ✅ Validate status transition
        if (!$this->validStatusTransition($oldStatus, $newStatus)) {
            abort(422, 'Invalid status transition');
        }

        // ✅ No-op protection
        if ($oldStatus === $newStatus && $oldDueDate == $request->due_date) {
            return $task;
        }

        $task->update([
            'status' => $newStatus,
            'due_date' => $request->due_date, // Update due_date
        ]);

        // ✅ Log due date changes
        if ($oldDueDate != $request->due_date) {
            ActivityLogger::log(
                auth()->id(),
                'task_due_date_changed',
                "Due date changed from " . ($oldDueDate ? $oldDueDate->format('Y-m-d') : 'Not set') . 
                " to " . ($request->due_date ? $request->due_date : 'Not set'),
                $task->project_id,
                $task->id,
                [
                    'from' => $oldDueDate,
                    'to'   => $request->due_date
                ]
            );
        }

        // ✅ Auto-log overdue status
        if ($task->isOverdue()) {
            ActivityLogger::log(
                auth()->id(),
                'task_overdue',
                "Task is now overdue",
                $task->project_id,
                $task->id
            );
        }

        // ✅ Notify on ANY status change
    if ($newStatus !== $oldStatus && $task->assignee && $task->assignee->id != auth()->id()) {
        $eventType = $newStatus === 'done' ? 'completed' : 'status_changed';
        $task->assignee->notify(new TaskUpdated($task, $eventType));
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

    public function updateDueDate(Request $request, Task $task)
    {
        // Use the same permission as task update
        $this->authorize('update', $task);
        
        $request->validate([
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);
        
        $oldDate = $task->due_date;
        $task->update(['due_date' => $request->due_date]);
        
        // Log the change
        ActivityLogger::log(
            auth()->id(),
            'task_due_date_changed',
            "Due date " . 
                ($oldDate ? 'changed from ' . $oldDate->format('M d, Y') : 'set to') . 
                ($request->due_date ? ' ' . date('M d, Y', strtotime($request->due_date)) : ' removed'),
            $task->project_id,
            $task->id,
            [
                'from' => $oldDate,
                'to'   => $request->due_date
            ]
        );
        
        return response()->json(['success' => true]);
    }

    public function assign(Request $request, Task $task)
{
    // 1. Validate the request
    $request->validate([
        'assigned_to' => 'nullable|exists:users,id',
    ]);

    // 2. Authorize - let the Policy handle ALL permission checks
    $this->authorize('assign', $task);

    // 3. Additional check: assignee must be project member
    if ($request->filled('assigned_to')) {
        $isAssigneeMember = $task->project->members()
            ->where('user_id', $request->assigned_to)
            ->exists();
            
        if (!$isAssigneeMember) {
            return response()->json([
                'error' => 'Cannot assign task to non-project member.'
            ], 422);
        }
    }

    $oldAssignee = $task->assigned_to;
    $assignedTo = $request->filled('assigned_to') ? $request->assigned_to : null;

    // 4. Update the task
    $task->update(['assigned_to' => $assignedTo]);
    $task->load('assignee');

    // 5. Log activity
    if ($oldAssignee && $request->assigned_to) {
        // Reassignment
        $oldName = User::find($oldAssignee)?->name;
        
        ActivityLogger::log(
            auth()->id(),
            'task_reassigned',
            "Task reassigned from {$oldName} to {$task->assignee->name}",
            $task->project_id,
            $task->id
        );
        
    } elseif ($request->assigned_to) {
        // New assignment
        ActivityLogger::log(
            auth()->id(),
            'task_assigned',
            "Task assigned to {$task->assignee->name}",
            $task->project_id,
            $task->id
        );
        
    } elseif ($oldAssignee && !$request->assigned_to) {
        // Unassignment
        $oldName = User::find($oldAssignee)?->name;
        ActivityLogger::log(
            auth()->id(),
            'task_unassigned',
            "Task unassigned from {$oldName}",
            $task->project_id,
            $task->id
        );
    }

    // 6. Send notification (if new assignee is different from current user)
    if ($task->assignee && $task->assignee->id != auth()->id()) {
        $task->assignee->notify(new \App\Notifications\TaskUpdated($task, 'assigned'));
    }

    // 7. Return response
    return response()->json([
        'success' => true,
        'assigned_to' => $assignedTo,
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

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        
        ActivityLogger::log(
            auth()->id(),
            'task_deleted',
            "Task '{$task->title}' deleted",
            $task->project_id,
            $task->id
        );
        
        $task->delete();
        
        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }
        
        return back()->with('success', 'Task deleted successfully');
    }

}
