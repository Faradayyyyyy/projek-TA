<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Status - Lab Otomasi 2</title>
    
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
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-slate-200 hover:bg-white/5 transition-all active:scale-95">
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
            <a href="{{ route('device.status') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 font-medium transition-all shadow-[0_0_15px_rgba(6,182,212,0.1)]">
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
                    <h2 class="text-lg font-bold text-white leading-tight">Status Perangkat Hardware</h2>
                    <p class="text-xs text-slate-400 hidden sm:block">Monitoring ESP32 Controller, Servo 1 & Servo 2 Real-Time</p>
                </div>
            </div>
            
            <div class="flex items-center gap-4 sm:gap-6">
                <!-- ESP32 Online Pulse -->
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

        <!-- Main Content -->
        <div class="p-4 sm:p-6 lg:p-8 flex-1 space-y-6 z-10">

            <!-- Microcontroller Main Status Grid (2 ESP32 Boards) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- ESP32 #1 Card -->
                <div class="glass-panel rounded-2xl p-5 border border-cyan-500/20 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-cyan-500/20 border border-cyan-500/40 rounded-xl text-cyan-400">
                                <i data-lucide="cpu" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-white">ESP32 #1 (Servo Lampu 1)</h3>
                                    <span id="esp32-1-badge" class="px-2 py-0.5 bg-rose-500/20 text-rose-400 text-[10px] font-bold rounded-full border border-rose-500/30 font-mono">MEMERIKSA...</span>
                                </div>
                                <p id="esp32-1-ip" class="text-[11px] text-slate-400 font-mono mt-0.5">IP: 10.32.72.150 | Node #1</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs font-mono">
                        <div class="bg-slate-900/60 p-2 rounded-lg border border-white/5">
                            <span class="text-[9px] text-slate-500 block">Wi-Fi RSSI</span>
                            <span id="esp32-1-rssi" class="text-cyan-400 font-bold">-58 dBm</span>
                        </div>
                        <div class="bg-slate-900/60 p-2 rounded-lg border border-white/5">
                            <span class="text-[9px] text-slate-500 block">Suhu Chip</span>
                            <span id="esp32-1-temp" class="text-emerald-400 font-bold">41.8 &deg;C</span>
                        </div>
                        <div class="bg-slate-900/60 p-2 rounded-lg border border-white/5">
                            <span class="text-[9px] text-slate-500 block">Output Pin</span>
                            <span class="text-indigo-400 font-bold">GPIO 13</span>
                        </div>
                    </div>
                </div>

                <!-- ESP32 #2 Card -->
                <div class="glass-panel rounded-2xl p-5 border border-cyan-500/20 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-cyan-500/20 border border-cyan-500/40 rounded-xl text-cyan-400">
                                <i data-lucide="cpu" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-white">ESP32 #2 (Servo Lampu 2)</h3>
                                    <span id="esp32-2-badge" class="px-2 py-0.5 bg-rose-500/20 text-rose-400 text-[10px] font-bold rounded-full border border-rose-500/30 font-mono">MEMERIKSA...</span>
                                </div>
                                <p id="esp32-2-ip" class="text-[11px] text-slate-400 font-mono mt-0.5">IP: 10.32.72.151 | Node #2</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs font-mono">
                        <div class="bg-slate-900/60 p-2 rounded-lg border border-white/5">
                            <span class="text-[9px] text-slate-500 block">Wi-Fi RSSI</span>
                            <span id="esp32-2-rssi" class="text-cyan-400 font-bold">-60 dBm</span>
                        </div>
                        <div class="bg-slate-900/60 p-2 rounded-lg border border-white/5">
                            <span class="text-[9px] text-slate-500 block">Suhu Chip</span>
                            <span id="esp32-2-temp" class="text-emerald-400 font-bold">42.2 &deg;C</span>
                        </div>
                        <div class="bg-slate-900/60 p-2 rounded-lg border border-white/5">
                            <span class="text-[9px] text-slate-500 block">Output Pin</span>
                            <span class="text-indigo-400 font-bold">GPIO 13</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hardware Component Modules Grid -->
            <div>
                <h3 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
                    <i data-lucide="layers" class="w-5 h-5 text-cyan-400"></i>
                    Status Komponen Fisik Terhubung (Live Real-Time Telemetry)
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <!-- Module 1: Motor Servo 1 (Saklar Lampu 1) -->
                    <div class="glass-panel rounded-2xl p-5 space-y-3 hover:border-cyan-500/30 transition-all">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-cyan-500/20 text-cyan-400 rounded-xl border border-cyan-500/30">
                                    <i data-lucide="lightbulb" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-white">Motor Servo 1 (Lampu 1)</h4>
                                    <p class="text-[11px] text-slate-400 font-mono">GPIO 13 | Penekan Saklar 1</p>
                                </div>
                            </div>
                            <span id="module-servo1-badge" class="px-2 py-1 bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded text-xs font-bold font-mono">OFFLINE</span>
                        </div>
                        <div class="pt-2 border-t border-white/5 grid grid-cols-2 gap-2 text-xs font-mono">
                            <div><span class="text-slate-500">Sudut Servo:</span> <span id="module-servo1-angle" class="text-slate-200">0&deg; (Idle)</span></div>
                            <div><span class="text-slate-500">Tegangan:</span> <span id="module-servo1-volt" class="text-slate-200">0.0 V</span></div>
                        </div>
                    </div>

                    <!-- Module 2: Motor Servo 2 (Saklar Lampu 2) -->
                    <div class="glass-panel rounded-2xl p-5 space-y-3 hover:border-cyan-500/30 transition-all">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-cyan-500/20 text-cyan-400 rounded-xl border border-cyan-500/30">
                                    <i data-lucide="lightbulb" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-white">Motor Servo 2 (Lampu 2)</h4>
                                    <p class="text-[11px] text-slate-400 font-mono">GPIO 13 | Penekan Saklar 2</p>
                                </div>
                            </div>
                            <span id="module-servo2-badge" class="px-2 py-1 bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded text-xs font-bold font-mono">OFFLINE</span>
                        </div>
                        <div class="pt-2 border-t border-white/5 grid grid-cols-2 gap-2 text-xs font-mono">
                            <div><span class="text-slate-500">Sudut Servo:</span> <span id="module-servo2-angle" class="text-slate-200">0&deg; (Idle)</span></div>
                            <div><span class="text-slate-500">Tegangan:</span> <span id="module-servo2-volt" class="text-slate-200">0.0 V</span></div>
                        </div>
                    </div>

                    <!-- Module 3: Kamera Pemantau CCTV -->
                    <div class="glass-panel rounded-2xl p-5 space-y-3 hover:border-cyan-500/30 transition-all">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-cyan-500/20 text-cyan-400 rounded-xl border border-cyan-500/30">
                                    <i data-lucide="cctv" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-white">Kamera CCTV Lab 2</h4>
                                    <p class="text-[11px] text-slate-400 font-mono">Stream RTSP | IP Camera</p>
                                </div>
                            </div>
                            <span id="module-cctv-badge" class="px-2 py-1 bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded text-xs font-bold font-mono">OFFLINE</span>
                        </div>
                        <div class="pt-2 border-t border-white/5 grid grid-cols-2 gap-2 text-xs font-mono">
                            <div><span class="text-slate-500">Resolusi:</span> <span id="module-cctv-res" class="text-slate-200">1080p</span></div>
                            <div><span class="text-slate-500">FPS:</span> <span id="module-cctv-fps" class="text-slate-200">25 FPS</span></div>
                        </div>
                    </div>

                    <!-- Module 5: Power Supply 5V 3A Adapter -->
                    <div class="glass-panel rounded-2xl p-5 space-y-3 hover:border-emerald-500/30 transition-all">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-emerald-500/20 text-emerald-400 rounded-xl border border-emerald-500/30">
                                    <i data-lucide="zap" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-white">Power Supply Adapter</h4>
                                    <p class="text-[11px] text-slate-400 font-mono">5V 3A DC | Step-Down</p>
                                </div>
                            </div>
                            <span id="module-power-badge" class="px-2 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded text-xs font-bold font-mono">STABLE</span>
                        </div>
                        <div class="pt-2 border-t border-white/5 grid grid-cols-2 gap-2 text-xs font-mono">
                            <div><span class="text-slate-500">Tegangan:</span> <span id="module-power-volt" class="text-slate-200">5.04 V</span></div>
                            <div><span class="text-slate-500">Arus:</span> <span id="module-power-amp" class="text-slate-200">0.82 A</span></div>
                        </div>
                    </div>

                    <!-- Module 6: Wi-Fi Access Point Module -->
                    <div class="glass-panel rounded-2xl p-5 space-y-3 hover:border-purple-500/30 transition-all">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-purple-500/20 text-purple-400 rounded-xl border border-purple-500/30">
                                    <i data-lucide="wifi" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-white">Wi-Fi Network Module</h4>
                                    <p id="module-wifi-ssid" class="text-[11px] text-slate-400 font-mono">SSID: Bengkel Mekanik</p>
                                </div>
                            </div>
                            <span id="module-wifi-badge" class="px-2 py-1 bg-purple-500/10 text-purple-400 border border-purple-500/20 rounded text-xs font-bold font-mono">CONNECTED</span>
                        </div>
                        <div class="pt-2 border-t border-white/5 grid grid-cols-2 gap-2 text-xs font-mono">
                            <div><span class="text-slate-500">Frekuensi:</span> <span class="text-slate-200">2.4 GHz</span></div>
                            <div><span class="text-slate-500">Koneksi:</span> <span id="module-wifi-state" class="text-cyan-400">Aktif</span></div>
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

        // Polling Telemetri Komponen & ESP32 Nyata Setiap 2 Detik
        function updateDeviceTelemetry() {
            fetch('/api/device-telemetry')
                .then(res => res.json())
                .then(data => {
                    // 1. ESP32 #1 Card
                    const esp1Badge = document.getElementById('esp32-1-badge');
                    const esp1Ip = document.getElementById('esp32-1-ip');
                    const esp1Rssi = document.getElementById('esp32-1-rssi');
                    const esp1Temp = document.getElementById('esp32-1-temp');
                    if (data.esp32_1.online) {
                        esp1Badge.textContent = 'ONLINE';
                        esp1Badge.className = 'px-2 py-0.5 bg-emerald-500/20 text-emerald-400 text-[10px] font-bold rounded-full border border-emerald-500/30 font-mono';
                    } else {
                        esp1Badge.textContent = 'OFFLINE';
                        esp1Badge.className = 'px-2 py-0.5 bg-rose-500/20 text-rose-400 text-[10px] font-bold rounded-full border border-rose-500/30 font-mono';
                    }
                    if (esp1Ip) esp1Ip.textContent = `IP: ${data.esp32_1.ip} | MAC: ${data.esp32_1.mac}`;
                    if (esp1Rssi) esp1Rssi.textContent = data.esp32_1.rssi;
                    if (esp1Temp) esp1Temp.textContent = data.esp32_1.temp;

                    // 2. ESP32 #2 Card
                    const esp2Badge = document.getElementById('esp32-2-badge');
                    const esp2Ip = document.getElementById('esp32-2-ip');
                    const esp2Rssi = document.getElementById('esp32-2-rssi');
                    const esp2Temp = document.getElementById('esp32-2-temp');
                    if (data.esp32_2.online) {
                        esp2Badge.textContent = 'ONLINE';
                        esp2Badge.className = 'px-2 py-0.5 bg-emerald-500/20 text-emerald-400 text-[10px] font-bold rounded-full border border-emerald-500/30 font-mono';
                    } else {
                        esp2Badge.textContent = 'OFFLINE';
                        esp2Badge.className = 'px-2 py-0.5 bg-rose-500/20 text-rose-400 text-[10px] font-bold rounded-full border border-rose-500/30 font-mono';
                    }
                    if (esp2Ip) esp2Ip.textContent = `IP: ${data.esp32_2.ip} | MAC: ${data.esp32_2.mac}`;
                    if (esp2Rssi) esp2Rssi.textContent = data.esp32_2.rssi;
                    if (esp2Temp) esp2Temp.textContent = data.esp32_2.temp;

                    // 3. Module Servo 1
                    const servo1Badge = document.getElementById('module-servo1-badge');
                    const servo1Angle = document.getElementById('module-servo1-angle');
                    const servo1Volt = document.getElementById('module-servo1-volt');
                    if (servo1Badge) {
                        servo1Badge.textContent = data.esp32_1.servo_badge;
                        servo1Badge.className = data.esp32_1.online 
                            ? 'px-2 py-1 bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 rounded text-xs font-bold font-mono'
                            : 'px-2 py-1 bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded text-xs font-bold font-mono';
                    }
                    if (servo1Angle) servo1Angle.textContent = data.esp32_1.servo_angle;
                    if (servo1Volt) servo1Volt.textContent = data.esp32_1.voltage;

                    // 4. Module Servo 2
                    const servo2Badge = document.getElementById('module-servo2-badge');
                    const servo2Angle = document.getElementById('module-servo2-angle');
                    const servo2Volt = document.getElementById('module-servo2-volt');
                    if (servo2Badge) {
                        servo2Badge.textContent = data.esp32_2.servo_badge;
                        servo2Badge.className = data.esp32_2.online 
                            ? 'px-2 py-1 bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 rounded text-xs font-bold font-mono'
                            : 'px-2 py-1 bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded text-xs font-bold font-mono';
                    }
                    if (servo2Angle) servo2Angle.textContent = data.esp32_2.servo_angle;
                    if (servo2Volt) servo2Volt.textContent = data.esp32_2.voltage;

                    // 5. Module CCTV
                    const cctvBadge = document.getElementById('module-cctv-badge');
                    const cctvRes = document.getElementById('module-cctv-res');
                    const cctvFps = document.getElementById('module-cctv-fps');
                    if (cctvBadge) {
                        cctvBadge.textContent = data.cctv.badge;
                        cctvBadge.className = data.cctv.online
                            ? 'px-2 py-1 bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 rounded text-xs font-bold font-mono'
                            : 'px-2 py-1 bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded text-xs font-bold font-mono';
                    }
                    if (cctvRes) cctvRes.textContent = data.cctv.resolution;
                    if (cctvFps) cctvFps.textContent = data.cctv.fps;

                    // 6. Top Heartbeat Bar Indicator
                    const pingDot = document.getElementById('esp32-ping-dot');
                    const statusDot = document.getElementById('esp32-status-dot');
                    const statusText = document.getElementById('esp32-status-text');
                    const anyOnline = (data.esp32_1.online || data.esp32_2.online);
                    if (anyOnline) {
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
                .catch(err => console.error("Error fetching telemetry:", err));
        }

        setInterval(updateDeviceTelemetry, 2000);
        updateDeviceTelemetry();
    </script>
</body>
</html>
