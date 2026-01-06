@php
    $projectUsers = $project->users->map(fn ($u) => [
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

        <form @submit.prevent="createTask"
            class="flex gap-2 mb-6">

            <input
                type="text"
                x-model="newTaskTitle"
                placeholder="New task title"
                class="flex-1 border rounded px-3 py-2"
                required
            >

            <button
                class="px-4 py-2 bg-indigo-600 text-white rounded">
                Add
            </button>
        </form>

        <h1 class="text-2xl font-semibold tracking-tight">
            <p class="text-sm text-gray-400">
                Project activity timeline
            </p>
            Activity — {{ $project->name }}
        </h1>

        <!-- Filters -->
        <div class="mb-4 flex gap-2">
            <select x-model="filters.action"
                    @change="fetchLogs()"
                    class="border rounded px-3 py-2">
                <option value="">All actions</option>
                <option value="task_created">Task created</option>
                <option value="task_status_changed">Status changed</option>
            </select>
        </div>

        <!-- Loading -->
        <template x-if="loading">
            <p class="text-gray-500">Loading activity…</p>
        </template>

        <!-- Empty -->
        <template x-if="!loading && logs.length === 0">
            <div class="text-center text-gray-400 py-12">
                <p class="text-lg">No activity yet</p>
                <p class="text-sm">Actions on this project will appear here.</p>
            </div>
        </template>

        <!-- Activity list -->
        <ul class="space-y-3">
            <template x-for="log in logs" :key="log.id">
                <li class="relative pl-4 border-l border-gray-700">
                    <span
                        class="absolute -left-1.5 top-2 h-3 w-3 rounded-full bg-indigo-500">
                    </span>

                    <div class="text-sm text-gray-700 flex items-center gap-2 flex-wrap">
                        <strong x-text="log.user?.name ?? 'System'"></strong>

                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                            :class="{
                                'bg-indigo-100 text-indigo-800': log.action === 'task_created',
                                'bg-green-100 text-green-800': log.action === 'task_completed',
                                'bg-yellow-100 text-yellow-800': log.action === 'task_status_changed'
                            }"
                        >
                            <template x-if="log.action === 'task_status_changed'">
                                <span
                                    x-show="log.meta?.from && log.meta?.to"
                                    x-text="`status changed (${log.meta?.from} → ${log.meta?.to})`">
                                </span>

                                <span x-show="!log.meta">
                                    status changed
                                </span>
                            </template>

                            <template x-if="log.action !== 'task_status_changed'">
                                <span x-text="log.action.replace('_', ' ')"></span>
                            </template>
                        </span>

                        <template x-if="log.task">
                            <span>
                                — <em x-text="log.task.title"></em>
                            </span>
                        </template>
                    </div>

                    <!-- Description (human-readable explanation) -->
                    <template x-if="log.description">
                        <div class="ml-6 text-sm text-gray-600">
                            <span x-text="log.description"></span>
                        </div>
                    </template>

                    <!-- assignment & Status dropdown -->
                    <template x-if="log.is_latest && log.task">
                        <div class="flex items-center gap-2 mt-1">

                            <!-- Status -->
                            <template x-if="log.task.status">
                                <select
                                    class="border rounded px-2 py-1 text-xs bg-white"
                                    @change="updateStatus(log.task.id, $event.target.value)"
                                >
                                    <option disabled selected>
                                        Current: <span x-text="log.task.status"></span>
                                    </option>

                                    <template
                                        x-for="option in allowedNextStatuses(log.task.status)"
                                        :key="option.value"
                                    >
                                        <option :value="option.value" x-text="option.label"></option>
                                    </template>
                                </select>
                            </template>

                            <!-- Assignment -->
                            @can('assign', App\Models\Task::class)
                            <select
                                class="border rounded px-2 py-1 text-xs bg-white"
                                @change="assignTask(log.task.id, $event.target.value)"
                                x-model="log.task.assigned_to"
                            >
                                <option value="">Unassigned</option>

                                <template x-for="user in users" :key="user.id">
                                    <option :value="user.id" x-text="user.name"></option>
                                </template>
                            </select>
                            @endcan

                        </div>
                    </template>

                    <div class="text-xs text-gray-400"
                        x-text="log.created_at">
                    </div>
                </li>
            </template>
        </ul>

        <!-- Pagination -->
        <div class="mt-6 flex gap-2">
            <button @click="prevPage"
                    :disabled="!pagination.prev"
                    class="px-3 py-1 border rounded disabled:opacity-50">
                Previous
            </button>

            <button @click="nextPage"
                    :disabled="!pagination.next"
                    class="px-3 py-1 border rounded disabled:opacity-50">
                Next
            </button>
        </div>
    </div>


</x-app-layout>
