<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $tasksByProject = Task::with('project')
            ->where('assigned_to', auth()->id())
            ->get()
            ->groupBy(fn ($task) => $task->project->name);

        return view('dashboard', compact('tasksByProject'));
    }
}
