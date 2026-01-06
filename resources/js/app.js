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

    init() {
        console.log('Component initialized');
        console.log('Project ID:', this.projectId);
        console.log('Users:', this.users);
        console.log('First user:', this.users[0]);
        console.log('Type of user ID:', typeof this.users[0]?.id);
        this.fetchLogs();
    },

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
    
    // DEBUG: Check raw data for log 91
    const log91 = data.data?.find(log => log.id === 91);
    console.log('RAW log 91 from API:', log91);
    console.log('RAW log 91 task:', log91?.task);
    console.log('RAW log 91 assigned_to:', log91?.task?.assigned_to);
    console.log('Type of assigned_to:', typeof log91?.task?.assigned_to);
    
    this.logs = data.data ?? [];
    this.pagination.next = data.links?.next ?? null;
    this.pagination.prev = data.links?.prev ?? null;
    
    // Remove the conversion code temporarily
    // this.logs.forEach(log => {
    //     if (log.task && log.task.assigned_to !== null && log.task.assigned_to !== undefined) {
    //         log.task.assigned_to = log.task.assigned_to.toString();
    //     }
    // });
    
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
    try {
        console.log('assignTask: userId =', userId, 'type:', typeof userId);
        
        // Convert empty string to null, otherwise keep as number
        const assignedTo = userId === '' ? null : Number(userId);
        
        const response = await fetch(`/tasks/${taskId}/assign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ assigned_to: assignedTo })
        });
        
        const data = await response.json();
        console.log('Assignment response:', data);
        
        if (data.success) {
            await this.fetchLogs();
        }
    } catch (error) {
        console.error('Assignment failed:', error);
        this.fetchLogs();
    }
}

}));

Alpine.start();

