#include <Arduino.h>
#include <WiFi.h>
#include <WiFiMulti.h>
#include <PubSubClient.h>
#include <ESP32Servo.h>

WiFiMulti wifiMulti;
WiFiClient espClient;
PubSubClient mqttClient(espClient);

// =========================================================================
// I. KONFIGURASI WIFI & IP STATIS TETAP (BENGKEL & KOST) - ALAT 1 (IP .150)
// =========================================================================
// Jaringan 1: Bengkel Mekanik (Subnet 10.32.72.x) - IP .150
IPAddress ip_bengkel(10, 32, 72, 150);
IPAddress gw_bengkel(10, 32, 72, 1);
IPAddress subnet_bengkel(255, 255, 255, 0);

// Jaringan 2: Kost Jelbie (Subnet 192.168.1.x) - IP .150
IPAddress ip_kost(192, 168, 1, 150);
IPAddress gw_kost(192, 168, 1, 1);
IPAddress subnet_kost(255, 255, 255, 0);

IPAddress dns(8, 8, 8, 8);

// =========================================================================
// II. KONFIGURASI MQTT BROKER
// =========================================================================
const char* mqtt_server = "48.193.45.137"; // Azure VPS
const int   mqtt_port   = 1883;
const char* mqtt_topic  = "lab/lampu1";

unsigned long lastMqttReconnectAttempt = 0;

// =========================================================================
// III. INISIALISASI HARDWARE SERVO LAMPU 1
// =========================================================================
Servo servoSaklar;

// PIN SERVO: GPIO 13 (D13)
const int SERVO_PIN     = 13; 

// --- KALIBRASI SUDUT SAKLAR ---
const int POSISI_NETRAL = 90;  // Posisi Standby Tegak Netral (90 Derajat)
const int POSISI_ON     = 30;  // Sudut Menekan ON
const int POSISI_OFF    = 150; // Sudut Menekan OFF

volatile int   targetSudut         = -1; 
int            statusSudutTerakhir = -1;
unsigned long  waktuEksekusiTerakhir = 0;

// =========================================================================
// IV. FUNGSI PENSUKSER GERAKAN: TEKAN -> KEMBALI NETRAL SEMPURNA -> DETACH
// =========================================================================
void eksekusiSaklar(int sudutSasaran) {
  // Proteksi Debounce 1.5 Detik
  if (sudutSasaran == statusSudutTerakhir && (millis() - waktuEksekusiTerakhir < 1500)) {
    return;
  }
  
  statusSudutTerakhir = sudutSasaran;
  waktuEksekusiTerakhir = millis();

  Serial.println("\n==========================================");
  Serial.printf("[SERVO 1] 1. Menekan Saklar ke Sudut: %d Deg\n", sudutSasaran);

  // 1. Hubungkan sinyal PWM ke GPIO 13
  servoSaklar.attach(SERVO_PIN, 500, 2400);
  servoSaklar.write(POSISI_NETRAL);
  delay(60);

  // 2. Putar motor untuk menekan saklar fisik
  servoSaklar.write(sudutSasaran);
  delay(380); // Waktu tekan mantap

  // 3. Kembalikan ke posisi netral (90 derajat)
  Serial.println("[SERVO 1] 2. Memutar Balik Kembali ke Posisi Netral (90 Deg)...");
  servoSaklar.write(POSISI_NETRAL);
  delay(450); // Waktu yang cukup untuk sampai di titik netral 90°

  // 4. Putuskan sinyal PWM
  servoSaklar.detach();
  pinMode(SERVO_PIN, OUTPUT);
  digitalWrite(SERVO_PIN, LOW); // Tarik ke 0V
  
  Serial.println("[SERVO 1] 3. Selesai di Posisi Netral & Detached (Siaga Aman).\n");
  Serial.println("==========================================\n");
}

// =========================================================================
// V. CALLBACK MQTT (MENDENGARKAN PESAN DARI TOPIK 'lab/lampu1')
// =========================================================================
void mqttCallback(char* topic, byte* payload, unsigned int length) {
  String message = "";
  for (unsigned int i = 0; i < length; i++) {
    message += (char)payload[i];
  }
  message.trim();
  message.toUpperCase();

  Serial.println("\n------------------------------------------");
  Serial.printf("[MQTT INCOMING] Topik: %s | Pesan: %s\n", topic, message.c_str());

  if (String(topic) == mqtt_topic) {
    if (message == "ON" || message == "1") {
      Serial.println("[MQTT] Perintah ON Diterima -> Jadwalkan Putar ke POSISI_ON (30 Deg)");
      targetSudut = POSISI_ON;
    } 
    else if (message == "OFF" || message == "0") {
      Serial.println("[MQTT] Perintah OFF Diterima -> Jadwalkan Putar ke POSISI_OFF (150 Deg)");
      targetSudut = POSISI_OFF;
    } 
    else {
      Serial.printf("[MQTT] Perintah '%s' tidak valid! Hanya menerima ON / OFF.\n", message.c_str());
    }
  }
  Serial.println("------------------------------------------");
}

