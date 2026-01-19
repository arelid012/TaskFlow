<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Welcome Header with Quick Actions -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-100">
                        Dashboard
                    </h1>
                    <p class="text-gray-400 mt-2">
                        Welcome back, {{ auth()->user()->name }}!
                        @if($stats['pending_tasks'] > 0)
                            <span class="ml-2 text-yellow-400">
                                You have {{ $stats['pending_tasks'] }} pending tasks
                            </span>
                        @endif
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('projects.create') }}" 
                       class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">
                        + New Project
                    </a>
                </div>
            </div>

            {{-- <!-- Add Debug Info Section (TEMPORARY) -->
            @if(app()->environment('local'))
            <div class="mb-6 p-4 bg-gray-900 border border-yellow-500/30 rounded-lg">
                <h3 class="text-sm font-semibold text-yellow-400 mb-3">🛠 Debug Info</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-gray-400">User ID: <span class="text-white">{{ auth()->id() }}</span></p>
                        <p class="text-gray-400">Role: <span class="text-white">{{ auth()->user()->role }}</span></p>
                    </div>
                    <div>
                        <p class="text-gray-400">Projects: <span class="text-white">{{ $projects->count() }}</span></p>
                        <p class="text-gray-400">My Tasks: <span class="text-white">{{ $userTasks->count() }}</span></p>
                    </div>
                    <div>
                        <p class="text-gray-400">Overdue: <span class="text-white">{{ $overdueTasks ? $overdueTasks->count() : 0 }}</span></p>
                        <p class="text-gray-400">Due Soon: <span class="text-white">{{ $dueSoonTasks ? $dueSoonTasks->count() : 0 }}</span></p>
                    </div>
                    <div>
                        <p class="text-gray-400">Total Tasks: <span class="text-white">{{ $stats['total_tasks'] }}</span></p>
                        <p class="text-gray-400">Completed: <span class="text-white">{{ $stats['completed_tasks'] }}</span></p>
                    </div>
                </div>
                <!-- Show actual task data -->
                @if($userTasks->count() > 0)
                <div class="mt-3 pt-3 border-t border-gray-700">
                    <p class="text-xs text-gray-400 mb-2">Loaded Tasks:</p>
                    @foreach($userTasks as $task)
                        <p class="text-xs text-gray-300">
                            {{ $task->title }} (Status: {{ $task->status }}, Project: {{ $task->project ? $task->project->name : 'None' }})
                        </p>
                    @endforeach
                </div>
                @endif
            </div>
            @endif --}}

            <!-- Stats Cards with Icons -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 rounded-lg p-4 hover:border-indigo-500/30 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-500/20 rounded-lg">
                            <span class="text-indigo-400">📊</span>
                        </div>
                        <div>
                            <div class="text-sm text-gray-400">Total Projects</div>
                            <div class="text-2xl font-semibold text-gray-200">{{ $stats['total_projects'] }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 rounded-lg p-4 hover:border-yellow-500/30 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-yellow-500/20 rounded-lg">
                            <span class="text-yellow-400">📝</span>
                        </div>
                        <div>
                            <div class="text-sm text-gray-400">Pending Tasks</div>
                            <div class="text-2xl font-semibold text-yellow-400">{{ $stats['pending_tasks'] }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 rounded-lg p-4 hover:border-red-500/30 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-red-500/20 rounded-lg">
                            <span class="text-red-400">⏰</span>
                        </div>
                        <div>
                            <div class="text-sm text-gray-400">Overdue</div>
                            <div class="text-2xl font-semibold text-red-400">{{ $stats['overdue_tasks'] }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 rounded-lg p-4 hover:border-orange-500/30 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-orange-500/20 rounded-lg">
                            <span class="text-orange-400">🔥</span>
                        </div>
                        <div>
                            <div class="text-sm text-gray-400">Due Soon</div>
                            <div class="text-2xl font-semibold text-orange-400">{{ $stats['due_soon_tasks'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add a Progress Summary Section -->
            @if($stats['total_tasks'] > 0)
            <div class="mb-8 bg-gray-800/50 border border-gray-700 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-200 mb-4">Your Progress</h2>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm text-gray-400 mb-1">
                            <span>Task Completion</span>
                            <span>{{ $stats['completed_tasks'] }} of {{ $stats['total_tasks'] }}</span>
                        </div>
                        <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full bg-green-500 rounded-full" 
                                 style="width: {{ $stats['total_tasks'] > 0 ? ($stats['completed_tasks'] / $stats['total_tasks']) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Overdue Progress -->
                    @if($stats['overdue_tasks'] > 0)
                    <div>
                        <div class="flex justify-between text-sm text-gray-400 mb-1">
                            <span>Overdue Tasks</span>
                            <span class="text-red-400">{{ $stats['overdue_tasks'] }} tasks</span>
                        </div>
                        <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full bg-red-500 rounded-full" 
                                 style="width: {{ min(100, ($stats['overdue_tasks'] / $stats['pending_tasks']) * 100) }}%">
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- My Projects with Status -->
                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-200">My Projects</h2>
                            <p class="text-sm text-gray-500 mt-1">Active projects you're involved in</p>
                        </div>
                        <a href="{{ route('projects.index') }}" 
                           class="text-sm text-indigo-400 hover:text-indigo-300 font-medium">
                            View all →
                        </a>
                    </div>
                    
                    @if($projects->count() > 0)
                    <div class="space-y-4">
                        @foreach($projects as $project)
                            <a href="{{ route('projects.show', $project) }}" 
                               class="block group relative bg-gray-800 border border-gray-700 rounded-lg p-4 hover:border-indigo-500/50 transition-all duration-300 hover:bg-gray-750">
                                <!-- Project Health Indicator -->
                                @php
                                    $projectOverdueTaskCount = $project->tasks()
                                        ->where('assigned_to', auth()->id())
                                        ->where('due_date', '<', now())
                                        ->where('status', '!=', 'done')
                                        ->count();
                                @endphp

                                @if($projectOverdueTaskCount > 0)
                                    <span class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                                        {{ $projectOverdueTaskCount }}
                                    </span>
                                @endif
                                
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-medium text-gray-200 group-hover:text-indigo-300 transition-colors">
                                        {{ $project->name }}
                                    </h3>
                                    <span class="text-xs px-2 py-1 rounded bg-gray-700 text-gray-300">
                                        {{ $project->tasks_count ?? 0 }} pending
                                    </span>
                                </div>
                                
                                @if($project->description)
                                    <p class="text-sm text-gray-400 mb-3">{{ Str::limit($project->description, 100) }}</p>
                                @endif
                                
                                <!-- Progress bar for project -->
                                @php
                                    $totalTasks = $project->tasks()->count();
                                    $completedTasks = $project->tasks()->where('status', 'done')->count();
                                    $progress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
                                @endphp
                                
                                <div class="mt-2">
                                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                                        <span>Progress</span>
                                        <span>{{ round($progress) }}%</span>
                                    </div>
                                    <div class="h-1.5 bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $progress }}%"></div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8">
                        <div class="text-4xl mb-3 text-gray-600">📁</div>
                        <p class="text-gray-500">No projects yet</p>
                        <p class="text-sm text-gray-600 mt-1 mb-4">Create or join a project to get started</p>
                        <a href="{{ route('projects.create') }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">
                            <span>+ Create Project</span>
                        </a>
                    </div>
                    @endif
                </div>

                <!-- My Tasks - COMPLETE SECTION (This was missing!) -->
                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-200">My Tasks</h2>
                            <p class="text-sm text-gray-500 mt-1">Tasks assigned to you</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-gray-400">
                                {{ $userTasks->count() }} pending
                            </span>
                            @if($userTasks->count() > 0)
                                <a href="{{ route('tasks.index') }}"
                                   class="text-sm text-indigo-400 hover:text-indigo-300 font-medium">
                                    View all →
                                </a>
                            @endif
                        </div>
                    </div>
                    
                    <!-- TASKS DISPLAY - This was missing from your code! -->
                    <div class="space-y-3">
                        @forelse($userTasks as $task)
                            <a href="{{ route('projects.activity.page', ['project' => $task->project, 'highlight' => $task->id]) }}" 
                               class="block bg-gray-800 border border-gray-700 rounded-lg p-3 hover:border-gray-600 transition-colors hover:bg-gray-750 group cursor-pointer"
                               title="Click to view task details">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-medium text-gray-200 group-hover:text-indigo-300 transition-colors">{{ $task->title }}</h3>
                                    @if($task->due_date)
                                        <span class="text-xs px-2 py-1 rounded flex items-center gap-1
                                            {{ $task->isOverdue() ? 'bg-red-500/20 text-red-400 border border-red-500/30' : 
                                               ($task->isDueSoon() ? 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30' : 
                                               'bg-blue-500/20 text-blue-400 border border-blue-500/30') }}"
                                            title="Due: {{ $task->due_date->format('M d, Y') }}">
                                            <span>{{ $task->status === 'done' ? '✅' : 
                                                   ($task->isOverdue() ? '🔴' : 
                                                   ($task->isDueSoon() ? '🟡' : '🟢')) }}</span>
                                            <span>{{ $task->due_date->format('M d') }}</span>
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400 group-hover:text-gray-300 transition-colors">
                                        {{ $task->project ? $task->project->name : 'No Project' }}
                                    </span>
                                    <span class="text-xs px-2 py-1 rounded
                                        {{ $task->status === 'todo' ? 'bg-gray-700 text-gray-300' : 
                                           ($task->status === 'doing' ? 'bg-blue-500/20 text-blue-300' : 
                                           'bg-green-500/20 text-green-300') }}">
                                        {{ ucfirst($task->status) }}
                                    </span>
                                </div>
                            </a>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <div class="text-3xl mb-3">🎯</div>
                                <p class="text-gray-400 mb-2">No tasks assigned to you</p>
                                <p class="text-sm text-gray-500 mb-4">You're all caught up or haven't been assigned tasks yet.</p>
                                
                                <!-- Helpful suggestions -->
                                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4 max-w-md mx-auto">
                                    <p class="text-sm text-gray-400 mb-2">Suggestions:</p>
                                    <ul class="text-sm text-gray-500 space-y-1">
                                        <li>• Ask your project manager to assign you tasks</li>
                                        <li>• Check if tasks are assigned to you in project activity pages</li>
                                        <li>• Create your own tasks in projects</li>
                                    </ul>
                                </div>
                                
                                <!-- Quick actions -->
                                <div class="mt-6 flex justify-center gap-3">
                                    <a href="{{ route('projects.index') }}" 
                                       class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded-lg text-sm font-medium transition-colors">
                                        Browse Projects
                                    </a>
                                    <a href="{{ route('projects.create') }}" 
                                       class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">
                                        Create Project
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    
                    <!-- Overdue Tasks Warning -->
                    @if($overdueTasks && $overdueTasks->count() > 0)
                        <div class="mt-6 p-4 bg-red-500/10 border border-red-500/30 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="text-red-400 text-xl">⚠️</span>
                                <div>
                                    <h3 class="font-medium text-red-300">Overdue Tasks</h3>
                                    <p class="text-sm text-red-400/80 mt-1">
                                        You have {{ $overdueTasks->count() }} overdue task(s). 
                                        @if($overdueTasks->first() && $overdueTasks->first()->project)
                                            <a href="{{ route('projects.activity.page', ['project' => $overdueTasks->first()->project, 'highlight' => $overdueTasks->first()->id]) }}" 
                                               class="underline hover:text-red-300">
                                                Check them now
                                            </a>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Due Soon Warning -->
                    @if($dueSoonTasks && $dueSoonTasks->count() > 0)
                        <div class="mt-4 p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="text-yellow-400 text-xl">⏰</span>
                                <div>
                                    <h3 class="font-medium text-yellow-300">Tasks Due Soon</h3>
                                    <p class="text-sm text-yellow-400/80 mt-1">
                                        {{ $dueSoonTasks->count() }} task(s) due in the next 2 days.
                                        @if($dueSoonTasks->first())
                                            <a href="{{ route('projects.activity.page', ['project' => $dueSoonTasks->first()->project, 'highlight' => $dueSoonTasks->first()->id]) }}" 
                                               class="underline hover:text-yellow-300">
                                                View first due task
                                            </a>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- All Tasks by Project -->
            @if($tasksByProject && $tasksByProject->count() > 0)
            <div class="mt-8 bg-gray-800/50 border border-gray-700 rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-200">All Tasks by Project</h2>
                    <p class="text-sm text-gray-500">Click any task to view details</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($tasksByProject as $projectName => $tasks)
                        <div class="bg-gray-800 border border-gray-700 rounded-lg p-4 hover:border-gray-600 transition-colors">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-medium text-gray-200">{{ $projectName }}</h3>
                                <span class="text-xs px-2 py-1 rounded bg-gray-700 text-gray-300">
                                    {{ $tasks->count() }} tasks
                                </span>
                            </div>
                            <div class="space-y-2">
                                @foreach($tasks as $task)
                                    <!-- FIXED LINK -->
                                    <a href="{{ route('projects.activity.page', ['project' => $task->project, 'highlight' => $task->id]) }}" 
                                       class="flex items-center justify-between p-2 bg-gray-700/50 rounded hover:bg-gray-600/50 transition-colors group cursor-pointer">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm text-gray-300 group-hover:text-indigo-300">{{ $task->title }}</span>
                                            @if($task->isOverdue())
                                                <span class="text-xs text-red-400">⚠️</span>
                                            @endif
                                        </div>
                                        <span class="text-xs px-2 py-1 rounded
                                            {{ $task->status === 'todo' ? 'bg-gray-600 text-gray-300' : 
                                               ($task->status === 'doing' ? 'bg-blue-500/20 text-blue-300' : 
                                               'bg-green-500/20 text-green-300') }}">
                                            {{ $task->status }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>