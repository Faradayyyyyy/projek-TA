<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs Aktivitas - Lab Otomasi 2</title>
    
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
            <a href="{{ route('logs') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 font-medium transition-all shadow-[0_0_15px_rgba(6,182,212,0.1)]">
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
                    <h2 class="text-lg font-bold text-white leading-tight">Log Aktivitas & Audit Perangkat</h2>
                    <p class="text-xs text-slate-400 hidden sm:block">Riwayat Perintah Saklar Servo Lampu Utama 1 & 2</p>
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

        <!-- Main Content -->
        <div class="p-4 sm:p-6 lg:p-8 flex-1 space-y-6 z-10">

            <!-- Stats Bar -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="glass-panel p-4 rounded-2xl flex items-center justify-between border-l-4 border-l-cyan-400">
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Perintah Kontrol Lampu 1 & 2</p>
                        <h4 id="stat-lamp-actions" class="text-xl font-bold text-white mt-1">0 Aksi</h4>
                    </div>
                    <div class="p-3 bg-cyan-500/10 text-cyan-400 rounded-xl">
                        <i data-lucide="lightbulb" class="w-6 h-6"></i>
                    </div>
                </div>

                <div class="glass-panel p-4 rounded-2xl flex items-center justify-between border-l-4 border-l-emerald-400">
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Keberhasilan Perintah</p>
                        <h4 id="stat-success-rate" class="text-xl font-bold text-emerald-400 mt-1">100% OK</h4>
                    </div>
                    <div class="p-3 bg-emerald-500/10 text-emerald-400 rounded-xl">
                        <i data-lucide="check-circle" class="w-6 h-6"></i>
                    </div>
                </div>

                <div class="glass-panel p-4 rounded-2xl flex items-center justify-between border-l-4 border-l-indigo-400">
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Sistem Dual-ESP32 Uptime</p>
                        <h4 id="stat-esp32-uptime" class="text-xl font-bold text-emerald-400 mt-1">ONLINE</h4>
                    </div>
                    <div class="p-3 bg-indigo-500/10 text-indigo-400 rounded-xl">
                        <i data-lucide="cpu" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>



            <!-- Table Section -->
            <div class="glass-panel rounded-2xl overflow-hidden border border-white/10">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="bg-slate-900/80 text-xs font-mono uppercase text-slate-400 border-b border-white/5">
                            <tr>
                                <th class="px-6 py-4">Waktu & Tanggal</th>
                                <th class="px-6 py-4">Pengguna</th>
                                <th class="px-6 py-4">Aksi / Perintah Kontrol</th>
                                <th class="px-6 py-4">Komponen Perangkat</th>
                                <th class="px-6 py-4">Sinyal / Parameter</th>
                                <th class="px-6 py-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="logs-tbody" class="divide-y divide-white/5 font-mono text-xs">
                            <!-- Data log real-time akan di-render otomatis via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination -->
                <div class="px-6 py-4 bg-slate-900/60 border-t border-white/5 flex items-center justify-between text-xs text-slate-400 font-mono">
                    <div class="flex items-center gap-2">
                        <div class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-cyan-500"></span>
                        </div>
                        <span>Live Real-Time Logs Active (Auto-sync 2s)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="px-3 py-1 bg-cyan-500/20 text-cyan-400 font-bold rounded border border-cyan-500/30">1</button>
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

        // Real-time Logs Auto-Fetch & Dynamic Stat Cards (Every 2 Seconds)
        function fetchRealtimeLogs() {
            fetch('/api/logs')
                .then(res => res.json())
                .then(data => {
                    const tbody = document.getElementById('logs-tbody');
                    if (!tbody) return;
                    
                    let html = '';
                    let lampCount = 0;
                    let irCount = 0;

                    data.forEach(log => {
                        if (log.device.includes('Servo') || log.device.includes('Lampu')) lampCount++;
                        if (log.device.includes('IR') || log.device.includes('AC')) irCount++;

                        let statusBadge = `<span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 rounded-full border border-emerald-500/20 font-bold">${log.status}</span>`;
                        if(log.status === 'TRANSMITTED') {
                            statusBadge = `<span class="px-2.5 py-1 bg-blue-500/10 text-blue-400 rounded-full border border-blue-500/20 font-bold">TRANSMITTED</span>`;
                        } else if(log.status === 'ONLINE') {
                            statusBadge = `<span class="px-2.5 py-1 bg-cyan-500/10 text-cyan-400 rounded-full border border-cyan-500/20 font-bold">ONLINE</span>`;
                        }

                        html += `
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 text-slate-400 font-mono">${log.time}</td>
                                <td class="px-6 py-4 font-semibold text-cyan-400 flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-cyan-400"></div>
                                    ${log.user}
                                </td>
                                <td class="px-6 py-4 text-slate-200">${log.action}</td>
                                <td class="px-6 py-4 font-mono">${log.device}</td>
                                <td class="px-6 py-4 text-cyan-300 font-mono">${log.param}</td>
                                <td class="px-6 py-4 text-center">${statusBadge}</td>
                            </tr>
                        `;
                    });
                    tbody.innerHTML = html;

                    // Update Real-Time Stat Cards
                    document.getElementById('stat-lamp-actions').textContent = `${lampCount} Aksi`;
                    document.getElementById('stat-ir-transmissions').textContent = `${irCount} Transmisi`;
                    document.getElementById('stat-success-rate').textContent = '100% OK';
                })
                .catch(err => console.error("Error fetching real-time logs:", err));

            // Fetch ESP32 Heartbeat for Card 4
            fetch('/api/esp32-heartbeat')
                .then(res => res.json())
                .then(hb => {
                    const uptimeEl = document.getElementById('stat-esp32-uptime');
                    if (!uptimeEl) return;
                    if (hb.connected) {
                        uptimeEl.textContent = 'ONLINE (99.9%)';
                        uptimeEl.className = 'text-xl font-bold text-emerald-400 mt-1';
                    } else {
                        uptimeEl.textContent = 'OFFLINE';
                        uptimeEl.className = 'text-xl font-bold text-rose-400 mt-1';
                    }
                })
                .catch(() => {});
        }

        setInterval(fetchRealtimeLogs, 2000);
        fetchRealtimeLogs();
    </script>
</body>
</html>
