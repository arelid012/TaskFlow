<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Notifications
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Notifications</h2>
                        
                        @php
                            $unreadCount = auth()->user()->unreadNotifications->count();
                        @endphp
                        
                        @if($unreadCount > 0)
                        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                                Mark All as Read
                            </button>
                        </form>
                        @endif
                    </div>
                    
                    @php
                        $notifications = auth()->user()->notifications()->latest()->paginate(15);
                    @endphp
                    
                    @if($notifications->count() > 0)
                        <div class="space-y-4">
                            @foreach($notifications as $notification)
                                @php
                                    $data = $notification->data;
                                    $isUnread = is_null($notification->read_at);
                                @endphp
                                
                                <div class="border-l-4 {{ $isUnread ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50' }} p-4 rounded-lg">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2 mb-1">
                                                @if($isUnread)
                                                    <span class="inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
                                                @endif
                                                <p class="font-medium">{{ $data['message'] ?? 'Notification' }}</p>
                                            </div>
                                            
                                            <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
                                                @if(isset($data['task_title']))
                                                    <p>Task: <span class="font-medium">{{ $data['task_title'] }}</span></p>
                                                @endif
                                                
                                                @if(isset($data['project_name']))
                                                    <p>Project: <span class="font-medium">{{ $data['project_name'] }}</span></p>
                                                @endif
                                                
                                                @if(isset($data['status']))
                                                    <p>Status: <span class="font-medium capitalize">{{ $data['status'] }}</span></p>
                                                @endif
                                                
                                                @if(isset($data['assigned_by']))
                                                    <p class="text-gray-500 dark:text-gray-400">By: {{ $data['assigned_by'] }}</p>
                                                @endif
                                            </div>
                                            
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                        
                                        <div class="flex space-x-2 ml-4">
                                            @if($isUnread)
                                            <form method="POST" action="{{ route('notifications.mark-as-read', $notification) }}">
                                                @csrf
                                                <button type="submit" 
                                                        class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    Mark as read
                                                </button>
                                            </form>
                                            @endif
                                            
                                            <form method="POST" action="{{ route('notifications.destroy', $notification) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        onclick="return confirm('Delete this notification?')"
                                                        class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-6">
                            {{ $notifications->links() }}
                        </div>
                        
                    @else
                        <div class="text-center py-12">
                            <div class="text-gray-400 dark:text-gray-500 mb-4">
                                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-medium text-gray-700 dark:text-gray-300 mb-2">No notifications yet</h3>
                            <p class="text-gray-500 dark:text-gray-400">When you get notifications, they'll appear here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>