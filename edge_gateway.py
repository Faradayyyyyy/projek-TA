import sys
import time
import os
import requests
import json

# =========================================================================
# KONFIGURASI EDGE GATEWAY (LAPTOP / LOCAL SERVER)
# =========================================================================
# Alamat server VPS Anda (bisa berupa IP atau Domain).
# Contoh: http://20.198.xxx.xxx:8000 atau https://smartlab-ta.my.id
DEFAULT_VPS_URL = "http://localhost:8000"

# Alamat Agent DVR lokal di laptop
AGENT_DVR_SNAPSHOT_URL = "http://localhost:8090/grab.jpg?oid=4"

# IP Mikrokontroler ESP32 Lokal
ESP32_LAMPU1_URL = "http://10.32.72.150"
ESP32_LAMPU2_URL = "http://10.32.72.151"

def print_banner(vps_url):
    print("=" * 65)
    print("   IOT EDGE GATEWAY - PROJEK TA LAB OTOMASI 2")
    print("   Jembatan Komunikasi Lokal (Kamera dan ESP32) ke Cloud VPS")
    print("=" * 65)
    print(f"[*] Target VPS Server : {vps_url}")
    print(f"[*] Local Agent DVR   : {AGENT_DVR_SNAPSHOT_URL}")
    print(f"[*] ESP32 Lampu 1     : {ESP32_LAMPU1_URL}")
    print(f"[*] ESP32 Lampu 2     : {ESP32_LAMPU2_URL}")
    print("=" * 65)
    print("[*] Menghubungkan ke Cloud VPS...\n")

def run_gateway(vps_url):
    vps_url = vps_url.rstrip('/')
    upload_url = f"{vps_url}/api/cctv/upload-frame?oid=4"
    poll_url = f"{vps_url}/api/hardware/poll"

    print_banner(vps_url)

    # Import driver PTZ lokal
    try:
        import tapo_move
        has_ptz_driver = True
    except Exception as e:
        print(f"[!] Warning: Driver tapo_move tidak dapat diimpor: {e}")
        has_ptz_driver = False

    session = requests.Session()
    last_poll_time = 0
    fps_counter = 0
    fps_timer = time.time()

    while True:
        try:
            # -------------------------------------------------------------
            # 1. AMBIL SNAPSHOT DARI AGENT DVR LOKAL (PORT 8090)
            # -------------------------------------------------------------
            try:
                res = session.get(AGENT_DVR_SNAPSHOT_URL, timeout=1.0)
                if res.status_code == 200 and len(res.content) > 100:
                    # ---------------------------------------------------------
                    # 2. UPLOAD FRAME GAMBAR KE CLOUD VPS
                    # ---------------------------------------------------------
                    up_res = session.post(
                        upload_url,
                        data=res.content,
                        headers={"Content-Type": "image/jpeg"},
                        timeout=1.5
                    )
                    if up_res.status_code == 200:
                        fps_counter += 1
                    else:
                        print(f"[!] Gagal upload frame ke VPS: HTTP {up_res.status_code}")
                else:
                    time.sleep(0.5)
            except requests.exceptions.RequestException as e:
                time.sleep(1.0)

            # -------------------------------------------------------------
            # 3. POLLING PERINTAH HARDWARE DARI VPS (SETIAP ~250ms)
            # -------------------------------------------------------------
            now = time.time()
            if now - last_poll_time >= 0.25:
                last_poll_time = now
                try:
                    poll_res = session.get(poll_url, timeout=1.0)
                    if poll_res.status_code == 200:
                        data = poll_res.json()
                        commands = data.get("commands", [])
                        for cmd in commands:
                            cmd_type = cmd.get("type")
                            if cmd_type == "ptz":
                                action = cmd.get("action")
                                print(f"[>>> HARDWARE] Menerima Perintah PTZ dari VPS: {action}")
                                if has_ptz_driver:
                                    tapo_move.move_tapo(action)
                            elif cmd_type == "lamp1":
                                state = cmd.get("state")
                                subcmd = "on" if state == 1 else "off"
                                print(f"[>>> HARDWARE] Menerima Perintah Saklar Lampu 1: {subcmd.upper()}")
                                try:
                                    requests.get(f"{ESP32_LAMPU1_URL}/lampu1/{subcmd}", timeout=1.0)
                                except Exception:
                                    pass
                            elif cmd_type == "lamp2":
                                state = cmd.get("state")
                                subcmd = "on" if state == 1 else "off"
                                print(f"[>>> HARDWARE] Menerima Perintah Saklar Lampu 2: {subcmd.upper()}")
                                try:
                                    requests.get(f"{ESP32_LAMPU2_URL}/lampu2/{subcmd}", timeout=1.0)
                                except Exception:
                                    pass
                except Exception:
                    pass

            # Tampilkan statistik FPS setiap 5 detik
            if now - fps_timer >= 5.0:
                calc_fps = fps_counter / (now - fps_timer)
                print(f"[STREAM LIVE] Streaming ke Cloud VPS aktif: ~{calc_fps:.1f} FPS")
                fps_counter = 0
                fps_timer = now

            # Throttle ~10 - 12 FPS
            time.sleep(0.08)

        except KeyboardInterrupt:
            print("\n[*] Edge Gateway dimatikan.")
            break
        except Exception as e:
            print(f"[!] Error loop Edge Gateway: {e}")
            time.sleep(1.0)

if __name__ == "__main__":
    target_vps = sys.argv[1] if len(sys.argv) > 1 else DEFAULT_VPS_URL
    run_gateway(target_vps)
