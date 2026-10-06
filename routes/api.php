<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CctvController;
use App\Http\Controllers\LampuController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rute-rute di bawah ini otomatis memiliki prefix '/api' oleh Laravel.
|
*/

// =========================================================================
// API ENDPOINT MANAJEMEN CCTV & STREAMING EDGE GATEWAY
// =========================================================================

// 1. Ambil daftar seluruh kamera aktif (JSON Array untuk Edge Gateway & Web)
Route::get('/cctv-devices', [CctvController::class, 'index']);

// 2. Tambah / Edit Device Kamera dari Form Web
Route::post('/cctv-devices', [CctvController::class, 'store']);

// 3. Ganti Kamera Aktif (Switch Camera)
Route::post('/cctv-devices/switch/{id}', [CctvController::class, 'switchCamera']);

// 4. Hapus Device Kamera
Route::delete('/cctv-devices/{id}', [CctvController::class, 'destroy']);

// 5. Uji Koneksi Kamera (TCP Socket Ping Test)
Route::post('/cctv-devices/test', [CctvController::class, 'testConnection']);

// 5b. Update OID Resmi dari Agent DVR
Route::post('/cctv-devices/{id}/update-oid', [CctvController::class, 'updateOid']);
Route::post('/cctv-devices/update-oid', [CctvController::class, 'updateOid']);

// 6. Upload Frame Gambar dari Edge Gateway (Laptop) ke Cloud VPS
Route::post('/cctv/upload-frame', [CctvController::class, 'uploadFrame']);

// 7. Ambil Snapshot Gambar Kamera Aktif (Proxy Stream)
Route::get('/cctv-snapshot', [CctvController::class, 'snapshot']);

// 8. Kontrol ONVIF PTZ Kamera Fisik
Route::match(['get', 'post'], '/cctv-ptz/{command}', [CctvController::class, 'ptz']);

// 9. Kontrol Power On/Off Kamera
Route::get('/cctv-power/{action}', [CctvController::class, 'power']);

// 10. Polling Antrian Perintah Hardware untuk Edge Gateway (menghasilkan field oid, type, action)
Route::get('/hardware/poll', [CctvController::class, 'pollHardwareCommands']);

// 11. Kontrol Hardware Terpadu (PTZ per OID, Lampu) via POST JSON
Route::post('/hardware/control', [CctvController::class, 'controlHardware']);

// =========================================================================
// API ENDPOINT KONTROL MQTT & HARDWARE
// =========================================================================
Route::post('/mqtt/control', [LampuController::class, 'kontrolMqtt'])->name('api.mqtt.control');
