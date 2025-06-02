<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Code Genetator</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,wght@0,200..1000;1,200..1000&display=swap');
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>

<body class="bg-gray-50 container max-w-full overflow-x-hidden" style="font-family: 'Nunito Sans'">
<div class="w-full">
    @yield('header')
    <main>
        @yield('content')
    </main>
</div>
    @livewireScripts
</body>
</html>