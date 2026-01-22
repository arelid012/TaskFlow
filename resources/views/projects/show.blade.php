<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Project Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-100">
                            {{ $project->name }}
                        </h1>
                        <p class="text-gray-400 mt-2">{{ $project->description }}</p>
                    </div>
                    <div class="text-sm text-gray-500">
                        Created by: {{ $project->creator->name ?? 'Unknown' }}
                    </div>
                </div>
                
                <!-- Progress Bar -->
                <div class="mt-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-300">Project Progress</span>
                        <span class="text-sm font-medium text-gray-300">{{ $progress }}%</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-2.5">
                        <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $progress }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-700 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <a href="{{ route('projects.show', $project) }}"
                       class="border-b-2 border-indigo-500 text-indigo-400 px-1 py-3 text-sm font-medium">
                        Tasks
                    </a>
                    <a href="{{ route('projects.activity.page', $project) }}"
                       class="border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300 px-1 py-3 text-sm font-medium">
                        Activity
                    </a>
                    @can('update', $project)
                    <a href="{{ route('projects.edit', $project) }}"
                       class="border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300 px-1 py-3 text-sm font-medium">
                        Settings
                    </a>
                    @endcan
                    @can('update', $project)
                    <a href="{{ route('projects.members.index', $project) }}"
                    class="border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300 px-1 py-3 text-sm font-medium">
                        Members
                    </a>
                    @endcan
                </nav>
            </div>

            <!-- Tasks Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- To Do Column -->
                <div class="bg-gray-800/50 rounded-lg border border-gray-700 p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-200">To Do</h2>
                        <span class="bg-gray-700 text-gray-300 text-xs font-medium px-2.5 py-0.5 rounded">
                            {{ $tasks->where('status', 'todo')->count() }}
                        </span>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($tasks->where('status', 'todo') as $task)
                            <div class="bg-gray-800 border border-gray-700 rounded-lg p-3 hover:border-gray-600 transition-colors">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-medium text-gray-200">{{ $task->title }}</h3>
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
                                    <div class="flex items-center gap-2">
                                        @if($task->assignee)
                                            <span class="text-gray-400">Assigned to: {{ $task->assignee->name }}</span>
                                        @else
                                            <span class="text-gray-500">Unassigned</span>
                                        @endif
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('projects.activity.page', $task->project) }}?highlight={{ $task->id }}"
                                           class="text-indigo-400 hover:text-indigo-300 text-xs">
                                            View Activity →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        @if($tasks->where('status', 'todo')->count() === 0)
                            <div class="text-center py-8 text-gray-500">
                                <div class="text-2xl mb-2">📋</div>
                                <p>No tasks to do</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Doing Column -->
                <div class="bg-gray-800/50 rounded-lg border border-gray-700 p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-200">Doing</h2>
                        <span class="bg-blue-500/20 text-blue-300 text-xs font-medium px-2.5 py-0.5 rounded">
                            {{ $tasks->where('status', 'doing')->count() }}
                        </span>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($tasks->where('status', 'doing') as $task)
                            <div class="bg-gray-800 border border-gray-700 rounded-lg p-3 hover:border-gray-600 transition-colors">
                                <!-- Same task card structure as above -->
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-medium text-gray-200">{{ $task->title }}</h3>
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
                                    <div class="flex items-center gap-2">
                                        @if($task->assignee)
                                            <span class="text-gray-400">Assigned to: {{ $task->assignee->name }}</span>
                                        @else
                                            <span class="text-gray-500">Unassigned</span>
                                        @endif
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('projects.activity.page', $task->project) }}?highlight={{ $task->id }}"
                                            class="text-indigo-400 hover:text-indigo-300 text-xs">
                                            View Activity →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        @if($tasks->where('status', 'doing')->count() === 0)
                            <div class="text-center py-8 text-gray-500">
                                <div class="text-2xl mb-2">🚧</div>
                                <p>No tasks in progress</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Done Column -->
                <div class="bg-gray-800/50 rounded-lg border border-gray-700 p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-200">Done</h2>
                        <span class="bg-green-500/20 text-green-300 text-xs font-medium px-2.5 py-0.5 rounded">
                            {{ $tasks->where('status', 'done')->count() }}
                        </span>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($tasks->where('status', 'done') as $task)
                            <div class="bg-gray-800 border border-gray-700 rounded-lg p-3 hover:border-gray-600 transition-colors opacity-80">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-medium text-gray-200 line-through">{{ $task->title }}</h3>
                                    @if($task->due_date)
                                        <span class="text-xs px-2 py-1 rounded flex items-center gap-1 bg-green-500/20 text-green-400 border border-green-500/30"
                                            title="Completed on: {{ $task->due_date->format('M d, Y') }}">
                                            <span>✅</span>
                                            <span>{{ $task->due_date->format('M d') }}</span>
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2">
                                        @if($task->assignee)
                                            <span class="text-gray-400">Completed by: {{ $task->assignee->name }}</span>
                                        @else
                                            <span class="text-gray-500">Unassigned</span>
                                        @endif
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('projects.activity.page', $task->project) }}?highlight={{ $task->id }}"
                                        class="text-indigo-400 hover:text-indigo-300 text-xs">
                                            View Activity →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        @if($tasks->where('status', 'done')->count() === 0)
                            <div class="text-center py-8 text-gray-500">
                                <div class="text-2xl mb-2">🎉</div>
                                <p>No tasks completed yet</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4">
                    <div class="text-sm text-gray-400">Total Tasks</div>
                    <div class="text-2xl font-semibold text-gray-200">{{ $tasks->count() }}</div>
                </div>
                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4">
                    <div class="text-sm text-gray-400">Overdue</div>
                    <div class="text-2xl font-semibold text-red-400">{{ $tasks->filter(fn($t) => $t->isOverdue())->count() }}</div>
                </div>
                <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-4">
                    <div class="text-sm text-gray-400">Due Soon</div>
                    <div class="text-2xl font-semibold text-yellow-400">{{ $tasks->filter(fn($t) => $t->isDueSoon())->count() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>