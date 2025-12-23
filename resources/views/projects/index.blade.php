<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Projects</h2>
    </x-slot>

    <div class="p-6">
        <a href="{{ route('projects.create') }}" class="underline">Create Project</a>

        <ul class="mt-4">
            @foreach ($projects as $project)
                <li>{{ $project->name }}</li>
            @endforeach
        </ul>
    </div>
</x-app-layout>
