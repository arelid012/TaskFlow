<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Add this line

class Task extends Model
{
    protected $fillable = [
        'project_id',
        'assigned_to',
        'title',
        'description',
        'status',
        'due_date',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Add relationship to activity logs if you don't have it
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Status indicator logic
    public function getStatusIndicatorAttribute()
    {
        if (!$this->due_date) return null;

        $now = Carbon::now();
        $dueDate = Carbon::parse($this->due_date);
        
        if ($this->status === 'done') {
            return 'completed';
        }
        
        if ($dueDate->isPast()) {
            return 'overdue';
        }
        
        if ($dueDate->diffInDays($now) <= 2) {
            return 'due_soon';
        }
        
        return 'on_track';
    }

    public function getStatusIndicatorColorAttribute()
    {
        return match($this->status_indicator) {
            'completed' => 'green',
            'on_track'  => 'green',
            'due_soon'  => 'yellow',
            'overdue'   => 'red',
            default     => 'gray',
        };
    }

    public function getStatusIndicatorIconAttribute()
    {
        return match($this->status_indicator) {
            'completed' => '✅',
            'on_track'  => '🟢',
            'due_soon'  => '🟡',
            'overdue'   => '🔴',
            default     => '⚪',
        };
    }

    public function isOverdue()
    {
        return $this->due_date && 
               Carbon::parse($this->due_date)->isPast() && 
               $this->status !== 'done';
    }

    public function isDueSoon()
    {
        if (!$this->due_date || $this->status === 'done') {
            return false;
        }
        
        $dueDate = Carbon::parse($this->due_date);
        return !$dueDate->isPast() && $dueDate->diffInDays(Carbon::now()) <= 2;
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', Carbon::today())
                    ->where('status', '!=', 'done');
    }

    public function scopeDueSoon($query)
    {
        return $query->where('due_date', '>=', Carbon::today())
                    ->where('due_date', '<=', Carbon::today()->addDays(2))
                    ->where('status', '!=', 'done');
    }

    public function scopeWithDueDateStatus($query) // Fixed typo: was "scopelith" should be "scopeWith"
    {
        return $query->addSelect([
            '*',
            DB::raw("CASE 
                WHEN status = 'done' THEN 'completed'
                WHEN due_date IS NULL THEN NULL
                WHEN due_date < CURDATE() THEN 'overdue'
                WHEN due_date <= DATE_ADD(CURDATE(), INTERVAL 2 DAY) THEN 'due_soon'
                ELSE 'on_track'
            END as status_indicator_raw")
        ]);
    }
}