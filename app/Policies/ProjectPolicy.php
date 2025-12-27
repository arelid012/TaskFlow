<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Owner or admin can update
     */
    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->created_by || $user->role === 'admin';
    }

    /**
     * Owner or admin can delete
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->created_by || $user->role === 'admin';
    }

    /**
     * Everyone logged in can view
     */
    public function view(User $user, Project $project): bool
    {
        return $user->id === $project->created_by
            || in_array($user->role, ['admin', 'manager']);
    }
}
