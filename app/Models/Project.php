<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user');
    }
    
    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'project_user');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function progress(): int
    {
        $total = $this->tasks()->count();

        if ($total === 0) {
            return 0;
        }

        $done = $this->tasks()->where('status', 'done')->count();

        return (int) round(($done / $total) * 100);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function isMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function userCan(User $user, string $ability): bool
    {
        $role = $this->getUserRole($user);
        
        $permissions = [
            'viewer' => ['view'],
            'member' => ['view', 'create_task', 'update_own_tasks'],
            'lead'   => ['view', 'create_task', 'update_tasks', 'assign_tasks'],
            'manager'=> ['view', 'create_task', 'update_tasks', 'assign_tasks', 'manage_members'],
            'owner'  => ['view', 'create_task', 'update_tasks', 'assign_tasks', 'manage_members', 'delete_project'],
        ];
        
        return in_array($ability, $permissions[$role] ?? []);
    }

    public function getUserRole($userId)
    {
        // 1. First check if user is the project owner
        if ($this->created_by == $userId) {
            return 'owner';
        }
        
        // 2. If not owner, check pivot table for project membership
        return $this->members()
            ->where('user_id', $userId)
            ->first()
            ?->pivot
            ?->role ?? null; // Returns: member, viewer, lead, manager, or null
    }
}
