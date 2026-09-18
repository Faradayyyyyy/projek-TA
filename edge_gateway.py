import sys
import time
import os
import requests
import json
import threading

# =========================================================================
# KONFIGURASI EDGE GATEWAY (LAPTOP / LOCAL SERVER)
# =========================================================================
# Default mengarah ke domain lab Anda
DEFAULT_VPS_URL = "http://labotomasi.my.id"

# Alamat Agent DVR lokal di laptop
AGENT_DVR_SNAPSHOT_URL = "http://localhost:8090/grab.jpg?oid=4"

# IP Mikrokontroler ESP32 Lokal
ESP32_LAMPU1_URL = "http://10.32.72.150"
ESP32_LAMPU2_URL = "http://10.32.72.151"

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
    print(f"[*] ESP32 Lampu 1     : {ESP32_LAMPU1_URL}")
    print(f"[*] ESP32 Lampu 2     : {ESP32_LAMPU2_URL}")
    print(f"[*] PTZ Driver Ready  : {has_ptz_driver}")
    print("=" * 68)
    print("[*] Streaming video & kontrol hardware aktif tanpa delay...\n")

def execute_command_async(cmd):
    """Mengeksekusi perintah hardware secara non-blocking di thread terpisah."""
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
        subcmd = "on" if state == 1 else "off"
        print(f"\n[>>> KONTROL INSTAN] Mengubah Saklar Lampu 1: {subcmd.upper()}")
        try:
            r = requests.get(f"{ESP32_LAMPU1_URL}/lampu1/{subcmd}", timeout=1.0)
            print(f"[✓ KONTROL SELESAI] Lampu 1 {subcmd.upper()} (HTTP {r.status_code})")
        except Exception as e:
            print(f"[!] Gagal kontak ESP32 Lampu 1: {e}")
            
    elif cmd_type == "lamp2":
        state = cmd.get("state")
        subcmd = "on" if state == 1 else "off"
        print(f"\n[>>> KONTROL INSTAN] Mengubah Saklar Lampu 2: {subcmd.upper()}")
        try:
            r = requests.get(f"{ESP32_LAMPU2_URL}/lampu2/{subcmd}", timeout=1.0)
            print(f"[✓ KONTROL SELESAI] Lampu 2 {subcmd.upper()} (HTTP {r.status_code})")
        except Exception as e:
            print(f"[!] Gagal kontak ESP32 Lampu 2: {e}")

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

def run_gateway(vps_url):
    vps_url = vps_url.rstrip('/')
    # Jika tidak ada port yang disebutkan dan bukan localhost, gunakan port 80 default
    upload_url = f"{vps_url}/api/cctv/upload-frame?oid=4"
    poll_url = f"{vps_url}/api/hardware/poll"

    print_banner(vps_url)

    session = requests.Session()
    session.headers.update({"User-Agent": "EdgeGateway-IoT/2.0"})

    # Jalankan background poller thread untuk responsivitas maksimal (< 40ms)
    poller_thread = threading.Thread(target=hardware_poll_worker, args=(poll_url, session), daemon=True)
    poller_thread.start()

    fps_counter = 0
    fps_timer = time.time()

    while True:
        try:
            # 1. Ambil frame JPEG dari Agent DVR lokal
            try:
                res = session.get(AGENT_DVR_SNAPSHOT_URL, timeout=1.0)
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
                        # Cek apakah ada perintah hardware yang diselipkan dalam response upload
                        try:
                            resp_data = up_res.json()
                            commands = resp_data.get("commands", [])
                            for cmd in commands:
                                threading.Thread(target=execute_command_async, args=(cmd,), daemon=True).start()
                        except Exception:
                            pass
                    else:
                        print(f"[!] Upload frame gagal: HTTP {up_res.status_code}")
                else:
                    time.sleep(0.3)
            except requests.exceptions.RequestException as e:
                time.sleep(0.5)

            # Hitung statistik FPS setiap 5 detik
            now = time.time()
            if now - fps_timer >= 5.0:
                calc_fps = fps_counter / (now - fps_timer)
                print(f"[STREAM LIVE] Aliran Video ke Cloud Aktif: ~{calc_fps:.1f} FPS (Latensi Rendah)")
                fps_counter = 0
                fps_timer = now

            # Kecepatan frame ~12 FPS (80ms jeda)
            time.sleep(0.08)

        except KeyboardInterrupt:
            print("\n[*] Edge Gateway dihentikan oleh pengguna.")
            break
        except Exception as e:
            print(f"[!] Error loop utama: {e}")
            time.sleep(0.5)

if __name__ == "__main__":
    target_vps = sys.argv[1] if len(sys.argv) > 1 else DEFAULT_VPS_URL
    run_gateway(target_vps)
