@php
    $projectUsers = $users->map(fn ($u) => [
        'id' => $u->id,
        'name' => $u->name,
    ]);
@endphp

<x-app-layout>
    <div class="max-w-3xl mx-auto py-6 space-y-6"
        x-data="activityLog({
        projectId: {{ $project->id }},
        users: JSON.parse('{{ $projectUsers->toJson() }}')
        })"
        x-init="fetchLogs()"
        @task-created.window="fetchLogs()">

        <!-- Task Creation Form -->
        <form @submit.prevent="createTask"
            class="space-y-4 mb-6 p-4 bg-gray-800 rounded-lg border border-gray-700">
            
            <div>
                <label for="title" class="block text-sm font-medium text-gray-300 mb-1">
                    Task Title *
                </label>
                <input
                    type="text"
                    x-model="newTaskTitle"
                    placeholder="Enter task title"
                    class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    required
                >
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-300 mb-1">
                        Due Date (Optional)
                    </label>
                    <input
                        type="date"
                        x-model="newTaskDueDate"
                        class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        min="{{ date('Y-m-d') }}"
                    >
                    <p class="text-xs text-gray-500 mt-1">Leave empty for no deadline</p>
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-300 mb-1">
                        Status
                    </label>
                    <select
                        x-model="newTaskStatus"
                        class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="todo">To Do</option>
                        <option value="doing">Doing</option>
                        <option value="done">Done</option>
                    </select>
                </div>
            </div>
            
            <div>
                <label for="assigned_to" class="block text-sm font-medium text-gray-300 mb-1">
                    Assign To (Optional)
                </label>
                <select
                    x-model="newTaskAssignedTo"
                    class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="">Unassigned</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <button
                type="submit"
                class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded transition disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="!newTaskTitle.trim()"
            >
                Add Task
            </button>
        </form>

        <!-- Page Header -->
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-100">
                Activity — {{ $project->name }}
            </h1>
            <p class="text-sm text-gray-400 mt-1">
                Project activity timeline with task deadlines
            </p>
        </div>

        <!-- Filters -->
        <div class="mb-4 flex gap-2">
            <select x-model="filters.action"
                    @change="fetchLogs()"
                    class="bg-gray-800 border border-gray-700 rounded px-3 py-2 text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All actions</option>
                <option value="task_created">Task created</option>
                <option value="task_status_changed">Status changed</option>
                <option value="task_assigned">Task assigned</option>
                <option value="task_reassigned">Task reassigned</option>
                <option value="task_due_date_changed">Due date changed</option>
                <option value="task_overdue">Task overdue</option>
            </select>
        </div>

        <!-- Loading State -->
        <template x-if="loading">
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500"></div>
                <p class="text-gray-400 mt-2">Loading activity…</p>
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

        <!-- Activity List -->
        <ul class="space-y-4" x-show="!loading && logs.length > 0">
            <template x-for="log in logs" :key="log.id">
                <li class="relative pl-6 pb-4 border-l border-gray-700 hover:border-indigo-500 transition-colors duration-200">
                    <!-- Timeline Dot -->
                    <span class="absolute -left-2 top-0 h-4 w-4 rounded-full bg-indigo-500 border-2 border-gray-900"></span>
                    
                    <!-- Activity Content -->
                    <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700 hover:border-gray-600 transition-colors">
                        <!-- Header Row -->
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center gap-2 flex-wrap">
                                <!-- User -->
                                <strong class="text-gray-100 font-medium" x-text="log.user?.name ?? 'System'"></strong>
                                
                                <!-- Action Badge -->
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                                    :class="{
                                        'bg-indigo-500/20 text-indigo-300': log.action === 'task_created',
                                        'bg-green-500/20 text-green-300': log.action === 'task_completed',
                                        'bg-yellow-500/20 text-yellow-300': log.action === 'task_status_changed',
                                        'bg-blue-500/20 text-blue-300': log.action === 'task_assigned' || log.action === 'task_reassigned',
                                        'bg-red-500/20 text-red-300': log.action === 'task_overdue',
                                        'bg-purple-500/20 text-purple-300': log.action === 'task_due_date_changed',
                                        'bg-gray-700 text-gray-300': true
                                    }"
                                >
                                    <template x-if="log.action === 'task_status_changed'">
                                        <span
                                            x-show="log.meta?.from && log.meta?.to"
                                            x-text="`${log.meta?.from} → ${log.meta?.to}`">
                                        </span>
                                        <span x-show="!log.meta">
                                            status changed
                                        </span>
                                    </template>
                                    <template x-if="log.action !== 'task_status_changed'">
                                        <span x-text="log.action.replace('_', ' ')"></span>
                                    </template>
                                </span>
                            </div>
                            
                            <!-- Date -->
                            <span class="text-xs text-gray-500" x-text="new Date(log.created_at).toLocaleDateString()"></span>
                        </div>
                        
                        <!-- Task Info -->
                        <template x-if="log.task">
                            <div class="mt-3">
                                <!-- Task Title & Due Date -->
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-200 font-medium" x-text="log.task.title"></span>
                                        
                                        <!-- Task Status Badge -->
                                        <span class="text-xs px-2 py-1 rounded"
                                            :class="{
                                                'bg-gray-700 text-gray-300': log.task.status === 'todo',
                                                'bg-blue-500/20 text-blue-300': log.task.status === 'doing',
                                                'bg-green-500/20 text-green-300': log.task.status === 'done'
                                            }"
                                            x-text="log.task.status.charAt(0).toUpperCase() + log.task.status.slice(1)">
                                        </span>
                                    </div>
                                    
                                    <!-- Due Date Indicator -->
                                    <template x-if="log.task.due_date">
                                        <span class="text-xs px-2 py-1 rounded flex items-center gap-1"
                                            :class="{
                                                'bg-red-500/20 text-red-400 border border-red-500/30': isTaskOverdue(log.task),
                                                'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30': isTaskDueSoon(log.task),
                                                'bg-green-500/20 text-green-400 border border-green-500/30': log.task.status === 'done',
                                                'bg-gray-700 text-gray-300 border border-gray-600': !isTaskOverdue(log.task) && !isTaskDueSoon(log.task) && log.task.status !== 'done'
                                            }"
                                            :title="'Due: ' + new Date(log.task.due_date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })"
                                        >
                                            <span x-text="getTaskStatusIcon(log.task)"></span>
                                            <span x-text="formatDate(log.task.due_date)"></span>
                                        </span>
                                    </template>
                                </div>
                                
                                <!-- Description -->
                                <template x-if="log.description">
                                    <div class="ml-2 text-sm text-gray-400 border-l-2 border-gray-700 pl-3 py-1">
                                        <span x-text="log.description"></span>
                                    </div>
                                </template>
                                
                                <!-- Task Controls (for latest log) -->
                                <template x-if="log.is_latest">
                                    <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-700">
                                        <!-- Status Dropdown -->
                                        <template x-if="log.task.status">
                                            <select
                                                class="bg-gray-700 border border-gray-600 rounded px-2 py-1.5 text-xs text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                @change="updateStatus(log.task.id, $event.target.value)"
                                            >
                                                <option value="" disabled selected>
                                                    Status: <span x-text="log.task.status"></span>
                                                </option>
                                                <template
                                                    x-for="option in allowedNextStatuses(log.task.status)"
                                                    :key="option.value"
                                                >
                                                    <option :value="option.value" x-text="option.label"></option>
                                                </template>
                                            </select>
                                        </template>

                                        <!-- Assignment Dropdown -->
                                        @can('assign', App\Models\Task::class)
                                        <select
                                            class="bg-gray-700 border border-gray-600 rounded px-2 py-1.5 text-xs text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                            @change.debounce.300ms="assignTask(log.task.id, $event.target.value)"
                                            x-model="log.task.assigned_to"
                                        >
                                            <option value="">Unassigned</option>
                                            <template x-for="user in users" :key="user.id">
                                                <option :value="user.id" x-text="user.name"></option>
                                            </template>
                                        </select>
                                        @endcan
                                        
                                        <!-- Quick Actions -->
                                        <div class="flex-1"></div>
                                        <a :href="'/projects/' + projectId" class="text-xs text-indigo-400 hover:text-indigo-300 hover:underline">
                                            View Project →
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </template>
                        
                        <!-- Non-task activity description -->
                        <template x-if="!log.task && log.description">
                            <div class="text-sm text-gray-300 mt-2">
                                <span x-text="log.description"></span>
                            </div>
                        </template>
                    </div>
                </li>
            </template>
        </ul>

        <!-- Pagination -->
        <div class="mt-8 flex items-center justify-between" x-show="!loading && logs.length > 0">
            <div class="text-sm text-gray-500">
                Showing <span x-text="logs.length"></span> activity logs
            </div>
            
            <div class="flex gap-2">
                <button @click="prevPage"
                        :disabled="!pagination.prev"
                        class="px-4 py-2 bg-gray-800 border border-gray-700 rounded text-gray-300 hover:bg-gray-700 hover:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    ← Previous
                </button>

                <button @click="nextPage"
                        :disabled="!pagination.next"
                        class="px-4 py-2 bg-gray-800 border border-gray-700 rounded text-gray-300 hover:bg-gray-700 hover:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    Next →
                </button>
            </div>
        </div>
    </div>
</x-app-layout>