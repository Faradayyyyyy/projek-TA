import sys
import time
import os
import requests
import json
import threading
import paho.mqtt.client as mqtt

# =========================================================================
# KONFIGURASI EDGE GATEWAY (LAPTOP / LOCAL SERVER)
# =========================================================================
DEFAULT_VPS_URL = "http://labotomasi.my.id"
AGENT_DVR_SNAPSHOT_URL = "http://localhost:8090/grab.jpg?oid=4"

# Broker MQTT
MQTT_BROKER_HOST = "48.193.45.137"
MQTT_BROKER_PORT = 1883

# Inisialisasi MQTT Client Persisten
mqtt_client = mqtt.Client(client_id="EdgeGateway_Laptop")

def init_mqtt_background():
    """Menghubungkan MQTT Client dan menjalankannya di latar belakang."""
    try:
        mqtt_client.connect(MQTT_BROKER_HOST, MQTT_BROKER_PORT, keepalive=60)
        mqtt_client.loop_start() # Jalankan MQTT loop di background thread
        print("[*] MQTT Background Client berhasil terhubung & aktif di latar belakang.")
    except Exception as e:
        print(f"[!] Gagal menghubungkan MQTT Background Client: {e}")

# Import driver PTZ lokal
try:
    import tapo_move
    has_ptz_driver = True
except Exception as e:
    print(f"[!] Warning: Driver tapo_move tidak dapat diimpor: {e}")
    has_ptz_driver = False

def print_banner(vps_url):
    print("=" * 68)
    print("   IOT EDGE GATEWAY (HIGH PERFORMANCE) - LAB OTOMASI 2")
    print("   Jembatan Komunikasi Lokal (Kamera & ESP32) ke Cloud VPS")
    print("=" * 68)
    print(f"[*] Target VPS Server : {vps_url}")
    print(f"[*] Local Agent DVR   : {AGENT_DVR_SNAPSHOT_URL}")
    print(f"[*] MQTT Broker       : {MQTT_BROKER_HOST}:{MQTT_BROKER_PORT}")
    print(f"[*] PTZ Driver Ready  : {has_ptz_driver}")
    print("=" * 68)
    print("[*] Streaming video & kontrol hardware aktif tanpa delay...\n")

def execute_command_async(cmd):
    """Mengeksekusi perintah hardware secara non-blocking."""
    cmd_type = cmd.get("type")
    
    if cmd_type == "ptz":
        action = cmd.get("action")
        print(f"\n[>>> KONTROL INSTAN] Memutar Kamera PTZ ke arah: {action.upper()}")
        if has_ptz_driver:
            try:
                code, text = tapo_move.move_tapo(action)
                print(f"[✓ KONTROL SELESAI] PTZ {action.upper()} dieksekusi (Status {code})")
            except Exception as e:
                print(f"[!] Gagal gerakkan kamera: {e}")
                
    elif cmd_type == "lamp1":
        state = cmd.get("state")
        subcmd = "ON" if state == 1 else "OFF"
        print(f"\n[>>> KONTROL INSTAN] Mengubah Saklar Lampu 1: {subcmd}")
        try:
            # Mengirim via client background yang sudah terhubung
            mqtt_client.publish("lab/lampu1", subcmd, qos=0)
            print(f"[✓ KONTROL SELESAI] Lampu 1 {subcmd} terkirim instan (Topic: lab/lampu1)")
        except Exception as e:
            print(f"[!] Gagal kirim MQTT Lampu 1: {e}")
            
    elif cmd_type == "lamp2":
        state = cmd.get("state")
        subcmd = "ON" if state == 1 else "OFF"
        print(f"\n[>>> KONTROL INSTAN] Mengubah Saklar Lampu 2: {subcmd}")
        try:
            # Mengirim via client background yang sudah terhubung
            mqtt_client.publish("lab/lampu2", subcmd, qos=0)
            print(f"[✓ KONTROL SELESAI] Lampu 2 {subcmd} terkirim instan (Topic: lab/lampu2)")
        except Exception as e:
            print(f"[!] Gagal kirim MQTT Lampu 2: {e}")

def hardware_poll_worker(poll_url, session):
    """Worker thread terpisah khusus polling perintah setiap ~60ms."""
    while True:
        try:
            res = session.get(poll_url, timeout=1.0)
            if res.status_code == 200:
                data = res.json()
                commands = data.get("commands", [])
                for cmd in commands:
                    threading.Thread(target=execute_command_async, args=(cmd,), daemon=True).start()
        except Exception:
            pass
        time.sleep(0.06)

def sync_active_camera(cam):
    """Menyinkronkan info kamera aktif dari VPS ke storage/app/cctv_devices.json lokal."""
    if not cam:
        return
    try:
        cfg_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'storage', 'app')
        os.makedirs(cfg_dir, exist_ok=True)
        cfg_path = os.path.join(cfg_dir, 'cctv_devices.json')
        devices = []
        if os.path.exists(cfg_path):
            with open(cfg_path, 'r', encoding='utf-8') as f:
                devices = json.load(f)
        found = False
        for d in devices:
            if d.get('id') == cam.get('id'):
                d.update(cam)
                d['is_active'] = True
                found = True
            else:
                d['is_active'] = False
        if not found:
            devices.append(cam)
        with open(cfg_path, 'w', encoding='utf-8') as f:
            json.dump(devices, f, indent=2)
    except Exception as e:
        pass

