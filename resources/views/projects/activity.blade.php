<x-app-layout>
    <div class="max-w-3xl mx-auto py-6 space-y-6"
        x-data="activityLog({{ $project->id }})"
        x-init="fetchLogs()">

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
                <option value="task_completed">Task completed</option>
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
                    <span class="absolute -left-1.5 top-2 h-3 w-3 rounded-full bg-indigo-500"></span>
                    <div class="text-sm text-gray-700">
                        <strong x-text="log.user?.name ?? 'System'"></strong>
                        <span x-text="log.action.replace('_', ' ')"></span>

                        <template x-if="log.task">
                            <span>
                                — <em x-text="log.task.title"></em>
                            </span>
                        </template>
                    </div>

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

    <!-- Alpine logic -->
    <script>
            function activityLog(projectId) {
        return {
            logs: [],
            loading: false,
            filters: { action: '' },
            pagination: {
                next: null,
                prev: null
            },

            async fetchLogs(url = null) {
                this.loading = true;

                const endpoint = url ??
                    `/projects/${projectId}/activity/logs?action=${this.filters.action}`;

                const response = await fetch(endpoint, {
                    headers: { 'Accept': 'application/json' }
                });

                const data = await response.json();

                this.logs = data.data ?? [];

                this.pagination.next = data.links?.next ?? null;
                this.pagination.prev = data.links?.prev ?? null;

                this.loading = false;
            }
        }
    }


    </script>
</x-app-layout>
