<x-app-layout>
    <div class="max-w-xl mx-auto py-6">

        <h1 class="text-2xl font-bold mb-4">Edit Project</h1>

        <form method="POST" action="{{ route('projects.update', $project) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block font-semibold mb-1">Project Name</label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $project->name) }}"
                    class="w-full border rounded px-3 py-2"
                    required
                >
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1">Description</label>
                <textarea
                    name="description"
                    class="w-full border rounded px-3 py-2"
                >{{ old('description', $project->description) }}</textarea>
            </div>

            <button
                type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded">
                Update
            </button>
        </form>

    </div>
</x-app-layout>
