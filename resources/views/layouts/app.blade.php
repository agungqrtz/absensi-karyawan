<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-g">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @stack('styles')
</head>
<body class="bg-gray-900">
    <div id="app">
        <nav class="bg-gray-800 shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center">
                        <a class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-500" href="{{ url('/') }}">
                            AbsensiApp
                        </a>
                    </div>
                    <div class="flex items-center">
                        @guest
                            @if (Route::has('login'))
                                <a class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium" href="{{ route('login') }}">Login</a>
                            @endif
                            @if (Route::has('register'))
                                <a class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium" href="{{ route('register') }}">Register</a>
                            @endif
                        @else
                            <span class="text-gray-300 px-3 py-2 rounded-md text-sm font-medium">{{ Auth::user()->name }}</span>
                            <a class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Logout
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        @endguest
                    </div>
                </div>
            </div>
        </nav>

        <main>
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
