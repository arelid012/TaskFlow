<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProjectMemberController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-logging', function() {
    \Illuminate\Support\Facades\Log::info('TEST LOG MESSAGE - If you see this, logging works!');
    return "Check storage/logs/laravel.log";
});

Route::middleware('auth')->group(function () {
    // Profile (accessible even if not verified)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])
    ->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
   

    // Email Verification Routes
    Route::get('/verify-email', [\App\Http\Controllers\Auth\EmailVerificationPromptController::class, '__invoke'])
        ->name('verification.notice');
    
    Route::get('/verify-email/{id}/{hash}', [\App\Http\Controllers\Auth\VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    
    Route::post('/email/verification-notification', [\App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

// Routes that REQUIRE email verification
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Projects
    Route::resource('projects', ProjectController::class)->except(['show']);
    Route::get('/projects/{project}', [ProjectController::class, 'show'])
        ->name('projects.show');

    // Admin
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');
    });

    // Tasks (project-scoped)
    Route::prefix('projects/{project}')->group(function () {
        Route::get('/tasks', [TaskController::class, 'index'])
            ->name('projects.tasks.index');

        Route::post('/tasks', [TaskController::class, 'store'])
            ->name('projects.tasks.store');

        Route::get('/kanban', [TaskController::class, 'kanban'])
            ->name('projects.kanban');
    });

    // Tasks (global)
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])
        ->name('tasks.update');

    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])
        ->name('tasks.destroy');

    Route::post('/tasks/{task}/assign', [TaskController::class, 'assign'])
        ->name('tasks.assign');

    Route::post('/tasks/{task}/due-date', [TaskController::class, 'updateDueDate'])
    ->name('tasks.update-due-date');

    // CHANGE ONLY THIS LINE in web.php:
    Route::get('/tasks/{task}', function (App\Models\Task $task) {
        // Add ?highlight=task_id instead of #task-id
        return redirect()->route('projects.activity.page', $task->project) . '?highlight=' . $task->id;
    })->name('tasks.show');

    // Project Activity
    Route::get('/projects/{project}/activity/logs', [ProjectActivityController::class, 'index'])
        ->name('projects.activity.index');

    Route::get('/projects/{project}/activity', [ProjectActivityController::class, 'page'])
        ->name('projects.activity.page');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
        
    Route::post('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.mark-as-read');
        
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.mark-all-read');
        
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])
        ->name('notifications.destroy');
        
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->name('notifications.unread-count');

    // Project member management
    Route::prefix('projects/{project}')->group(function () {
        Route::get('/members', [ProjectMemberController::class, 'index'])
            ->name('projects.members.index');
            
        Route::post('/members', [ProjectMemberController::class, 'store'])
            ->name('projects.members.store');
            
        Route::delete('/members/{user}', [ProjectMemberController::class, 'destroy'])
            ->name('projects.members.destroy');
            
        Route::put('/members/{user}/role', [ProjectMemberController::class, 'updateRole'])
            ->name('projects.members.update-role');
    });
});

require __DIR__.'/auth.php';