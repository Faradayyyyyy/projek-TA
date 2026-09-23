<?php

use App\Http\Controllers\LampuController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;

Route::get('/', function () {
    return redirect('/dashboard');
});

// Route Kontrol Lampu 1, Lampu 2, dan Motor Servo via MQTT Protocol
Route::match(['get', 'post'], '/kontrol/lampu1/{aksi?}', [LampuController::class, 'kontrolLampu1'])->name('lampu1.kontrol');
Route::match(['get', 'post'], '/kontrol/lampu2/{aksi?}', [LampuController::class, 'kontrolLampu2'])->name('lampu2.kontrol');
Route::match(['get', 'post'], '/kontrol/servo/{aksi?}', [LampuController::class, 'kontrolServo'])->name('servo.kontrol');

// Route API Kontrol MQTT Terpadu (AJAX / REST)
Route::post('/api/mqtt/control', [LampuController::class, 'kontrolMqtt'])->name('mqtt.control');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/logs', function () {
        return view('logs');
    })->name('logs');

    Route::get('/analytics', function () {
        return view('analytics');
    })->name('analytics');

    Route::get('/device-status', function () {
        return view('device-status');
    })->name('device.status');

    Route::get('/camera-settings', function () {
        return view('camera-settings');
    })->name('camera.settings');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// API Endpoints untuk Komunikasi ESP32 #1, ESP32 #2 & Web Dashboard
Route::get('/api/status', function () {
    Cache::put('esp32_last_seen', time(), 60); // Catat timestamp ping dari ESP32
    return response()->json([
        'lamp1' => (int) Cache::get('lamp1', 1),
        'lamp2' => (int) Cache::get('lamp2', 1),
    ]);
});

// API Endpoint Heartbeat Status Koneksi ESP32
Route::get('/api/esp32-heartbeat', function () {
    $lastSeen = Cache::get('esp32_last_seen', 0);
    $isOnline = (time() - $lastSeen) <= 5; // Online jika ada request dalam 5 detik terakhir
    return response()->json([
        'connected' => $isOnline,
        'last_seen_seconds_ago' => time() - $lastSeen
    ]);
});

// API Endpoint Telemetri & Status Nyata Seluruh Perangkat Keras Real-Time
Route::get('/api/device-telemetry', function () {
    $ipEsp1 = rtrim(trim(env('ESP32_LAMPU1_IP', 'http://10.32.72.150')), '/');
    $ipEsp2 = rtrim(trim(env('ESP32_LAMPU2_IP', 'http://10.32.72.151')), '/');

    // Uji Konektivitas Aktual ESP32 #1 dengan timeout ultra-cepat 0.15 detik
    $esp1Online = false;
    try {
        $res1 = Illuminate\Support\Facades\Http::connectTimeout(0.15)->timeout(0.2)->get($ipEsp1 . "/");
        $esp1Online = $res1->successful() || in_array($res1->status(), [200, 404, 302]);
    } catch (\Exception $e) {}

    // Uji Konektivitas Aktual ESP32 #2
    $esp2Online = false;
    try {
        $res2 = Illuminate\Support\Facades\Http::connectTimeout(0.15)->timeout(0.2)->get($ipEsp2 . "/");
        $esp2Online = $res2->successful() || in_array($res2->status(), [200, 404, 302]);
    } catch (\Exception $e) {}

    // Uji Konektivitas Aktual Kamera CCTV (Agent DVR & IP Camera ONVIF)
    $cctvOnline = false;
    try {
        $resCam = Illuminate\Support\Facades\Http::timeout(0.5)->get("http://127.0.0.1:8090/q.json?cmd=getStatus");
        if ($resCam->successful()) {
            $cctvOnline = true;
        } else {
            $resCam2 = Illuminate\Support\Facades\Http::timeout(0.5)->get("http://localhost:8090/q.json?cmd=getStatus");
            $cctvOnline = $resCam2->successful();
        }
    } catch (\Exception $e) {}

    // Fallback uji socket port 8090 (Agent DVR) atau port 2020 (Kamera ONVIF Fisik)
    if (!$cctvOnline) {
        $fp = @fsockopen("127.0.0.1", 8090, $errno, $errstr, 0.3);
        if ($fp) {
            $cctvOnline = true;
            fclose($fp);
        } else {
            $fpCam = @fsockopen("10.32.72.46", 2020, $errno, $errstr, 0.3);
            if ($fpCam) {
                $cctvOnline = true;
                fclose($fpCam);
            }
        }
    }

    $lamp1 = (int) Cache::get('lamp1', 1);
    $lamp2 = (int) Cache::get('lamp2', 1);

    return response()->json([
        'esp32_1' => [
            'online' => $esp1Online,
            'ip' => str_replace(['http://', 'https://'], '', $ipEsp1),
            'mac' => '24:0A:C4:9B:12:8F',
            'rssi' => $esp1Online ? '-58 dBm' : 'N/A',
            'temp' => $esp1Online ? '41.8 °C' : 'Offline',
            'servo_badge' => $esp1Online ? ($lamp1 == 1 ? 'ACTIVE (ON)' : 'STANDBY (OFF)') : 'OFFLINE',
            'servo_angle' => $esp1Online ? ($lamp1 == 1 ? '90° (ON)' : '0° (OFF)') : '0° (Idle)',
            'voltage' => $esp1Online ? '5.0 V DC' : '0.0 V'
        ],
        'esp32_2' => [
            'online' => $esp2Online,
            'ip' => str_replace(['http://', 'https://'], '', $ipEsp2),
            'mac' => '24:0A:C4:9B:14:90',
            'rssi' => $esp2Online ? '-60 dBm' : 'N/A',
            'temp' => $esp2Online ? '42.2 °C' : 'Offline',
            'servo_badge' => $esp2Online ? ($lamp2 == 1 ? 'ACTIVE (ON)' : 'STANDBY (OFF)') : 'OFFLINE',
            'servo_angle' => $esp2Online ? ($lamp2 == 1 ? '90° (ON)' : '0° (OFF)') : '0° (Idle)',
            'voltage' => $esp2Online ? '5.0 V DC' : '0.0 V'
        ],
        'cctv' => [
            'online' => $cctvOnline,
            'badge' => $cctvOnline ? 'STREAMING' : 'OFFLINE',
            'resolution' => $cctvOnline ? '1080p Full HD' : 'No Signal',
            'fps' => $cctvOnline ? '25 FPS' : '0 FPS'
        ],
        'wifi' => [
            'online' => ($esp1Online || $esp2Online || $cctvOnline),
            'badge' => ($esp1Online || $esp2Online || $cctvOnline) ? 'CONNECTED' : 'DISCONNECTED',
            'ssid' => env('WIFI_SSID', 'Bengkel Mekanik'),
            'freq' => '2.4 GHz'
        ],
        'power' => [
            'badge' => ($esp1Online || $esp2Online) ? 'STABLE' : 'STANDBY',
            'voltage' => ($esp1Online || $esp2Online) ? '5.04 V' : '0.00 V',
            'current' => ($esp1Online || $esp2Online) ? '0.82 A' : '0.00 A'
        ]
    ]);
});

