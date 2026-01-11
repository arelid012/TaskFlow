<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'description' => $this->description,

            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],

            'task' => $this->task ? [
                'id' => $this->task->id,
                'title' => $this->task->title,
                'status' => $this->task->status,
                'assigned_to' => $this->task->assigned_to,
                'due_date' => $this->task->due_date,
                'created_by' => $this->task->created_by, 
                'project_id' => $this->task->project_id, 
            ] : null,

            'is_latest' => $this->is_latest ?? false,

            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}