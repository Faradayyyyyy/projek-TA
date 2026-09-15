<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Monitoring - Lab Otomasi 2</title>
    
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
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }

        /* CCTV Animation */
        @keyframes cctv-pan {
            0% { transform: scale(1.05) translate(0, 0); }
            100% { transform: scale(1.05) translate(-2%, 1%); }
        }
        /* Page Transition Animation */
        @keyframes pageFadeIn {
            from { opacity: 0.4; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .page-animate {
            animation: pageFadeIn 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="bg-navy text-slate-200 min-h-screen flex overflow-hidden font-sans selection:bg-cyan-500/30">

    <!-- Sidebar -->
    <aside class="w-64 glass-panel hidden lg:flex flex-col flex-shrink-0 z-20">
        <!-- Logo Area -->
        <div class="h-20 flex items-center px-5 border-b border-white/5 relative overflow-hidden group">
            <!-- Ambient Glow Effect behind Logo -->
            <div class="absolute -left-5 -top-5 w-24 h-24 bg-cyan-500/15 rounded-full blur-xl pointer-events-none group-hover:bg-cyan-500/25 transition-all duration-500"></div>

            <div class="flex items-center gap-3.5 relative z-10">
                <!-- High-Tech Animated Icon Box -->
                <div class="relative flex-shrink-0">
                    <!-- Glowing Border Gradient -->
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-cyan-500 via-blue-600 to-indigo-500 p-[1.5px] shadow-[0_0_20px_rgba(6,182,212,0.35)] group-hover:shadow-[0_0_25px_rgba(6,182,212,0.55)] transition-all duration-300">
                        <div class="w-full h-full bg-slate-900/90 rounded-[14px] flex items-center justify-center backdrop-blur-md">
                            <i data-lucide="cpu" class="w-6 h-6 text-cyan-400 drop-shadow-[0_0_8px_rgba(6,182,212,0.8)] group-hover:scale-110 transition-transform duration-300"></i>
                        </div>
                    </div>
                    <!-- Real-time Online Green Ping Badge -->
                    <span class="absolute -top-0.5 -right-0.5 flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500 border-2 border-slate-900 shadow-[0_0_6px_rgba(16,185,129,0.8)]"></span>
                    </span>
                </div>

                <!-- Brand Typography -->
                <div class="flex flex-col">
                    <div class="flex items-center gap-1.5">
                        <h1 class="text-sm font-extrabold tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-white via-slate-100 to-cyan-200">
                            LAB OTOMASI
                        </h1>
                        <span class="px-1.5 py-0.2 text-[10px] font-mono font-extrabold bg-gradient-to-r from-cyan-500/30 to-blue-500/30 text-cyan-300 border border-cyan-400/40 rounded-md shadow-[0_0_10px_rgba(6,182,212,0.3)]">
                            2
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="text-[9px] font-mono font-semibold tracking-[0.18em] text-cyan-400/90 uppercase">
                            VIRTUAL TWIN &bull; IoT
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 py-6 px-4 space-y-2 overflow-y-auto">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 font-medium transition-all shadow-[0_0_15px_rgba(6,182,212,0.1)]">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                Dashboard
            </a>
            <a href="{{ route('logs') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-slate-200 hover:bg-white/5 transition-all active:scale-95">
                <i data-lucide="scroll-text" class="w-5 h-5"></i>
                Logs
            </a>
            <a href="{{ route('analytics') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-slate-200 hover:bg-white/5 transition-all active:scale-95">
                <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                Analitik & Durasi
            </a>
            <a href="{{ route('device.status') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-slate-200 hover:bg-white/5 transition-all active:scale-95">
                <i data-lucide="radio" class="w-5 h-5"></i>
                Device Status
            </a>
        </nav>
    </aside>

    <!-- Main Wrapper -->
    <main class="flex-1 flex flex-col h-screen overflow-y-auto relative page-animate">
        
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
                    <h2 class="text-lg font-bold text-white leading-tight">Ruang Kontrol Virtual</h2>
                    <p class="text-xs text-slate-400 hidden sm:block">Laboratorium Otomasi 2 - ESP32 Controller</p>
                </div>
            </div>
            
            <div class="flex items-center gap-4 sm:gap-6">
                <!-- ESP32 Status -->
                <div id="esp32-status-badge" class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-800/50 border border-slate-700">
                    <div class="relative flex h-3 w-3">
                        <span id="esp32-ping-dot" class="hidden"></span>
                        <span id="esp32-status-dot" class="relative inline-flex rounded-full h-3 w-3 bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.8)]"></span>
                    </div>
                    <span id="esp32-status-text" class="text-xs font-semibold text-rose-400 hidden sm:block">ESP32 Offline</span>
                </div>

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

        <!-- Main Dashboard Content -->
        <div class="p-4 sm:p-6 lg:p-8 flex-1 space-y-6 z-10">
            
            <!-- Top Section: CCTV & Virtual Floorplan Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- CCTV Section (Span 2/3) -->
                <div class="lg:col-span-2 glass-panel rounded-2xl p-5 flex flex-col gap-4">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div id="cctv-icon-box" class="p-2.5 bg-cyan-500/20 text-cyan-400 rounded-xl border border-cyan-500/30 shadow-inner">
                                <i data-lucide="video" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 id="cctv-active-title" class="text-base font-semibold text-white leading-tight">Kamera CCTV</h3>
                                    <!-- Selector Dropdown Kamera -->
                                    <div class="relative inline-block">
                                        <select id="cctv-camera-select" onchange="onSelectCamera(this.value)"
                                                class="bg-slate-900/90 text-cyan-300 text-xs font-mono font-bold px-3 py-1 pr-8 rounded-lg border border-cyan-500/30 hover:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-500 cursor-pointer appearance-none shadow-sm">
                                            <option value="cam_4">Kamera 4 (Tapo C200 Lab 2)</option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-cyan-400">
                                            <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                                        </div>
                                    </div>
                                </div>
                                <p id="cctv-active-subtitle" class="text-[11px] text-slate-400 font-mono mt-0.5">Tapo C200 &bull; IP: 10.32.72.46</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <!-- Live Streaming Status Badge -->
                            <div id="cctv-status-badge" class="px-3 py-1.5 bg-rose-500/10 border border-rose-500/30 rounded-xl text-xs font-bold text-rose-400 flex items-center gap-2 shadow-[0_0_10px_rgba(244,63,94,0.1)] font-mono">
                                <div id="cctv-status-dot" class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></div>
                                <span id="cctv-status-text">LIVE STREAMING</span>
                            </div>

                            <!-- Tombol Tambah Device Kamera -->
                            <button type="button" onclick="openAddCameraModal()" 
                                    class="px-3 py-1.5 bg-emerald-500/20 hover:bg-emerald-500/30 border border-emerald-500/40 text-emerald-300 hover:text-emerald-200 font-bold rounded-xl text-xs font-mono flex items-center gap-1.5 transition-all active:scale-95 shadow-[0_0_12px_rgba(16,185,129,0.15)]"
                                    title="Tambah Device Kamera Baru Langsung dari Web">
                                <i data-lucide="plus-circle" class="w-4 h-4 text-emerald-400"></i>
                                <span>+ Kamera</span>
                            </button>

                            <!-- Tombol Kelola Kamera -->
                            <button type="button" onclick="openManageCamerasModal()" 
                                    class="p-2 bg-slate-800/80 hover:bg-slate-700/80 border border-white/10 text-slate-300 hover:text-white rounded-xl text-xs transition-all active:scale-95"
                                    title="Daftar & Kelola Kamera">
                                <i data-lucide="settings-2" class="w-4 h-4"></i>
                            </button>

                            <!-- Tombol Toggle ON / OFF Kamera -->
                            <button id="btn-cctv-toggle" onclick="toggleCameraPower()" 
                                    class="px-3.5 py-1.5 bg-cyan-500/20 hover:bg-cyan-500/30 border border-cyan-500/40 text-cyan-300 font-bold rounded-xl text-xs font-mono flex items-center gap-2 transition-all active:scale-95 shadow-[0_0_12px_rgba(6,182,212,0.2)]">
                                <i id="cctv-toggle-icon" data-lucide="power" class="w-4 h-4 text-cyan-400"></i>
                                <span id="cctv-toggle-text">KAMERA ON</span>
                            </button>
                        </div>
                    </div>

                    <!-- Video Frame (100% Full Pure Live Feed - Hanya Kamera 4 Saja) -->
                    <div id="cctv-container" class="relative w-full aspect-video bg-black rounded-xl overflow-hidden border border-cyan-500/30 shadow-2xl flex items-center justify-center">
                        <img id="real-agentdvr-stream"
                             src="http://localhost:8090/video.mjpg?oid=4" 
                             class="w-full h-full object-cover transition-opacity duration-300"
                             alt="Live CCTV Kamera 4"
                             onerror="if (!this.dataset.fallback) { this.dataset.fallback = 'true'; this.src = '/api/cctv-stream'; }">

                        <!-- Standby / Privacy Overlay saat Kamera Dimatikan (OFF) -->
                        <div id="cctv-off-overlay" class="absolute inset-0 bg-slate-950/95 backdrop-blur-md hidden flex-col items-center justify-center gap-3.5 p-6 text-center z-20">
                            <div class="p-4 bg-slate-900/90 border border-white/10 rounded-2xl text-slate-500 shadow-xl">
                                <i data-lucide="video-off" class="w-10 h-10 text-rose-400/80"></i>
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-sm font-bold text-slate-200 font-mono uppercase tracking-wider">Kamera Pengawas Non-Aktif</h4>
                                <p class="text-xs text-slate-400 max-w-sm">Feed video CCTV sedang dimatikan (Standby Mode / Privasi). Klik tombol di bawah untuk mengaktifkan kembali.</p>
                            </div>
                            <button onclick="toggleCameraPower(true)" class="mt-1 px-4 py-2 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold rounded-xl text-xs font-mono flex items-center gap-2 shadow-[0_0_15px_rgba(6,182,212,0.4)] transition-all active:scale-95">
                                <i data-lucide="power" class="w-4 h-4"></i>
                                Nyalakan Kamera CCTV
                            </button>
                        </div>
                    </div>

                    <!-- PTZ Directional Control Panel (Atas, Bawah, Kiri, Kanan, Home, Zoom) -->
                    <div class="mt-2 pt-4 border-t border-white/10 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <i data-lucide="navigation" class="w-4 h-4 text-cyan-400"></i>
                            <span class="text-xs font-bold text-slate-200 uppercase tracking-wider font-mono">Kontrol Kamera CCTV (PTZ)</span>
                        </div>

                        <div class="flex items-center gap-6 w-full sm:w-auto justify-center">
                            <!-- D-Pad Directional Grid -->
                            <div class="relative w-32 h-32 bg-slate-950/80 rounded-2xl p-2 border border-cyan-500/20 shadow-inner flex items-center justify-center">
                                <!-- Top Button (Atas) -->
                                <button onclick="controlCctvPtz('up')" class="absolute top-1.5 left-1/2 -translate-x-1/2 p-2 bg-slate-900 hover:bg-cyan-500/30 text-cyan-400 hover:text-white rounded-xl border border-white/10 transition-all active:scale-90 shadow-md" title="Geser Atas">
                                    <i data-lucide="chevron-up" class="w-4 h-4"></i>
                                </button>
                                
                                <!-- Left Button (Kiri) -->
                                <button onclick="controlCctvPtz('left')" class="absolute left-1.5 top-1/2 -translate-y-1/2 p-2 bg-slate-900 hover:bg-cyan-500/30 text-cyan-400 hover:text-white rounded-xl border border-white/10 transition-all active:scale-90 shadow-md" title="Geser Kiri">
                                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                                </button>

                                <!-- Center Button (Home / Reset) -->
                                <button onclick="controlCctvPtz('home')" class="p-2 bg-cyan-500/20 hover:bg-cyan-500/40 text-cyan-300 rounded-xl border border-cyan-500/40 transition-all active:scale-90 shadow-[0_0_15px_rgba(6,182,212,0.3)]" title="Posisi Tengah (Reset)">
                                    <i data-lucide="disc" class="w-4 h-4"></i>
                                </button>

                                <!-- Right Button (Kanan) -->
                                <button onclick="controlCctvPtz('right')" class="absolute right-1.5 top-1/2 -translate-y-1/2 p-2 bg-slate-900 hover:bg-cyan-500/30 text-cyan-400 hover:text-white rounded-xl border border-white/10 transition-all active:scale-90 shadow-md" title="Geser Kanan">
                                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                </button>

                                <!-- Bottom Button (Bawah) -->
                                <button onclick="controlCctvPtz('down')" class="absolute bottom-1.5 left-1/2 -translate-x-1/2 p-2 bg-slate-900 hover:bg-cyan-500/30 text-cyan-400 hover:text-white rounded-xl border border-white/10 transition-all active:scale-90 shadow-md" title="Geser Bawah">
                                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2D Virtual Lab Twin (Denah Virtual 2D) (Span 1/3) -->
                <div class="lg:col-span-1 glass-panel rounded-2xl p-5 flex flex-col justify-between gap-4 border border-cyan-500/20 shadow-[0_0_25px_rgba(6,182,212,0.05)]">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold flex items-center gap-2 text-white">
                            <i data-lucide="box" class="w-5 h-5 text-cyan-400"></i>
                            Denah Virtual 2D Lab 2
                        </h3>
                        <span class="text-[10px] font-mono text-cyan-400 bg-cyan-500/10 px-2 py-0.5 rounded border border-cyan-500/20">
                            DIGITAL TWIN
                        </span>
                    </div>

                    <!-- Virtual Lab 2D Floorplan Box -->
                    <div class="relative w-full aspect-square bg-slate-950/80 rounded-xl p-4 border border-white/10 flex flex-col justify-between overflow-hidden">
                        <!-- Floorplan Background Grid -->
                        <div class="absolute inset-0 bg-[linear-gradient(to_right,#0ea5e908_1px,transparent_1px),linear-gradient(to_bottom,#0ea5e908_1px,transparent_1px)] bg-[size:1.25rem_1.25rem] pointer-events-none"></div>

                        <!-- Top Wall Section: Lampu 1 & Lampu 2 -->
                        <div class="grid grid-cols-2 gap-4 items-center z-10 w-full">
                            <!-- Virtual Lampu 1 -->
                            <div id="v-lamp-1" class="flex flex-col items-center gap-1.5 p-3 bg-slate-900/90 border border-white/10 rounded-xl transition-all duration-300">
                                <div id="v-lamp-1-icon" class="p-2.5 rounded-lg bg-cyan-500/20 text-cyan-400 shadow-[0_0_15px_rgba(6,182,212,0.3)] transition-all">
                                    <i data-lucide="lightbulb" class="w-6 h-6"></i>
                                </div>
                                <span class="text-xs font-bold text-white font-mono">LAMPU UTAMA 1</span>
                                <span id="v-lamp-1-badge" class="text-[10px] font-mono font-bold px-2.5 py-0.5 bg-cyan-500/20 text-cyan-300 rounded-md border border-cyan-500/30">ON</span>
                            </div>

                            <!-- Virtual Lampu 2 -->
                            <div id="v-lamp-2" class="flex flex-col items-center gap-1.5 p-3 bg-slate-900/90 border border-white/10 rounded-xl transition-all duration-300">
                                <div id="v-lamp-2-icon" class="p-2.5 rounded-lg bg-cyan-500/20 text-cyan-400 shadow-[0_0_15px_rgba(6,182,212,0.3)] transition-all">
                                    <i data-lucide="lightbulb" class="w-6 h-6"></i>
                                </div>
                                <span class="text-xs font-bold text-white font-mono">LAMPU UTAMA 2</span>
                                <span id="v-lamp-2-badge" class="text-[10px] font-mono font-bold px-2.5 py-0.5 bg-cyan-500/20 text-cyan-300 rounded-md border border-cyan-500/30">ON</span>
                            </div>
                        </div>

                        <!-- Center Room Layout (Meja Praktikum Virtual) -->
                        <div class="my-auto py-3.5 px-4 bg-slate-900/60 border border-white/10 rounded-xl text-center z-10">
                            <p class="text-xs font-bold text-slate-200 uppercase tracking-widest flex items-center justify-center gap-2">
                                <i data-lucide="layers" class="w-4 h-4 text-cyan-400"></i>
                                Meja Praktikum Lab Otomasi 2
                            </p>
                            <p class="text-[10px] font-mono text-slate-400 mt-0.5">Area Kontrol Dual-ESP32 Servo Terpusat</p>
                        </div>

                        <!-- Bottom Section: CCTV Camera & Dual ESP32 Controller Box -->
                        <div class="flex justify-between items-center z-10">
                            <div class="flex items-center gap-2 p-2 bg-slate-900/90 border border-white/10 rounded-lg text-slate-400">
                                <i data-lucide="cctv" class="w-4 h-4 text-cyan-400"></i>
                                <span class="text-[9px] font-mono text-slate-300">CAM PTZ 01</span>
                            </div>

                            <div class="flex items-center gap-2 p-2 bg-slate-900/90 border border-cyan-500/30 rounded-lg text-cyan-400">
                                <i data-lucide="cpu" class="w-4 h-4"></i>
                                <span class="text-[9px] font-mono text-white font-bold">DUAL ESP32 NODE</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-[10px] text-slate-400 font-mono text-center">
                        *Indikator denah virtual berubah otomatis mengikuti aksi saklar fisik
                    </div>
                </div>

            </div>

            <!-- Control Section Grid (2 Hardware Cards: Lampu 1 & Lampu 2) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Card 1: Saklar Lampu 1 (Servo Motor 1) -->
                <div class="glass-panel rounded-2xl p-6 flex flex-col justify-between gap-4 transition-all duration-300 hover:border-cyan-500/40 hover:shadow-[0_0_25px_rgba(6,182,212,0.15)] relative overflow-hidden group">
                    <!-- Subtle Background Glow -->
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-cyan-500/10 rounded-full blur-2xl pointer-events-none group-hover:bg-cyan-500/20 transition-all"></div>

                    <!-- Header -->
                    <div class="flex items-start justify-between z-10">
                        <div class="flex items-center gap-3.5">
                            <div id="lamp1-icon-bg" class="p-3 bg-cyan-500/20 border border-cyan-500/40 rounded-xl text-cyan-400 shadow-[0_0_15px_rgba(6,182,212,0.25)] transition-all">
                                <i id="lamp1-icon" data-lucide="lightbulb" class="w-6 h-6 animate-pulse"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-white tracking-wide">Lampu Utama 1</h3>
                                    <span class="px-1.5 py-0.5 bg-cyan-500/20 text-cyan-300 text-[9px] font-mono font-bold rounded border border-cyan-500/30">SERVO 1</span>
                                </div>
                                <p class="text-xs text-slate-400 font-mono mt-0.5">GPIO 13 &bull; Saklar Fisik Utama</p>
                            </div>
                        </div>
                        <span id="lamp1-status-text" class="text-xs font-bold text-cyan-400 px-3 py-1 bg-cyan-500/10 rounded-lg border border-cyan-500/30 font-mono shadow-[0_0_10px_rgba(6,182,212,0.2)]">
                            ON (90&deg;)
                        </span>
                    </div>

                    <!-- Realistic Smart Light LCD Screen & Gauge -->
                    <div class="bg-slate-900/90 rounded-2xl p-4 border border-cyan-500/20 shadow-inner flex flex-col items-center justify-center gap-3 relative z-10">
                        
                        <!-- LCD Screen Status Bar -->
                        <div class="w-full flex items-center justify-between text-[10px] font-mono text-cyan-400/80 px-2 border-b border-white/5 pb-2">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="zap" class="w-3.5 h-3.5 text-cyan-300"></i>
                                <span id="lamp1-power-stat">EST: 18 WATT</span>
                            </span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="activity" class="w-3.5 h-3.5 text-cyan-300"></i>
                                <span id="lamp1-servo-status">SUDUT: 90&deg; TEKAN</span>
                            </span>
                            <span class="text-emerald-400 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                                <span>ESP32 #1</span>
                            </span>
                        </div>

                        <!-- Big Digital Illumination Readout -->
                        <div class="flex items-center justify-between w-full px-4 py-1.5">
                            <!-- Illumination Display -->
                            <div class="flex flex-col">
                                <span id="lamp1-illum-text" class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-br from-cyan-200 via-cyan-400 to-blue-500 font-mono tracking-tight drop-shadow-[0_0_15px_rgba(6,182,212,0.4)]">
                                    100%
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono uppercase tracking-widest -mt-1">Pencahayaan</span>
                            </div>

                            <!-- Servo Angle Gauge Pill -->
                            <div class="flex flex-col items-end">
                                <span id="lamp1-angle-badge" class="px-3 py-1.5 bg-cyan-500/20 text-cyan-300 rounded-xl text-xs font-mono font-bold border border-cyan-500/30 shadow-[0_0_10px_rgba(6,182,212,0.2)]">
                                    90&deg; TEKAN (ON)
                                </span>
                                <span class="text-[9px] text-slate-500 font-mono mt-0.5">Posisi Sudut Servo</span>
                            </div>
                        </div>
                    </div>

                    <!-- Master ON / OFF Buttons -->
                    <div class="grid grid-cols-2 gap-2.5 z-10">
                        <button id="btn-lamp1-on" class="py-2.5 px-3 bg-gradient-to-r from-cyan-600 to-blue-500 hover:from-cyan-500 hover:to-blue-400 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-2 shadow-[0_0_15px_rgba(6,182,212,0.3)] transition-all active:scale-95">
                            <i data-lucide="power" class="w-4 h-4 text-white"></i>
                            SERVO 1 ON
                        </button>
                        <button id="btn-lamp1-off" class="py-2.5 px-3 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 text-rose-400 font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition-all active:scale-95">
                            <i data-lucide="power-off" class="w-4 h-4"></i>
                            SERVO 1 OFF
                        </button>
                    </div>
                </div>

                <!-- Card 2: Saklar Lampu 2 (Servo Motor 2) -->
                <div class="glass-panel rounded-2xl p-6 flex flex-col justify-between gap-4 transition-all duration-300 hover:border-indigo-500/40 hover:shadow-[0_0_25px_rgba(99,102,241,0.15)] relative overflow-hidden group">
                    <!-- Subtle Background Glow -->
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none group-hover:bg-indigo-500/20 transition-all"></div>

                    <!-- Header -->
                    <div class="flex items-start justify-between z-10">
                        <div class="flex items-center gap-3.5">
                            <div id="lamp2-icon-bg" class="p-3 bg-indigo-500/20 border border-indigo-500/40 rounded-xl text-indigo-400 shadow-[0_0_15px_rgba(99,102,241,0.25)] transition-all">
                                <i id="lamp2-icon" data-lucide="lightbulb" class="w-6 h-6 animate-pulse"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-white tracking-wide">Lampu Utama 2</h3>
                                    <span class="px-1.5 py-0.5 bg-indigo-500/20 text-indigo-300 text-[9px] font-mono font-bold rounded border border-indigo-500/30">SERVO 2</span>
                                </div>
                                <p class="text-xs text-slate-400 font-mono mt-0.5">GPIO 13 &bull; Saklar Fisik Kedua</p>
                            </div>
                        </div>
                        <span id="lamp2-status-text" class="text-xs font-bold text-indigo-400 px-3 py-1 bg-indigo-500/10 rounded-lg border border-indigo-500/30 font-mono shadow-[0_0_10px_rgba(99,102,241,0.2)]">
                            ON (90&deg;)
                        </span>
                    </div>

                    <!-- Realistic Smart Light LCD Screen & Gauge -->
                    <div class="bg-slate-900/90 rounded-2xl p-4 border border-indigo-500/20 shadow-inner flex flex-col items-center justify-center gap-3 relative z-10">
                        
                        <!-- LCD Screen Status Bar -->
                        <div class="w-full flex items-center justify-between text-[10px] font-mono text-indigo-400/80 px-2 border-b border-white/5 pb-2">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="zap" class="w-3.5 h-3.5 text-indigo-300"></i>
                                <span id="lamp2-power-stat">EST: 18 WATT</span>
                            </span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="activity" class="w-3.5 h-3.5 text-indigo-300"></i>
                                <span id="lamp2-servo-status">SUDUT: 90&deg; TEKAN</span>
                            </span>
                            <span class="text-emerald-400 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                                <span>ESP32 #2</span>
                            </span>
                        </div>

                        <!-- Big Digital Illumination Readout -->
                        <div class="flex items-center justify-between w-full px-4 py-1.5">
                            <!-- Illumination Display -->
                            <div class="flex flex-col">
                                <span id="lamp2-illum-text" class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-br from-indigo-200 via-indigo-400 to-purple-500 font-mono tracking-tight drop-shadow-[0_0_15px_rgba(99,102,241,0.4)]">
                                    100%
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono uppercase tracking-widest -mt-1">Pencahayaan</span>
                            </div>

                            <!-- Servo Angle Gauge Pill -->
                            <div class="flex flex-col items-end">
                                <span id="lamp2-angle-badge" class="px-3 py-1.5 bg-indigo-500/20 text-indigo-300 rounded-xl text-xs font-mono font-bold border border-indigo-500/30 shadow-[0_0_10px_rgba(99,102,241,0.2)]">
                                    90&deg; TEKAN (ON)
                                </span>
                                <span class="text-[9px] text-slate-500 font-mono mt-0.5">Posisi Sudut Servo</span>
                            </div>
                        </div>
                    </div>

                    <!-- Master ON / OFF Buttons -->
                    <div class="grid grid-cols-2 gap-2.5 z-10">
                        <button id="btn-lamp2-on" class="py-2.5 px-3 bg-gradient-to-r from-indigo-600 to-purple-500 hover:from-indigo-500 hover:to-purple-400 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-2 shadow-[0_0_15px_rgba(99,102,241,0.3)] transition-all active:scale-95">
                            <i data-lucide="power" class="w-4 h-4 text-white"></i>
                            SERVO 2 ON
                        </button>
                        <button id="btn-lamp2-off" class="py-2.5 px-3 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 text-rose-400 font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition-all active:scale-95">
                            <i data-lucide="power-off" class="w-4 h-4"></i>
                            SERVO 2 OFF
                        </button>
                    </div>
                </div>

            </div>

            <!-- Bottom Section (Footer Log) -->
            <div class="pb-6">
                <div class="glass-panel rounded-2xl p-5 flex flex-col justify-center">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-xl bg-slate-800/80 border border-white/5">
                                <i data-lucide="terminal-square" class="w-5 h-5 text-slate-400"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-white mb-1">Aktivitas Terbaru</p>
                                <p class="text-xs text-slate-400">
                                    <span class="text-cyan-400 font-medium">{{ Auth::user()->name }}</span> menyalakan Lampu Utama 1 (Servo 1) pada 
                                    <span class="text-slate-300 font-mono bg-white/5 px-1.5 py-0.5 rounded">16:30:45</span>
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('logs') }}" class="text-xs text-white hover:text-cyan-400 font-medium transition-all border border-white/10 px-4 py-2 rounded-xl hover:bg-white/5 hover:border-cyan-500/30 active:scale-95 text-center">
                            Lihat Semua Log
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- ========================================================================= -->
    <!-- MODAL 1: TAMBAH DEVICE KAMERA CCTV BARU LANGSUNG DARI WEB                -->
    <!-- ========================================================================= -->
    <div id="modal-add-cctv" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4 transition-all animate-fade-in">
        <div class="glass-panel w-full max-w-xl rounded-3xl p-6 sm:p-8 border border-cyan-500/40 shadow-[0_0_50px_rgba(6,182,212,0.2)] relative space-y-6 max-h-[90vh] overflow-y-auto">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-white/10 pb-4">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-emerald-500/20 text-emerald-400 rounded-2xl border border-emerald-500/30">
                        <i data-lucide="plus-circle" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white leading-tight">Tambah Device CCTV Baru</h3>
                        <p class="text-xs text-slate-400 font-mono">Daftarkan kamera IP / RTSP / ONVIF tanpa perlu buka Agent DVR</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddCameraModal()" class="p-2 text-slate-400 hover:text-white rounded-xl hover:bg-white/5 transition-all">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Form Tambah Kamera -->
            <form id="form-add-cctv" onsubmit="submitAddCameraForm(event)" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nama Kamera -->
                    <div class="space-y-1.5 sm:col-span-2">
                        <label class="text-xs font-mono text-slate-300 font-bold flex items-center gap-1.5">
                            <i data-lucide="tag" class="w-3.5 h-3.5 text-cyan-400"></i> Nama Kamera / Lokasi *
                        </label>
                        <input type="text" id="add-cam-name" required placeholder="Contoh: Kamera 1 - Lab Otomasi"
                               class="w-full bg-slate-900/90 text-white text-xs font-mono px-4 py-2.5 rounded-xl border border-white/10 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                    </div>

                    <!-- Tipe / Brand Kamera -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-mono text-slate-300 font-bold flex items-center gap-1.5">
                            <i data-lucide="cpu" class="w-3.5 h-3.5 text-cyan-400"></i> Tipe / Model Kamera
                        </label>
                        <select id="add-cam-brand" 
                                class="w-full bg-slate-900/90 text-slate-200 text-xs font-mono px-3.5 py-2.5 rounded-xl border border-white/10 focus:border-cyan-500 focus:outline-none cursor-pointer">
                            <option value="Tapo C200 / ONVIF PTZ">Tapo C200 / ONVIF PTZ</option>
                            <option value="Generic RTSP IP Camera">Generic RTSP IP Camera</option>
                            <option value="Hikvision / Dahua ONVIF">Hikvision / Dahua ONVIF</option>
                            <option value="HTTP / MJPEG Video Stream">HTTP / MJPEG Video Stream</option>
                        </select>
                    </div>

                    <!-- Alamat IP -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-mono text-slate-300 font-bold flex items-center gap-1.5">
                            <i data-lucide="network" class="w-3.5 h-3.5 text-cyan-400"></i> Alamat IP Kamera *
                        </label>
                        <input type="text" id="add-cam-ip" required placeholder="Contoh: 10.32.72.46"
                               class="w-full bg-slate-900/90 text-white text-xs font-mono px-4 py-2.5 rounded-xl border border-white/10 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                    </div>

                    <!-- Port ONVIF -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-mono text-slate-300 font-bold flex items-center gap-1.5">
                            <i data-lucide="hash" class="w-3.5 h-3.5 text-cyan-400"></i> Port ONVIF (PTZ)
                        </label>
                        <input type="number" id="add-cam-port" value="2020" placeholder="2020"
                               class="w-full bg-slate-900/90 text-white text-xs font-mono px-4 py-2.5 rounded-xl border border-white/10 focus:border-cyan-500 focus:outline-none">
                    </div>

                    <!-- Port RTSP -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-mono text-slate-300 font-bold flex items-center gap-1.5">
                            <i data-lucide="hash" class="w-3.5 h-3.5 text-cyan-400"></i> Port RTSP (Stream)
                        </label>
                        <input type="number" id="add-cam-rtsp-port" value="554" placeholder="554"
                               class="w-full bg-slate-900/90 text-white text-xs font-mono px-4 py-2.5 rounded-xl border border-white/10 focus:border-cyan-500 focus:outline-none">
                    </div>

                    <!-- Username Kamera -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-mono text-slate-300 font-bold flex items-center gap-1.5">
                            <i data-lucide="user" class="w-3.5 h-3.5 text-cyan-400"></i> Username Akun Kamera
                        </label>
                        <input type="text" id="add-cam-user" placeholder="Contoh: faradays"
                               class="w-full bg-slate-900/90 text-white text-xs font-mono px-4 py-2.5 rounded-xl border border-white/10 focus:border-cyan-500 focus:outline-none">
                    </div>

                    <!-- Password Kamera -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-mono text-slate-300 font-bold flex items-center gap-1.5">
                            <i data-lucide="key" class="w-3.5 h-3.5 text-cyan-400"></i> Password Kamera
                        </label>
                        <input type="password" id="add-cam-pass" placeholder="Password kamera..."
                               class="w-full bg-slate-900/90 text-white text-xs font-mono px-4 py-2.5 rounded-xl border border-white/10 focus:border-cyan-500 focus:outline-none">
                    </div>

                    <!-- OID Agent DVR (Opsional) -->
                    <div class="space-y-1.5 sm:col-span-2">
                        <label class="text-xs font-mono text-slate-300 font-bold flex items-center gap-1.5">
                            <i data-lucide="layers" class="w-3.5 h-3.5 text-cyan-400"></i> Object ID (OID Agent DVR)
                        </label>
                        <input type="text" id="add-cam-oid" placeholder="Otomatis (atau isi nomor OID Agent DVR jika ada)"
                               class="w-full bg-slate-900/90 text-white text-xs font-mono px-4 py-2.5 rounded-xl border border-white/10 focus:border-cyan-500 focus:outline-none">
                    </div>
                </div>

                <!-- Hasil Uji Koneksi -->
                <div id="add-cam-test-result" class="hidden text-xs font-mono p-3 rounded-xl border"></div>

                <!-- Tombol Aksi Modal -->
                <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-white/10">
                    <button type="button" onclick="testAddCameraConnection()" 
                            class="px-4 py-2.5 bg-cyan-500/20 hover:bg-cyan-500/30 border border-cyan-500/40 text-cyan-300 font-bold rounded-xl text-xs font-mono flex items-center gap-2 transition-all active:scale-95">
                        <i data-lucide="radio" class="w-4 h-4"></i>
                        <span>Uji Koneksi (Test)</span>
                    </button>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="closeAddCameraModal()" 
                                class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl text-xs font-mono transition-all">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-cyan-500 hover:from-emerald-400 hover:to-cyan-400 text-white font-bold rounded-xl text-xs font-mono flex items-center gap-2 shadow-[0_0_20px_rgba(16,185,129,0.3)] transition-all active:scale-95">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Simpan & Aktifkan</span>
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 2: KELOLA DAFTAR PERANGKAT KAMERA CCTV                              -->
    <!-- ========================================================================= -->
    <div id="modal-manage-cctv" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4 transition-all animate-fade-in">
        <div class="glass-panel w-full max-w-2xl rounded-3xl p-6 sm:p-8 border border-cyan-500/40 shadow-[0_0_50px_rgba(6,182,212,0.2)] relative space-y-6 max-h-[90vh] overflow-y-auto">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-white/10 pb-4">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-cyan-500/20 text-cyan-400 rounded-2xl border border-cyan-500/30">
                        <i data-lucide="layers" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white leading-tight">Kelola Daftar Kamera CCTV</h3>
                        <p class="text-xs text-slate-400 font-mono">Daftar seluruh kamera CCTV yang tersimpan dalam sistem</p>
                    </div>
                </div>
                <button type="button" onclick="closeManageCamerasModal()" class="p-2 text-slate-400 hover:text-white rounded-xl hover:bg-white/5 transition-all">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- List Container -->
            <div id="manage-cam-list" class="space-y-3">
                <div class="p-4 bg-slate-900/60 rounded-2xl border border-white/5 text-center text-xs text-slate-400 font-mono">
                    Memuat daftar kamera...
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between pt-4 border-t border-white/10">
                <button type="button" onclick="closeManageCamerasModal(); openAddCameraModal();" 
                        class="px-4 py-2.5 bg-emerald-500/20 hover:bg-emerald-500/30 border border-emerald-500/40 text-emerald-300 font-bold rounded-xl text-xs font-mono flex items-center gap-2 transition-all">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    + Tambah Kamera Baru
                </button>
                <button type="button" onclick="closeManageCamerasModal()" 
                        class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-xs font-mono transition-all">
                    Tutup
                </button>
            </div>

        </div>
    </div>

    <!-- JavaScript Interaktif -->
    <script>
        lucide.createIcons();

        // Real-time Clock
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById('realtime-clock').textContent = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Konfigurasi URL IP Perangkat ESP32 (Static IP Default)
        const ESP32_LAMPU1_URL = "{{ rtrim(trim(env('ESP32_LAMPU1_IP', 'http://10.32.72.150')), '/') }}";
        const ESP32_LAMPU2_URL = "{{ rtrim(trim(env('ESP32_LAMPU2_IP', 'http://10.32.72.151')), '/') }}";

        // Logika Saklar Lampu 1 (Servo 1)
        let isLamp1On = true;
        const lamp1ToggleBtn = document.getElementById('lamp1-toggle');
        const lamp1ToggleKnob = document.getElementById('lamp1-toggle-knob');
        const lamp1StatusText = document.getElementById('lamp1-status-text');
        const lamp1IconBg = document.getElementById('lamp1-icon-bg');
        const lamp1Icon = document.getElementById('lamp1-icon');
        const vLamp1Icon = document.getElementById('v-lamp-1-icon');
        const vLamp1Badge = document.getElementById('v-lamp-1-badge');

        // Helper Function untuk Kirim State ke API Laravel / ESP32
        function sendControlApi(data) {
            fetch('/api/control', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            }).catch(err => console.error("Error update API:", err));
        }

        function setLamp1State(onState) {
            isLamp1On = onState;
            const cmd = isLamp1On ? 'on' : 'off';

            // 1. DIRECT FAST-FIRING (Ke IP ESP32 #1 Langsung < 2ms - Tunggal & Seketika)
            fetch(ESP32_LAMPU1_URL + '/lampu1/' + cmd, { mode: 'no-cors', cache: 'no-store' }).catch(() => {});

            // 2. SINKRONISASI LOG & CACHE DATABASE LARAVEL (Background Async)
            sendControlApi({ lamp1: isLamp1On ? 1 : 0 });

            const illum1 = document.getElementById('lamp1-illum-text');
            const angle1 = document.getElementById('lamp1-angle-badge');
            const power1 = document.getElementById('lamp1-power-stat');
            const servo1Stat = document.getElementById('lamp1-servo-status');

            if(isLamp1On) {
                if (lamp1StatusText) {
                    lamp1StatusText.textContent = 'ON (90°)';
                    lamp1StatusText.className = 'text-xs font-bold text-cyan-400 px-3 py-1 bg-cyan-500/10 rounded-lg border border-cyan-500/30 font-mono shadow-[0_0_10px_rgba(6,182,212,0.2)]';
                }
                if (lamp1IconBg) lamp1IconBg.className = 'p-3 bg-cyan-500/20 border border-cyan-500/40 rounded-xl text-cyan-400 shadow-[0_0_15px_rgba(6,182,212,0.25)] transition-all';
                if (lamp1Icon) lamp1Icon.className = 'w-6 h-6 text-cyan-400 animate-pulse';

                if (illum1) illum1.textContent = '100%';
                if (angle1) {
                    angle1.textContent = '90° TEKAN (ON)';
                    angle1.className = 'px-3 py-1.5 bg-cyan-500/20 text-cyan-300 rounded-xl text-xs font-mono font-bold border border-cyan-500/30 shadow-[0_0_10px_rgba(6,182,212,0.2)]';
                }
                if (power1) power1.textContent = 'EST: 18 WATT';
                if (servo1Stat) servo1Stat.textContent = 'SUDUT: 90° TEKAN';

                // Update Virtual Twin
                if (vLamp1Icon) vLamp1Icon.className = 'p-2 rounded-lg bg-cyan-500/20 text-cyan-400 shadow-[0_0_15px_rgba(6,182,212,0.3)] transition-all';
                if (vLamp1Badge) {
                    vLamp1Badge.textContent = 'ON';
                    vLamp1Badge.className = 'text-[9px] font-mono px-1.5 py-0.2 bg-cyan-500/20 text-cyan-300 rounded';
                }
            } else {
                if (lamp1StatusText) {
                    lamp1StatusText.textContent = 'OFF (0°)';
                    lamp1StatusText.className = 'text-xs font-bold text-rose-400 px-3 py-1 bg-rose-500/10 rounded-lg border border-rose-500/30 font-mono';
                }
                if (lamp1IconBg) lamp1IconBg.className = 'p-3 bg-slate-800 border border-white/5 rounded-xl transition-all';
                if (lamp1Icon) lamp1Icon.className = 'w-6 h-6 text-slate-500';

                if (illum1) illum1.textContent = '0%';
                if (angle1) {
                    angle1.textContent = '0° STANDBY (OFF)';
                    angle1.className = 'px-3 py-1.5 bg-slate-800 text-slate-400 rounded-xl text-xs font-mono font-bold border border-white/10';
                }
                if (power1) power1.textContent = 'EST: 0 WATT';
                if (servo1Stat) servo1Stat.textContent = 'SUDUT: 0° STANDBY';

                // Update Virtual Twin
                if (vLamp1Icon) vLamp1Icon.className = 'p-2 rounded-lg bg-slate-800 text-slate-500 transition-all';
                if (vLamp1Badge) {
                    vLamp1Badge.textContent = 'OFF';
                    vLamp1Badge.className = 'text-[9px] font-mono px-1.5 py-0.2 bg-rose-500/20 text-rose-400 rounded';
                }
            }
        }

        const btnLamp1On = document.getElementById('btn-lamp1-on');
        const btnLamp1Off = document.getElementById('btn-lamp1-off');
        if (btnLamp1On) btnLamp1On.addEventListener('click', () => setLamp1State(true));
        if (btnLamp1Off) btnLamp1Off.addEventListener('click', () => setLamp1State(false));

        // Logika Saklar Lampu 2 (Servo 2)
        let isLamp2On = true;
        const lamp2StatusText = document.getElementById('lamp2-status-text');
        const lamp2IconBg = document.getElementById('lamp2-icon-bg');
        const lamp2Icon = document.getElementById('lamp2-icon');
        const vLamp2Icon = document.getElementById('v-lamp-2-icon');
        const vLamp2Badge = document.getElementById('v-lamp-2-badge');

        function setLamp2State(onState) {
            isLamp2On = onState;
            const cmd = isLamp2On ? 'on' : 'off';

            // 1. DIRECT FAST-FIRING (Ke IP ESP32 #2 Langsung < 2ms - Tunggal & Seketika)
            fetch(ESP32_LAMPU2_URL + '/lampu2/' + cmd, { mode: 'no-cors', cache: 'no-store' }).catch(() => {});

            // 2. SINKRONISASI LOG & CACHE DATABASE LARAVEL (Background Async)
            sendControlApi({ lamp2: isLamp2On ? 1 : 0 });

            const illum2 = document.getElementById('lamp2-illum-text');
            const angle2 = document.getElementById('lamp2-angle-badge');
            const power2 = document.getElementById('lamp2-power-stat');
            const servo2Stat = document.getElementById('lamp2-servo-status');

            if(isLamp2On) {
                if (lamp2StatusText) {
                    lamp2StatusText.textContent = 'ON (90°)';
                    lamp2StatusText.className = 'text-xs font-bold text-indigo-400 px-3 py-1 bg-indigo-500/10 rounded-lg border border-indigo-500/30 font-mono shadow-[0_0_10px_rgba(99,102,241,0.2)]';
                }
                if (lamp2IconBg) lamp2IconBg.className = 'p-3 bg-indigo-500/20 border border-indigo-500/40 rounded-xl text-indigo-400 shadow-[0_0_15px_rgba(99,102,241,0.25)] transition-all';
                if (lamp2Icon) lamp2Icon.className = 'w-6 h-6 text-indigo-400 animate-pulse';

                if (illum2) illum2.textContent = '100%';
                if (angle2) {
                    angle2.textContent = '90° TEKAN (ON)';
                    angle2.className = 'px-3 py-1.5 bg-indigo-500/20 text-indigo-300 rounded-xl text-xs font-mono font-bold border border-indigo-500/30 shadow-[0_0_10px_rgba(99,102,241,0.2)]';
                }
                if (power2) power2.textContent = 'EST: 18 WATT';
                if (servo2Stat) servo2Stat.textContent = 'SUDUT: 90° TEKAN';

                // Update Virtual Twin
                if (vLamp2Icon) vLamp2Icon.className = 'p-2 rounded-lg bg-cyan-500/20 text-cyan-400 shadow-[0_0_15px_rgba(6,182,212,0.3)] transition-all';
                if (vLamp2Badge) {
                    vLamp2Badge.textContent = 'ON';
                    vLamp2Badge.className = 'text-[9px] font-mono px-1.5 py-0.2 bg-cyan-500/20 text-cyan-300 rounded';
                }
            } else {
                if (lamp2StatusText) {
                    lamp2StatusText.textContent = 'OFF (0°)';
                    lamp2StatusText.className = 'text-xs font-bold text-rose-400 px-3 py-1 bg-rose-500/10 rounded-lg border border-rose-500/30 font-mono';
                }
                if (lamp2IconBg) lamp2IconBg.className = 'p-3 bg-slate-800 border border-white/5 rounded-xl transition-all';
                if (lamp2Icon) lamp2Icon.className = 'w-6 h-6 text-slate-500';

                if (illum2) illum2.textContent = '0%';
                if (angle2) {
                    angle2.textContent = '0° STANDBY (OFF)';
                    angle2.className = 'px-3 py-1.5 bg-slate-800 text-slate-400 rounded-xl text-xs font-mono font-bold border border-white/10';
                }
                if (power2) power2.textContent = 'EST: 0 WATT';
                if (servo2Stat) servo2Stat.textContent = 'SUDUT: 0° STANDBY';

                // Update Virtual Twin
                if (vLamp2Icon) vLamp2Icon.className = 'p-2 rounded-lg bg-slate-800 text-slate-500 transition-all';
                if (vLamp2Badge) {
                    vLamp2Badge.textContent = 'OFF';
                    vLamp2Badge.className = 'text-[9px] font-mono px-1.5 py-0.2 bg-rose-500/20 text-rose-400 rounded';
                }
            }
        }

        const btnLamp2On = document.getElementById('btn-lamp2-on');
        const btnLamp2Off = document.getElementById('btn-lamp2-off');
        if (btnLamp2On) btnLamp2On.addEventListener('click', () => setLamp2State(true));
        if (btnLamp2Off) btnLamp2Off.addEventListener('click', () => setLamp2State(false));

        // Check Dual-ESP32 Real-Time Heartbeat Telemetry (Online / Offline Nyata)
        function checkEsp32Heartbeat() {
            fetch('/api/device-telemetry')
                .then(res => res.json())
                .then(data => {
                    const pingDot = document.getElementById('esp32-ping-dot');
                    const statusDot = document.getElementById('esp32-status-dot');
                    const statusText = document.getElementById('esp32-status-text');

                    let count = 0;
                    if (data.esp32_1 && data.esp32_1.online) count++;
                    if (data.esp32_2 && data.esp32_2.online) count++;

                    if (count > 0) {
                        if (pingDot) pingDot.className = 'animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75';
                        if (statusDot) statusDot.className = 'relative inline-flex rounded-full h-3 w-3 bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.8)]';
                        if (statusText) {
                            statusText.textContent = 'ESP32 Online';
                            statusText.className = 'text-xs font-semibold text-emerald-400 hidden sm:block font-mono';
                        }
                    } else {
                        if (pingDot) pingDot.className = 'hidden';
                        if (statusDot) statusDot.className = 'relative inline-flex rounded-full h-3 w-3 bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.8)]';
                        if (statusText) {
                            statusText.textContent = 'ESP32 Offline';
                            statusText.className = 'text-xs font-semibold text-rose-400 hidden sm:block font-mono';
                        }
                    }
                })
                .catch(() => {});
        }
        setInterval(checkEsp32Heartbeat, 2000);
        checkEsp32Heartbeat();

        // Change Agent DVR Stream Endpoint Dynamically (MJPEG or iFrame Web UI)
        function changeAgentDvrEndpoint(url) {
            const img = document.getElementById('real-agentdvr-mjpeg');
            const iframe = document.getElementById('real-agentdvr-iframe');
            
            if (!img || !iframe) return;

            if (url.endsWith('.html') || url.endsWith('/') || url.includes('live.html')) {
                img.classList.add('hidden');
                iframe.classList.remove('hidden');
            } else {
                iframe.classList.add('hidden');
                img.classList.remove('hidden');
                img.src = url;
            }
        }

        // Instant Navigation Prefetching for Ultra-Fast Transitions
        document.querySelectorAll('nav a').forEach(link => {
            link.addEventListener('mouseenter', () => {
                const href = link.getAttribute('href');
                if (href && href !== '#' && !document.querySelector(`link[rel="prefetch"][href="${href}"]`)) {
                    const pLink = document.createElement('link');
                    pLink.rel = 'prefetch';
                    pLink.href = href;
                    document.head.appendChild(pLink);
                }
            });
        });

        // =========================================================================
        // LOGIKA KONTROL ON / OFF KAMERA CCTV (SINKRON LANGSUNG DENGAN AGENT DVR)
        // =========================================================================
        let isCameraPowerOn = true;
        const CCTV_STREAM_URL = "http://localhost:8090/video.mjpg?oid=4";

        function toggleCameraPower(forceState = null) {
            if (forceState !== null) {
                isCameraPowerOn = forceState;
            } else {
                isCameraPowerOn = !isCameraPowerOn;
            }

            const cmd = isCameraPowerOn ? 'on' : 'off';
            const agentCmd = isCameraPowerOn ? 'switchon' : 'switchoff';

            // 1. Eksekusi Perintah Langsung ke Agent DVR (Kamera 4 / oid=4)
            fetch(`http://localhost:8090/q.json?cmd=${agentCmd}&oid=4&ot=2`, { mode: 'no-cors' }).catch(() => {});

            // 2. Sinkronisasi Aktivitas ke Backend Laravel & Database Log
            fetch('/api/cctv-power/' + cmd).catch(() => {});

            const img = document.getElementById('real-agentdvr-stream');
            const overlay = document.getElementById('cctv-off-overlay');
            const badge = document.getElementById('cctv-status-badge');
            const dot = document.getElementById('cctv-status-dot');
            const statusText = document.getElementById('cctv-status-text');
            const toggleBtn = document.getElementById('btn-cctv-toggle');
            const toggleIcon = document.getElementById('cctv-toggle-icon');
            const toggleText = document.getElementById('cctv-toggle-text');
            const iconBox = document.getElementById('cctv-icon-box');

            if (isCameraPowerOn) {
                // STATE: ON
                if (img) {
                    setTimeout(() => {
                        if (isCameraPowerOn && img) {
                            img.src = CCTV_STREAM_URL + '&t=' + Date.now();
                            img.classList.remove('opacity-0');
                        }
                    }, 250);
                }
                if (overlay) {
                    overlay.classList.add('hidden');
                    overlay.classList.remove('flex');
                }
                
                if (badge) badge.className = 'px-3 py-1.5 bg-rose-500/10 border border-rose-500/30 rounded-xl text-xs font-bold text-rose-400 flex items-center gap-2 shadow-[0_0_10px_rgba(244,63,94,0.1)] font-mono';
                if (dot) dot.className = 'w-2 h-2 rounded-full bg-rose-500 animate-pulse';
                if (statusText) statusText.textContent = 'LIVE STREAMING';

                if (toggleBtn) {
                    toggleBtn.className = 'px-3.5 py-1.5 bg-cyan-500/20 hover:bg-cyan-500/30 border border-cyan-500/40 text-cyan-300 font-bold rounded-xl text-xs font-mono flex items-center gap-2 transition-all active:scale-95 shadow-[0_0_12px_rgba(6,182,212,0.2)]';
                }
                if (toggleText) toggleText.textContent = 'KAMERA ON';
                if (toggleIcon) toggleIcon.className = 'w-4 h-4 text-cyan-400';
                if (iconBox) iconBox.className = 'p-2.5 bg-cyan-500/20 text-cyan-400 rounded-xl border border-cyan-500/30';
            } else {
                // STATE: OFF (Standby)
                if (img) {
                    img.src = '';
                    img.classList.add('opacity-0');
                }
                if (overlay) {
                    overlay.classList.remove('hidden');
                    overlay.classList.add('flex');
                }

                if (badge) badge.className = 'px-3 py-1.5 bg-slate-800 border border-white/10 rounded-xl text-xs font-bold text-slate-400 flex items-center gap-2 font-mono';
                if (dot) dot.className = 'w-2 h-2 rounded-full bg-slate-500';
                if (statusText) statusText.textContent = 'STANDBY / OFF';

                if (toggleBtn) {
                    toggleBtn.className = 'px-3.5 py-1.5 bg-rose-500/20 hover:bg-rose-500/30 border border-rose-500/40 text-rose-300 font-bold rounded-xl text-xs font-mono flex items-center gap-2 transition-all active:scale-95';
                }
                if (toggleText) toggleText.textContent = 'KAMERA OFF';
                if (toggleIcon) toggleIcon.className = 'w-4 h-4 text-rose-400';
                if (iconBox) iconBox.className = 'p-2.5 bg-slate-800 text-slate-500 rounded-xl border border-white/5';
            }

            // Notifikasi Toast
            const toast = document.createElement('div');
            toast.className = `fixed bottom-6 right-6 px-4 py-3 bg-slate-900/90 backdrop-blur-md border ${isCameraPowerOn ? 'border-cyan-500/50 text-cyan-300' : 'border-rose-500/50 text-rose-300'} rounded-xl text-xs font-mono font-bold shadow-2xl z-50 flex items-center gap-2`;
            toast.innerHTML = `<i data-lucide="${isCameraPowerOn ? 'video' : 'video-off'}" class="w-4 h-4"></i> Agent DVR: Kamera 4 ${isCameraPowerOn ? 'Dinyalakan (ON) 🟢' : 'Dimatikan (OFF) 🔴'}`;
            document.body.appendChild(toast);
            if (typeof lucide !== 'undefined') lucide.createIcons();
            setTimeout(() => toast.remove(), 2500);
        }

        // External PTZ Physical Camera Controller (Kamera 4 / ONVIF Driver)
        function controlCctvPtz(command) {
            if (!isCameraPowerOn) {
                const toast = document.createElement('div');
                toast.className = 'fixed bottom-6 right-6 px-4 py-3 bg-rose-950/90 backdrop-blur-md border border-rose-500/50 text-rose-300 rounded-xl text-xs font-mono font-bold shadow-2xl z-50 flex items-center gap-2';
                toast.innerHTML = `<i data-lucide="alert-triangle" class="w-4 h-4 text-rose-400"></i> Kamera Sedang OFF! Nyalakan kamera terlebih dahulu.`;
                document.body.appendChild(toast);
                if (typeof lucide !== 'undefined') lucide.createIcons();
                setTimeout(() => toast.remove(), 2500);
                return;
            }

            const ispyMap = {
                'up': 'ispydir_1',
                'down': 'ispydir_7',
                'left': 'ispydir_3',
                'right': 'ispydir_5',
                'home': 'ispydir_4'
            };
            const ispyCmd = ispyMap[command] || 'ispydir_4';

            // 1. Eksekusi Driver ONVIF Python Langsung ke Kamera 4 Fisik (IP: 10.32.72.46)
            fetch('/api/cctv-ptz/' + command).catch(() => {});

            // 2. Kirim Perintah Putar Motor ke Agent DVR (Target ID: oid=4)
            fetch(`http://localhost:8090/q.json?cmd=ptzCommand&command=${ispyCmd}&oid=4&ot=2`, { mode: 'no-cors' }).catch(() => {});
            fetch(`http://localhost:8090/command/ptzDirection?dir=${command}&oid=4&ot=2`, { mode: 'no-cors' }).catch(() => {});
            
            // 3. Beri jeda 600ms lalu hentikan putaran agar kamera bergerak bertahap per klik
            if (command !== 'home') {
                setTimeout(() => {
                    fetch('http://localhost:8090/q.json?cmd=ptzCommand&command=ispydir_4&oid=4&ot=2', { mode: 'no-cors' }).catch(() => {});
                }, 600);
            }

            const labels = {
                'up': 'Putar Atas ⬆️',
                'down': 'Putar Bawah ⬇️',
                'left': 'Putar Kiri ⬅️',
                'right': 'Putar Kanan ➡️',
                'home': 'Posisi Tengah (Reset) 🎯'
            };

            const existingToast = document.getElementById('ptz-toast');
            if (existingToast) existingToast.remove();

            const toast = document.createElement('div');
            toast.id = 'ptz-toast';
            toast.className = 'fixed bottom-6 right-6 px-4 py-3 bg-slate-900/90 backdrop-blur-md border border-cyan-500/50 text-cyan-300 rounded-xl text-xs font-mono font-bold shadow-[0_0_25px_rgba(6,182,212,0.3)] z-50 flex items-center gap-2';
            toast.innerHTML = `<i data-lucide="navigation" class="w-4 h-4 text-cyan-400"></i> PTZ Fisik: ${labels[command] || command}`;
            document.body.appendChild(toast);
            if (typeof lucide !== 'undefined') lucide.createIcons();

            setTimeout(() => { if (toast) toast.remove(); }, 2000);
        }

        // =========================================================================
        // MANAJEMEN PERANGKAT KAMERA CCTV (TAMBAH / GANTI / UJI KONEKSI / HAPUS)
        // =========================================================================
        let currentCctvList = [];
        let currentActiveCctv = null;

        function openAddCameraModal() {
            const modal = document.getElementById('modal-add-cctv');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.getElementById('add-cam-test-result').classList.add('hidden');
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function closeAddCameraModal() {
            const modal = document.getElementById('modal-add-cctv');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function openManageCamerasModal() {
            const modal = document.getElementById('modal-manage-cctv');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                renderManageCamerasList();
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function closeManageCamerasModal() {
            const modal = document.getElementById('modal-manage-cctv');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        // Muat Daftar Kamera dari Server
        function loadCctvDevices() {
            fetch('/api/cctv-devices')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success' && data.devices) {
                        currentCctvList = data.devices;
                        currentActiveCctv = data.active || data.devices[0];

                        // Update Dropdown Selector
                        const select = document.getElementById('cctv-camera-select');
                        if (select) {
                            select.innerHTML = '';
                            data.devices.forEach(cam => {
                                const opt = document.createElement('option');
                                opt.value = cam.id;
                                opt.textContent = cam.name;
                                if (currentActiveCctv && cam.id === currentActiveCctv.id) {
                                    opt.selected = true;
                                }
                                select.appendChild(opt);
                            });
                        }

                        // Update Header Label & Subtitle
                        if (currentActiveCctv) {
                            const sub = document.getElementById('cctv-active-subtitle');
                            if (sub) {
                                sub.innerHTML = `${currentActiveCctv.brand || 'IP Camera'} &bull; IP: <span class="text-cyan-400 font-bold">${currentActiveCctv.ip}</span> (Port ${currentActiveCctv.port || 2020})`;
                            }
                        }
                    }
                })
                .catch(err => console.error("Error loading CCTV devices:", err));
        }

        // Ganti Kamera Aktif dari Dropdown
        function onSelectCamera(camId) {
            fetch('/api/cctv-devices/switch/' + camId, { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success' && data.active) {
                        currentActiveCctv = data.active;
                        
                        // Update Subtitle
                        const sub = document.getElementById('cctv-active-subtitle');
                        if (sub) {
                            sub.innerHTML = `${currentActiveCctv.brand || 'IP Camera'} &bull; IP: <span class="text-cyan-400 font-bold">${currentActiveCctv.ip}</span> (Port ${currentActiveCctv.port || 2020})`;
                        }

                        // Update Live Stream Video Frame
                        const img = document.getElementById('real-agentdvr-stream');
                        if (img && isCameraPowerOn) {
                            img.src = currentActiveCctv.stream_url + '&t=' + Date.now();
                        }

                        // Tampilkan Notifikasi Toast
                        showCctvToast(`Beralih ke Kamera: ${currentActiveCctv.name} 📹`, 'success');
                    }
                })
                .catch(err => {
                    console.error("Error switching camera:", err);
                    showCctvToast("Gagal beralih kamera!", "error");
                });
        }

        // Render List Kamera di Modal Kelola
        function renderManageCamerasList() {
            const container = document.getElementById('manage-cam-list');
            if (!container) return;

            if (currentCctvList.length === 0) {
                container.innerHTML = `<div class="p-6 text-center text-xs text-slate-400 font-mono">Belum ada kamera terdaftar.</div>`;
                return;
            }

            container.innerHTML = '';
            currentCctvList.forEach(cam => {
                const isActive = currentActiveCctv && cam.id === currentActiveCctv.id;
                const card = document.createElement('div');
                card.className = `p-4 rounded-2xl border transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 ${
                    isActive 
                        ? 'bg-cyan-500/10 border-cyan-500/40 shadow-[0_0_15px_rgba(6,182,212,0.15)]' 
                        : 'bg-slate-900/60 border-white/5 hover:border-white/20'
                }`;

                card.innerHTML = `
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-xl ${isActive ? 'bg-cyan-500/20 text-cyan-400' : 'bg-slate-800 text-slate-400'}">
                            <i data-lucide="video" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-bold text-white">${cam.name}</h4>
                                ${isActive ? '<span class="px-2 py-0.5 bg-cyan-500/20 text-cyan-300 text-[10px] font-mono font-bold rounded-full border border-cyan-500/40">AKTIF</span>' : ''}
                            </div>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">
                                IP: <span class="text-slate-200">${cam.ip}</span> | ONVIF: ${cam.port} | RTSP: ${cam.rtsp_port || 554} | OID: ${cam.oid || '-'}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 self-end sm:self-center">
                        ${!isActive ? `
                            <button onclick="onSelectCamera('${cam.id}'); closeManageCamerasModal();" 
                                    class="px-3 py-1.5 bg-cyan-500/20 hover:bg-cyan-500/30 text-cyan-300 rounded-xl text-xs font-mono font-bold border border-cyan-500/30 transition-all active:scale-95">
                                Aktifkan
                            </button>
                        ` : ''}
                        ${currentCctvList.length > 1 ? `
                            <button onclick="deleteCctvCamera('${cam.id}', '${cam.name}')" 
                                    class="p-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-xl text-xs border border-rose-500/20 transition-all active:scale-95"
                                    title="Hapus Kamera">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        ` : ''}
                    </div>
                `;
                container.appendChild(card);
            });

            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        // Uji Koneksi Kamera Sebelum Disimpan
        function testAddCameraConnection() {
            const ip = document.getElementById('add-cam-ip').value.trim();
            const port = document.getElementById('add-cam-port').value.trim() || 2020;
            const resBox = document.getElementById('add-cam-test-result');

            if (!ip) {
                resBox.className = 'text-xs font-mono p-3 rounded-xl border bg-rose-500/10 border-rose-500/30 text-rose-300 block';
                resBox.innerHTML = '<i data-lucide="alert-circle" class="w-4 h-4 inline mr-1 text-rose-400"></i> Silakan isi Alamat IP Kamera terlebih dahulu!';
                if (typeof lucide !== 'undefined') lucide.createIcons();
                return;
            }

            resBox.className = 'text-xs font-mono p-3 rounded-xl border bg-cyan-500/10 border-cyan-500/30 text-cyan-300 block animate-pulse';
            resBox.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 inline mr-1 animate-spin text-cyan-400"></i> Menguji koneksi ke ' + ip + ':' + port + '...';
            if (typeof lucide !== 'undefined') lucide.createIcons();

            fetch('/api/cctv-devices/test', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ip: ip, port: port })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.online) {
                    resBox.className = 'text-xs font-mono p-3 rounded-xl border bg-emerald-500/10 border-emerald-500/30 text-emerald-300 block';
                    resBox.innerHTML = `<i data-lucide="check-circle" class="w-4 h-4 inline mr-1 text-emerald-400"></i> ${data.message}`;
                } else {
                    resBox.className = 'text-xs font-mono p-3 rounded-xl border bg-rose-500/10 border-rose-500/30 text-rose-300 block';
                    resBox.innerHTML = `<i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1 text-rose-400"></i> ${data.message || 'Koneksi gagal!'}`;
                }
                if (typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(err => {
                resBox.className = 'text-xs font-mono p-3 rounded-xl border bg-rose-500/10 border-rose-500/30 text-rose-300 block';
                resBox.innerHTML = '<i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1 text-rose-400"></i> Terjadi kesalahan saat menguji koneksi.';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        }

        // Submit Form Tambah Kamera
        function submitAddCameraForm(e) {
            e.preventDefault();

            const payload = {
                name: document.getElementById('add-cam-name').value.trim(),
                brand: document.getElementById('add-cam-brand').value,
                ip: document.getElementById('add-cam-ip').value.trim(),
                port: document.getElementById('add-cam-port').value.trim() || 2020,
                rtsp_port: document.getElementById('add-cam-rtsp-port').value.trim() || 554,
                user: document.getElementById('add-cam-user').value.trim(),
                pass: document.getElementById('add-cam-pass').value.trim(),
                oid: document.getElementById('add-cam-oid').value.trim()
            };

            fetch('/api/cctv-devices', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showCctvToast(data.message, 'success');
                    closeAddCameraModal();
                    document.getElementById('form-add-cctv').reset();
                    loadCctvDevices();

                    // Update live stream langsung
                    if (data.device && isCameraPowerOn) {
                        currentActiveCctv = data.device;
                        const img = document.getElementById('real-agentdvr-stream');
                        if (img) img.src = data.device.stream_url + '&t=' + Date.now();
                    }
                } else {
                    showCctvToast(data.message || 'Gagal menambahkan kamera!', 'error');
                }
            })
            .catch(err => {
                console.error("Error adding camera:", err);
                showCctvToast("Terjadi kesalahan saat menyimpan kamera!", "error");
            });
        }

        // Hapus Kamera
        function deleteCctvCamera(camId, camName) {
            if (!confirm(`Apakah Anda yakin ingin menghapus "${camName}"?`)) return;

            fetch('/api/cctv-devices/' + camId, { method: 'DELETE' })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        showCctvToast(`Kamera "${camName}" berhasil dihapus!`, 'success');
                        loadCctvDevices();
                        renderManageCamerasList();
                    }
                })
                .catch(err => {
                    console.error("Error deleting camera:", err);
                    showCctvToast("Gagal menghapus kamera!", "error");
                });
        }

        function showCctvToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-6 right-6 px-4 py-3 bg-slate-900/90 backdrop-blur-md border ${
                type === 'success' ? 'border-cyan-500/50 text-cyan-300' : 'border-rose-500/50 text-rose-300'
            } rounded-xl text-xs font-mono font-bold shadow-2xl z-50 flex items-center gap-2 animate-fade-in`;
            toast.innerHTML = `<i data-lucide="${type === 'success' ? 'check-circle' : 'alert-circle'}" class="w-4 h-4"></i> ${message}`;
            document.body.appendChild(toast);
            if (typeof lucide !== 'undefined') lucide.createIcons();
            setTimeout(() => toast.remove(), 2500);
        }

        // Panggil loadCctvDevices saat inisialisasi awal
        loadCctvDevices();
    </script>
</body>
</html>
