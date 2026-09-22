<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class LampuController extends Controller
{
    /**
     * Konfigurasi MQTT Broker
     */
    private string $mqttHost;
    private int $mqttPort;

    public function __construct()
    {
        // Host & Port MQTT Broker (Default: 48.193.45.137:1883)
        $this->mqttHost = env('MQTT_HOST', '48.193.45.137');
        $this->mqttPort = (int) env('MQTT_PORT', 1883);
    }

    /**
     * Helper privat untuk mempublikasikan pesan ke MQTT Broker dengan QoS 0
     *
     * @param string $topic
     * @param string $payload
     * @throws \Throwable
     */
    private function publishToBroker(string $topic, string $payload): void
    {
        $clientId = 'laravel_' . uniqid();
        $mqtt = new MqttClient($this->mqttHost, $this->mqttPort, $clientId);

        $settings = (new ConnectionSettings)
            ->setConnectTimeout(3)
            ->setSocketTimeout(3);

        $mqtt->connect($settings, true);
        $mqtt->publish($topic, $payload, 0); // QoS 0
        $mqtt->disconnect();
    }

    /**
     * Handler Kontrol Lampu 1 via MQTT
     * Topik: 'lab/lampu1'
     * Payload: 'ON' atau 'OFF'
     */
    public function kontrolLampu1(Request $request, $aksi = null)
    {
        $input = $aksi ?? $request->input('status') ?? $request->input('aksi') ?? $request->input('state');
        $inputLower = strtolower(trim((string)$input));

        if (in_array($inputLower, ['on', '1', 'true', 'hidup'])) {
            $payload = 'ON';
            $stateVal = 1;
        } elseif (in_array($inputLower, ['off', '0', 'false', 'mati'])) {
            $payload = 'OFF';
            $stateVal = 0;
        } else {
            return response()->json([
                'status'  => 'error',
                'message' => 'Aksi tidak valid! Gunakan ON atau OFF.'
            ], 400);
        }

        try {
            $this->publishToBroker('lab/lampu1', $payload);

            // Sinkronisasi status di cache Laravel untuk dashboard web
            Cache::put('lamp1', $stateVal, 86400);
            $this->catatLog('Lampu 1 (Servo 1)', $payload === 'ON' ? 'Menyalakan Saklar Lampu Utama 1 via MQTT' : 'Mematikan Saklar Lampu Utama 1 via MQTT', "Payload: {$payload} | lab/lampu1");

            return response()->json([
                'status'  => 'success',
                'message' => "Lampu 1 Berhasil Diubah ke {$payload} via MQTT!",
                'topic'   => 'lab/lampu1',
                'payload' => $payload,
                'broker'  => "{$this->mqttHost}:{$this->mqttPort}"
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengirim perintah ke MQTT broker: ' . $e->getMessage(),
                'topic'   => 'lab/lampu1',
                'broker'  => "{$this->mqttHost}:{$this->mqttPort}"
            ], 500);
        }
    }

    /**
     * Handler Kontrol Lampu 2 via MQTT
     * Topik: 'lab/lampu2'
     * Payload: 'ON' atau 'OFF'
     */
    public function kontrolLampu2(Request $request, $aksi = null)
    {
        $input = $aksi ?? $request->input('status') ?? $request->input('aksi') ?? $request->input('state');
        $inputLower = strtolower(trim((string)$input));

        if (in_array($inputLower, ['on', '1', 'true', 'hidup'])) {
            $payload = 'ON';
            $stateVal = 1;
        } elseif (in_array($inputLower, ['off', '0', 'false', 'mati'])) {
            $payload = 'OFF';
            $stateVal = 0;
        } else {
            return response()->json([
                'status'  => 'error',
                'message' => 'Aksi tidak valid! Gunakan ON atau OFF.'
            ], 400);
        }

        try {
            $this->publishToBroker('lab/lampu2', $payload);

            // Sinkronisasi status di cache Laravel untuk dashboard web
            Cache::put('lamp2', $stateVal, 86400);
            $this->catatLog('Lampu 2 (Servo 2)', $payload === 'ON' ? 'Menyalakan Saklar Lampu Utama 2 via MQTT' : 'Mematikan Saklar Lampu Utama 2 via MQTT', "Payload: {$payload} | lab/lampu2");

            return response()->json([
                'status'  => 'success',
                'message' => "Lampu 2 Berhasil Diubah ke {$payload} via MQTT!",
                'topic'   => 'lab/lampu2',
                'payload' => $payload,
                'broker'  => "{$this->mqttHost}:{$this->mqttPort}"
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengirim perintah ke MQTT broker: ' . $e->getMessage(),
                'topic'   => 'lab/lampu2',
                'broker'  => "{$this->mqttHost}:{$this->mqttPort}"
            ], 500);
        }
    }

    /**
     * Handler Kontrol Motor Servo via MQTT
     * Topik: 'lab/servo'
     * Payload: 'BUKA', 'TUTUP', atau angka sudut 0-180
     */
    public function kontrolServo(Request $request, $aksi = null)
    {
        $input = $aksi ?? $request->input('status') ?? $request->input('aksi') ?? $request->input('sudut') ?? $request->input('angle') ?? $request->input('state');
        $inputTrimmed = trim((string)$input);
        $inputLower = strtolower($inputTrimmed);

        // Validasi payload BUKA / TUTUP atau sudut numerik 0-180
        if (is_numeric($inputTrimmed)) {
            $angle = (int)$inputTrimmed;
            if ($angle < 0 || $angle > 180) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sudut servo harus berada di rentang 0 sampai 180 derajat!'
                ], 400);
            }
            $payload = (string)$angle;
        } elseif (in_array($inputLower, ['buka', 'open', 'on', '1'])) {
            $payload = 'BUKA';
        } elseif (in_array($inputLower, ['tutup', 'close', 'off', '0'])) {
            $payload = 'TUTUP';
        } else {
            return response()->json([
                'status'  => 'error',
                'message' => 'Aksi tidak valid! Gunakan BUKA, TUTUP, atau angka sudut 0-180.'
            ], 400);
        }

        try {
            $this->publishToBroker('lab/servo', $payload);

            // Simpan status servo di cache
            Cache::put('servo_state', $payload, 86400);
            $this->catatLog('Motor Servo', "Mengatur Posisi Servo ke {$payload} via MQTT", "Payload: {$payload} | lab/servo");

            return response()->json([
                'status'  => 'success',
                'message' => "Motor Servo Berhasil Diatur ke {$payload} via MQTT!",
                'topic'   => 'lab/servo',
                'payload' => $payload,
                'broker'  => "{$this->mqttHost}:{$this->mqttPort}"
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengirim perintah ke MQTT broker: ' . $e->getMessage(),
                'topic'   => 'lab/servo',
                'broker'  => "{$this->mqttHost}:{$this->mqttPort}"
            ], 500);
        }
    }

    /**
     * Handler API Terpadu untuk AJAX/Frontend (POST /api/mqtt/control)
     * Contoh Body JSON:
     * { "device": "lampu1", "status": "ON" }
     * { "device": "servo", "status": "BUKA" } atau { "device": "servo", "status": 90 }
     */
    public function kontrolMqtt(Request $request)
    {
        $device = strtolower(trim((string)$request->input('device', '')));
        $status = $request->input('status') ?? $request->input('state') ?? $request->input('aksi');

        switch ($device) {
            case 'lampu1':
            case 'lamp1':
                return $this->kontrolLampu1($request, $status);

            case 'lampu2':
            case 'lamp2':
                return $this->kontrolLampu2($request, $status);

            case 'servo':
            case 'motorservo':
                return $this->kontrolServo($request, $status);

            default:
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Perangkat tidak dikenal! Pilihan yang tersedia: lampu1, lampu2, servo.'
                ], 400);
        }
    }

    /**
     * Helper untuk mencatat riwayat aktivitas ke Cache
     */
    private function catatLog(string $device, string $action, string $param): void
    {
        try {
            $user = auth()->user() ? auth()->user()->name : 'User';
            $now = date('d/m/Y H:i:s');
            $existingLogs = Cache::get('activity_logs', []);
            array_unshift($existingLogs, [
                'time'   => $now,
                'user'   => $user,
                'action' => $action,
                'device' => $device,
                'param'  => $param,
                'status' => 'SUCCESS'
            ]);
            Cache::put('activity_logs', array_slice($existingLogs, 0, 50), 86400);
        } catch (\Throwable $e) {}
    }
}