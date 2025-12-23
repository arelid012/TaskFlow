<x-app-layout>
    <div class="max-w-4xl mx-auto py-6">

        <h1 class="text-2xl font-bold mb-4">My Projects</h1>

        <a href="{{ route('projects.create') }}"
           class="inline-block mb-4 px-4 py-2 bg-blue-600 text-white rounded">
            + New Project
        </a>

        @forelse ($projects as $project)
            <div class="p-4 mb-4 border rounded">
                <h2 class="text-lg font-semibold">{{ $project->name }}</h2>
                <p class="text-gray-600">{{ $project->description }}</p>

                <div class="mt-3 flex gap-3">
                    @can('update', $project)
                        <a href="{{ route('projects.edit', $project) }}"
                           class="text-blue-600 underline">
                            Edit
                        </a>
                    @endcan

                    @can('delete', $project)
                        <form method="POST"
                              action="{{ route('projects.destroy', $project) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600 underline">Delete</button>
                        </form>
                    @endcan
                </div>
            </div>
        @empty
            <p>No projects yet.</p>
        @endforelse

    </div>
</x-app-layout>
