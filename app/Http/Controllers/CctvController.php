<?php

namespace App\Http\Controllers;

use App\Models\CctvDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CctvController extends Controller
{
    /**
     * Helper: Simpan perintah hardware (PTZ, Lampu) ke antrian
     */
    public function pushHardwareCommand(array $command): void
    {
        // 1. Simpan ke file queue di storage
        try {
            $queueFile = storage_path('app/hardware_queue.json');
            $queue = [];
            if (file_exists($queueFile)) {
                $queue = json_decode(@file_get_contents($queueFile), true) ?: [];
            }
            $queue[] = $command;
            @file_put_contents($queueFile, json_encode(array_slice($queue, -50)));
        } catch (\Throwable $e) {}

        // 2. Simpan ke Cache Laravel
        try {
            $cacheQueue = Cache::get('hardware_command_queue', []);
            $cacheQueue[] = $command;
            Cache::put('hardware_command_queue', array_slice($cacheQueue, -50), 120);

            $pendingQueue = Cache::get('pending_hardware_queue', []);
            $pendingQueue[] = $command;
            Cache::put('pending_hardware_queue', array_slice($pendingQueue, -50), 120);
        } catch (\Throwable $e) {}
    }

    /**
     * Helper: Ambil antrian perintah hardware (PTZ, Lampu) dengan field oid, type, dan action
     */
    public function popHardwareCommands(): array
    {
        $commands = [];

        // 1. Ambil dari file queue storage
        try {
            $queueFile = storage_path('app/hardware_queue.json');
            if (file_exists($queueFile)) {
                $fileCommands = json_decode(@file_get_contents($queueFile), true) ?: [];
                if (!empty($fileCommands)) {
                    $commands = array_merge($commands, $fileCommands);
                    @file_put_contents($queueFile, json_encode([]));
                }
            }
        } catch (\Throwable $e) {}

        // 2. Ambil dari Cache
        try {
            $cacheQueue = Cache::pull('hardware_command_queue', []);
            if (!empty($cacheQueue)) {
                $commands = array_merge($commands, $cacheQueue);
            }
            $pendingQueue = Cache::pull('pending_hardware_queue', []);
            if (!empty($pendingQueue)) {
                $commands = array_merge($commands, $pendingQueue);
            }
        } catch (\Throwable $e) {}

        // Pastikan setiap command menyertakan field oid, type, dan action
        $formatted = [];
        foreach ($commands as $cmd) {
            if (!is_array($cmd)) continue;
            $type = $cmd['type'] ?? 'ptz';
            $oid = (string) ($cmd['oid'] ?? '4');
            $action = (string) ($cmd['action'] ?? ($cmd['command'] ?? ''));

            $formatted[] = [
                'type' => $type,
                'oid' => $oid,
                'action' => $action,
                'command' => $action,
                'state' => $cmd['state'] ?? null,
                'time' => $cmd['time'] ?? microtime(true)
            ];
        }

        return $formatted;
    }

    /**
     * 1. GET /api/cctv-devices
     * Response berupa JSON array dari seluruh kamera aktif (is_active = true) beserta OID-nya.
     */
    public function index(Request $request)
    {
        // Ambil semua kamera yang aktif
        $devices = CctvDevice::where('is_active', true)->get();

        // Fallback jika belum ada data di database
        if ($devices->isEmpty()) {
            $default = CctvDevice::create([
                'name' => 'Kamera 4 (Tapo C200 Lab 2)',
                'ip_address' => '10.32.72.177',
                'oid' => '4',
                'agent_oid' => 4,
                'rtsp_port' => 554,
                'onvif_port' => 2020,
                'username' => 'faradays',
                'password' => '12345678',
                'stream_path' => '/stream2',
                'is_active' => true,
                'description' => 'Kamera Utama Lab Otomasi 2'
            ]);
            $devices = collect([$default]);
        }

        // Format respon menjadi array objek sesuai spesifikasi
        $data = $devices->map(function ($dev) {
            $oid = (string) ($dev->oid ?: $dev->agent_oid ?: '4');
            $ip = (string) $dev->ip_address;
            $rtspPort = (int) ($dev->rtsp_port ?: 554);
            $onvifPort = (int) ($dev->onvif_port ?: 2020);
            $streamPath = (string) ($dev->stream_path ?: '/stream2');

            return [
                'id' => (int) $dev->id,
                'name' => (string) $dev->name,
                'oid' => $oid,
                'ip' => $ip,
                'ip_address' => $ip,
                'port' => $onvifPort,
                'onvif_port' => $onvifPort,
                'rtsp_port' => $rtspPort,
                'user' => (string) ($dev->username ?? ''),
                'username' => (string) ($dev->username ?? ''),
                'pass' => (string) ($dev->password ?? ''),
                'password' => (string) ($dev->password ?? ''),
                'stream_path' => $streamPath,
                'stream_url' => "http://localhost:8090/video.mjpg?oid={$oid}",
                'rtsp_url' => "rtsp://{$ip}:{$rtspPort}{$streamPath}",
                'is_active' => (bool) $dev->is_active,
                'description' => (string) ($dev->description ?? ''),
            ];
        });

        return response()->json($data->values());
    }

    /**
     * 2. POST /api/cctv-devices
     * Tambah Device Kamera Baru atau Update Kamera yang sudah ada
     */
    public function store(Request $request)
    {
        $raw = json_decode($request->getContent(), true) ?: [];
        $data = array_merge($request->all(), $raw);

        $id = !empty($data['id']) ? $data['id'] : null;
        $name = trim($data['name'] ?? '');
        $ip = trim($data['ip'] ?? ($data['ip_address'] ?? ''));
        $port = (int) ($data['port'] ?? ($data['onvif_port'] ?? 2020));
        $rtspPort = (int) ($data['rtsp_port'] ?? 554);
        $user = trim($data['user'] ?? ($data['username'] ?? 'faradays'));
        $pass = trim($data['pass'] ?? ($data['password'] ?? ''));
        $oidInput = trim((string) ($data['oid'] ?? ($data['agent_oid'] ?? '')));
        $brand = trim($data['brand'] ?? ($data['description'] ?? 'Tapo C200 / ONVIF PTZ'));

        if (empty($name) || empty($ip)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nama kamera dan alamat IP wajib diisi!'
            ], 400);
        }

        $device = null;
        $isUpdate = false;

        if ($id) {
            $device = CctvDevice::find($id);
            if ($device) {
                $isUpdate = true;
            }
        }

        // Tentukan OID kamera
        $assignedOid = !empty($oidInput) ? $oidInput : ($device ? $device->oid : (string) (CctvDevice::max('id') + 1));

        $attributes = [
            'name' => $name,
            'ip_address' => $ip,
            'oid' => $assignedOid,
            'agent_oid' => is_numeric($assignedOid) ? (int) $assignedOid : null,
            'onvif_port' => $port,
            'rtsp_port' => $rtspPort,
            'username' => $user,
            'is_active' => true,
            'description' => $brand,
        ];

        if ($pass !== '') {
            $attributes['password'] = $pass;
        }

        if ($isUpdate) {
            $device->update($attributes);
        } else {
            $device = CctvDevice::create($attributes);
        }

        // Simpan kamera yang baru ditambahkan/diperbarui sebagai kamera yang sedang dilihat di web
        Cache::put('active_cctv_id', $device->id, 86400);

        // Update script tapo_move.py dengan IP dan kredensial kamera jika ada
        if (!empty($user) && !empty($ip)) {
            try {
                $pyPath = base_path('tapo_move.py');
                if (file_exists($pyPath)) {
                    $pyCode = file_get_contents($pyPath);
                    $pyCode = preg_replace('/IP = ".*?"/', 'IP = "' . $ip . '"', $pyCode);
                    $pyCode = preg_replace('/PORT = \d+/', 'PORT = ' . $port, $pyCode);
                    $pyCode = preg_replace('/USER = ".*?"/', 'USER = "' . $user . '"', $pyCode);
                    if ($pass !== '') {
                        $pyCode = preg_replace('/PASS = ".*?"/', 'PASS = "' . $pass . '"', $pyCode);
                    }
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
            'action' => ($isUpdate ? 'Memperbarui Konfigurasi Kamera: ' : 'Menambahkan Device Kamera CCTV Baru: ') . $name,
            'device' => 'CCTV Manager Web',
            'param' => "IP: {$ip}, Port: {$port}, OID: {$assignedOid}",
            'status' => $isUpdate ? 'UPDATED' : 'ADDED'
        ]);
        Cache::put('activity_logs', array_slice($existingLogs, 0, 50), 86400);

        return response()->json([
            'status' => 'success',
            'message' => $isUpdate ? "Konfigurasi kamera '{$name}' berhasil diperbarui!" : "Kamera '{$name}' berhasil ditambahkan dan diaktifkan!",
            'device' => [
                'id' => $device->id,
                'name' => $device->name,
                'oid' => (string) $device->oid,
                'ip' => $device->ip_address,
                'ip_address' => $device->ip_address,
                'port' => $device->onvif_port,
                'onvif_port' => $device->onvif_port,
                'rtsp_port' => $device->rtsp_port,
                'user' => $device->username,
                'username' => $device->username,
                'is_active' => (bool) $device->is_active,
                'stream_url' => "http://localhost:8090/video.mjpg?oid={$device->oid}"
            ],
            'is_update' => $isUpdate
        ]);
    }

    /**
     * 3. POST /api/cctv-devices/switch/{id}
     * Ganti kamera yang sedang aktif dipilih di dashboard
     */
    public function switchCamera($id)
    {
        $device = CctvDevice::find($id);
        if (!$device) {
            $device = CctvDevice::where('oid', $id)->first();
        }

        if (!$device) {
            return response()->json(['status' => 'error', 'message' => 'Kamera tidak ditemukan!'], 404);
        }

        // Catat active_cctv_id di cache
        Cache::put('active_cctv_id', $device->id, 86400);

        // Update tapo_move.py
        if (!empty($device->ip_address) && !empty($device->username)) {
            try {
                $pyPath = base_path('tapo_move.py');
                if (file_exists($pyPath)) {
                    $pyCode = file_get_contents($pyPath);
                    $pyCode = preg_replace('/IP = ".*?"/', 'IP = "' . $device->ip_address . '"', $pyCode);
                    $pyCode = preg_replace('/PORT = \d+/', 'PORT = ' . ($device->onvif_port ?: 2020), $pyCode);
                    $pyCode = preg_replace('/USER = ".*?"/', 'USER = "' . $device->username . '"', $pyCode);
                    if ($device->password) {
                        $pyCode = preg_replace('/PASS = ".*?"/', 'PASS = "' . $device->password . '"', $pyCode);
                    }
                    file_put_contents($pyPath, $pyCode);
                }
            } catch (\Exception $e) {}
        }

        $formatted = [
            'id' => $device->id,
            'name' => $device->name,
            'oid' => (string) $device->oid,
            'ip' => $device->ip_address,
            'ip_address' => $device->ip_address,
            'port' => $device->onvif_port,
            'onvif_port' => $device->onvif_port,
            'rtsp_port' => $device->rtsp_port,
            'user' => $device->username,
            'username' => $device->username,
            'is_active' => (bool) $device->is_active,
            'stream_url' => "http://localhost:8090/video.mjpg?oid={$device->oid}"
        ];

        return response()->json([
            'status' => 'success',
            'message' => "Beralih ke {$device->name}",
            'active' => $formatted,
            'device' => $formatted
        ]);
    }

    /**
     * 4. DELETE /api/cctv-devices/{id}
     * Hapus device kamera dari database dan bersihkan cache stream frame
     */
    public function destroy($id)
    {
        $device = CctvDevice::find($id);
        if (!$device) {
            $device = CctvDevice::where('oid', $id)->first();
        }

        if ($device) {
            $deletedOid = (string) ($device->oid ?: $device->agent_oid);
            $deletedName = $device->name;

            // 1. Bersihkan file cache frame gambar stream_{oid}.jpg untuk OID yang dihapus
            if (!empty($deletedOid)) {
                $file1 = public_path("cctv/stream_{$deletedOid}.jpg");
                $file2 = storage_path("app/public/stream_{$deletedOid}.jpg");
                $file3 = storage_path("app/cctv_frame_{$deletedOid}.jpg");
                if (file_exists($file1)) @unlink($file1);
                if (file_exists($file2)) @unlink($file2);
                if (file_exists($file3)) @unlink($file3);
            }

            // 2. Sinkronkan hapus di tabel cameras jika tabel tersebut ada
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('cameras')) {
                    \Illuminate\Support\Facades\DB::table('cameras')
                        ->where('id', $device->id)
                        ->orWhere('oid', $deletedOid)
                        ->delete();
                }
            } catch (\Throwable $e) {}

            // 3. Hapus record kamera dari tabel cctv_devices
            $device->delete();

            // 4. Update active_cctv_id jika kamera yang aktif sedang dihapus
            $activeId = Cache::get('active_cctv_id');
            if ($activeId == $id || $activeId == $device->id) {
                $next = CctvDevice::where('is_active', true)->first();
                Cache::put('active_cctv_id', $next ? $next->id : null, 86400);
            }

            // 5. Catat Log Aktivitas
            try {
                $logUser = auth()->user() ? auth()->user()->name : 'Admin';
                $existingLogs = Cache::get('activity_logs', []);
                array_unshift($existingLogs, [
                    'time' => date('d/m/Y H:i:s'),
                    'user' => $logUser,
                    'action' => "Menghapus Device Kamera CCTV: {$deletedName} (OID {$deletedOid})",
                    'device' => 'CCTV Manager Web',
                    'param' => "OID: {$deletedOid}",
                    'status' => 'DELETED'
                ]);
                Cache::put('activity_logs', array_slice($existingLogs, 0, 50), 86400);
            } catch (\Throwable $e) {}

            return response()->json([
                'status' => 'success',
                'message' => "Kamera '{$deletedName}' (OID {$deletedOid}) berhasil dihapus dari database!",
                'deleted_oid' => $deletedOid
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Kamera tidak ditemukan di database!'
        ], 404);
    }

    /**
     * 5. POST /api/cctv-devices/test
     * Uji koneksi TCP Socket ke IP kamera (ONVIF 2020 / RTSP 554)
     */
    public function testConnection(Request $request)
    {
        $raw = json_decode($request->getContent(), true) ?: [];
        $data = array_merge($request->all(), $raw);
        $ip = trim($data['ip'] ?? $request->input('ip', ''));
        $port = (int) ($data['port'] ?? ($request->input('port') ?: 2020));

        if (empty($ip)) {
            return response()->json(['status' => 'error', 'message' => 'Alamat IP wajib diisi!'], 400);
        }

        // 1. Uji koneksi socket TCP ONVIF
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

        // 2. Coba port alternatif RTSP 554 jika port ONVIF gagal
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

        // 3. Penanganan Jika Server Berjalan di Cloud VPS & IP adalah IP Private LAN (10.x / 192.168.x)
        $isPrivateIp = preg_match('/^(10\.|192\.168\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|127\.)/', $ip);
        $isCloudServer = !in_array($request->getHost(), ['localhost', '127.0.0.1']);

        if ($isPrivateIp && $isCloudServer) {
            $lastFrame = storage_path('app/public/stream_4.jpg');
            $gatewayActive = file_exists($lastFrame) && (time() - filemtime($lastFrame)) < 30;

            return response()->json([
                'status' => 'success',
                'online' => true,
                'is_private_subnet' => true,
                'gateway_active' => $gatewayActive,
                'message' => "IP {$ip} adalah IP LAN lokal. Kamera akan diakses melalui Edge Gateway laptop Anda" . ($gatewayActive ? " (Edge Gateway saat ini AKTIF)." : " (Pastikan Edge Gateway aktif di laptop).")
            ]);
        }

        return response()->json([
            'status' => 'error',
            'online' => false,
            'message' => "Tidak dapat terhubung ke {$ip}:{$port}. Pastikan IP dan kamera terhubung ke jaringan lokal yang sama."
        ]);
    }

    /**
     * 6. POST /api/cctv/upload-frame
     * Menerima upload frame gambar dari Edge Gateway dengan parameter ?oid={oid}&cam_id={cam_id}
     * Menyimpan file frame terpisah berdasarkan OID kamera ke public/cctv/stream_{oid}.jpg
     */
    public function uploadFrame(Request $request)
    {
        $oid = $request->query('oid') ?? $request->input('oid');
        $camId = $request->query('cam_id') ?? $request->input('cam_id');

        if (!$oid && $camId) {
            try {
                $cam = CctvDevice::find($camId);
                if ($cam && $cam->oid) {
                    $oid = $cam->oid;
                }
            } catch (\Throwable $e) {}
        }

        $oid = $oid ?: 'default';

        // Pastikan folder public/cctv tersedia
        $cctvDir = public_path('cctv');
        if (!is_dir($cctvDir)) {
            @mkdir($cctvDir, 0777, true);
        }

        $fileName = 'stream_' . $oid . '.jpg';
        $targetPublicFile = $cctvDir . DIRECTORY_SEPARATOR . $fileName;
        $frameData = null;

        // Simpan file frame terpisah berdasarkan OID sesuai spesifikasi request
        if ($request->hasFile('frame')) {
            $request->file('frame')->move($cctvDir, $fileName);
            if (file_exists($targetPublicFile)) {
                $frameData = @file_get_contents($targetPublicFile);
            }
        } elseif ($request->getContent() && strlen($request->getContent()) > 10) {
            $frameData = $request->getContent();
            @file_put_contents($targetPublicFile, $frameData);
        }

        // Simpan juga ke storage/app/public/stream_{oid}.jpg dan storage/app/cctv_frame_{oid}.jpg untuk kompatibilitas
        if ($frameData) {
            $storagePublicDir = storage_path('app/public');
            if (!is_dir($storagePublicDir)) {
                @mkdir($storagePublicDir, 0755, true);
            }
            @file_put_contents(storage_path("app/public/stream_{$oid}.jpg"), $frameData);
            @file_put_contents(storage_path("app/cctv_frame_{$oid}.jpg"), $frameData);
        }

        // Ambil perintah hardware untuk diselipkan ke Edge Gateway
        $commands = $this->popHardwareCommands();

        // Ambil info kamera aktif yang sedang dipilih di web
        $activeDev = null;
        try {
            $activeId = Cache::get('active_cctv_id');
            $activeDev = $activeId ? CctvDevice::find($activeId) : null;
            if (!$activeDev) {
                $activeDev = CctvDevice::where('is_active', true)->first();
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'status' => 'success',
            'oid' => (string) $oid,
            'cam_id' => $camId,
            'bytes' => $frameData ? strlen($frameData) : 0,
            'commands' => $commands,
            'active_camera' => $activeDev ? [
                'id' => $activeDev->id,
                'name' => $activeDev->name,
                'oid' => (string) $activeDev->oid,
                'ip' => $activeDev->ip_address,
                'port' => $activeDev->onvif_port,
                'user' => $activeDev->username,
                'pass' => $activeDev->password
            ] : null,
            'timestamp' => microtime(true)
        ]);
    }

    /**
     * Helper: Menghasilkan gambar placeholder offline resmi per OID (JPEG / SVG)
     * Dilarang mengalihkan ke Kamera 1 atau kamera lain.
     */
    public function renderOfflinePlaceholder($oid)
    {
        $oidStr = (string) $oid;
        if (extension_loaded('gd')) {
            $width = 1280;
            $height = 720;
            $im = imagecreatetruecolor($width, $height);
            $bg = imagecolorallocate($im, 15, 23, 42); // #0f172a
            imagefilledrectangle($im, 0, 0, $width, $height, $bg);

            $red = imagecolorallocate($im, 239, 68, 68); // #ef4444
            $white = imagecolorallocate($im, 241, 245, 249); // #f1f5f9
            $gray = imagecolorallocate($im, 148, 163, 184); // #94a3b8
            $darkRed = imagecolorallocate($im, 69, 10, 10);

            // Border bingkai merah
            imagerectangle($im, 30, 30, $width - 30, $height - 30, $red);
            imagerectangle($im, 31, 31, $width - 31, $height - 31, $red);

            // Teks status offline
            imagestring($im, 5, 540, 310, "CAMERA OFFLINE", $red);
            imagestring($im, 5, 510, 345, "NO SIGNAL - OID: " . $oidStr, $white);
            imagestring($im, 4, 465, 385, "Sinyal kamera terputus atau frame usang (>10s)", $gray);
            imagestring($im, 3, 505, 420, "Menunggu update live frame dari Edge Gateway...", $gray);

            ob_start();
            imagejpeg($im, null, 75);
            $jpegData = ob_get_clean();
            imagedestroy($im);

            return response($jpegData, 200, [
                'Content-Type' => 'image/jpeg',
                'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="720" viewBox="0 0 1280 720">
            <rect width="1280" height="720" fill="#0f172a"/>
            <rect x="30" y="30" width="1220" height="660" fill="none" stroke="#ef4444" stroke-width="2"/>
            <circle cx="640" cy="280" r="45" fill="#1e293b" stroke="#ef4444" stroke-width="3"/>
            <line x1="615" y1="255" x2="665" y2="305" stroke="#ef4444" stroke-width="4" stroke-linecap="round"/>
            <text x="640" y="380" font-family="monospace, sans-serif" font-size="28" font-weight="bold" fill="#ef4444" text-anchor="middle">CAMERA OFFLINE</text>
            <text x="640" y="420" font-family="monospace, sans-serif" font-size="18" font-weight="bold" fill="#f1f5f9" text-anchor="middle">NO SIGNAL - OID: ' . htmlspecialchars($oidStr) . '</text>
            <text x="640" y="460" font-family="monospace, sans-serif" font-size="14" fill="#94a3b8" text-anchor="middle">Sinyal terputus atau frame usang (&gt; 10 detik)</text>
        </svg>';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }

    /**
     * Endpoint: GET /cctv/stream_{oid}.jpg
     * Memeriksa keberadaan file dan waktu modifikasi < 10 detik.
     * Jika tidak ada atau usang, tampilkan placeholder CAMERA OFFLINE untuk OID tersebut.
     * Dilarang redirect ke kamera 1 atau kamera lain.
     */
    public function streamFrame($oid, Request $request)
    {
        $cleanOid = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$oid);
        $frameFile0 = public_path("cctv/stream_{$cleanOid}.jpg");
        $frameFile1 = storage_path("app/public/stream_{$cleanOid}.jpg");
        $frameFile2 = storage_path("app/cctv_frame_{$cleanOid}.jpg");

        $targetFile = file_exists($frameFile0) ? $frameFile0 : (file_exists($frameFile1) ? $frameFile1 : (file_exists($frameFile2) ? $frameFile2 : null));

        if ($targetFile && (time() - filemtime($targetFile)) < 10) {
            return response()->file($targetFile, [
                'Content-Type' => 'image/jpeg',
                'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        }

        return $this->renderOfflinePlaceholder($cleanOid);
    }

    /**
     * 7. GET /api/cctv-snapshot
     * Menampilkan frame gambar langsung dari public/cctv/stream_{oid}.jpg atau storage
     */
    public function snapshot(Request $request)
    {
        $oid = $request->query('oid', 4);
        $cleanOid = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$oid);

        $frameFile0 = public_path("cctv/stream_{$cleanOid}.jpg");
        $frameFile1 = storage_path("app/public/stream_{$cleanOid}.jpg");
        $frameFile2 = storage_path("app/cctv_frame_{$cleanOid}.jpg");

        $targetFile = file_exists($frameFile0) ? $frameFile0 : (file_exists($frameFile1) ? $frameFile1 : (file_exists($frameFile2) ? $frameFile2 : null));

        // Prioritas 1: Frame terbaru dari Edge Gateway (< 10 detik)
        if ($targetFile && (time() - filemtime($targetFile)) < 10) {
            return response()->file($targetFile, [
                'Content-Type' => 'image/jpeg',
                'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        }

        // Prioritas 2: Jika server di lokal LAN Windows, coba ambil dari Agent DVR lokal port 8090
        if (in_array($request->getHost(), ['localhost', '127.0.0.1'])) {
            try {
                $res = Http::timeout(0.8)->get("http://localhost:8090/grab.jpg?oid={$cleanOid}");
                if ($res->successful() && strlen($res->body()) > 2500) {
                    return response($res->body(), 200, [
                        'Content-Type' => 'image/jpeg',
                        'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
                        'Pragma' => 'no-cache',
                        'Expires' => '0'
                    ]);
                }
            } catch (\Exception $e) {}
        }

        // Prioritas 3: Tampilkan gambar placeholder CAMERA OFFLINE untuk OID tersebut (Tanpa redirect)
        return $this->renderOfflinePlaceholder($cleanOid);
    }

    /**
     * Endpoint Sinkronisasi OID: POST /api/cctv-devices/{id}/update-oid atau /api/cctv-devices/update-oid
     * Menerima OID resmi Agent DVR dari Edge Gateway dan menyimpannya ke database
     */
    public function updateOid(Request $request, $id = null)
    {
        $raw = json_decode($request->getContent(), true) ?: [];
        $data = array_merge($request->all(), $raw);

        $devId = $id ?: ($data['id'] ?? null);
        $newOid = trim((string) ($data['oid'] ?? ($data['agent_oid'] ?? '')));

        if (empty($newOid)) {
            return response()->json(['status' => 'error', 'message' => 'Parameter oid wajib diisi!'], 400);
        }

        $device = null;
        if ($devId) {
            $device = CctvDevice::find($devId);
        }

        if (!$device && !empty($data['ip'])) {
            $device = CctvDevice::where('ip_address', trim($data['ip']))->first();
        }

        if (!$device && !empty($data['name'])) {
            $device = CctvDevice::where('name', trim($data['name']))->first();
        }

        if (!$device) {
            return response()->json(['status' => 'error', 'message' => 'Device kamera tidak ditemukan untuk update OID!'], 404);
        }

        $oldOid = $device->oid;
        $device->oid = $newOid;
        $device->agent_oid = is_numeric($newOid) ? (int)$newOid : null;
        $device->save();

        // Jika ada tabel cameras lama, sinkronkan juga jika ada
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('cameras')) {
                \Illuminate\Support\Facades\DB::table('cameras')
                    ->where('id', $device->id)
                    ->orWhere('ip_address', $device->ip_address)
                    ->update(['oid' => $newOid]);
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'status' => 'success',
            'message' => "OID kamera '{$device->name}' berhasil diperbarui dari '{$oldOid}' menjadi '{$newOid}'",
            'device' => [
                'id' => $device->id,
                'name' => $device->name,
                'oid' => (string) $device->oid,
                'agent_oid' => $device->agent_oid,
                'ip_address' => $device->ip_address
            ]
        ]);
    }

    /**
     * 8. GET|POST /api/cctv-ptz/{command}
     * Kontrol ONVIF PTZ Fisik
     */
    public function ptz($command, Request $request)
    {
        $raw = $request->json()->all() ?: ($request->all() ?: (json_decode($request->getContent(), true) ?: []));
        $oid = (string) ($raw['oid'] ?? ($request->query('oid') ?? ($request->input('oid', '4'))));
        $action = strtolower($raw['action'] ?? $command);

        $allowed = ['up', 'down', 'left', 'right', 'home', 'center', 'zoomin', 'zoomout'];
        if (!in_array($action, $allowed)) {
            return response()->json(['status' => 'error', 'message' => 'Perintah tidak valid'], 400);
        }

        // Catat ke antrian hardware dengan field oid, type, dan action
        $cmd = [
            'type' => 'ptz',
            'oid' => $oid,
            'action' => $action,
            'command' => $action,
            'time' => microtime(true)
        ];
        $this->pushHardwareCommand($cmd);

        // Eksekusi lokal jika di server lokal Windows
        if (PHP_OS_FAMILY === 'Windows' || in_array(request()->getHost(), ['localhost', '127.0.0.1'])) {
            try {
                $ispyMap = [
                    'up' => 'ispydir_3',
                    'down' => 'ispydir_7',
                    'left' => 'ispydir_1',
                    'right' => 'ispydir_5',
                    'home' => 'home',
                    'center' => 'home',
                    'stop' => 'ispydir_11'
                ];
                $val = $ispyMap[$action] ?? $action;
                if ($action === 'home' || $action === 'center') {
                    Http::timeout(0.4)->get("http://127.0.0.1:8090/q.json?cmd=ptzcommand&field=ptz&value=home&command=home&oid={$oid}&ot=2");
                } else {
                    Http::timeout(0.4)->get("http://127.0.0.1:8090/q.json?cmd=ptzcommand&field=ptz&value={$val}&command={$val}&oid={$oid}&ot=2");
                }
            } catch (\Throwable $e) {}

            try {
                $pyPath = base_path('tapo_move.py');
                if (file_exists($pyPath) && in_array($oid, ['4', '5'])) {
                    $cmdExec = "python \"" . $pyPath . "\" " . escapeshellarg($action) . " " . escapeshellarg($oid);
                    pclose(popen("start /B " . $cmdExec, "r"));
                }
            } catch (\Exception $e) {}
        }

        return response()->json([
            'status' => 'success',
            'type' => 'ptz',
            'action' => $action,
            'command' => $action,
            'oid' => $oid,
            'message' => "Perintah PTZ '{$action}' untuk kamera OID {$oid} berhasil dikirim"
        ]);
    }

    /**
     * 9. GET /api/cctv-power/{action}
     * Kontrol Power On/Off Kamera
     */
    public function power($action, Request $request)
    {
        $oid = (string) ($request->query('oid') ?? ($request->input('oid', '4')));
        $isOn = ($action === 'on');
        $agentCmd = $isOn ? 'switchon' : 'switchoff';

        $cmd = [
            'type' => 'power',
            'action' => $action,
            'oid' => $oid,
            'time' => microtime(true)
        ];
        $this->pushHardwareCommand($cmd);

        if (PHP_OS_FAMILY === 'Windows' || in_array(request()->getHost(), ['localhost', '127.0.0.1'])) {
            try {
                Http::timeout(0.2)->get("http://localhost:8090/q.json?cmd={$agentCmd}&oid={$oid}&ot=2");
            } catch (\Exception $e) {}
        }

        return response()->json([
            'status' => 'success',
            'action' => $action,
            'oid' => $oid,
            'message' => "Kamera (OID: {$oid}) berhasil di-" . ($isOn ? 'aktifkan' : 'non-aktifkan')
        ]);
    }

    /**
     * 10. GET /api/hardware/poll
     * Endpoint polling hardware untuk Edge Gateway (mengembalikan field oid, type, action)
     */
    public function pollHardwareCommands(Request $request)
    {
        $commands = $this->popHardwareCommands();

        return response()->json([
            'status' => 'success',
            'commands' => $commands,
            'count' => count($commands),
            'timestamp' => microtime(true)
        ]);
    }

    /**
     * 11. POST /api/hardware/control
     * Menerima payload POST { "type": "ptz", "oid": selectedOid, "action": "up" }
     */
    public function controlHardware(Request $request)
    {
        $raw = $request->json()->all() ?: ($request->all() ?: (json_decode($request->getContent(), true) ?: []));

        $type = $raw['type'] ?? 'ptz';
        $oid = (string) ($raw['oid'] ?? ($request->query('oid', '4')));
        $action = strtolower((string) ($raw['action'] ?? ($raw['command'] ?? ($raw['ptz'] ?? ''))));

        if ($type === 'ptz') {
            if (!$action) {
                return response()->json(['status' => 'error', 'message' => 'Parameter action wajib diisi'], 400);
            }

            $cmd = [
                'type' => 'ptz',
                'oid' => $oid,
                'action' => $action,
                'command' => $action,
                'time' => microtime(true)
            ];
            $this->pushHardwareCommand($cmd);

            // Eksekusi lokal jika di server lokal Windows
            if (PHP_OS_FAMILY === 'Windows' || in_array(request()->getHost(), ['localhost', '127.0.0.1'])) {
                try {
                    $ispyMap = [
                        'up' => 'ispydir_3',
                        'down' => 'ispydir_7',
                        'left' => 'ispydir_1',
                        'right' => 'ispydir_5',
                        'home' => 'home',
                        'center' => 'home',
                        'stop' => 'ispydir_11'
                    ];
                    $val = $ispyMap[$action] ?? $action;
                    if ($action === 'home' || $action === 'center') {
                        Http::timeout(0.4)->get("http://127.0.0.1:8090/q.json?cmd=ptzcommand&field=ptz&value=home&command=home&oid={$oid}&ot=2");
                    } else {
                        Http::timeout(0.4)->get("http://127.0.0.1:8090/q.json?cmd=ptzcommand&field=ptz&value={$val}&command={$val}&oid={$oid}&ot=2");
                    }
                } catch (\Throwable $e) {}

                try {
                    $pyPath = base_path('tapo_move.py');
                    if (file_exists($pyPath) && in_array($oid, ['4', '5'])) {
                        $cmdExec = "python \"" . $pyPath . "\" " . escapeshellarg($action) . " " . escapeshellarg($oid);
                        pclose(popen("start /B " . $cmdExec, "r"));
                    }
                } catch (\Exception $e) {}
            }

            return response()->json([
                'status' => 'success',
                'message' => "Perintah PTZ {$action} (OID {$oid}) berhasil diantrikan",
                'command' => $cmd
            ]);
        }

        if (in_array($type, ['lamp1', 'lamp2'])) {
            $state = isset($raw['state']) ? (int) $raw['state'] : ($action === 'on' ? 1 : 0);
            $cmd = [
                'type' => $type,
                'state' => $state,
                'oid' => $oid,
                'action' => $state === 1 ? 'on' : 'off',
                'time' => microtime(true)
            ];
            $this->pushHardwareCommand($cmd);

            return response()->json([
                'status' => 'success',
                'message' => "Perintah {$type} berhasil diantrikan",
                'command' => $cmd
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Tipe perintah tidak dikenali'], 400);
    }
}
