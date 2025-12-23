<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Create Project</h2>
    </x-slot>

    <div class="p-6">
        <form method="POST" action="{{ route('projects.store') }}">
            @csrf

            <div>
                <input name="name" placeholder="Project name" class="border p-2 w-full">
            </div>

            <div class="mt-2">
                <textarea name="description" placeholder="Description" class="border p-2 w-full"></textarea>
            </div>

            <button class="mt-4 px-4 py-2 bg-black text-white">Create</button>
        </form>
    </div>
</x-app-layout>
