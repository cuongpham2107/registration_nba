<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('images/ASG.png') }}">
    <title>{{ $title ?? 'Đăng ký xe khai thác' }}</title>
    
    @filamentStyles
    @vite('resources/css/app.css')
    
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>
<body class="antialiased">
    {{ $slot }}

    @filamentScripts
    @vite('resources/js/app.js')
</body>
</html>
