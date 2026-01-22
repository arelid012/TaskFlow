@php
    $projectUsers = $users->map(fn ($u) => [
        'id' => $u->id,
        'name' => $u->name,
    ]);
@endphp

@php
    $user = auth()->user();
    $userRoleInProject = $project->getUserRole($user->id);
    $canAssign = Gate::allows('assignTask', $project) || Gate::allows('manage-projects');
@endphp

<x-app-layout>
    <div class="max-w-4xl mx-auto py-8"
        x-data="activityLog({
            projectId: {{ $project->id }},
            users: JSON.parse('{{ $projectUsers->toJson() }}'),
            userRole: '{{ $user->role }}',
            userRoleInProject: '{{ $userRoleInProject }}',
            userId: {{ auth()->id() }},  
            highlightTaskId: {{ $highlightTaskId ?? 'null' }},
            initialPage: {{ $initialPage ?? 1 }}
        })"
        x-init="init()"
        @task-created.window="fetchLogs()">
        
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('projects.show', $project) }}" 
                       class="text-gray-400 hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-100">
                            {{ $project->name }} Activity
                        </h1>
                        <p class="text-sm text-gray-400 mt-1">
                            Complete task management and activity timeline
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <!-- Quick Stats -->
                <div class="hidden md:flex items-center gap-4 text-sm">
                    <div class="text-center">
                        <div class="text-gray-400">Tasks</div>
                        <div class="text-xl font-semibold text-gray-200" x-text="totalTasks || '0'"></div>
                    </div>
                    <div class="text-center">
                        <div class="text-gray-400">Overdue</div>
                        <div class="text-xl font-semibold text-red-400" x-text="overdueTasks || '0'"></div>
                    </div>
                </div>
                
                @can('update', $project)
                <a href="{{ route('projects.members.index', $project) }}" 
                   class="text-sm px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg border border-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-8A8.5 8.5 0 1112 3.5a8.5 8.5 0 018.5 8.5z"></path>
                    </svg>
                    Members
                </a>
                @endcan
            </div>
        </div>

        <!-- Task Creation Card -->
        @if($userRoleInProject !== 'viewer')
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-100">Create New Task</h2>
                    <p class="text-sm text-gray-400">Quickly add a task to this project</p>
                </div>
                <button @click="showTaskForm = !showTaskForm" 
                        class="text-sm text-indigo-400 hover:text-indigo-300">
                    <span x-show="!showTaskForm">Show Form</span>
                    <span x-show="showTaskForm">Hide Form</span>
                </button>
            </div>
            
            <form @submit.prevent="createTask"
                  x-show="showTaskForm"
                  x-transition
                  class="space-y-4 p-4 bg-gray-800/50 rounded-lg border border-gray-700">
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">
                        Task Title *
                    </label>
                    <input
                        type="text"
                        x-model="newTaskTitle"
                        placeholder="What needs to be done?"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        required
                    >
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">
                            Due Date
                        </label>
                        <input
                            type="date"
                            x-model="newTaskDueDate"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                        <p class="text-xs text-gray-500 mt-1">Optional</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">
                            Status
                        </label>
                        <select
                            x-model="newTaskStatus"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="todo">To Do</option>
                            <option value="doing">Doing</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                    
                    @if($canAssign)
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">
                            Assign To
                        </label>
                        <select
                            x-model="newTaskAssignedTo"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">Unassigned</option>
                            @foreach($project->members as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
                
                <div class="pt-2">
                    <button
                        type="submit"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        :disabled="!newTaskTitle.trim()"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create Task
                    </button>
                </div>
            </form>
        </div>
        @else
        <div class="mb-8 p-4 bg-gray-800/50 border border-gray-700 rounded-lg text-center">
            <p class="text-gray-400">📋 Viewers cannot create tasks</p>
        </div>
        @endif

        <!-- Filters -->
        <div class="mb-6 p-4 bg-gray-800/30 rounded-lg border border-gray-700">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-medium text-gray-300">Filter Activity</h3>
                <button @click="filters.action = ''; fetchLogs()" 
                        class="text-xs text-gray-500 hover:text-gray-400">
                    Clear filters
                </button>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <button @click="filters.action = ''; fetchLogs()" 
                        class="px-3 py-1.5 rounded-lg text-sm"
                        :class="filters.action === '' ? 'bg-indigo-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'">
                    All actions
                </button>
                
                <button @click="filters.action = 'task_created'; fetchLogs()" 
                        class="px-3 py-1.5 rounded-lg text-sm flex items-center gap-1"
                        :class="filters.action === 'task_created' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'">
                    <span>➕</span>
                    Created
                </button>
                
                <button @click="filters.action = 'task_status_changed'; fetchLogs()" 
                        class="px-3 py-1.5 rounded-lg text-sm flex items-center gap-1"
                        :class="filters.action === 'task_status_changed' ? 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'">
                    <span>🔄</span>
                    Status
                </button>
                
                <button @click="filters.action = 'task_assigned'; fetchLogs()" 
                        class="px-3 py-1.5 rounded-lg text-sm flex items-center gap-1"
                        :class="filters.action === 'task_assigned' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'">
                    <span>👤</span>
                    Assigned
                </button>
                
                <button @click="filters.action = 'task_due_date_changed'; fetchLogs()" 
                        class="px-3 py-1.5 rounded-lg text-sm flex items-center gap-1"
                        :class="filters.action === 'task_due_date_changed' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'">
                    <span>📅</span>
                    Due Dates
                </button>
                
                <button @click="filters.action = 'task_overdue'; fetchLogs()" 
                        class="px-3 py-1.5 rounded-lg text-sm flex items-center gap-1"
                        :class="filters.action === 'task_overdue' ? 'bg-red-500/20 text-red-300 border border-red-500/30' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'">
                    <span>⚠️</span>
                    Overdue
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <template x-if="loading">
            <div class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-500"></div>
                <p class="text-gray-400 mt-3">Loading activity…</p>
            </div>
        </template>

        <!-- Empty State -->
        <template x-if="!loading && logs.length === 0">
            <div class="text-center text-gray-400 py-12 bg-gray-800/50 rounded-lg border border-gray-700">
                <div class="text-4xl mb-4">📋</div>
                <p class="text-lg font-medium">No activity yet</p>
                <p class="text-sm mt-1">Actions on this project will appear here.</p>
                <p class="text-xs mt-2 text-gray-500">Create a task above to get started!</p>
            </div>
        </template>

        <!-- Activity Timeline -->
        <div class="relative" x-show="!loading && logs.length > 0">
            <!-- Vertical Timeline Line -->
            <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-700/50"></div>
            
            <ul class="space-y-6">
                <template x-for="log in logs" :key="log.id">
                    <li class="relative">
                        <!-- Timeline Dot -->
                        <div class="absolute left-6 transform -translate-x-1/2 z-10">
                            <div class="h-3 w-3 rounded-full"
                                 :class="{
                                    'bg-indigo-500': log.action === 'task_created',
                                    'bg-green-500': log.action === 'task_completed',
                                    'bg-yellow-500': log.action === 'task_status_changed',
                                    'bg-blue-500': log.action === 'task_assigned' || log.action === 'task_reassigned',
                                    'bg-red-500': log.action === 'task_overdue',
                                    'bg-purple-500': log.action === 'task_due_date_changed',
                                    'bg-gray-500': true
                                 }"></div>
                        </div>
                        
                        <!-- Activity Card -->
                        <div class="ml-12 bg-gray-800/50 rounded-xl border border-gray-700 hover:border-gray-600 transition-all duration-200 hover:shadow-lg"
                             :id="'task-' + (log.task?.id || '')">
                            
                            <!-- Card Header -->
                            <div class="p-4 border-b border-gray-700/50">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <!-- User Avatar -->
                                            <div class="h-6 w-6 rounded-full bg-gray-700 flex items-center justify-center">
                                                <span class="text-xs text-gray-300" 
                                                      x-text="(log.user?.name || 'S').charAt(0).toUpperCase()"></span>
                                            </div>
                                            
                                            <!-- User Name -->
                                            <strong class="text-gray-100 font-medium" 
                                                    x-text="log.user?.name || 'System'"></strong>
                                            
                                            <!-- Action -->
                                            <span class="text-xs px-2 py-1 rounded"
                                                  :class="{
                                                    'bg-indigo-500/20 text-indigo-300': log.action === 'task_created',
                                                    'bg-green-500/20 text-green-300': log.action === 'task_completed',
                                                    'bg-yellow-500/20 text-yellow-300': log.action === 'task_status_changed',
                                                    'bg-blue-500/20 text-blue-300': log.action === 'task_assigned' || log.action === 'task_reassigned',
                                                    'bg-red-500/20 text-red-300': log.action === 'task_overdue',
                                                    'bg-purple-500/20 text-purple-300': log.action === 'task_due_date_changed',
                                                    'bg-gray-700 text-gray-300': true
                                                  }"
                                                  x-text="log.action.replace(/_/g, ' ')"></span>
                                        </div>
                                        
                                        <!-- Date & Time -->
                                        <div class="text-xs text-gray-500 flex items-center gap-2">
                                            <span x-text="new Date(log.created_at).toLocaleDateString()"></span>
                                            <span>•</span>
                                            <span x-text="new Date(log.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Task Content -->
                            <template x-if="log.task">
                                <div class="p-4">
                                    <!-- Task Header -->
                                    <div class="flex items-start justify-between mb-3">
                                        <div>
                                            <h4 class="text-lg font-medium text-gray-100 mb-1" 
                                                x-text="log.task.title"></h4>
                                            
                                            <!-- Status Badge -->
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs px-2 py-1 rounded font-medium"
                                                      :class="{
                                                        'bg-gray-700 text-gray-300': log.task.status === 'todo',
                                                        'bg-blue-500/20 text-blue-300 border border-blue-500/30': log.task.status === 'doing',
                                                        'bg-green-500/20 text-green-300 border border-green-500/30': log.task.status === 'done'
                                                      }"
                                                      x-text="log.task.status.charAt(0).toUpperCase() + log.task.status.slice(1)">
                                                </span>
                                                
                                                <!-- Assignee -->
                                                <template x-if="log.task.assigned_to">
                                                    <div class="text-xs text-gray-400 flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                        </svg>
                                                        <span x-text="getUserName(log.task.assigned_to)"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                        
                                        <!-- Due Date -->
                                        <template x-if="log.task.due_date">
                                            <button @click="editDueDate(log.task)"
                                                    x-show="canEditDueDate(log.task)"
                                                    class="text-xs px-3 py-1.5 rounded-lg flex items-center gap-2 hover:opacity-90 transition-opacity"
                                                    :class="{
                                                        'bg-red-500/20 text-red-300 border border-red-500/30': isTaskOverdue(log.task),
                                                        'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30': isTaskDueSoon(log.task),
                                                        'bg-green-500/20 text-green-300 border border-green-500/30': log.task.status === 'done',
                                                        'bg-blue-500/20 text-blue-300 border border-blue-500/30': !isTaskOverdue(log.task) && !isTaskDueSoon(log.task) && log.task.status !== 'done'
                                                    }"
                                                    :title="'Click to edit. Due: ' + new Date(log.task.due_date).toLocaleDateString()">
                                                <span x-text="getTaskStatusIcon(log.task)"></span>
                                                <span x-text="formatDate(log.task.due_date)"></span>
                                            </button>
                                            
                                            <span x-show="!canEditDueDate(log.task)"
                                                  class="text-xs px-3 py-1.5 rounded-lg flex items-center gap-2"
                                                  :class="{
                                                    'bg-red-500/20 text-red-300 border border-red-500/30': isTaskOverdue(log.task),
                                                    'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30': isTaskDueSoon(log.task),
                                                    'bg-green-500/20 text-green-300 border border-green-500/30': log.task.status === 'done',
                                                    'bg-blue-500/20 text-blue-300 border border-blue-500/30': !isTaskOverdue(log.task) && !isTaskDueSoon(log.task) && log.task.status !== 'done'
                                                  }"
                                                  :title="'Due: ' + new Date(log.task.due_date).toLocaleDateString()">
                                                <span x-text="getTaskStatusIcon(log.task)"></span>
                                                <span x-text="formatDate(log.task.due_date)"></span>
                                            </span>
                                        </template>
                                        
                                        <!-- Add Due Date Button -->
                                        <template x-if="!log.task.due_date && canAddDueDate(log.task)">
                                            <button @click="addDueDate(log.task)"
                                                    class="text-xs px-3 py-1.5 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-300 border border-gray-600 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                                Add Due
                                            </button>
                                        </template>
                                    </div>
                                    
                                    <!-- Description -->
                                    <template x-if="log.description">
                                        <div class="text-sm text-gray-400 mb-4 p-3 bg-gray-800/30 rounded-lg">
                                            <span x-text="log.description"></span>
                                        </div>
                                    </template>
                                    
                                    <!-- Quick Actions -->
                                    <template x-if="log.is_latest">
                                        <div class="flex items-center gap-2 pt-4 border-t border-gray-700/50">
                                            <!-- Status Dropdown -->
                                            <template x-if="canChangeStatus(log.task)">
                                                <select @change="updateStatus(log.task.id, $event.target.value)"
                                                        class="text-sm bg-gray-700 border border-gray-600 rounded-lg px-3 py-1.5 text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                    <option value="" disabled selected>
                                                        <span x-text="'Status: ' + log.task.status.charAt(0).toUpperCase() + log.task.status.slice(1)"></span>
                                                    </option>
                                                    <template x-for="option in allowedNextStatuses(log.task.status)" :key="option.value">
                                                        <option :value="option.value" x-text="option.label"></option>
                                                    </template>
                                                </select>
                                            </template>
                                            <template x-if="!canChangeStatus(log.task)">
                                                <span class="text-xs px-2 py-1 rounded bg-gray-700 text-gray-300">
                                                    Status: <span x-text="log.task.status.charAt(0).toUpperCase() + log.task.status.slice(1)"></span>
                                                </span>
                                            </template>
                                            
                                            <!-- Assignment -->
                                            @if($canAssign)
                                            <select @change.debounce.300ms="assignTask(log.task.id, $event.target.value)"
                                                    x-model="log.task.assigned_to"
                                                    class="text-sm bg-gray-700 border border-gray-600 rounded-lg px-3 py-1.5 text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="">Assign...</option>
                                                @foreach($project->members as $member)
                                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                                @endforeach
                                            </select>
                                            @endif
                                            
                                            <!-- Spacer -->
                                            <div class="flex-1"></div>
                                            
                                            <!-- Delete Button -->
                                            <template x-if="canDeleteTask(log.task, log)">
                                                <button @click="deleteTask(log.task.id)"
                                                        class="text-sm px-3 py-1.5 bg-red-600/10 hover:bg-red-600/20 text-red-400 rounded-lg border border-red-600/20 flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            
                            <!-- Non-task activity -->
                            <template x-if="!log.task && log.description">
                                <div class="p-4 text-gray-300">
                                    <span x-text="log.description"></span>
                                </div>
                            </template>
                        </div>
                    </li>
                </template>
            </ul>
        </div>

        <!-- Pagination -->
        <div class="mt-8 flex items-center justify-between" x-show="!loading && logs.length > 0">
            <div class="text-sm text-gray-500">
                Showing <span x-text="logs.length"></span> activity logs
            </div>
            
            <div class="flex gap-2">
                <button @click="prevPage"
                        :disabled="!pagination.prev"
                        class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    ← Previous
                </button>

                <button @click="nextPage"
                        :disabled="!pagination.next"
                        class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    Next →
                </button>
            </div>
        </div>
    </div>
</x-app-layout>