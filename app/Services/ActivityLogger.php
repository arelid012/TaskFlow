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
        ?int $taskId = null
    ) {
        ActivityLog::create([
            'user_id'    => $userId,
            'action'     => $action,
            'description'=> $description,
            'project_id' => $projectId,
            'task_id'    => $taskId,
        ]);
    }
}