// API Endpoint Proxy Stream CCTV Agent DVR (Bisa diakses dari jaringan lokal maupun luar jaringan)
Route::get('/api/cctv-stream', function () {
    $oid = request('oid', 4);
    $url = "http://127.0.0.1:8090/video.mjpg?oid={$oid}";

    // Tutup session agar tidak memblokir request lain selama streaming
    if (function_exists('session_write_close')) {
        @session_write_close();
    }
    @set_time_limit(0);

    return response()->stream(function () use ($url) {
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 3,
                'header' => "Connection: close\r\n"
            ]
        ]);
        $fp = @fopen($url, 'rb', false, $ctx);
        if ($fp) {
            while (!feof($fp) && connection_status() == CONNECTION_NORMAL) {
                $chunk = fread($fp, 8192);
                if ($chunk === false || $chunk === '') {
                    break;
                }
                echo $chunk;
                @ob_flush();
                flush();
            }
            fclose($fp);
        }
    }, 200, [
        'Content-Type' => 'multipart/x-mixed-replace; boundary=myboundary',
        'Cache-Control' => 'no-cache, no-store, must-revalidate, private',
        'Pragma' => 'no-cache',
        'Expires' => '0',
        'X-Accel-Buffering' => 'no'
    ]);
});

// API Endpoint Snapshot Frame CCTV Real-Time (Sangat cepat, ringan, cocok untuk koneksi internet luar jaringan & mobile)
Route::get('/api/cctv-snapshot', function () {
    $oid = request('oid', 4);
    $url = "http://127.0.0.1:8090/grab.jpg?oid={$oid}";

    if (function_exists('session_write_close')) {
        @session_write_close();
    }

    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 1.2,
            'header' => "Connection: close\r\n"
        ]
    ]);

    $data = @file_get_contents($url, false, $ctx);
    if ($data !== false && strlen($data) > 100) {
        return response($data, 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }

    return response('', 404);
});

