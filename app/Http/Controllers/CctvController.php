<?php

namespace App\Http\Controllers;

use App\Models\CctvDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CctvController extends Controller
{
    /**
     * Helper: Ambil antrian perintah hardware (PTZ, Lampu) untuk diselipkan ke Edge Gateway
     */
    private function popHardwareCommands(): array
    {
        $queue = Cache::get('hardware_command_queue', []);
        if (!empty($queue)) {
            Cache::put('hardware_command_queue', [], 60);
        }
        return $queue;
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
                'ip_address' => '10.32.72.78',
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
     * Hapus device kamera dari database
     */
    public function destroy($id)
    {
        $device = CctvDevice::find($id);
        if ($device) {
            $device->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Kamera berhasil dihapus!'
        ]);
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
     * Menyimpan file frame terpisah berdasarkan OID kamera: storage/app/public/stream_{oid}.jpg
     */
    public function uploadFrame(Request $request)
    {
        $oid = $request->query('oid') ?? $request->input('oid');
        $camId = $request->query('cam_id') ?? $request->input('cam_id');

        if (!$oid && $camId) {
            $cam = CctvDevice::find($camId);
            if ($cam && $cam->oid) {
                $oid = $cam->oid;
            }
        }

        $oid = $oid ?: '4';

        $frameData = null;
        if ($request->hasFile('frame')) {
            $frameData = file_get_contents($request->file('frame')->getRealPath());
        } elseif ($request->getContent()) {
            $frameData = $request->getContent();
        }

        if ($frameData && strlen($frameData) > 10) {
            // Pastikan folder storage/app/public ada
            $publicDir = storage_path('app/public');
            if (!is_dir($publicDir)) {
                @mkdir($publicDir, 0755, true);
            }

            // Simpan file frame terpisah berdasarkan OID
            $targetStreamFile = storage_path("app/public/stream_{$oid}.jpg");
            @file_put_contents($targetStreamFile, $frameData);

            // Simpan juga ke storage/app/cctv_frame_{oid}.jpg untuk kompatibilitas
            @file_put_contents(storage_path("app/cctv_frame_{$oid}.jpg"), $frameData);
        }

        // Ambil perintah hardware untuk diselipkan ke Edge Gateway
        $commands = $this->popHardwareCommands();

        // Ambil info kamera aktif yang sedang dipilih di web
        $activeId = Cache::get('active_cctv_id');
        $activeDev = $activeId ? CctvDevice::find($activeId) : null;
        if (!$activeDev) {
            $activeDev = CctvDevice::where('is_active', true)->first();
        }

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
     * 7. GET /api/cctv-snapshot
     * Menampilkan frame gambar langsung dari storage/app/public/stream_{oid}.jpg
     */
    public function snapshot(Request $request)
    {
        $oid = $request->query('oid', 4);

        $frameFile1 = storage_path("app/public/stream_{$oid}.jpg");
        $frameFile2 = storage_path("app/cctv_frame_{$oid}.jpg");

        $targetFile = file_exists($frameFile1) ? $frameFile1 : (file_exists($frameFile2) ? $frameFile2 : null);

        // Prioritas 1: Frame terbaru dari Edge Gateway (< 15 detik)
        if ($targetFile && (time() - filemtime($targetFile)) < 15) {
            return response()->file($targetFile, [
                'Content-Type' => 'image/jpeg',
                'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        }

        // Prioritas 2: Jika server di lokal LAN, ambil langsung dari Agent DVR lokal port 8090
        try {
            $res = Http::timeout(0.8)->get("http://localhost:8090/grab.jpg?oid={$oid}");
            if ($res->successful() && strlen($res->body()) > 100) {
                return response($res->body(), 200, [
                    'Content-Type' => 'image/jpeg',
                    'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
                    'Pragma' => 'no-cache',
                    'Expires' => '0'
                ]);
            }
        } catch (\Exception $e) {}

        // Prioritas 3: Standby Placeholder SVG
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="640" height="360" viewBox="0 0 640 360">
            <rect width="640" height="360" fill="#020617"/>
            <circle cx="320" cy="150" r="36" fill="#0f172a" stroke="#0ea5e9" stroke-width="2"/>
            <path d="M308 142 L320 132 L332 142 M320 134 L320 168" stroke="#38bdf8" stroke-width="2.5" stroke-linecap="round"/>
            <text x="320" y="215" font-family="monospace" font-size="13" font-weight="bold" fill="#38bdf8" text-anchor="middle">EDGE GATEWAY RELAY STANDBY</text>
            <text x="320" y="240" font-family="monospace" font-size="11" fill="#64748b" text-anchor="middle">Menunggu sinyal video live dari Edge Gateway laptop (OID: ' . htmlspecialchars($oid) . ')...</text>
            <text x="320" y="260" font-family="monospace" font-size="10" fill="#0ea5e9" text-anchor="middle">Jalankan: python edge_gateway.py</text>
        </svg>';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-cache, no-store, must-revalidate'
        ]);
    }

    /**
     * 8. GET /api/cctv-ptz/{command}
     * Kontrol ONVIF PTZ Fisik
     */
    public function ptz($command, Request $request)
    {
        $oid = $request->query('oid', 4);

        $allowed = ['up', 'down', 'left', 'right', 'home'];
        if (!in_array($command, $allowed)) {
            return response()->json(['status' => 'error', 'message' => 'Perintah tidak valid'], 400);
        }

        // Catat ke antrian hardware
        $queue = Cache::get('hardware_command_queue', []);
        $queue[] = ['type' => 'ptz', 'command' => $command, 'oid' => $oid, 'time' => microtime(true)];
        Cache::put('hardware_command_queue', array_slice($queue, -20), 60);

        // Eksekusi lokal jika di server lokal Windows
        if (PHP_OS_FAMILY === 'Windows' || in_array(request()->getHost(), ['localhost', '127.0.0.1'])) {
            try {
                $pyPath = base_path('tapo_move.py');
                if (file_exists($pyPath)) {
                    $cmdExec = "python \"" . $pyPath . "\" " . escapeshellarg($command);
                    pclose(popen("start /B " . $cmdExec, "r"));
                }
            } catch (\Exception $e) {}
        }

        return response()->json([
            'status' => 'success',
            'command' => $command,
            'oid' => $oid,
            'message' => "Perintah PTZ '{$command}' berhasil dikirim"
        ]);
    }

    /**
     * 9. GET /api/cctv-power/{action}
     * Kontrol Power On/Off Kamera
     */
    public function power($action, Request $request)
    {
        $oid = $request->query('oid', 4);
        $isOn = ($action === 'on');
        $agentCmd = $isOn ? 'switchon' : 'switchoff';

        $queue = Cache::get('hardware_command_queue', []);
        $queue[] = ['type' => 'power', 'action' => $action, 'oid' => $oid, 'time' => microtime(true)];
        Cache::put('hardware_command_queue', array_slice($queue, -20), 60);

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
}
