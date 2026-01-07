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
    newTaskDueDate: '',       // Add this
    newTaskAssignedTo: '',    // Add this
    newTaskStatus: 'todo',    // Add this (default value)

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
        
        this.loading = false;
    },

    async createTask() {
        if (!this.newTaskTitle.trim()) return;
        
        // Prepare task data with new fields
        const taskData = {
            title: this.newTaskTitle,
            due_date: this.newTaskDueDate || null,
            assigned_to: this.newTaskAssignedTo || null,
            status: this.newTaskStatus
        };
        
        console.log('Creating task with:', taskData);
        
        try {
            const response = await fetch(`/projects/${projectId}/tasks`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(taskData)
            });
            
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || 'Failed to create task');
            }
            
            // Reset form
            this.newTaskTitle = '';
            this.newTaskDueDate = '';
            this.newTaskAssignedTo = '';
            this.newTaskStatus = 'todo';
            
            // Refresh activity logs
            this.$dispatch('task-created');
            await this.fetchLogs();
            
        } catch (error) {
            console.error('Error creating task:', error);
            alert('Failed to create task: ' + error.message);
        }
    },

    async updateStatus(taskId, status) {
        try {
            const response = await fetch(`/tasks/${taskId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ status })
            });
            
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || 'Failed to update status');
            }
            
            this.$dispatch('task-created');
            await this.fetchLogs();
            
        } catch (error) {
            console.error('Error updating status:', error);
            alert('Failed to update status: ' + error.message);
        }
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
    },

    // Helper methods for task status indicators
    isTaskOverdue(task) {
        if (!task.due_date || task.status === 'done') return false;
        const dueDate = new Date(task.due_date);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return dueDate < today;
    },
    
    isTaskDueSoon(task) {
        if (!task.due_date || task.status === 'done') return false;
        const dueDate = new Date(task.due_date);
        const today = new Date();
        const twoDaysLater = new Date(today);
        twoDaysLater.setDate(today.getDate() + 2);
        
        today.setHours(0, 0, 0, 0);
        twoDaysLater.setHours(23, 59, 59, 999);
        
        return dueDate >= today && dueDate <= twoDaysLater;
    },
    
    getTaskStatusIcon(task) {
        if (!task.due_date) return '';
        if (task.status === 'done') return '✅';
        if (this.isTaskOverdue(task)) return '🔴';
        if (this.isTaskDueSoon(task)) return '🟡';
        return '🟢';
    },
    
    formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric' 
        });
    }
}));

Alpine.start();