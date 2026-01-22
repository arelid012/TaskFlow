<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'action' => $this->action,
            'description' => $this->description,
            'meta' => $this->meta ? json_decode($this->meta, true) : null,

            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null,

            'is_latest' => $this->is_latest ?? false,

            'created_at' => $this->created_at->toDateTimeString(),
        ];

        // Handle task data
        if ($this->task) {
            $taskData = [
                'id' => $this->task->id,
                'title' => $this->task->title,
                'status' => $this->task->status,
                'assigned_to' => $this->task->assigned_to,
                'due_date' => $this->task->due_date,
                'created_by' => $this->task->created_by,
                'project_id' => $this->task->project_id,
            ];

            // Load project relationship if available
            if ($this->task->relationLoaded('project')) {
                $taskData['project'] = [
                    'id' => $this->task->project->id,
                    'created_by' => $this->task->project->created_by
                ];
            }

            $data['task'] = $taskData;
        } else {
            $data['task'] = null;
        }

        return $data;
    }
}