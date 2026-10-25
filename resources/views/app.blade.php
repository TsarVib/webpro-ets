<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        <script>
            var theme = localStorage.getItem('theme');

            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }

            var accent = localStorage.getItem('accent');

            if (accent) {
                document.documentElement.dataset.accent = accent;
            }
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        <x-inertia::head>
            <title></title>
        </x-inertia::head>
    </head>
    <body>
        <x-inertia::app />
    </body>
</html>
