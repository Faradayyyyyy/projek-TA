<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Camera Settings - Lab Otomasi 2</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Tailwind Config for Custom Colors -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        navy: '#0f172a',
                        glass: 'rgba(30, 41, 59, 0.6)',
                        glassBorder: 'rgba(255, 255, 255, 0.08)'
                    }
                }
            }
        }
    </script>
    
    <!-- Custom Glassmorphism Styles -->
    <style>
        .glass-panel {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        @keyframes cctv-pan {
            0% { transform: scale(1.05) translate(0, 0); }
            100% { transform: scale(1.05) translate(-2%, 1%); }
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="bg-navy text-slate-200 min-h-screen flex overflow-hidden font-sans selection:bg-cyan-500/30">

    <!-- Sidebar -->
    <aside class="w-64 glass-panel hidden lg:flex flex-col flex-shrink-0 z-20">
        <!-- Logo Area -->
        <div class="h-20 flex items-center px-6 border-b border-white/5">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-cyan-500/20 rounded-lg border border-cyan-500/30">
                    <i data-lucide="cpu" class="w-6 h-6 text-cyan-400"></i>
                </div>
                <div>
                    <h1 class="text-sm font-bold text-white tracking-wider">LAB OTOMASI<span class="text-cyan-400">2</span></h1>
                    <p class="text-[10px] text-slate-400 font-mono">SYSTEM CONTROL</p>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 py-6 px-4 space-y-2 overflow-y-auto">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-slate-200 hover:bg-white/5 transition-all active:scale-95">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                Dashboard
            </a>
            <a href="{{ route('logs') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-slate-200 hover:bg-white/5 transition-all active:scale-95">
                <i data-lucide="scroll-text" class="w-5 h-5"></i>
                Logs
            </a>
            <a href="{{ route('camera.settings') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 font-medium transition-all shadow-[0_0_15px_rgba(6,182,212,0.1)]">
                <i data-lucide="cctv" class="w-5 h-5"></i>
                Camera Settings
            </a>
            <a href="{{ route('device.status') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-slate-200 hover:bg-white/5 transition-all active:scale-95">
                <i data-lucide="radio" class="w-5 h-5"></i>
                Device Status
            </a>
        </nav>
    </aside>

    <!-- Main Wrapper -->
    <main class="flex-1 flex flex-col h-screen overflow-y-auto relative">
        
        <!-- Background Glow Effects -->
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-cyan-500/10 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-indigo-500/10 rounded-full blur-[100px] pointer-events-none"></div>

        <!-- Header -->
        <header class="h-20 glass-panel flex items-center justify-between px-6 sticky top-0 z-30 border-t-0 border-l-0 border-r-0">
            <div class="flex items-center gap-4">
                <button class="lg:hidden p-2 text-slate-400 hover:text-white bg-white/5 rounded-lg active:scale-95 transition-all">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div class="flex flex-col">
                    <h2 class="text-lg font-bold text-white leading-tight">Pengaturan Kamera CCTV Lab 2</h2>
                    <p class="text-xs text-slate-400 hidden sm:block">Konfigurasi Stream Video Pemantau Saklar Servo & Unit AC</p>
                </div>
            </div>
            
            <div class="flex items-center gap-4 sm:gap-6">
                <!-- Clock -->
                <div class="hidden sm:flex items-center gap-2 text-cyan-400 bg-cyan-500/10 border border-cyan-500/20 px-4 py-1.5 rounded-lg">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                    <span id="realtime-clock" class="text-sm font-mono font-medium tracking-wide">00:00:00</span>
                </div>

                <!-- User Profile -->
                <div class="flex items-center gap-3 pl-2 sm:pl-6 sm:border-l border-white/10">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-white">{{ Auth::user()->name }}</p>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="text-xs text-slate-400 hover:text-rose-500 font-medium transition-colors flex items-center gap-1 mt-0.5">
                                <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                                Log Out
                            </button>
                        </form>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 p-[2px] shadow-lg shadow-cyan-500/20">
                        <div class="w-full h-full bg-navy rounded-[10px] flex items-center justify-center font-bold text-white">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Grid -->
        <div class="p-4 sm:p-6 lg:p-8 flex-1 space-y-6 z-10">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Camera Preview Card (Span 2) -->
                <div class="lg:col-span-2 glass-panel rounded-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-white flex items-center gap-2">
                            <i data-lucide="video" class="w-5 h-5 text-cyan-400"></i>
                            Live Stream Monitor (Kamera Pemantau Ruang Lab 2)
                        </h3>
                        <span class="px-3 py-1 bg-cyan-500/10 border border-cyan-500/30 rounded-full text-xs font-mono text-cyan-400">
                            1080p | 30 FPS
                        </span>
                    </div>

                    <!-- Video Container -->
                    <div class="relative w-full aspect-video bg-black rounded-xl overflow-hidden border border-white/10 group shadow-2xl">
                        <div class="absolute inset-0 w-full h-full opacity-80 overflow-hidden bg-slate-900">
                            <img src="https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&q=80&w=2070" 
                                 class="w-full h-full object-cover animate-[cctv-pan_20s_ease-in-out_infinite_alternate] filter grayscale contrast-125 brightness-75" alt="CCTV Stream">
                            <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,0,0,0.5) 2px, rgba(0,0,0,0.5) 4px);"></div>
                        </div>

                        <!-- Focus Target Simulation -->
                        <div class="absolute top-1/4 left-1/4 w-28 h-24 border-2 border-cyan-400/80 rounded-lg pointer-events-none flex items-start justify-end p-1 shadow-[0_0_10px_rgba(34,211,238,0.5)]">
                            <span class="bg-cyan-500/80 text-[9px] font-mono text-black font-bold px-1 rounded">SERVO 1 & 2</span>
                        </div>

                        <div class="absolute top-1/4 right-1/4 w-28 h-24 border-2 border-blue-400/80 rounded-lg pointer-events-none flex items-start justify-end p-1 shadow-[0_0_10px_rgba(59,130,246,0.5)]">
                            <span class="bg-blue-500/80 text-[9px] font-mono text-white font-bold px-1 rounded">AC UNIT</span>
                        </div>

                        <!-- Overlay Info -->
                        <div class="absolute bottom-4 left-4 right-4 flex items-end justify-between">
                            <div class="bg-black/60 backdrop-blur px-3 py-1.5 rounded-lg text-xs font-mono text-white border border-white/10">
                                RTSP://192.168.1.100/LAB2-CCTV
                            </div>
                            <div class="flex items-center gap-2">
                                <button class="p-2 bg-black/50 hover:bg-white/10 backdrop-blur rounded-lg text-slate-300 border border-white/10 transition-all">
                                    <i data-lucide="camera" class="w-4 h-4"></i>
                                </button>
                                <button class="p-2 bg-black/50 hover:bg-white/10 backdrop-blur rounded-lg text-slate-300 border border-white/10 transition-all">
                                    <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Stream Action Buttons -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                        <button class="py-2.5 bg-cyan-500/20 border border-cyan-500/40 text-cyan-400 rounded-xl text-xs font-bold flex items-center justify-center gap-2 hover:bg-cyan-500/30 transition-all">
                            <i data-lucide="play" class="w-4 h-4"></i>
                            Start Stream
                        </button>
                        <button class="py-2.5 bg-slate-800 border border-white/10 text-slate-300 rounded-xl text-xs font-medium flex items-center justify-center gap-2 hover:bg-slate-700 transition-all">
                            <i data-lucide="pause" class="w-4 h-4"></i>
                            Pause Stream
                        </button>
                        <button class="py-2.5 bg-slate-800 border border-white/10 text-slate-300 rounded-xl text-xs font-medium flex items-center justify-center gap-2 hover:bg-slate-700 transition-all">
                            <i data-lucide="image" class="w-4 h-4"></i>
                            Take Snapshot
                        </button>
                        <button class="py-2.5 bg-slate-800 border border-white/10 text-slate-300 rounded-xl text-xs font-medium flex items-center justify-center gap-2 hover:bg-slate-700 transition-all">
                            <i data-lucide="maximize" class="w-4 h-4"></i>
                            Fullscreen
                        </button>
                    </div>
                </div>

                <!-- RTSP & Stream Config Card -->
                <div class="lg:col-span-1 glass-panel rounded-2xl p-6 space-y-5">
                    <h3 class="text-base font-semibold text-white flex items-center gap-2">
                        <i data-lucide="sliders" class="w-5 h-5 text-cyan-400"></i>
                        Koneksi & Kualitas Stream
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">RTSP Stream URL</label>
                            <div class="flex gap-2">
                                <input type="text" value="rtsp://192.168.1.100/lab2-cctv" 
                                    class="w-full bg-slate-900/60 border border-white/10 rounded-xl px-3.5 py-2.5 text-xs text-slate-200 font-mono focus:outline-none focus:border-cyan-400">
                                <button class="px-3 py-2.5 bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 rounded-xl text-xs font-bold hover:bg-cyan-500/30 transition-all whitespace-nowrap">
                                    Tes
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Kualitas Resolusi Stream</label>
                            <select class="w-full bg-slate-900/60 border border-white/10 rounded-xl px-3.5 py-2.5 text-xs text-slate-200 font-mono focus:outline-none focus:border-cyan-400">
                                <option>1080p Full HD (1920x1080)</option>
                                <option>720p HD (1280x720)</option>
                            </select>
                        </div>

                        <div>
                            <div class="flex justify-between text-xs font-semibold text-slate-400 mb-2">
                                <span class="uppercase tracking-wider">Target Frame Rate</span>
                                <span class="text-cyan-400 font-mono">30 FPS</span>
                            </div>
                            <input type="range" min="15" max="60" value="30" class="w-full accent-cyan-400 bg-slate-800 rounded-lg h-2">
                        </div>

                        <div class="pt-4 border-t border-white/5 flex justify-end">
                            <button class="w-full py-3 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold rounded-xl text-xs shadow-[0_0_15px_rgba(6,182,212,0.4)] transition-all active:scale-95 flex items-center justify-center gap-2">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                Simpan Konfigurasi Kamera
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <script>
        lucide.createIcons();

        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById('realtime-clock').textContent = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>