// =========================================================================
// VI. RECONNECT MQTT OTOMATIS (NON-BLOCKING)
// =========================================================================
void reconnectMQTT() {
  unsigned long now = millis();
  // Coba hubungkan kembali setiap 5 detik jika koneksi terputus
  if (now - lastMqttReconnectAttempt > 5000 || lastMqttReconnectAttempt == 0) {
    lastMqttReconnectAttempt = now;

    Serial.print("[MQTT] Menghubungkan ke Broker ");
    Serial.print(mqtt_server);
    Serial.print(":");
    Serial.print(mqtt_port);
    Serial.print("... ");

    // Buat Client ID unik berdasarkan MAC Address ESP32 (Lampu 1)
    String clientId = "ESP32-Lampu1-" + String((uint32_t)ESP.getEfuseMac(), HEX);

    if (mqttClient.connect(clientId.c_str())) {
      Serial.println("BERHASIL TERHUBUNG!");
      
      // Subscribe ke topik 'lab/lampu1'
      mqttClient.subscribe(mqtt_topic);
      Serial.printf("[MQTT] Berhasil Subscribe ke Topik: %s\n", mqtt_topic);
      
      // Kirim status online ke broker
      mqttClient.publish("lab/lampu1/status", "ONLINE", true);
    } else {
      Serial.print("GAGAL, rc=");
      Serial.print(mqttClient.state());
      Serial.println(" (Akan mencoba lagi dalam 5 detik)");
    }
  }
}

// =========================================================================
// VII. SETUP PROGRAM
// =========================================================================
void setup() {
  Serial.begin(115200);

  // Alokasi Timer PWM untuk Servo ESP32
  ESP32PWM::allocateTimer(0);
  ESP32PWM::allocateTimer(1);
  servoSaklar.setPeriodHertz(50);

  // Pastikan pin servo MATI TOTAL saat booting awal
  pinMode(SERVO_PIN, OUTPUT);
  digitalWrite(SERVO_PIN, LOW);

  // 1. Daftarkan SSID & Password Wi-Fi
  wifiMulti.addAP("Bengkel Mekanik", "bengkel24");
  wifiMulti.addAP("Kost Jelbie Lt 2", "wlan47d147");

  Serial.println("\n[ESP32] Menghubungkan ke Wi-Fi...");
  while (wifiMulti.run() != WL_CONNECTED) {
    delay(300);
    Serial.print(".");
  }

  // 2. Atur Static IP SETELAH Wi-Fi terhubung sesuai dengan SSID aktif (IP .150)
  String currentSSID = WiFi.SSID();
  if (currentSSID == "Bengkel Mekanik") {
    WiFi.config(ip_bengkel, gw_bengkel, subnet_bengkel, dns);
  } else if (currentSSID == "Kost Jelbie Lt 2") {
    WiFi.config(ip_kost, gw_kost, subnet_kost, dns);
  }

  Serial.println("\n==========================================");
  Serial.println("    ESP32 LAMPU 1 ONLINE (MQTT CLIENT)   ");
  Serial.print("  Wi-Fi Terhubung : ");
  Serial.println(WiFi.SSID());
  Serial.print("  IP Address      : ");
  Serial.println(WiFi.localIP());
  Serial.print("  MQTT Broker     : ");
  Serial.println(mqtt_server);
  Serial.print("  Topik Kontrol   : ");
  Serial.println(mqtt_topic);
  Serial.println("  Status Servo    : Siaga Diam (GPIO 13)");
  Serial.println("==========================================\n");

  // 3. Konfigurasi Client MQTT
  mqttClient.setServer(mqtt_server, mqtt_port);
  mqttClient.setCallback(mqttCallback);

  // Hubungkan ke broker
  reconnectMQTT();
}

// =========================================================================
// VIII. LOOP UTAMA
// =========================================================================
void loop() {
  // 1. Pastikan Wi-Fi tetap terhubung
  if (wifiMulti.run() != WL_CONNECTED) {
    delay(100);
    return;
  }

  // 2. Jaga koneksi MQTT Broker tetap aktif
  if (!mqttClient.connected()) {
    reconnectMQTT();
  } else {
    mqttClient.loop();
  }

  // 3. Eksekusi Gerakan Servo jika ada target sudut baru dari MQTT
  if (targetSudut != -1) {
    int sudut = targetSudut;
    targetSudut = -1; // Reset target agar tidak berulang
    eksekusiSaklar(sudut);
  }
}
