<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;


class ProjectMemberController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('update', $project); // Only project owners/admins can manage members
        
        $members = $project->members()->get();
        $availableUsers = User::whereNotIn('id', $members->pluck('id'))
            ->where('id', '!=', $project->created_by) // Exclude creator (they're auto-owner)
            ->get(['id', 'name', 'email', 'role']);
            
        return view('projects.members', compact('project', 'members', 'availableUsers'));
    }
    
    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'nullable|in:viewer,member,lead,manager'
        ]);
        
        // Check if user is already a member
        if ($project->members()->where('user_id', $request->user_id)->exists()) {
            return back()->with('error', 'User is already a project member.');
        }
        
        // Add user to project
        $project->members()->attach($request->user_id, [
            'role' => $request->role ?? 'member',
            // 'added_by' => auth()->id(),
            // 'joined_at' => now()
        ]);
        
        // Log activity
        ActivityLogger::log(
            auth()->id(),
            'project_member_added',
            "Added " . User::find($request->user_id)->name . " to project",
            $project->id
            // No task_id needed for member actions
        );
        
        return back()->with('success', 'Member added successfully.');
    }
    
    public function destroy(Project $project, User $user)
    {
        $this->authorize('update', $project);
        
        // Prevent removing project creator
        if ($user->id === $project->created_by) {
            return back()->with('error', 'Cannot remove project creator.');
        }
        
        // Remove user from project
        $project->members()->detach($user->id);
        
        // Unassign user from all tasks in this project
        $project->tasks()->where('assigned_to', $user->id)->update(['assigned_to' => null]);
        
        // Log activity
        ActivityLogger::log(
            auth()->id(),
            'project_member_removed',
            "Removed " . $user->name . " from project",
            $project->id
        );
        
        return back()->with('success', 'Member removed successfully.');
    }
    
    public function updateRole(Request $request, Project $project, User $user)
    {
        $this->authorize('update', $project);
        
        $request->validate([
            'role' => 'required|in:viewer,member,lead,manager'
        ]);
        
        // Update user's role in project
        $project->members()->updateExistingPivot($user->id, [
            'role' => $request->role
        ]);
        
        return back()->with('success', 'Member role updated.');
    }
}