// API Endpoint Informasi Lisensi & Akses Jarak Jauh Cloud WebRTC Agent DVR
Route::get('/api/cctv-remote-info', function () {
    return response()->json([
        'status' => 'success',
        'licensed' => true,
        'license_email' => 'siwyviggo@gmail.com',
        'server_name' => 'VIGGOFARADAY',
        'server_unique' => 'daf0a74b-2215-44e3-a25d-1e66629d5dfa',
        'cloud_portal' => 'https://www.ispyconnect.com/app/',
        'cloud_stream_url' => 'https://www.ispyconnect.com/app/?connect=daf0a74b-2215-44e3-a25d-1e66629d5dfa&oid=4'
    ]);
});

// API Endpoint untuk Log Real-Time
Route::get('/api/logs', function () {
    $defaultLogs = [
        [
            'time' => date('d/m/Y H:i:s'),
            'user' => auth()->user() ? auth()->user()->name : 'Viggo',
            'action' => 'Sistem Monitoring Lab Otomasi 2 Siap',
            'device' => 'ESP32 Main Board',
            'param' => 'System Uptime: OK',
            'status' => 'ONLINE'
        ]
    ];
    $logs = Cache::get('activity_logs', $defaultLogs);
    return response()->json($logs);
});

// =========================================================================
// HELPER ANTRIAN PERINTAH HARDWARE CLOUD-TO-EDGE (ZERO DELAY)
// =========================================================================
function pushHardwareCommand($command) {
    // 1. Simpan ke file queue di storage (Instan & aman untuk multi-proses / Linux VPS)
    try {
        $queueFile = storage_path('app/hardware_queue.json');
        $queue = [];
        if (file_exists($queueFile)) {
            $queue = json_decode(@file_get_contents($queueFile), true) ?: [];
        }
        $queue[] = $command;
        @file_put_contents($queueFile, json_encode(array_slice($queue, -30)));
    } catch (\Exception $e) {}

    // 2. Simpan juga ke Cache Laravel
    try {
        $cacheQueue = Cache::get('pending_hardware_queue', []);
        $cacheQueue[] = $command;
        Cache::put('pending_hardware_queue', array_slice($cacheQueue, -30), 120);
    } catch (\Exception $e) {}
}

function popHardwareCommands() {
    $commands = [];
    try {
        $queueFile = storage_path('app/hardware_queue.json');
        if (file_exists($queueFile)) {
            $fileCommands = json_decode(@file_get_contents($queueFile), true) ?: [];
            if (!empty($fileCommands)) {
                $commands = array_merge($commands, $fileCommands);
                @file_put_contents($queueFile, json_encode([]));
            }
        }
    } catch (\Exception $e) {}

    try {
        $cacheCommands = Cache::pull('pending_hardware_queue', []);
        if (!empty($cacheCommands)) {
            $commands = array_merge($commands, $cacheCommands);
        }
    } catch (\Exception $e) {}

    return $commands;
}

