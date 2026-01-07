<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectActivityController;
use App\Http\Controllers\DashboardController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Projects
    Route::resource('projects', ProjectController::class);

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

    Route::post('/tasks/{task}/assign', [TaskController::class, 'assign'])
        ->name('tasks.assign');

    // Project Activity
    Route::get('/projects/{project}/activity/logs', [ProjectActivityController::class, 'index'])
        ->name('projects.activity.index');

    Route::get('/projects/{project}/activity', [ProjectActivityController::class, 'page'])
        ->name('projects.activity.page');

    
});



require __DIR__.'/auth.php';
