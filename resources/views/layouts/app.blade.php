<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'StayEasy Bookings')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="container">
        @if (session('status'))
            <p class="flash">{{ session('status') }}</p>
        @endif
        @yield('content')
    </main>
</body>
</html>
