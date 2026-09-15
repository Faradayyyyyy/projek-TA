<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class LampuController extends Controller
{
    private $ipEsp32_Lampu1;
    private $ipEsp32_Lampu2;

    public function __construct()
    {
        // Ambil IP dari file .env (dengan fallback default IP Statis)
        $this->ipEsp32_Lampu1 = rtrim(trim(env('ESP32_LAMPU1_IP', 'http://10.32.72.150')), '/');
        $this->ipEsp32_Lampu2 = rtrim(trim(env('ESP32_LAMPU2_IP', 'http://10.32.72.151')), '/');
    }

    /**
     * Handler Kontrol Lampu 1 (Servo 1)
     */
    public function kontrolLampu1($aksi)
    {
        if (!in_array($aksi, ['on', 'off'])) {
            return response()->json(['status' => 'error', 'message' => 'Aksi tidak valid!'], 400);
        }

        Cache::put('lamp1', ($aksi === 'on') ? 1 : 0, 86400);

        try {
            // Fast timeout agar respons web instan saat ESP32 offline
            $response = Http::connectTimeout(0.2)->timeout(0.3)->get($this->ipEsp32_Lampu1 . "/lampu1/" . $aksi);
            
            return response()->json([
                'status'  => 'success',
                'message' => 'Lampu 1 Berhasil Diubah!',
                'esp32'   => 'connected',
                'data'    => $response->body()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Status Lampu 1 Diperbarui di Web! (ESP32 Offline)',
                'esp32'   => 'offline'
            ], 200);
        }
    }

    /**
     * Handler Kontrol Lampu 2 (Servo 2)
     */
    public function kontrolLampu2($aksi)
    {
        if (!in_array($aksi, ['on', 'off'])) {
            return response()->json(['status' => 'error', 'message' => 'Aksi tidak valid!'], 400);
        }

        Cache::put('lamp2', ($aksi === 'on') ? 1 : 0, 86400);

        try {
            // Fast timeout agar respons web instan saat ESP32 offline
            $response = Http::connectTimeout(0.2)->timeout(0.3)->get($this->ipEsp32_Lampu2 . "/lampu2/" . $aksi);
            
            return response()->json([
                'status'  => 'success',
                'message' => 'Lampu 2 Berhasil Diubah!',
                'esp32'   => 'connected',
                'data'    => $response->body()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Status Lampu 2 Diperbarui di Web! (ESP32 Offline)',
                'esp32'   => 'offline'
            ], 200);
        }
    }
}