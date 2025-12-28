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
            ] : null,

            'is_latest' => $this->is_latest ?? false,

            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
