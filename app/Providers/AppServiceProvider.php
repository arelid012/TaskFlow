<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Task;
use App\Models\Project;
use App\Policies\TaskPolicy;
use App\Policies\ProjectPolicy;
use Illuminate\Notifications\DatabaseNotification;
use App\Policies\NotificationPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(DatabaseNotification::class, NotificationPolicy::class);
        
        // Optional: Define additional gates if needed
        Gate::define('manage-projects', function ($user) {
            return in_array($user->role, ['admin', 'manager']);
        });
        
        Gate::define('view-reports', function ($user) {
            return in_array($user->role, ['admin', 'manager', 'lead']);
        });
    }
}