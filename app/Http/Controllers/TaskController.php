<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Notifications\TaskUpdated;
use App\Services\ActivityLogger;
use App\Models\ActivityLog;


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

        $task->update([
            'status' => $request->status
        ]);

        if ($task->status === 'done' && $task->assignee) {
            $task->assignee->notify(
                new TaskUpdated($task, 'completed')
            );

            ActivityLogger::log(
            auth()->id(),
            'completed',
            'Completed task "' . $task->title . '"',
            $task->project_id,
            $task->id
            );
        }

        if (!$this->validStatusTransition($task->status, $request->status)) {
            abort(422, 'Invalid status transition');
        }

        return $task;
    }

    public function assign(Request $request, Task $task)
    {
        if ($task->assigned_to == $request->assigned_to) {
        return response()->json(['message' => 'Already assigned'], 200);
        }

        $this->authorize('assign', Task::class);

        $request->validate([
            'assigned_to' => 'required|exists:users,id'
        ]);

        $task->update([
            'assigned_to' => $request->assigned_to
        ]);

        $task->assignee->notify(
            new TaskUpdated($task, 'assigned')
        );

        ActivityLogger::log(
        auth()->id(),
        'assigned',
        'Assigned task "' . $task->title . '"',
        $task->project_id,
        $task->id
        );

        return $task;
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
