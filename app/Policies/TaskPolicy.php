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
            ->value('project_user.role');
        
        // 0. VIEWERS CANNOT UPDATE ANYTHING
        if ($userRoleInProject === 'viewer') {
            return false;
        }
        
        // 1. Project owners can always update (check created_by, not pivot)
        if ($task->project->created_by === $user->id) {
            return true;
        }
        
        // 2. Admins and managers (global) can update
        if (in_array($user->role, ['admin', 'manager'])) {
            return true;
        }
        
        // 3. Task creator can update (if not viewer)
        if ($task->created_by === $user->id) {
            return true;
        }
        
        // 4. Assignee can update their own tasks
        if ($task->assigned_to === $user->id) {
            return true;
        }
        
        // 5. Project leads/managers (pivot) can update any
        return in_array($userRoleInProject, ['lead', 'manager', 'owner']);
    }

    public function delete(User $user, Task $task): bool
    {
        // Global admins/managers
        if (in_array($user->role, ['admin', 'manager'])) return true;
        
        // Task creator
        if ($task->created_by && $task->created_by == $user->id) return true;
        
        // Project owner
        $task->load('project');
        if ($task->project->created_by == $user->id) return true;
        
        // Project manager (pivot)
        $userRoleInProject = $task->project->members()
            ->where('user_id', $user->id)
            ->value('project_user.role');
        
        if ($userRoleInProject === 'manager') return true;
        
        return false;
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
        // 1. Task creator can change status
        if ($task->created_by === $user->id) {
            return true;
        }
        
        // 2. Assignee can change their own task status
        if ($task->assigned_to === $user->id) {
            return true;
        }
        
        // 3. Project leads/managers can change any status
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
        // More specific than just update() - focused on due dates
        
        // 1. Admins and managers (global) can edit
        if (in_array($user->role, ['admin', 'manager'])) {
            return true;
        }
        
        // 2. Task creator can edit due date
        if ($task->created_by === $user->id) {
            return true;
        }
        
        // 3. Assignee can edit due date of their task
        if ($task->assigned_to === $user->id) {
            return true;
        }
        
        // 4. Project owner can edit any task in their project
        if ($task->project->created_by === $user->id) {
            return true;
        }
        
        // 5. Project leads/managers (pivot) can edit
        $userRoleInProject = $task->project->members()
            ->where('user_id', $user->id)
            ->value('project_user.role');
            
        return in_array($userRoleInProject, ['lead', 'manager']);
    }
}