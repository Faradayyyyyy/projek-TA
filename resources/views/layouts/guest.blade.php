<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Portal Masuk</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Lucide Icons -->
        <script src="https://unpkg.com/lucide@latest"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            @keyframes pulse-slow {
                0%, 100% { transform: scale(1) translate(0px, 0px); opacity: 0.1; }
                33% { transform: scale(1.1) translate(30px, -50px); opacity: 0.15; }
                66% { transform: scale(0.9) translate(-20px, 20px); opacity: 0.08; }
            }
            .animate-blob-1 {
                animation: pulse-slow 15s infinite alternate;
            }
            .animate-blob-2 {
                animation: pulse-slow 18s infinite alternate-reverse;
            }
        </style>
    </head>
    <body class="font-sans text-slate-200 antialiased bg-[#0b0f19] relative overflow-hidden flex items-center justify-center min-h-screen" style="background-color: #0b0f19;">
        
        <!-- Tech Grid Background -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#0ea5e905_1px,transparent_1px),linear-gradient(to_bottom,#0ea5e905_1px,transparent_1px)] bg-[size:3rem_3rem] pointer-events-none"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_800px_at_100%_200px,#0f172a,transparent)] pointer-events-none"></div>

        <!-- Animated Glowing Blobs -->
        <div class="absolute top-10 left-10 w-[500px] h-[500px] bg-cyan-500 rounded-full blur-[130px] opacity-10 pointer-events-none animate-blob-1"></div>
        <div class="absolute bottom-10 right-10 w-[450px] h-[450px] bg-indigo-600 rounded-full blur-[130px] opacity-10 pointer-events-none animate-blob-2"></div>

        <!-- Login Glass Panel Container -->
        <div class="w-full sm:max-w-md px-8 py-10 bg-slate-900/60 backdrop-blur-2xl border border-white/10 shadow-[0_0_50px_rgba(6,182,212,0.1)] rounded-3xl z-10 m-4 relative overflow-hidden" style="background-color: rgba(15, 23, 42, 0.85);">
            <!-- Decorative Tech Corner Borders -->
            <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-cyan-400/40 rounded-tl-lg"></div>
            <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-cyan-400/40 rounded-tr-lg"></div>
            <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-cyan-400/40 rounded-bl-lg"></div>
            <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-cyan-400/40 rounded-br-lg"></div>

            <div class="flex flex-col items-center mb-8 relative">
                <div class="p-3 bg-cyan-500/10 border border-cyan-500/20 rounded-2xl shadow-[0_0_15px_rgba(6,182,212,0.1)] mb-4">
                    <i data-lucide="cpu" class="w-8 h-8 text-cyan-400"></i>
                </div>
                <h1 class="text-lg font-bold text-white tracking-widest uppercase">LAB OTOMASI <span class="text-cyan-400">2</span></h1>
                <p class="text-[10px] text-slate-400 mt-1 font-mono uppercase tracking-[0.2em]">Security Access Terminal</p>
            </div>

            {{ $slot }}
        </div>

        <script>
            lucide.createIcons();
        </script>
    </body>
</html>
