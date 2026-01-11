<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function createTask(User $user, Project $project): bool
    {
        // User must be a project member to create tasks
        return $project->members()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->created_by || $user->role === 'admin';
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->created_by || $user->role === 'admin';
    }

    public function view(User $user, Project $project): bool
    {
        // 1. Admins and managers can view everything
        if (in_array($user->role, ['admin', 'manager'])) {
            return true;
        }

        // 2. Project creator can view their own projects
        if ($project->created_by === $user->id) {
            return true;
        }

        // 3. STRICT: Only members explicitly added to project can view
        return $project->members()->where('user_id', $user->id)->exists();
    }
    
    /**
     * Check if user can assign tasks in this project
     */
    public function assignTask(User $user, Project $project): bool
    {
        // Only project members can assign tasks
        return $this->view($user, $project) && 
            ($user->role === 'manager' || 
                $project->created_by === $user->id ||
                $project->members()
                    ->where('user_id', $user->id)
                    ->whereIn('project_user.role', ['manager', 'lead', 'senior']) // FIXED
                    ->exists());
    }

    public function viewAny(User $user): bool
    {
        // All authenticated users can view projects list
        // The actual filtering happens in the controller
        return true;
    }
}