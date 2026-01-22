<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header with Actions -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h1 class="text-3xl font-bold text-gray-900">My Projects</h1>
                <p class="mt-2 text-gray-600">Manage and track all your projects in one place</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('projects.create') }}"
                   class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 border border-transparent rounded-lg font-medium text-white shadow-sm hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Project
                </a>
            </div>
        </div>

        <!-- Projects Grid -->
        @if($projects->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($projects as $project)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-300 overflow-hidden group">
                        <a href="{{ route('projects.show', $project) }}" class="block cursor-pointer">
                            <div class="p-6">
                                <!-- Project Header -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-100 to-blue-50 flex items-center justify-center mr-3 group-hover:from-blue-200 group-hover:to-blue-100 transition-colors">
                                                <svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                </svg>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-900 line-clamp-1 group-hover:text-blue-700 transition-colors">
                                                {{ $project->name }}
                                            </h3>
                                        </div>
                                    </div>
                                </div>

                                <!-- Description -->
                                @if($project->description)
                                    <p class="text-gray-600 mb-6 line-clamp-3 group-hover:text-gray-700 transition-colors">
                                        {{ $project->description }}
                                    </p>
                                @else
                                    <p class="text-gray-400 italic mb-6 group-hover:text-gray-500 transition-colors">
                                        No description provided
                                    </p>
                                @endif

                                <!-- Stats -->
                                <div class="flex items-center text-sm text-gray-500 mb-6">
                                    <div class="flex items-center mr-4">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                        </svg>
                                        <span>{{ $project->tasks_count }} tasks</span>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <!-- Actions - OUTSIDE the link so they remain clickable separately -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100 px-6 pb-6">
                            <div class="flex space-x-3">
                                @can('update', $project)
                                    <a href="{{ route('projects.edit', $project) }}"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors duration-200 z-10 relative">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Edit
                                    </a>
                                @endcan
                            </div>

                            @can('delete', $project)
                                <form method="POST" action="{{ route('projects.destroy', $project) }}" 
                                    onsubmit="return confirm('Are you sure you want to delete this project?');"
                                    class="z-10 relative">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-medium text-gray-900 mb-2">No projects yet</h3>
                <p class="text-gray-600 mb-6 max-w-md mx-auto">Get started by creating your first project to organize tasks and collaborate with your team.</p>
                <a href="{{ route('projects.create') }}"
                   class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 border border-transparent rounded-lg font-medium text-white shadow-sm hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create Your First Project
                </a>
            </div>
        @endif
    </div>
</x-app-layout>