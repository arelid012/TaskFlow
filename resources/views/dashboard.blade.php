<div class="max-w-6xl mx-auto px-6 py-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-100">My Tasks</h2>
            <p class="text-sm text-gray-400">
                Tasks assigned to you across all projects
            </p>
        </div>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="text-sm text-red-400 hover:text-red-300 transition"
            >
                Log out
            </button>
        </form>
    </div>

    {{-- Empty State --}}
    @if($tasksByProject->isEmpty())
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 text-center text-gray-400">
            No tasks assigned to you yet.
        </div>
    @else

        {{-- Projects --}}
        <div class="space-y-8">
            @foreach($tasksByProject as $projectName => $tasks)

                <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
                    {{-- Project title --}}
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-100">
                            {{ $projectName }}
                        </h3>

                        <span class="text-xs text-gray-500">
                            {{ $tasks->count() }} task(s)
                        </span>
                    </div>

                    {{-- Tasks --}}
                    <ul class="space-y-3">
                        @foreach($tasks as $task)
                            <li class="flex items-center justify-between bg-gray-800 hover:bg-gray-750 transition rounded-lg p-4">
                                <div>
                                    <p class="font-medium text-gray-100">
                                        {{ $task->title }}
                                    </p>

                                    {{-- Status badge --}}
                                    <span class="inline-block mt-1 text-xs px-2 py-1 rounded
                                        @if($task->status === 'todo')
                                            bg-gray-700 text-gray-300
                                        @elseif($task->status === 'doing')
                                            bg-blue-600/20 text-blue-400
                                        @elseif($task->status === 'done')
                                            bg-green-600/20 text-green-400
                                        @endif
                                    ">
                                        {{ ucfirst($task->status) }}
                                    </span>
                                </div>

                                <a
                                    href="{{ route('projects.show', $task->project_id) }}"
                                    class="text-sm text-blue-400 hover:text-blue-300 whitespace-nowrap"
                                >
                                    View project →
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

            @endforeach
        </div>

    @endif
</div>
