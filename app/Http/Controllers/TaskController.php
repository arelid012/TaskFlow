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
        $this->authorize('view', $project);

        $request->validate([
            'title' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'in:todo,doing,done',
            'due_date' => 'nullable|date|after_or_equal:today', // Add validation
        ]);

        $task = $project->tasks()->create([
            'title' => $request->title,
            'assigned_to' => $request->assigned_to,
            'status' => $request->status ?? 'todo',
            'due_date' => $request->due_date, // Add due_date
        ]);

        // ✅ ADD NOTIFICATION HERE
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


    public function assign(Request $request, Task $task)
{
    // Clear old logs and start fresh
    Log::info("\n\n========== NEW ASSIGNMENT REQUEST ==========");
    Log::info('Task ID:', ['id' => $task->id, 'current_assignee' => $task->assigned_to]);
    Log::info('Request data:', $request->all());
    Log::info('Auth user:', ['id' => auth()->id(), 'name' => auth()->user()->name]);

    $this->authorize('assign', $task);

    $request->validate([
        'assigned_to' => 'nullable|exists:users,id',
    ]);

    $oldAssignee = $task->assigned_to;
    Log::info('Old assignee ID:', ['old' => $oldAssignee]);

    $assignedTo = $request->filled('assigned_to') ? $request->assigned_to : null;
    Log::info('New assignee ID:', ['new' => $assignedTo]);

    $task->update(['assigned_to' => $assignedTo]);
    Log::info('Task updated in database');

    $task->load('assignee');
    Log::info('Task after load:', [
        'assigned_to' => $task->assigned_to,
        'has_assignee' => !is_null($task->assignee),
        'assignee_name' => $task->assignee ? $task->assignee->name : 'null',
        'assignee_id' => $task->assignee ? $task->assignee->id : 'null'
    ]);

    // DEBUG: Which condition will trigger?
    Log::info('Condition check:', [
        'condition1' => $oldAssignee && $request->assigned_to,
        'condition2' => $request->assigned_to,
        'condition3' => $oldAssignee && !$request->assigned_to
    ]);

    if ($oldAssignee && $request->assigned_to) {
        Log::info('✅ Entering CASE 1: Reassignment');
        $oldName = User::find($oldAssignee)?->name;

        ActivityLogger::log(
            auth()->id(),
            'task_reassigned',
            "Task reassigned from {$oldName} to {$task->assignee->name}",
            $task->project_id,
            $task->id
        );
        
        Log::info('Checking if should send notification in CASE 1:', [
            'assignee_exists' => !is_null($task->assignee),
            'assignee_id' => $task->assignee ? $task->assignee->id : 'null',
            'auth_id' => auth()->id(),
            'different_users' => $task->assignee && $task->assignee->id != auth()->id(),
            'should_send' => $task->assignee && $task->assignee->id != auth()->id()
        ]);
        
        if ($task->assignee && $task->assignee->id != auth()->id()) {
            $task->assignee->notify(new \App\Notifications\TaskUpdated($task, 'assigned'));
            Log::info('🎯 NOTIFICATION SENT in CASE 1 to: ' . $task->assignee->name);
        } else {
            Log::info('🚫 NOTIFICATION NOT SENT in CASE 1 - same user or no assignee');
        }
        
    } elseif ($request->assigned_to) {
        Log::info('✅ Entering CASE 2: New assignment');
        ActivityLogger::log(
            auth()->id(),
            'task_assigned',
            "Task assigned to {$task->assignee->name}",
            $task->project_id,
            $task->id
        );
        
        Log::info('Checking if should send notification in CASE 2:', [
            'assignee_exists' => !is_null($task->assignee),
            'assignee_id' => $task->assignee ? $task->assignee->id : 'null',
            'auth_id' => auth()->id(),
            'different_users' => $task->assignee && $task->assignee->id != auth()->id(),
            'should_send' => $task->assignee && $task->assignee->id != auth()->id()
        ]);
        
        if ($task->assignee && $task->assignee->id != auth()->id()) {
            $task->assignee->notify(new \App\Notifications\TaskUpdated($task, 'assigned'));
            Log::info('🎯 NOTIFICATION SENT in CASE 2 to: ' . $task->assignee->name);
        } else {
            Log::info('🚫 NOTIFICATION NOT SENT in CASE 2 - same user or no assignee');
        }
        
    } elseif ($oldAssignee && !$request->assigned_to) {
        Log::info('✅ Entering CASE 3: Unassignment');
        $oldName = User::find($oldAssignee)?->name;
        ActivityLogger::log(
            auth()->id(),
            'task_unassigned',
            "Task unassigned from {$oldName}",
            $task->project_id,
            $task->id
        );
    } else {
        Log::info('⚠️ No condition matched! This should not happen.');
    }

    Log::info("========== END ASSIGNMENT REQUEST ==========\n");
    
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

}
