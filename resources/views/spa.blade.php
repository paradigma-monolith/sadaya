<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#f4f7f9] dark:bg-[#0b1116] text-slate-900 dark:text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Dashboard SPA - BKK Kelas I Bandung</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Theme Boot (Avoid Flash of Wrong Theme) -->
    <script>
        (function() {
            try {
                var saved = localStorage.getItem('hybrid_theme');
                var isDark = saved === 'dark' || (!saved && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/spa.js'])

    <script>
        window.authUser = {!! json_encode($authUser ?? null) !!};
    </script>
</head>
<body class="h-full font-sans antialiased selection:bg-[#00A99D] selection:text-white bg-[#f4f7f9] dark:bg-[#0b1116] text-slate-900 dark:text-slate-100 overflow-x-clip w-full max-w-full transition-colors duration-200">
    <div id="spa-app"></div>
</body>
</html>