def run_gateway(vps_url):
    vps_url = vps_url.rstrip('/')
    current_oid = "4"
    current_cam_id = ""
    agent_dvr_snapshot_url = f"http://localhost:8090/grab.jpg?oid={current_oid}"
    upload_url = f"{vps_url}/api/cctv/upload-frame?oid={current_oid}"
    poll_url = f"{vps_url}/api/hardware/poll"

    print_banner(vps_url)

    # Jalankan koneksi MQTT Latar Belakang
    init_mqtt_background()

    session = requests.Session()
    session.headers.update({"User-Agent": "EdgeGateway-IoT/2.0"})

    # Coba ambil info kamera aktif pertama kali dari VPS
    try:
        dev_res = session.get(f"{vps_url}/api/cctv-devices", timeout=2.0)
        if dev_res.status_code == 200:
            dev_data = dev_res.json()
            if isinstance(dev_data, list):
                active_cam = dev_data[0] if len(dev_data) > 0 else None
            elif isinstance(dev_data, dict):
                active_cam = dev_data.get("active") or (dev_data.get("devices", [None])[0] if dev_data.get("devices") else None)
            else:
                active_cam = None

            if active_cam:
                current_oid = str(active_cam.get("oid") or "4")
                current_cam_id = str(active_cam.get("id") or "")
                agent_dvr_snapshot_url = f"http://localhost:8090/grab.jpg?oid={current_oid}"
                upload_url = f"{vps_url}/api/cctv/upload-frame?oid={current_oid}&cam_id={current_cam_id}"
                sync_active_camera(active_cam)
                print(f"[*] Kamera Aktif Awal: {active_cam.get('name', 'CCTV')} (OID {current_oid} | IP {active_cam.get('ip')})")
    except Exception:
        pass

    poller_thread = threading.Thread(target=hardware_poll_worker, args=(poll_url, session), daemon=True)
    poller_thread.start()

    fps_counter = 0
    fps_timer = time.time()

    while True:
        try:
            # 1. Ambil frame JPEG dari Agent DVR lokal sesuai OID aktif
            try:
                res = session.get(agent_dvr_snapshot_url, timeout=1.0)
                if res.status_code == 200 and len(res.content) > 100:
                    # 2. Upload frame ke Cloud VPS
                    up_res = session.post(
                        upload_url,
                        data=res.content,
                        headers={"Content-Type": "image/jpeg"},
                        timeout=1.5
                    )
                    
                    if up_res.status_code == 200:
                        fps_counter += 1
                        try:
                            resp_data = up_res.json()
                            commands = resp_data.get("commands", [])
                            for cmd in commands:
                                threading.Thread(target=execute_command_async, args=(cmd,), daemon=True).start()
                            
                            # Sinkronisasi kamera aktif otomatis jika berganti di web
                            active_cam = resp_data.get("active_camera")
                            if active_cam:
                                new_oid = str(active_cam.get("oid") or "4")
                                new_cam_id = str(active_cam.get("id") or "")
                                if new_oid != current_oid:
                                    print(f"\n[*] Beralih ke Kamera: {active_cam.get('name', 'CCTV')} (OID {new_oid} | IP {active_cam.get('ip')})")
                                    current_oid = new_oid
                                    current_cam_id = new_cam_id
                                    agent_dvr_snapshot_url = f"http://localhost:8090/grab.jpg?oid={current_oid}"
                                    upload_url = f"{vps_url}/api/cctv/upload-frame?oid={current_oid}&cam_id={current_cam_id}"
                                sync_active_camera(active_cam)
                        except Exception:
                            pass
                    else:
                        print(f"[!] Upload frame gagal: HTTP {up_res.status_code}")
                else:
                    time.sleep(0.3)
            except requests.exceptions.RequestException:
                time.sleep(0.5)

            # Hitung statistik FPS setiap 5 detik
            now = time.time()
            if now - fps_timer >= 5.0:
                calc_fps = fps_counter / (now - fps_timer)
                print(f"[STREAM LIVE] Aliran Video ke Cloud Aktif: ~{calc_fps:.1f} FPS (OID {current_oid})")
                fps_counter = 0
                fps_timer = now

            time.sleep(0.08)

        except KeyboardInterrupt:
            print("\n[*] Edge Gateway dihentikan oleh pengguna.")
            mqtt_client.loop_stop() # Hentikan thread MQTT saat dihentikan
            break
        except Exception as e:
            print(f"[!] Error loop utama: {e}")
            time.sleep(0.5)

if __name__ == "__main__":
    target_vps = sys.argv[1] if len(sys.argv) > 1 else DEFAULT_VPS_URL
    run_gateway(target_vps)