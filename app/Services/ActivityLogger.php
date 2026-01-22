<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogger
{
    public static function log(
        int $userId,
        string $action,
        string $description,
        ?int $projectId = null,
        ?int $taskId = null,
        ?array $meta = null  // Add this parameter
    ) {
        return ActivityLog::create([
            'user_id'    => $userId,
            'action'     => $action,
            'description'=> $description,
            'project_id' => $projectId,
            'task_id'    => $taskId,
            'meta'       => $meta ? json_encode($meta) : null, // Add meta column
        ]);
    }
}