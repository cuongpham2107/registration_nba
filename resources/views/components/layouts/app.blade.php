<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">

        <meta name="application-name" content="{{ config('app.name') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>

        @filamentStyles
        @vite(['resources/css/app.css', 'resources/css/filament/admin/theme.css'])
    </head>

    <body class="antialiased">
        {{ $slot }}

        @livewire('notifications')

        @filamentScripts
        @vite('resources/js/app.js')
    </body>
    <script>
        window.printFile = function (url) {
            const iframe = document.createElement('iframe');
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';

            iframe.src = url;
            document.body.appendChild(iframe);

            iframe.onload = function () {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
                
                // Xóa iframe sau một lúc
                setTimeout(() => {
                    document.body.removeChild(iframe);
                }, 5000);
            };
        };
    </script>
</html>