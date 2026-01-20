<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h1 class="text-3xl font-bold text-gray-900">My Tasks</h1>
                <p class="mt-2 text-gray-600">View and manage all tasks assigned to you or created by you</p>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <form method="GET" action="{{ route('tasks.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Search tasks or projects..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Status</option>
                                <option value="todo" {{ request('status') == 'todo' ? 'selected' : '' }}>To Do</option>
                                <option value="doing" {{ request('status') == 'doing' ? 'selected' : '' }}>In Progress</option>
                                <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>

                        <!-- Project Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                            <select name="project" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Projects</option>
                                @foreach($userProjects as $project)
                                    <option value="{{ $project->id }}" {{ request('project') == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Date Range Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date From</label>
                            <input
                                type="date"
                                name="due_from"
                                value="{{ request('due_from') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date To</label>
                            <input
                                type="date"
                                name="due_to"
                                value="{{ request('due_to') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>
                        <div class="flex items-end">
                            <div class="flex space-x-2 w-full">
                                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                                    Apply Filters
                                </button>
                                <a href="{{ route('tasks.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                    Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">{{ $totalCount ?? $tasks->total() }}</div>
                        <div class="text-sm text-gray-600">My Tasks</div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200 rounded-xl p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">
                            {{ $todoCount ?? 0 }}
                        </div>
                        <div class="text-sm text-gray-600">To Do</div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 border border-indigo-200 rounded-xl p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">
                            {{ $doingCount ?? 0 }}
                        </div>
                        <div class="text-sm text-gray-600">In Progress</div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-xl p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">
                            {{ $doneCount ?? 0 }}
                        </div>
                        <div class="text-sm text-gray-600">Completed</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tasks List -->
        @if($tasks->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Table Header -->
                <div class="grid grid-cols-12 gap-4 px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200 text-sm font-medium text-gray-700">
                    <div class="col-span-6">Task & Project</div>
                    <div class="col-span-2">Status</div>
                    <div class="col-span-2">Due Date</div>
                    <div class="col-span-2">Actions</div>
                </div>

                <!-- Tasks -->
                <div class="divide-y divide-gray-100">
                    @foreach($tasks as $task)
                        <div class="grid grid-cols-12 gap-4 px-6 py-4 hover:bg-gray-50 transition-colors duration-150 {{ $task->isOverdue() ? 'bg-red-50 hover:bg-red-100' : '' }}">
                            <!-- Task & Project -->
                            <div class="col-span-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="w-8 h-8 rounded-md bg-gradient-to-br 
                                            {{ $task->isOverdue() ? 'from-red-100 to-red-50' : 'from-indigo-100 to-indigo-50' }} 
                                            flex items-center justify-center">
                                            <svg class="w-4 h-4 {{ $task->isOverdue() ? 'text-red-600' : 'text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                        </div>   
                                    </div>
                                    <div class="ml-3">
                                        <a href="{{ route('projects.activity.page', $task->project) }}?highlight={{ $task->id }}"  class="text-gray-900 font-medium hover:text-indigo-600"> 
                                            {{ $task->title }}
                                        </a>
                                        <div class="flex items-center text-sm text-gray-500 mt-1">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                            </svg>
                                            <span>{{ $task->project->name }}</span>
                                            <!-- Show task relationship to user -->
                                            <span class="ml-2 px-1.5 py-0.5 text-xs rounded 
                                                {{ $task->assigned_to == auth()->id() ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $task->assigned_to == auth()->id() ? 'Assigned' : 'Created' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-span-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $task->status === 'todo' ? 'bg-gray-100 text-gray-800' : '' }}
                                    {{ $task->status === 'doing' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $task->status === 'done' ? 'bg-green-100 text-green-800' : '' }}">
                                    {{ ucfirst($task->status) }}
                                </span>
                            </div>

                            <!-- Due Date -->
                            <div class="col-span-2">
                                @if($task->due_date)
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 {{ $task->isOverdue() ? 'text-red-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-sm {{ $task->isOverdue() ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                                            {{ $task->due_date->format('M d, Y') }}
                                            @if($task->isOverdue())
                                                <span class="text-xs ml-1 px-1 py-0.5 bg-red-100 text-red-800 rounded">Overdue</span>
                                            @elseif($task->isDueSoon())
                                                <span class="text-xs ml-1 px-1 py-0.5 bg-yellow-100 text-yellow-800 rounded">Due Soon</span>
                                            @endif
                                        </span>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400 italic">No due date</span>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="col-span-2">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('projects.activity.page', $task->project) }}?highlight={{ $task->id }}" 
                                       class="text-gray-400 hover:text-indigo-600 p-1 rounded hover:bg-gray-100"
                                       title="View Details">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($tasks->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $tasks->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-medium text-gray-900 mb-2">No tasks found</h3>
                <p class="text-gray-600 mb-6 max-w-md mx-auto">
                    @if(request()->hasAny(['search', 'status', 'project', 'due_from', 'due_to']))
                        Try adjusting your filters to see more results.
                    @else
                        You don't have any tasks assigned to you or created by you yet.
                    @endif
                </p>
                <div class="space-x-3">
                    <a href="{{ route('tasks.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Clear Filters
                    </a>
                    <a href="{{ route('projects.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 border border-transparent rounded-lg font-medium text-white shadow-sm hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                        Go to Projects
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>