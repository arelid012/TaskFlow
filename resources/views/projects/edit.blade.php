<x-app-layout>
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Back Navigation -->
        <div class="mb-6">
            <a href="{{ route('projects.index') }}" 
               class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Projects
            </a>
        </div>

        <!-- Form Container -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Form Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-100 to-blue-50 flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">Edit Project</h2>
                            <p class="text-sm text-gray-600">Update project details</p>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500">
                        Created {{ $project->created_at->format('M d, Y') }}
                    </div>
                </div>
            </div>

            <!-- Form Content -->
            <form method="POST" action="{{ route('projects.update', $project) }}" class="p-6">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Project Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Project Name *
                        </label>
                        <input 
                            type="text" 
                            id="name"
                            name="name" 
                            value="{{ old('name', $project->name) }}"
                            placeholder="Project name"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 @error('name') border-red-300 @enderror"
                            required
                            autofocus
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Description
                        </label>
                        <textarea 
                            id="description"
                            name="description" 
                            rows="5"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 @error('description') border-red-300 @enderror"
                        >{{ old('description', $project->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Project Stats (Read-only) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-gray-200">
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-gray-900">{{ $project->tasks()->count() }}</div>
                            <div class="text-sm text-gray-600">Total Tasks</div>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-gray-900">{{ $project->members()->count() }}</div>
                            <div class="text-sm text-gray-600">Team Members</div>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-gray-900">
                                {{ $project->created_at->format('M Y') }}
                            </div>
                            <div class="text-sm text-gray-600">Created Date</div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-6 mt-6 border-t border-gray-200">
                    @can('delete', $project)
                        <button 
                            type="button"
                            onclick="confirmDelete()"
                            class="px-4 py-2 text-sm font-medium text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Project
                        </button>
                    @else
                        <div></div> <!-- Empty div for flex spacing -->
                    @endcan
                    
                    <div class="flex space-x-3">
                        <a href="{{ route('projects.index') }}"
                           class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                            Cancel
                        </a>
                        <button 
                            type="submit"
                            class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 border border-transparent rounded-lg font-medium text-white shadow-sm hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update Project
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @can('delete', $project)
        <script>
        function confirmDelete() {
            if (confirm('Are you sure you want to delete this project? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('projects.destroy', $project) }}';
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        </script>
    @endcan
</x-app-layout>