import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('activityLog', ({projectId, users}) => ({
    projectId,
    users,
    logs: [],
    loading: false,
    filters: { action: '' },
    pagination: { next: null, prev: null },
    newTaskTitle: '',

    allowedNextStatuses(current) {
        const map = { todo: [{ value: 'doing', label: 'Move to Doing' }], doing: [{ value: 'done', label: 'Mark as Done' }], done: [] };
        return map[current] ?? [];
    },

    nextPage() { if (!this.pagination.next) return; this.fetchLogs(this.pagination.next); },
    prevPage() { if (!this.pagination.prev) return; this.fetchLogs(this.pagination.prev); },

    async fetchLogs(url = null) {
        this.loading = true;
        const endpoint = url ?? `/projects/${projectId}/activity/logs?action=${this.filters.action}`;
        const response = await fetch(endpoint, { headers: { 'Accept': 'application/json' } });
        const data = await response.json();
        this.logs = data.data ?? [];
        this.pagination.next = data.links?.next ?? null;
        this.pagination.prev = data.links?.prev ?? null;
        this.logs.forEach(log => { if (log.task) log.task.assigned_to = log.task.assigned_to ? parseInt(log.task.assigned_to) : ''; });
        this.loading = false;
    },

    async createTask() {
        if (!this.newTaskTitle.trim()) return;
        await fetch(`/projects/${projectId}/tasks`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ title: this.newTaskTitle })
        });
        this.newTaskTitle = '';
        this.$dispatch('task-created');
    },

    async updateStatus(taskId, status) {
        await fetch(`/tasks/${taskId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status })
        });
        this.$dispatch('task-created');
    },

    async assignTask(taskId, userId) {
        const log = this.logs.find(l => l.task?.id === taskId && l.is_latest);
        if (log) log.task.assigned_to = userId ? parseInt(userId) : '';
        await fetch(`/tasks/${taskId}/assign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ assigned_to: userId })
        });
        // Optional: this.$dispatch('task-created');
    }

}));

Alpine.start();

