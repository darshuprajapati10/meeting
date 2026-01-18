<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MeetUI - Meeting Management Platform')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 min-h-screen">
    <!-- Navigation -->
    @auth
        @include('layouts.navigation')
    @endauth
    
    <!-- Main Content -->
    <main class="@auth min-h-screen @else min-h-screen flex items-center justify-center @endauth">
        @yield('content')
    </main>
    
    <!-- Footer -->
    @auth
        @include('layouts.footer')
    @endauth
    
    <!-- Scripts -->
    @stack('scripts')
    
    <script>
        // Set up Axios defaults
        window.axios = require('axios');
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        window.axios.defaults.headers.common['Accept'] = 'application/json';
        
        // Get CSRF token from meta tag
        const token = document.head.querySelector('meta[name="csrf-token"]');
        if (token) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
        }
        
        // Get auth token from localStorage if available
        const authToken = localStorage.getItem('auth_token');
        if (authToken) {
            window.axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
        }
    </script>
</body>
</html>











