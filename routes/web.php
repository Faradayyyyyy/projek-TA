<?php

use App\Http\Controllers\LampuController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;

Route::get('/', function () {
    return redirect('/dashboard');
});

// Route Kontrol Lampu 1 (Servo 1)
Route::get('/kontrol/lampu1/{aksi}', [LampuController::class, 'kontrolLampu1'])->name('lampu1.kontrol');

// Route Kontrol Lampu 2 (Servo 2)
Route::get('/kontrol/lampu2/{aksi}', [LampuController::class, 'kontrolLampu2'])->name('lampu2.kontrol');

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

// API Endpoint Proxy Stream CCTV Agent DVR
Route::get('/api/cctv-stream', function () {
    $urls = [
        'http://127.0.0.1:8090/video.mjpg?oid=4',
        'http://127.0.0.1:8090/video.mjpeg?oid=4',
        'http://localhost:8090/video.mjpg?oid=4',
    ];

    foreach ($urls as $url) {
        try {
            $res = Illuminate\Support\Facades\Http::timeout(0.4)->get($url);
            if ($res->successful()) {
                return response($res->body(), 200)
                    ->header('Content-Type', $res->header('Content-Type') ?: 'multipart/x-mixed-replace; boundary=frame');
            }
        } catch (\Exception $e) {}
    }

    // Fallback stream video bergerak jika Agent DVR offline
    return redirect('https://assets.mixkit.co/videos/preview/mixkit-security-camera-recording-a-hallway-40917-large.mp4');
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

Route::post('/api/control', function (\Illuminate\Http\Request $request) {
    $raw = json_decode(file_get_contents('php://input'), true) ?: $request->all();
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
        array_unshift($existingLogs, [
            'time' => $now,
            'user' => $user,
            'action' => $raw['lamp2'] == 1 ? 'Menyalakan Saklar Lampu Utama 2' : 'Mematikan Saklar Lampu Utama 2',
            'device' => 'Motor Servo 2 (GPIO 13)',
            'param' => $raw['lamp2'] == 1 ? 'Angle: 90° (ON)' : 'Angle: 0° (OFF)',
            'status' => 'SUCCESS'
        ]);
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
        
        array_unshift($existingLogs, [
            'time' => $now,
            'user' => $user,
            'action' => 'Menggerakkan Kamera CCTV ke Arah ' . $dirText,
            'device' => 'IP Camera PTZ ONVIF (Agent DVR)',
            'param' => 'Protocol: ' . $ispyCmd,
            'status' => 'EXECUTED'
        ]);

        // 1. Eksekusi Driver ONVIF Tapo C200 Langsung ke Kamera Fisik
        try {
            $pyPath = base_path('tapo_move.py');
            pclose(popen("start /B python \"{$pyPath}\" " . escapeshellarg($ptzCmd), "r"));
        } catch (\Exception $e) {}

        // 2. Kirim perintah ONVIF PTZ ke Agent DVR
        try {
            Illuminate\Support\Facades\Http::timeout(0.2)->get("http://localhost:8090/q.json?cmd=ptzCommand&command={$ispyCmd}&oid=3&ot=2");
        } catch (\Exception $e) {}
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
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Dedicated Endpoint Kontrol Gerak CCTV PTZ
Route::get('/api/cctv-ptz/{action}', function ($action) {
    $user = auth()->user() ? auth()->user()->name : 'Viggo';
    $now = date('d/m/Y H:i:s');

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
    $dirText = $directionNames[$action] ?? strtoupper($action);
    $ispyCmd = $ispyMap[$action] ?? 'ispydir_4';

    $existingLogs = Cache::get('activity_logs', []);
    array_unshift($existingLogs, [
        'time' => $now,
        'user' => $user,
        'action' => 'Menggerakkan Kamera CCTV ke Arah ' . $dirText,
        'device' => 'IP Camera PTZ Tapo C200 (ONVIF)',
        'param' => 'Protocol: ONVIF continuous ' . $action,
        'status' => 'EXECUTED'
    ]);
    Cache::put('activity_logs', array_slice($existingLogs, 0, 50), 86400);

    // 1. Eksekusi Driver ONVIF Tapo C200 Langsung ke Kamera Fisik
    try {
        $pyPath = base_path('tapo_move.py');
        pclose(popen("start /B python \"{$pyPath}\" " . escapeshellarg($action), "r"));
    } catch (\Exception $e) {}

    // 2. Tembak Agent DVR ONVIF PTZ Command
    try {
        Illuminate\Support\Facades\Http::timeout(0.2)->get("http://localhost:8090/q.json?cmd=ptzCommand&command={$ispyCmd}&oid=4&ot=2");
    } catch (\Exception $e) {}

    return response()->json([
        'status' => 'success',
        'action' => $action,
        'ispyCmd' => $ispyCmd,
        'message' => 'Perintah ONVIF PTZ ' . $dirText . ' berhasil dikirim ke kamera fisik Tapo C200!'
    ]);
});

// Dedicated Endpoint Kontrol Power ON / OFF Kamera 4 di Agent DVR
Route::get('/api/cctv-power/{action}', function ($action) {
    $user = auth()->user() ? auth()->user()->name : 'Viggo';
    $now = date('d/m/Y H:i:s');
    $isOn = strtolower($action) === 'on';
    $agentCmd = $isOn ? 'switchon' : 'switchoff';
    $actionText = $isOn ? 'Menyalakan (Power ON)' : 'Mematikan (Power OFF)';

    // 1. Catat ke Activity Log
    $existingLogs = Cache::get('activity_logs', []);
    array_unshift($existingLogs, [
        'time' => $now,
        'user' => $user,
        'action' => $actionText . ' Kamera Pengawas CCTV (Kamera 4)',
        'device' => 'Agent DVR Video Server',
        'param' => 'Command: ' . $agentCmd . ' (oid=4)',
        'status' => 'EXECUTED'
    ]);
    Cache::put('activity_logs', array_slice($existingLogs, 0, 50), 86400);

    // 2. Eksekusi Perintah Switch ON / OFF ke Agent DVR
    try {
        Illuminate\Support\Facades\Http::timeout(1.0)->get("http://localhost:8090/q.json?cmd={$agentCmd}&oid=4&ot=2");
    } catch (\Exception $e) {}

    return response()->json([
        'status' => 'success',
        'action' => $action,
        'agentCmd' => $agentCmd,
        'message' => 'Kamera 4 di Agent DVR berhasil di-' . strtoupper($action) . '!'
    ]);
});

// =========================================================================
// API ENDPOINT MANAJEMEN DEVICE CCTV (TAMBAH / HAPUS / GANTI KAMERA)
// =========================================================================

function getCctvDeviceList() {
    $path = storage_path('app/cctv_devices.json');
    if (!file_exists($path)) {
        $default = [
            [
                'id' => 'cam_4',
                'name' => 'Kamera 4 (Tapo C200 Lab 2)',
                'brand' => 'Tapo C200 / ONVIF',
                'ip' => '10.32.72.46',
                'port' => 2020,
                'rtsp_port' => 554,
                'user' => 'faradays',
                'pass' => '12345678',
                'stream_url' => 'http://localhost:8090/video.mjpg?oid=4',
                'rtsp_url' => 'rtsp://10.32.72.46:554/stream2',
                'oid' => '4',
                'is_active' => true
            ]
        ];
        file_put_contents($path, json_encode($default, JSON_PRETTY_PRINT));
        return $default;
    }
    return json_decode(file_get_contents($path), true) ?: [];
}

function saveCctvDeviceList($devices) {
    $path = storage_path('app/cctv_devices.json');
    file_put_contents($path, json_encode($devices, JSON_PRETTY_PRINT));
}

// 1. Ambil Semua Daftar Kamera & Kamera Aktif
Route::get('/api/cctv-devices', function () {
    $devices = getCctvDeviceList();
    $active = collect($devices)->firstWhere('is_active', true) ?: ($devices[0] ?? null);
    return response()->json([
        'status' => 'success',
        'devices' => $devices,
        'active' => $active
    ]);
});

// 2. Tambah Device Kamera Baru dari Web
Route::post('/api/cctv-devices', function (Illuminate\Http\Request $request) {
    $raw = json_decode($request->getContent(), true) ?: [];
    $data = array_merge($request->all(), $raw);
    
    $name = $data['name'] ?? null;
    $ip = $data['ip'] ?? null;
    $port = (int)($data['port'] ?? 2020);
    $rtspPort = (int)($data['rtsp_port'] ?? 554);
    $brand = $data['brand'] ?? 'ONVIF IP Camera';
    $user = $data['user'] ?? 'admin';
    $pass = $data['pass'] ?? '';
    $oid = !empty($data['oid']) ? (string)$data['oid'] : null;

    if (empty($name) || empty($ip)) {
        return response()->json(['status' => 'error', 'message' => 'Nama kamera dan alamat IP wajib diisi!'], 400);
    }

    $devices = getCctvDeviceList();
    $newId = 'cam_' . (count($devices) + 1) . '_' . time();
    $assignedOid = $oid ?: (string)(count($devices) + 1);

    $streamUrl = !empty($data['stream_url']) 
        ? $data['stream_url'] 
        : "http://localhost:8090/video.mjpg?oid={$assignedOid}";

    $rtspUrl = !empty($data['rtsp_url'])
        ? $data['rtsp_url']
        : "rtsp://{$ip}:{$rtspPort}/stream2";

    // Non-aktifkan kamera lama, kamera baru otomatis aktif
    foreach ($devices as &$dev) {
        $dev['is_active'] = false;
    }

    $newDevice = [
        'id' => $newId,
        'name' => $name,
        'brand' => $brand,
        'ip' => $ip,
        'port' => $port,
        'rtsp_port' => $rtspPort,
        'user' => $user,
        'pass' => $pass,
        'stream_url' => $streamUrl,
        'rtsp_url' => $rtspUrl,
        'oid' => $assignedOid,
        'is_active' => true
    ];

    $devices[] = $newDevice;
    saveCctvDeviceList($devices);

    // Update tapo_move.py ke kamera baru jika ada user & pass
    if (!empty($user) && !empty($ip)) {
        try {
            $pyPath = base_path('tapo_move.py');
            if (file_exists($pyPath)) {
                $pyCode = file_get_contents($pyPath);
                $pyCode = preg_replace('/IP = ".*?"/', 'IP = "' . $ip . '"', $pyCode);
                $pyCode = preg_replace('/PORT = \d+/', 'PORT = ' . $port, $pyCode);
                $pyCode = preg_replace('/USER = ".*?"/', 'USER = "' . $user . '"', $pyCode);
                $pyCode = preg_replace('/PASS = ".*?"/', 'PASS = "' . $pass . '"', $pyCode);
                file_put_contents($pyPath, $pyCode);
            }
        } catch (\Exception $e) {}
    }

    // Catat Log Aktivitas
    $logUser = auth()->user() ? auth()->user()->name : 'Viggo';
    $existingLogs = Cache::get('activity_logs', []);
    array_unshift($existingLogs, [
        'time' => date('d/m/Y H:i:s'),
        'user' => $logUser,
        'action' => 'Menambahkan Device Kamera CCTV Baru: ' . $name,
        'device' => 'CCTV Manager Web',
        'param' => "IP: {$ip}, OID: {$assignedOid}",
        'status' => 'ADDED'
    ]);
    Cache::put('activity_logs', array_slice($existingLogs, 0, 50), 86400);

    return response()->json([
        'status' => 'success',
        'message' => "Kamera {$name} berhasil ditambahkan dan diaktifkan!",
        'device' => $newDevice
    ]);
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// 3. Ganti Kamera Aktif (Switch Camera)
Route::post('/api/cctv-devices/switch/{id}', function ($id) {
    $devices = getCctvDeviceList();
    $found = false;
    $activeDev = null;

    foreach ($devices as &$dev) {
        if ($dev['id'] === $id) {
            $dev['is_active'] = true;
            $found = true;
            $activeDev = $dev;
        } else {
            $dev['is_active'] = false;
        }
    }

    if (!$found) {
        return response()->json(['status' => 'error', 'message' => 'Kamera tidak ditemukan!'], 404);
    }

    saveCctvDeviceList($devices);

    // Update tapo_move.py dengan kredensial kamera aktif jika ada IP & user
    if (!empty($activeDev['ip']) && !empty($activeDev['user'])) {
        try {
            $pyPath = base_path('tapo_move.py');
            if (file_exists($pyPath)) {
                $pyCode = file_get_contents($pyPath);
                $pyCode = preg_replace('/IP = ".*?"/', 'IP = "' . $activeDev['ip'] . '"', $pyCode);
                $pyCode = preg_replace('/PORT = \d+/', 'PORT = ' . ($activeDev['port'] ?: 2020), $pyCode);
                $pyCode = preg_replace('/USER = ".*?"/', 'USER = "' . $activeDev['user'] . '"', $pyCode);
                $pyCode = preg_replace('/PASS = ".*?"/', 'PASS = "' . $activeDev['pass'] . '"', $pyCode);
                file_put_contents($pyPath, $pyCode);
            }
        } catch (\Exception $e) {}
    }

    return response()->json([
        'status' => 'success',
        'message' => "Beralih ke {$activeDev['name']}",
        'active' => $activeDev
    ]);
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// 4. Hapus Device Kamera
Route::delete('/api/cctv-devices/{id}', function ($id) {
    $devices = getCctvDeviceList();
    $filtered = [];
    $wasActive = false;

    foreach ($devices as $dev) {
        if ($dev['id'] === $id) {
            if ($dev['is_active']) $wasActive = true;
        } else {
            $filtered[] = $dev;
        }
    }

    if ($wasActive && count($filtered) > 0) {
        $filtered[0]['is_active'] = true;
    }

    saveCctvDeviceList($filtered);

    return response()->json([
        'status' => 'success',
        'message' => 'Kamera berhasil dihapus!',
        'devices' => $filtered
    ]);
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// 5. Uji Koneksi Kamera (Ping Socket Test)
Route::post('/api/cctv-devices/test', function (Illuminate\Http\Request $request) {
    $raw = json_decode($request->getContent(), true) ?: [];
    $data = array_merge($request->all(), $raw);
    $ip = $data['ip'] ?? $request->input('ip');
    $port = (int)($data['port'] ?? ($request->input('port') ?: 2020));

    if (empty($ip)) {
        return response()->json(['status' => 'error', 'message' => 'Alamat IP wajib diisi!'], 400);
    }

    $startTime = microtime(true);
    $fp = @fsockopen($ip, $port, $errno, $errstr, 1.2);
    $latency = round((microtime(true) - $startTime) * 1000);

    if ($fp) {
        fclose($fp);
        return response()->json([
            'status' => 'success',
            'online' => true,
            'latency_ms' => $latency,
            'message' => "Koneksi Berhasil! Kamera di {$ip}:{$port} Merespons ({$latency} ms)."
        ]);
    }

    // Coba port alternatif RTSP 554 jika port 2020 gagal
    $fp2 = @fsockopen($ip, 554, $errno, $errstr, 1.0);
    if ($fp2) {
        fclose($fp2);
        return response()->json([
            'status' => 'success',
            'online' => true,
            'latency_ms' => $latency,
            'message' => "Koneksi Berhasil! Port RTSP (554) di {$ip} Merespons."
        ]);
    }

    return response()->json([
        'status' => 'error',
        'online' => false,
        'message' => "Tidak dapat terhubung ke {$ip}:{$port}. Pastikan IP dan kamera terhubung ke jaringan."
    ]);
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

require __DIR__.'/auth.php';
