<x-app-layout>
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-100">
                            {{ $project->name }} - Members
                        </h1>
                        <p class="text-gray-400 mt-2">Manage project members and their roles</p>
                    </div>
                    <a href="{{ route('projects.show', $project) }}" 
                       class="text-indigo-400 hover:text-indigo-300">
                        ← Back to Project
                    </a>
                </div>
            </div>

            <!-- Current Members -->
            <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-6 mb-8">
                <h2 class="text-lg font-semibold text-gray-200 mb-4">Current Members</h2>
                
                <div class="space-y-3">
                    <!-- Project Owner (Creator) -->
                    <div class="flex items-center justify-between p-3 bg-gray-800 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-purple-500/20 flex items-center justify-center">
                                <span class="text-purple-300">👑</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-200">{{ $project->creator->name }}</span>
                                <span class="text-xs text-purple-400 ml-2">Project Owner</span>
                            </div>
                        </div>
                        <span class="text-xs px-2 py-1 rounded bg-purple-500/20 text-purple-300">
                            Owner
                        </span>
                    </div>
                    
                    <!-- Other Members -->
                    @foreach($members as $member)
                        @if($member->id !== $project->created_by)
                        <div class="flex items-center justify-between p-3 bg-gray-800 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center">
                                    <span class="text-gray-300">{{ substr($member->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-200">{{ $member->name }}</span>
                                    <div class="flex items-center gap-2 text-xs text-gray-400">
                                        <span>{{ $member->email }}</span>
                                        <span>•</span>
                                        <span>{{ ucfirst($member->role) }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <!-- Role Selector -->
                                <select class="bg-gray-700 border border-gray-600 rounded px-2 py-1 text-xs text-gray-100"
                                        onchange="updateRole({{ $member->id }}, this.value)">
                                    <option value="viewer" {{ $member->pivot->role === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                    <option value="member" {{ $member->pivot->role === 'member' ? 'selected' : '' }}>Member</option>
                                    <option value="lead" {{ $member->pivot->role === 'lead' ? 'selected' : '' }}>Lead</option>
                                    <option value="manager" {{ $member->pivot->role === 'manager' ? 'selected' : '' }}>Manager</option>
                                </select>
                                
                                <!-- Remove Button -->
                                <button onclick="removeMember({{ $member->id }})"
                                        class="text-red-400 hover:text-red-300 p-1">
                                    🗑️
                                </button>
                            </div>
                        </div>
                        @endif
                    @endforeach
                    
                    @if($members->count() <= 1)
                        <div class="text-center py-8 text-gray-500">
                            <div class="text-3xl mb-3">👥</div>
                            <p>No other members yet</p>
                            <p class="text-sm mt-1">Add team members below</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Add New Member -->
            <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-200 mb-4">Add New Member</h2>
                
                <form action="{{ route('projects.members.store', $project) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Select User
                            </label>
                            <select name="user_id" 
                                    class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100">
                                <option value="">Choose a user...</option>
                                @foreach($availableUsers as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name }} ({{ $user->email }}) - {{ ucfirst($user->role) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Role
                            </label>
                            <select name="role" 
                                    class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-100">
                                <option value="member">Member</option>
                                <option value="viewer">Viewer</option>
                                <option value="lead">Lead</option>
                                <option value="manager">Manager</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded">
                            Add to Project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    function updateRole(userId, role) {
        fetch(`/projects/{{ $project->id }}/members/${userId}/role`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ role: role })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Role updated successfully', 'success');
            }
        });
    }
    
    function removeMember(userId) {
        if (!confirm('Remove this member from project?')) return;
        
        fetch(`/projects/{{ $project->id }}/members/${userId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            }
        });
    }
    
    function showToast(message, type) {
        // Simple toast notification
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-4 py-2 rounded ${type === 'success' ? 'bg-green-500/20 text-green-300' : 'bg-red-500/20 text-red-300'} border ${type === 'success' ? 'border-green-500/30' : 'border-red-500/30'}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => toast.remove(), 3000);
    }
    </script>
</x-app-layout>