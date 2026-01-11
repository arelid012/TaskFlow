<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        // Users can view tasks in projects they're members of
        // This is handled at project level, so return true
        // The actual filtering happens in controllers
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        // First check if user can view the project
        // This uses your ProjectPolicy::view()
        return $user->can('view', $task->project);
    }

    public function create(User $user): bool
    {
        // Allow creation if user is logged in
        // The actual project permission will be checked in controller
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        // Get user's role IN THIS PROJECT (pivot role)
        $userRoleInProject = $task->project->members()
            ->where('user_id', $user->id)
            ->value('project_user.role'); // FIXED
        
        // 0. VIEWERS CANNOT UPDATE ANYTHING
        if ($userRoleInProject === 'viewer') {
            return false;
        }
        
        // 1. Admins and managers (global) can update
        if (in_array($user->role, ['admin', 'manager'])) {
            return true;
        }
        
        // 2. Task creator can update (if not viewer)
        if ($task->created_by === $user->id) {
            return true;
        }
        
        // 3. Assignee can update their own tasks
        if ($task->assigned_to === $user->id) {
            return true;
        }
        
        // 4. Project leads/managers (pivot) can update any
        return in_array($userRoleInProject, ['lead', 'manager', 'owner']);
    }

    public function delete(User $user, Task $task): bool
    {
        Log::info("=== DELETE POLICY DEBUG ===");
        Log::info("User ID: {$user->id}, Role: {$user->role}");
        Log::info("Task ID: {$task->id}");
        Log::info("Task created_by: " . ($task->created_by ?? 'NULL'));
        Log::info("Task project_id: " . ($task->project_id ?? 'NULL'));
        
        // Load the project relationship
        $task->load('project');
        Log::info("Project created_by: " . ($task->project->created_by ?? 'NULL'));
        
        // Check each condition
        $isTaskCreator = $task->created_by && $task->created_by == $user->id;
        $isAdminOrManager = in_array($user->role, ['admin', 'manager']);
        $isProjectOwner = $task->project && $task->project->created_by == $user->id;
        
        Log::info("Is task creator? " . ($isTaskCreator ? 'YES' : 'NO'));
        Log::info("Is admin/manager? " . ($isAdminOrManager ? 'YES' : 'NO'));
        Log::info("Is project owner? " . ($isProjectOwner ? 'YES' : 'NO'));
        
        $result = $isTaskCreator || $isAdminOrManager || $isProjectOwner;
        
        Log::info("Final result: " . ($result ? 'ALLOW' : 'DENY'));
        Log::info("=== END DEBUG ===");
        
        return $result;
    }

    public function assign(User $user, Task $task): bool
    {
        // User must be a project member to assign tasks
        $isProjectMember = $task->project->members()
            ->where('user_id', $user->id)
            ->exists();
            
        if (!$isProjectMember) {
            return false;
        }
        
        // Admins and managers can always assign
        if (in_array($user->role, ['admin', 'manager'])) {
            return true;
        }
        
        // Project leads can assign
        $userRole = $task->project->members()
            ->where('user_id', $user->id)
            ->value('project_user.role');
            
        if (in_array($userRole, ['lead', 'manager'])) {
            return true;
        }
        
        // Regular members can only reassign their own tasks
        return $task->assigned_to === $user->id;
    }
    
    /**
     * Can user change task status?
     */
    public function changeStatus(User $user, Task $task): bool
    {
        // Similar to update, but maybe more restrictive
        
        // 1. Assignee can change their own task status
        if ($task->assigned_to === $user->id) {
            return true;
        }
        
        // 2. Project leads/managers can change any status
        $userRoleInProject = $task->project->members()
            ->where('user_id', $user->id)
            ->value('project_user.role');
            
        return in_array($userRoleInProject, ['lead', 'manager', 'owner']) ||
               in_array($user->role, ['admin', 'manager']);
    }
    
    /**
     * Can user change due date?
     */
    public function changeDueDate(User $user, Task $task): bool
    {
        // Usually same as update permission
        return $this->update($user, $task);
    }
}