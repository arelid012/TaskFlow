<!DOCTYPE html>
<html>
<head>
    <title>Notifications - TaskFlow</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen">
        <nav class="bg-gray-800 border-b border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="/" class="text-xl font-bold">TaskFlow</a>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="/dashboard" class="hover:text-gray-300">Dashboard</a>
                        <a href="/projects" class="hover:text-gray-300">Projects</a>
                        <a href="/notifications" class="relative p-2 hover:text-gray-300">
                            🔔
                            @auth
                                @php $unread = auth()->user()->unreadNotifications->count(); @endphp
                                @if($unread > 0)
                                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                        {{ $unread }}
                                    </span>
                                @endif
                            @endauth
                        </a>
                    </div>
                </div>
            </div>
        </nav>
        
        <main>
            @yield('content')
        </main>
    </div>
</body>
</html>