import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('activityLog', (projectId) => ({
    logs: [],
    loading: false,
    filters: { action: '' },
    pagination: { next: null, prev: null },

    async fetchLogs(url = null) {
        this.loading = true;

        const endpoint = url ??
            `/projects/${projectId}/activity?action=${this.filters.action}`;

        const response = await fetch(endpoint);
        const data = await response.json();

        this.logs = data.data;
        this.pagination.next = data.links.next;
        this.pagination.prev = data.links.prev;

        this.loading = false;
    },

    nextPage() {
        if (this.pagination.next) {
            this.fetchLogs(this.pagination.next);
        }
    },

    prevPage() {
        if (this.pagination.prev) {
            this.fetchLogs(this.pagination.prev);
        }
    }
}));

Alpine.start();