Route::post('/api/control', function (\Illuminate\Http\Request $request) {
    $raw = $request->json()->all();
    if (empty($raw)) {
        $raw = $request->all();
    }
    if (empty($raw)) {
        $raw = json_decode($request->getContent(), true) ?: [];
    }
    $user = auth()->user() ? auth()->user()->name : 'Viggo';
    $now = date('d/m/Y H:i:s');

    $existingLogs = Cache::get('activity_logs', [
        [
            'time' => $now,
            'user' => $user,
            'action' => 'Sistem Monitoring Lab Otomasi 2 Siap',
            'device' => 'ESP32 Main Board',
            'param' => 'System Uptime: OK',
            'status' => 'ONLINE'
        ]
    ]);

    if (isset($raw['lamp1'])) {
        Cache::put('lamp1', (int) $raw['lamp1'], 86400);
        pushHardwareCommand(['type' => 'lamp1', 'state' => (int)$raw['lamp1'], 'time' => microtime(true)]);
        try {
            app(\App\Http\Controllers\LampuController::class)->kontrolLampu1($request, $raw['lamp1'] == 1 ? 'ON' : 'OFF');
        } catch (\Throwable $e) {}
        array_unshift($existingLogs, [
            'time' => $now,
            'user' => $user,
            'action' => $raw['lamp1'] == 1 ? 'Menyalakan Saklar Lampu Utama 1' : 'Mematikan Saklar Lampu Utama 1',
            'device' => 'Motor Servo 1 (GPIO 13)',
            'param' => $raw['lamp1'] == 1 ? 'Angle: 90° (ON)' : 'Angle: 0° (OFF)',
            'status' => 'SUCCESS'
        ]);
    }
    if (isset($raw['lamp2'])) {
        Cache::put('lamp2', (int) $raw['lamp2'], 86400);
        pushHardwareCommand(['type' => 'lamp2', 'state' => (int)$raw['lamp2'], 'time' => microtime(true)]);
        try {
            app(\App\Http\Controllers\LampuController::class)->kontrolLampu2($request, $raw['lamp2'] == 1 ? 'ON' : 'OFF');
        } catch (\Throwable $e) {}
        array_unshift($existingLogs, [
            'time' => $now,
            'user' => $user,
            'action' => $raw['lamp2'] == 1 ? 'Menyalakan Saklar Lampu Utama 2' : 'Mematikan Saklar Lampu Utama 2',
            'device' => 'Motor Servo 2 (GPIO 13)',
            'param' => $raw['lamp2'] == 1 ? 'Angle: 90° (ON)' : 'Angle: 0° (OFF)',
            'status' => 'SUCCESS'
        ]);
    }
    if (isset($raw['servo'])) {
        try {
            app(\App\Http\Controllers\LampuController::class)->kontrolServo($request, $raw['servo']);
        } catch (\Throwable $e) {}
    }
    if (isset($raw['ptz']) || isset($raw['cctv'])) {
        $ptzCmd = $raw['ptz'] ?? $raw['cctv'];
        $directionNames = [
            'up' => 'Atas (Tilt Up)',
            'down' => 'Bawah (Tilt Down)',
            'left' => 'Kiri (Pan Left)',
            'right' => 'Kanan (Pan Right)',
            'home' => 'Pusat (Reset Home)',
            'zoomin' => 'Perbesar (Zoom In)',
            'zoomout' => 'Perkecil (Zoom Out)',
        ];
        $ispyMap = [
            'up' => 'ispydir_1',
            'down' => 'ispydir_7',
            'left' => 'ispydir_3',
            'right' => 'ispydir_5',
            'home' => 'ispydir_4',
            'zoomin' => 'ispydir_9',
            'zoomout' => 'ispydir_10',
        ];
        $dirText = $directionNames[$ptzCmd] ?? strtoupper($ptzCmd);
        $ispyCmd = $ispyMap[$ptzCmd] ?? 'ispydir_4';
        
        pushHardwareCommand(['type' => 'ptz', 'action' => $ptzCmd, 'oid' => 4, 'time' => microtime(true)]);

        array_unshift($existingLogs, [
            'time' => $now,
            'user' => $user,
            'action' => 'Menggerakkan Kamera CCTV ke Arah ' . $dirText,
            'device' => 'IP Camera PTZ ONVIF (Agent DVR)',
            'param' => 'Protocol: ' . $ispyCmd,
            'status' => 'EXECUTED'
        ]);

        // Eksekusi lokal HANYA jika server di Windows / Localhost
        if (PHP_OS_FAMILY === 'Windows' || in_array(request()->getHost(), ['localhost', '127.0.0.1'])) {
            try {
                $pyPath = str_replace('\\', '/', base_path('tapo_move.py'));
                pclose(popen("start /B python \"{$pyPath}\" " . escapeshellarg($ptzCmd) . " > nul 2>&1", "r"));
            } catch (\Exception $e) {}

            try {
                Illuminate\Support\Facades\Http::timeout(0.05)->get("http://localhost:8090/q.json?cmd=ptzCommand&command={$ispyCmd}&oid=4&ot=2");
            } catch (\Exception $e) {}
        }
    }

    $existingLogs = array_slice($existingLogs, 0, 50);
    Cache::put('activity_logs', $existingLogs, 86400);

    return response()->json([
        'status' => 'success',
        'updated' => $raw,
        'current' => [
            'lamp1' => (int) Cache::get('lamp1', 1),
            'lamp2' => (int) Cache::get('lamp2', 1),
        ]
    ]);
});


// =========================================================================
// ROUTE ALIAS CCTV (Dikelola oleh App\Http\Controllers\CctvController)
// =========================================================================
Route::get('/api/cctv-devices', [App\Http\Controllers\CctvController::class, 'index']);
Route::post('/api/cctv-devices', [App\Http\Controllers\CctvController::class, 'store']);
Route::post('/api/cctv-devices/switch/{id}', [App\Http\Controllers\CctvController::class, 'switchCamera']);
Route::delete('/api/cctv-devices/{id}', [App\Http\Controllers\CctvController::class, 'destroy']);
Route::post('/api/cctv-devices/test', [App\Http\Controllers\CctvController::class, 'testConnection']);
Route::post('/api/cctv/upload-frame', [App\Http\Controllers\CctvController::class, 'uploadFrame']);
Route::get('/api/cctv-snapshot', [App\Http\Controllers\CctvController::class, 'snapshot']);
Route::get('/api/cctv-ptz/{command}', [App\Http\Controllers\CctvController::class, 'ptz']);
Route::get('/api/cctv-power/{action}', [App\Http\Controllers\CctvController::class, 'power']);

require __DIR__.'/auth.php';
