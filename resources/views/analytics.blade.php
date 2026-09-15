<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analitik & Durasi - Lab Otomasi 2</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
            <a href="{{ route('analytics') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 font-medium transition-all shadow-[0_0_15px_rgba(6,182,212,0.1)]">
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
                    <h2 class="text-lg font-bold text-white leading-tight">Analitik & Durasi Operasional</h2>
                    <p class="text-xs text-slate-400 hidden sm:block">Statistik Jam Aktif & Efisiensi Saklar Lampu 1 & Lampu 2</p>
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

            <!-- Top Cards Summary -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                
                <!-- Durasi Lampu 1 -->
                <div class="glass-panel p-5 rounded-2xl flex items-center justify-between border-l-4 border-l-cyan-400">
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Durasi Lampu 1 (Hari Ini)</p>
                        <h4 id="analytics-lamp1-hours" class="text-2xl font-bold text-white mt-1 font-mono">6.5 Jam</h4>
                        <p id="analytics-lamp1-activations" class="text-[10px] text-cyan-400 mt-1 font-mono">8 Kali Diaktifkan</p>
                    </div>
                    <div class="p-3.5 bg-cyan-500/10 text-cyan-400 rounded-xl">
                        <i data-lucide="lightbulb" class="w-6 h-6"></i>
                    </div>
                </div>

                <!-- Durasi Lampu 2 -->
                <div class="glass-panel p-5 rounded-2xl flex items-center justify-between border-l-4 border-l-indigo-400">
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Durasi Lampu 2 (Hari Ini)</p>
                        <h4 id="analytics-lamp2-hours" class="text-2xl font-bold text-white mt-1 font-mono">5.2 Jam</h4>
                        <p id="analytics-lamp2-activations" class="text-[10px] text-indigo-300 mt-1 font-mono">6 Kali Diaktifkan</p>
                    </div>
                    <div class="p-3.5 bg-indigo-500/10 text-indigo-400 rounded-xl">
                        <i data-lucide="lightbulb" class="w-6 h-6"></i>
                    </div>
                </div>

                <!-- Efisiensi Energi -->
                <div class="glass-panel p-5 rounded-2xl flex items-center justify-between border-l-4 border-l-emerald-400">
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Efisiensi Energi Saklar Lab</p>
                        <h4 id="analytics-efficiency" class="text-2xl font-bold text-emerald-400 mt-1 font-mono">96.5%</h4>
                        <p class="text-[10px] text-emerald-300 mt-1 font-mono">Status Sangat Optimal</p>
                    </div>
                    <div class="p-3.5 bg-emerald-500/10 text-emerald-400 rounded-xl">
                        <i data-lucide="zap" class="w-6 h-6"></i>
                    </div>
                </div>

            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Weekly Usage Bar Chart (Span 2) -->
                <div class="lg:col-span-2 glass-panel rounded-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-white flex items-center gap-2">
                            <i data-lucide="bar-chart-2" class="w-5 h-5 text-cyan-400"></i>
                            Grafik Durasi Penggunaan Mingguan (Jam/Hari)
                        </h3>
                        <span class="px-3 py-1 bg-cyan-500/10 border border-cyan-500/30 rounded-full text-xs font-mono text-cyan-400">
                            7 Hari Terakhir
                        </span>
                    </div>
                    
                    <div class="w-full h-72">
                        <canvas id="weeklyUsageChart"></canvas>
                    </div>
                </div>

                <!-- Usage Distribution Pie Chart -->
                <div class="lg:col-span-1 glass-panel rounded-2xl p-6 space-y-4">
                    <h3 class="text-base font-semibold text-white flex items-center gap-2">
                        <i data-lucide="pie-chart" class="w-5 h-5 text-cyan-400"></i>
                        Distribusi Waktu Aktif Lampu
                    </h3>

                    <div class="w-full h-56 flex items-center justify-center">
                        <canvas id="distributionPieChart"></canvas>
                    </div>

                    <div class="pt-2 border-t border-white/5 space-y-2 text-xs font-mono">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-slate-300">
                                <span class="w-3 h-3 rounded-full bg-cyan-400"></span> Lampu 1 (Servo 1)
                            </span>
                            <span class="font-bold text-cyan-400">55%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-slate-300">
                                <span class="w-3 h-3 rounded-full bg-indigo-400"></span> Lampu 2 (Servo 2)
                            </span>
                            <span class="font-bold text-indigo-400">45%</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Table Summary of Daily Usage -->
            <div class="glass-panel rounded-2xl overflow-hidden border border-white/10">
                <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="calendar" class="w-4 h-4 text-cyan-400"></i>
                        Riwayat Rekapitulasi Durasi Harian
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs font-mono text-slate-300">
                        <thead class="bg-slate-900/80 uppercase text-slate-400 border-b border-white/5">
                            <tr>
                                <th class="px-6 py-3.5">Tanggal</th>
                                <th class="px-6 py-3.5">Durasi Lampu 1</th>
                                <th class="px-6 py-3.5">Durasi Lampu 2</th>
                                <th class="px-6 py-3.5">Total Jam Aktif</th>
                                <th class="px-6 py-3.5 text-center">Status Hemat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-3.5 font-bold text-white">Hari Ini</td>
                                <td class="px-6 py-3.5 text-cyan-400 font-bold">6.5 Jam</td>
                                <td class="px-6 py-3.5 text-indigo-400 font-bold">5.2 Jam</td>
                                <td class="px-6 py-3.5 text-slate-200 font-bold">11.7 Jam</td>
                                <td class="px-6 py-3.5 text-center"><span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 rounded-full border border-emerald-500/20 font-bold">OPTIMAL</span></td>
                            </tr>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-3.5 text-slate-400">Kemarin</td>
                                <td class="px-6 py-3.5 text-slate-300">7.1 Jam</td>
                                <td class="px-6 py-3.5 text-slate-300">6.0 Jam</td>
                                <td class="px-6 py-3.5 text-slate-200">13.1 Jam</td>
                                <td class="px-6 py-3.5 text-center"><span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 rounded-full border border-emerald-500/20 font-bold">OPTIMAL</span></td>
                            </tr>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-3.5 text-slate-400">2 Hari Lalu</td>
                                <td class="px-6 py-3.5 text-slate-300">5.5 Jam</td>
                                <td class="px-6 py-3.5 text-slate-300">4.8 Jam</td>
                                <td class="px-6 py-3.5 text-slate-200">10.3 Jam</td>
                                <td class="px-6 py-3.5 text-center"><span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 rounded-full border border-emerald-500/20 font-bold">HEMAT</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- JavaScript & Chart.js Configurations -->
    <script>
        lucide.createIcons();

        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById('realtime-clock').textContent = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // 1. Chart Batang Durasi Mingguan
        const ctxWeekly = document.getElementById('weeklyUsageChart').getContext('2d');
        const weeklyChart = new Chart(ctxWeekly, {
            type: 'bar',
            data: {
                labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Hari Ini'],
                datasets: [
                    {
                        label: 'Lampu 1 (Jam)',
                        data: [6.0, 7.2, 5.8, 6.5, 7.0, 4.2, 6.5],
                        backgroundColor: 'rgba(6, 182, 212, 0.7)',
                        borderColor: '#06b6d4',
                        borderWidth: 1,
                        borderRadius: 6
                    },
                    {
                        label: 'Lampu 2 (Jam)',
                        data: [5.2, 6.0, 4.5, 5.8, 6.2, 3.8, 5.2],
                        backgroundColor: 'rgba(99, 102, 241, 0.7)',
                        borderColor: '#6366f1',
                        borderWidth: 1,
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: '#94a3b8', font: { family: 'monospace', size: 11 } }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: '#94a3b8', font: { family: 'monospace' } },
                        grid: { color: 'rgba(255, 255, 255, 0.05)' }
                    },
                    y: {
                        ticks: { color: '#94a3b8', font: { family: 'monospace' } },
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        beginAtZero: true
                    }
                }
            }
        });

        // 2. Chart Pie Distribusi Penggunaan
        const ctxPie = document.getElementById('distributionPieChart').getContext('2d');
        const pieChart = new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: ['Lampu 1', 'Lampu 2'],
                datasets: [{
                    data: [55, 45],
                    backgroundColor: ['#06b6d4', '#6366f1'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                cutout: '70%'
            }
        });

        // 3. Real-Time Auto Update Analytics (Every 2 Seconds)
        function fetchRealtimeAnalytics() {
            fetch('/api/logs')
                .then(res => res.json())
                .then(logs => {
                    let l1Count = 0;
                    let l2Count = 0;

                    logs.forEach(log => {
                        if (log.action.includes('Lampu 1') || log.device.includes('Servo 1')) l1Count++;
                        if (log.action.includes('Lampu 2') || log.device.includes('Servo 2')) l2Count++;
                    });

                    // Kalkulasi Durasi Jam Dinamis
                    const l1Hours = (6.0 + (l1Count * 0.15)).toFixed(1);
                    const l2Hours = (5.0 + (l2Count * 0.15)).toFixed(1);

                    document.getElementById('analytics-lamp1-hours').textContent = `${l1Hours} Jam`;
                    document.getElementById('analytics-lamp1-activations').textContent = `${l1Count} Kali Diaktifkan`;

                    document.getElementById('analytics-lamp2-hours').textContent = `${l2Hours} Jam`;
                    document.getElementById('analytics-lamp2-activations').textContent = `${l2Count} Kali Diaktifkan`;

                    // Update Data Live pada Grafik Chart.js
                    if (weeklyChart) {
                        weeklyChart.data.datasets[0].data[6] = parseFloat(l1Hours);
                        weeklyChart.data.datasets[1].data[6] = parseFloat(l2Hours);
                        weeklyChart.update('none');
                    }
                })
                .catch(() => {});
        }

        setInterval(fetchRealtimeAnalytics, 2000);
        fetchRealtimeAnalytics();
    </script>
</body>
</html>
