#include <Arduino.h>
#include <WiFi.h>
#include <ESPAsyncWebServer.h>
#include <ESP32Servo.h>

// =========================================================================
// I. KONFIGURASI WIFI
// =========================================================================
const char* ssid     = "Kost Jelbie Lt 2"; 
const char* password = "wlan47d147"; 

// =========================================================================
// II. INISIALISASI HARDWARE SERVO ALAT KEDUA
// =========================================================================
Servo servoSaklar2;

const int SERVO_PIN     = 18;  // Menggunakan GPIO 18 sesuai rangkaian fisikmu

// --- KALIBRASI SUDUT TEKAN (Sudut disesuaikan agar menekan lebih dalam) ---
const int POSISI_NETRAL = 90;  // Posisi Siaga / Standby Tegak
const int POSISI_ON     = 30;  // Sudut memutar ke satu arah untuk ON (Bisa dibalik ke 150 jika terbalik)
const int POSISI_OFF    = 150; // Sudut memutar ke arah sebaliknya untuk OFF (Bisa dibalik ke 30 jika terbalik)

// Variabel Antrean Sinyal Non-Blocking
volatile int targetSudut = -1; 

AsyncWebServer server(80);

// =========================================================================
// III. FUNGSI EKSEKUSI GERAKAN SERVO
// =========================================================================
void eksekusiSaklar(int sudut) {
  Serial.print("[ALAT 2] Memulai Putaran ke Sudut: ");
  Serial.println(sudut);

  // Attach sinyal PWM ke pin servo
  servoSaklar2.attach(SERVO_PIN, 500, 2400);
  delay(100); 
  
  // 1. Putar servo ke sudut target (ON / OFF)
  servoSaklar2.write(sudut);
  delay(800); // Tahan 0.8 detik agar saklar fisik tertekan "klik"
  
  // 2. Kembalikan lengan servo ke posisi netral agar tidak terus menekan saklar
  Serial.println("[ALAT 2] Kembali ke Posisi Netral (90 Deg)");
  servoSaklar2.write(POSISI_NETRAL); 
  delay(500); 
  
  // 3. Matikan sinyal PWM agar motor servo dingin & hemat daya
  servoSaklar2.detach();
  Serial.println("[ALAT 2] Selesai & Detached.\n");
}

// =========================================================================
// IV. SETUP PROGRAM
// =========================================================================
void setup() {
  Serial.begin(115200);

  // Alokasi Timer PWM untuk Servo ESP32
  ESP32PWM::allocateTimer(0);
  ESP32PWM::allocateTimer(1);
  servoSaklar2.setPeriodHertz(50);

  // 1. Menghubungkan ke Wi-Fi Kost
  Serial.println();
  Serial.print("Connecting to WiFi: ");
  Serial.println(ssid);
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\n==========================================");
  Serial.println("  ESP32 ALAT KEDUA BERHASIL TERHUBUNG!");
  Serial.print("  IP Address: http://");
  Serial.println(WiFi.localIP()); // <-- CATAT IP ALAT KEDUA INI DI LAREVEL!
  Serial.println("==========================================");

  // -----------------------------------------------------------------------
  // API ROUTING ENDPOINTS KHUSUS ALAT KEDUA
  // -----------------------------------------------------------------------
  
  // Endpoint Nyalakan Lampu 2: http://<IP_ALAT_2>/lampu2/on
  server.on("/lampu2/on", HTTP_GET, [](AsyncWebServerRequest *request){
    Serial.println("\n[API] Received Request: Lampu 2 ON");
    targetSudut = POSISI_ON; // Oper target ke loop utama
    request->send(200, "text/plain", "Lampu 2 Berhasil Menyala!");
  });

  // Endpoint Matikan Lampu 2: http://<IP_ALAT_2>/lampu2/off
  server.on("/lampu2/off", HTTP_GET, [](AsyncWebServerRequest *request){
    Serial.println("\n[API] Received Request: Lampu 2 OFF");
    targetSudut = POSISI_OFF; // Oper target ke loop utama
    request->send(200, "text/plain", "Lampu 2 Berhasil Dimatikan!");
  });

  server.begin();
  Serial.println("Web Server ESP32 Alat 2 Aktif & Siap!");
}

// =========================================================================
// V. LOOP UTAMA
// =========================================================================
void loop() {
  // Mengeksekusi gerakan servo dengan aman di luar callback web server
  if (targetSudut != -1) {
    int sudutYangDieksekusi = targetSudut;
    targetSudut = -1; // Reset flag antrean
    
    eksekusiSaklar(sudutYangDieksekusi);
  }
  
  delay(10);
}