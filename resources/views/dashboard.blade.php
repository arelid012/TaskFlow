<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Welcome Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-semibold text-gray-100">
                    Dashboard
                </h1>
                <p class="text-gray-400 mt-2">
                    Welcome back, {{ auth()->user()->name }}!
                </p>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4">
                    <div class="text-sm text-gray-400">Projects</div>
                    <div class="text-2xl font-semibold text-gray-200">{{ $stats['total_projects'] }}</div>
                </div>
                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4">
                    <div class="text-sm text-gray-400">Pending Tasks</div>
                    <div class="text-2xl font-semibold text-yellow-400">{{ $stats['pending_tasks'] }}</div>
                </div>
                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4">
                    <div class="text-sm text-gray-400">Overdue</div>
                    <div class="text-2xl font-semibold text-red-400">{{ $stats['overdue_tasks'] }}</div>
                </div>
                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4">
                    <div class="text-sm text-gray-400">Due Soon</div>
                    <div class="text-2xl font-semibold text-orange-400">{{ $stats['due_soon_tasks'] }}</div>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- My Projects -->
                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-200">My Projects</h2>
                        <a href="{{ route('projects.index') }}" class="text-sm text-indigo-400 hover:text-indigo-300">
                            View all →
                        </a>
                    </div>
                    
                    <div class="space-y-4">
                        @forelse($projects as $project)
                            <a href="{{ route('projects.show', $project) }}" 
                               class="block bg-gray-800 border border-gray-700 rounded-lg p-4 hover:border-gray-600 transition-colors hover:bg-gray-750">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-medium text-gray-200">{{ $project->name }}</h3>
                                    <span class="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded">
                                        {{ $project->pending_tasks_count ?? 0 }} pending
                                    </span>
                                </div>
                                @if($project->description)
                                    <p class="text-sm text-gray-400">{{ Str::limit($project->description, 100) }}</p>
                                @endif
                            </a>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <div class="text-3xl mb-3">📁</div>
                                <p>No projects yet</p>
                                <a href="{{ route('projects.create') }}" class="text-indigo-400 hover:text-indigo-300 text-sm mt-2 inline-block">
                                    Create your first project →
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- My Tasks -->
                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-200">My Tasks</h2>
                        <span class="text-sm text-gray-400">
                            {{ $userTasks->count() }} pending
                        </span>
                    </div>
                    
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
                                        {{ $task->project->name }}
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
                                <div class="text-3xl mb-3">🎉</div>
                                <p>No tasks assigned to you</p>
                                <p class="text-sm mt-1">Enjoy your free time!</p>
                            </div>
                        @endforelse
                    </div>
                    
                    <!-- Overdue Tasks Warning -->
                    @if($overdueTasks->count() > 0)
                        <div class="mt-6 p-4 bg-red-500/10 border border-red-500/30 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="text-red-400 text-xl">⚠️</span>
                                <div>
                                    <h3 class="font-medium text-red-300">Overdue Tasks</h3>
                                    <p class="text-sm text-red-400/80 mt-1">
                                        You have {{ $overdueTasks->count() }} overdue task(s). 
                                        <a href="{{ route('projects.activity.page', $overdueTasks->first()->project) }}" 
                                           class="underline hover:text-red-300">
                                            Check them now
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Due Soon Warning -->
                    @if($dueSoonTasks->count() > 0)
                        <div class="mt-4 p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="text-yellow-400 text-xl">⏰</span>
                                <div>
                                    <h3 class="font-medium text-yellow-300">Tasks Due Soon</h3>
                                    <p class="text-sm text-yellow-400/80 mt-1">
                                        {{ $dueSoonTasks->count() }} task(s) due in the next 2 days.
                                        <a href="{{ route('tasks.show', $dueSoonTasks->first()) }}" 
                                           class="underline hover:text-yellow-300">
                                            View first due task
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- All Tasks by Project (Your Original View) -->
            @if($tasksByProject->count() > 0)
            <div class="mt-8 bg-gray-800/50 border border-gray-700 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-200 mb-6">All Tasks by Project</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($tasksByProject as $projectName => $tasks)
                        <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
                            <h3 class="font-medium text-gray-200 mb-3">{{ $projectName }}</h3>
                            <div class="space-y-2">
                                @foreach($tasks as $task)
                                    <a href="{{ route('tasks.show', $task) }}" 
                                       class="flex items-center justify-between p-2 bg-gray-700/50 rounded hover:bg-gray-600/50 transition-colors group cursor-pointer">
                                        <span class="text-sm text-gray-300 group-hover:text-indigo-300">{{ $task->title }}</span>
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