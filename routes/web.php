<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectActivityController;



Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Projects (ALL authenticated users)
    Route::resource('projects', ProjectController::class);

    // Admin-only routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');
    });

    // task controller routes
    Route::get('/projects/{project}/tasks', [TaskController::class, 'index']);
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
    Route::patch('/tasks/{task}', [TaskController::class, 'update']);

    Route::patch('/tasks/{task}', [TaskController::class, 'update']);
    Route::patch('/tasks/{task}/assign', [TaskController::class, 'assign']);
    Route::get('/projects/{project}/kanban', [TaskController::class, 'kanban']);
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])
    ->middleware('auth')
    ->name('projects.tasks.store');


    // Activity page (Blade)
    Route::get(
        '/projects/{project}/activity',
        [ProjectActivityController::class, 'page']
    )->name('projects.activity.page');

    // Activity data (JSON)
    Route::get(
        '/projects/{project}/activity/logs',
        [ProjectActivityController::class, 'index']
    )->middleware('auth');
    });



require __DIR__.'/auth.php';
