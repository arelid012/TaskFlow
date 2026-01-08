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

        {{-- Add this to your navbar --}}
        @auth
            <div class="relative">
                <a href="{{ route('notifications.index') }}" 
                class="p-2 text-gray-300 hover:text-white relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    
                    @php
                        $unreadCount = auth()->user()->unreadNotifications->count();
                    @endphp
                    
                    @if($unreadCount > 0)
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                    @endif
                </a>
            </div>
        @endauth
    </div>

    {{-- Make alerts more prominent --}}
    @if($overdueTasks > 0)
        <div class="mb-6 p-4 bg-red-900/30 border-l-4 border-red-600 rounded-lg">
            <div class="flex items-center gap-3">
                <span class="text-xl">🔴</span>
                <div>
                    <p class="font-medium text-red-300">Overdue Tasks</p>
                    <p class="text-sm text-red-400">You have {{ $overdueTasks }} task(s) past their due date</p>
                </div>
            </div>
        </div>
    @endif

    @if($dueSoonTasks > 0)
        <div class="mb-6 p-4 bg-yellow-900/30 border border-yellow-700 rounded-lg">
            <div class="flex items-center gap-2 text-yellow-300">
                <span class="text-lg">🟡</span>
                <span class="font-medium">You have {{ $dueSoonTasks }} task(s) due soon (within 2 days)</span>
            </div>
        </div>
    @endif

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
                            {{-- In your task list item --}}
                            <li class="flex items-center justify-between bg-gray-800 hover:bg-gray-750 transition rounded-lg p-4">
                                <div>
                                    <p class="font-medium text-gray-100">
                                        {{ $task->title }}
                                    </p>
                                    
                                    <div class="flex items-center gap-3 mt-2">
                                        {{-- Status badge --}}
                                        <span class="inline-block text-xs px-2 py-1 rounded
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
                                        
                                        {{-- Due Date Indicator --}}
                                        @if($task->due_date)
                                            @php
                                                $indicatorColor = match($task->status_indicator) {
                                                    'completed' => 'bg-green-600/20 text-green-400 border border-green-700/30',
                                                    'on_track' => 'bg-green-600/20 text-green-400 border border-green-700/30',
                                                    'due_soon' => 'bg-yellow-600/20 text-yellow-400 border border-yellow-700/30',
                                                    'overdue' => 'bg-red-600/20 text-red-400 border border-red-700/30',
                                                    default => 'bg-gray-700 text-gray-300',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded {{ $indicatorColor }}">
                                                {{ $task->status_indicator_icon }}
                                                Due: {{ $task->due_date->format('M d') }}
                                            </span>
                                        @endif
                                    </div>
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