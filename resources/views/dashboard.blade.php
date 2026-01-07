<h2 class="text-lg font-semibold mb-4">My Tasks</h2>

<form method="POST" action="{{ route('logout') }}">
    @csrf

    <x-dropdown-link
        :href="route('logout')"
        onclick="event.preventDefault();
                this.closest('form').submit();">
        {{ __('Log Out') }}
    </x-dropdown-link>
</form>

@if($tasksByProject->isEmpty())
    <p class="text-gray-500">No tasks assigned to you yet.</p>
@else
    @foreach($tasksByProject as $projectName => $tasks)
        <div class="mb-6">
            <h3 class="text-md font-bold mb-2">
                {{ $projectName }}
            </h3>

            <ul class="space-y-2">
                @foreach($tasks as $task)
                    <li class="p-3 bg-gray-800 rounded flex items-center justify-between">
                        <div>
                            <p class="font-medium">{{ $task->title }}</p>
                            <p class="text-xs uppercase text-gray-400">
                                {{ ucfirst($task->status) }}
                            </p>
                        </div>

                        <a
                            href="{{ route('projects.show', $task->project_id) }}"
                            class="text-blue-400 text-sm whitespace-nowrap"
                        >
                            View project →
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
@endif
