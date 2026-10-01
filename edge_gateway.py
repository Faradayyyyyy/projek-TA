import sys
import time
import os
import io
import json
import queue
import threading
import requests
from PIL import Image
import paho.mqtt.client as mqtt

# =========================================================================
# KONFIGURASI EDGE GATEWAY (LAPTOP / LOCAL SERVER)
# =========================================================================
DEFAULT_VPS_URL = "http://labotomasi.my.id"
AGENT_DVR_BASE_URL = "http://localhost:8090"

# Target Frame Rate (10 - 15 FPS per kamera)
TARGET_FPS = 12
FRAME_INTERVAL = 1.0 / TARGET_FPS

# Target Resolusi dan Kualitas Kompresi HD
TARGET_RESOLUTION = (1280, 720) # 720p HD
JPEG_QUALITY = 75               # Kualitas kompresi 75 (optimize=True)

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
    print("=" * 72)
    print("   IOT EDGE GATEWAY (MULTI-CAMERA HD 720p) - LAB OTOMASI 2")
    print("   Streaming Multi-Threading Independen & Kontrol Hardware Instan")
    print("=" * 72)
    print(f"[*] Target VPS Server : {vps_url}")
    print(f"[*] Local Agent DVR   : {AGENT_DVR_BASE_URL}")
    print(f"[*] Target FPS        : {TARGET_FPS} FPS per kamera aktif")
    print(f"[*] Resolusi & JPEG   : {TARGET_RESOLUTION[0]}x{TARGET_RESOLUTION[1]} (Quality {JPEG_QUALITY}, optimize=True)")
    print(f"[*] MQTT Broker       : {MQTT_BROKER_HOST}:{MQTT_BROKER_PORT}")
    print(f"[*] PTZ Driver Ready  : {has_ptz_driver}")
    print("=" * 72)
    print("[*] Menginisialisasi streaming multi-kamera dan kontrol non-blocking...\n")

def strip_white_letterbox(img):
    """
    Mendeteksi dan memotong bar putih (letterbox) di bagian atas dan bawah frame
    yang dihasilkan oleh kanvas letterboxing Agent DVR. Menjamin video 100% full screen.
    """
    try:
        w, h = img.size
        sample_xs = [int(w * f) for f in (0.1, 0.25, 0.5, 0.75, 0.9)]
        top = 0
        for y in range(0, h // 2):
            if all(img.getpixel((x, y))[:3] == (255, 255, 255) for x in sample_xs):
                top = y + 1
            else:
                break
        bottom = h
        for y in range(h - 1, h // 2, -1):
            if all(img.getpixel((x, y))[:3] == (255, 255, 255) for x in sample_xs):
                bottom = y
            else:
                break
        if (top > 3 or bottom < h - 3) and (bottom > top + 30):
            img = img.crop((0, top, w, bottom))
    except Exception:
        pass
    return img

def process_frame(raw_bytes, target_size=TARGET_RESOLUTION, quality=JPEG_QUALITY):
    """
    Mengubah ukuran frame ke 1280x720 (720p HD) dan mengompres dengan JPEG Quality 75 optimize=True.
    Otomatis memotong letterbox putih agar gambar kamera 100% full tanpa padding dan tidak burik.
    """
    if not raw_bytes or len(raw_bytes) < 100:
        return raw_bytes
    try:
        img = Image.open(io.BytesIO(raw_bytes))
        if img.mode != 'RGB':
            img = img.convert('RGB')
        
        # Bersihkan bar putih letterbox jika ada
        img = strip_white_letterbox(img)

        # Resize ke 1280x720 HD jika belum sesuai
        if img.size != target_size:
            img = img.resize(target_size, Image.Resampling.BILINEAR)

        buf = io.BytesIO()
        safe_quality = max(70, quality) # Jangan gunakan kompresi di bawah 70
        img.save(buf, format="JPEG", quality=safe_quality, optimize=True)
        return buf.getvalue()
    except Exception:
        # Fallback aman jika proses gambar gagal
        return raw_bytes

def execute_command_async(cmd):
    """Mengeksekusi perintah hardware (PTZ & Saklar Lampu) secara non-blocking dan aman."""
    if not isinstance(cmd, dict):
        return

    cmd_type = cmd.get("type")
    
    if cmd_type == "ptz":
        try:
            target_oid = cmd.get("oid")
            action = cmd.get("action")

            # Validasi variabel action: jika None atau string kosong, cetak log peringatan & return
            if action is None or not str(action).strip():
                print("[!] Perintah PTZ diabaikan: parameter action kosong atau None.")
                return

            action = str(action).strip().lower()
            target_oid_str = str(target_oid or "").strip()

            # Default ke OID 4 jika target_oid tidak diberikan
            if not target_oid_str:
                target_oid_str = "4"

            # 1. Jika target_oid adalah Kamera Tapo (OID 4): panggil tapo_move.move_tapo(action, target_oid="4")
            if target_oid_str == "4":
                print(f"\n[>>> KONTROL INSTAN TAPO C200] Memutar Kamera PTZ (OID {target_oid_str}) ke arah: {action.upper()}")
                if has_ptz_driver:
                    try:
                        code, text = tapo_move.move_tapo(action, target_oid="4")
                        print(f"[OK KONTROL SELESAI] Tapo PTZ {action.upper()} dieksekusi (Status {code})")
                    except Exception as e:
                        print(f"[!] Gagal menggerakkan kamera Tapo via tapo_move: {e}")
                else:
                    print("[!] Driver tapo_move tidak tersedia di gateway ini.")

            # 2. Jika target_oid adalah Kamera 1 - Lab Otomasi (OID 5 / ID 2): panggil tapo_move.move_tapo(action, target_oid="5")
            elif target_oid_str in ["5", "1", "2"]:
                print(f"\n[>>> KONTROL INSTAN TAPO C200] Memutar Kamera 1 PTZ (OID {target_oid_str}) ke arah: {action.upper()}")
                if has_ptz_driver:
                    try:
                        code, text = tapo_move.move_tapo(action, target_oid="5")
                        print(f"[OK KONTROL SELESAI] Kamera 1 PTZ {action.upper()} dieksekusi (Status {code})")
                    except Exception as e:
                        print(f"[!] Gagal menggerakkan Kamera 1 via tapo_move: {e}")
                else:
                    print("[!] Driver tapo_move tidak tersedia di gateway ini.")

            # 3. Jika target_oid adalah Kamera Agent DVR lainnya:
            # Kirim request PTZ ke HTTP API Agent DVR lokal
            else:
                ispy_map = {
                    'up': 'ispydir_3',
                    'down': 'ispydir_7',
                    'left': 'ispydir_1',
                    'right': 'ispydir_5',
                    'home': 'home',
                    'center': 'home',
                    'stop': 'ispydir_11',
                    'zoomin': 'ispydir_9',
                    'zoomout': 'ispydir_10'
                }
                action_code = ispy_map.get(action, f"ispydir_{action}" if not action.startswith("ispy") else action)
                base_q = f"{AGENT_DVR_BASE_URL}/q.json"
                print(f"\n[>>> KONTROL INSTAN AGENT DVR] Mengirim PTZ (OID {target_oid_str}, Action: {action.upper()}) -> {base_q}")
                try:
                    if action in ['home', 'center']:
                        params = {
                            'cmd': 'ptzcommand',
                            'field': 'ptz',
                            'value': 'home',
                            'command': 'home',
                            'oid': target_oid_str,
                            'ot': 2
                        }
                        r = requests.get(base_q, params=params, timeout=1.5)
                    else:
                        params_start = {
                            'cmd': 'ptzcommand',
                            'field': 'ptz',
                            'value': action_code,
                            'command': action_code,
                            'oid': target_oid_str,
                            'ot': 2
                        }
                        r = requests.get(base_q, params=params_start, timeout=1.5)
                        time.sleep(0.35)
                        params_stop = {
                            'cmd': 'ptzcommand',
                            'field': 'ptz',
                            'value': 'ispydir_11',
                            'command': 'ispydir_11',
                            'oid': target_oid_str,
                            'ot': 2
                        }
                        requests.get(base_q, params=params_stop, timeout=1.5)
                    print(f"[OK KONTROL SELESAI] Agent DVR PTZ (OID {target_oid_str}) {action.upper()} terkirim (Status {r.status_code})")
                except Exception as e:
                    print(f"[!] Gagal mengirim PTZ ke Agent DVR lokal (OID {target_oid_str}): {e}")

        except Exception as e:
            print(f"[!] Terjadi kesalahan pada eksekusi PTZ: {e}")

    elif cmd_type == "lamp1":
        state = cmd.get("state")
        subcmd = "ON" if state == 1 else "OFF"
        print(f"\n[>>> KONTROL INSTAN] Mengubah Saklar Lampu 1: {subcmd}")
        try:
            mqtt_client.publish("lab/lampu1", subcmd, qos=0)
            print(f"[OK KONTROL SELESAI] Lampu 1 {subcmd} terkirim instan (Topic: lab/lampu1)")
        except Exception as e:
            print(f"[!] Gagal kirim MQTT Lampu 1: {e}")

    elif cmd_type == "lamp2":
        state = cmd.get("state")
        subcmd = "ON" if state == 1 else "OFF"
        print(f"\n[>>> KONTROL INSTAN] Mengubah Saklar Lampu 2: {subcmd}")
        try:
            mqtt_client.publish("lab/lampu2", subcmd, qos=0)
            print(f"[OK KONTROL SELESAI] Lampu 2 {subcmd} terkirim instan (Topic: lab/lampu2)")
        except Exception as e:
            print(f"[!] Gagal kirim MQTT Lampu 2: {e}")

def hardware_poll_worker(poll_url, session):
    """Worker thread terpisah khusus polling perintah hardware setiap ~60ms."""
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
            if str(d.get('oid')) == str(cam.get('oid')) or str(d.get('id')) == str(cam.get('id')):
                d.update(cam)
                found = True
                break
        if not found:
            devices.append(cam)
        with open(cfg_path, 'w', encoding='utf-8') as f:
            json.dump(devices, f, indent=2)
    except Exception:
        pass

class CameraStreamWorker:
    """
    Worker streaming independen untuk satu kamera aktif.
    Memisahkan loop pengambilan frame lokal (Agent DVR) dan upload HTTP (Cloud VPS)
    secara asynchronous dan non-blocking melalui queue berukuran 1.
    """
    def __init__(self, vps_url, cam_info, session):
        self.vps_url = vps_url
        self.cam_info = cam_info
        self.oid = str(cam_info.get("oid") or "4")
        self.cam_id = str(cam_info.get("id") or "")
        self.name = cam_info.get("name", f"Kamera {self.oid}")
        self.session = session

        self.grab_url = f"{AGENT_DVR_BASE_URL}/grab.jpg?oid={self.oid}&size=1280x720"
        self.upload_url = f"{vps_url}/api/cctv/upload-frame?oid={self.oid}&cam_id={self.cam_id}"

        # Queue ukuran 1: jika uploader masih mengirim, frame lama dibuang dan diganti yang terbaru (Zero Lag)
        self.frame_queue = queue.Queue(maxsize=1)
        self.stop_event = threading.Event()

        # Thread grabber & uploader independen
        self.grab_thread = threading.Thread(target=self._grab_loop, daemon=True, name=f"GrabWorker-{self.oid}")
        self.upload_thread = threading.Thread(target=self._upload_loop, daemon=True, name=f"UploadWorker-{self.oid}")

        # Statistik FPS
        self.fps_counter = 0
        self.fps_timer = time.time()

    def start(self):
        print(f"[*] Menjalankan Stream Worker Independen: {self.name} (OID {self.oid})")
        self.grab_thread.start()
        self.upload_thread.start()

    def stop(self):
        self.stop_event.set()

    def _grab_loop(self):
        """Loop independen untuk mengambil frame JPEG dari Agent DVR lokal (Target: 10 - 15 FPS)."""
        target_interval = FRAME_INTERVAL # ~0.083s (12 FPS)
        while not self.stop_event.is_set():
            loop_start = time.time()
            try:
                res = self.session.get(self.grab_url, timeout=0.8)
                if res.status_code == 200 and len(res.content) > 100:
                    # Resize ke 1280x720 HD dan optimasi kompresi JPEG 75
                    processed_bytes = process_frame(res.content, TARGET_RESOLUTION, JPEG_QUALITY)

                    # Masukkan ke queue upload tanpa blocking
                    try:
                        self.frame_queue.put_nowait(processed_bytes)
                    except queue.Full:
                        try:
                            self.frame_queue.get_nowait()
                        except queue.Empty:
                            pass
                        try:
                            self.frame_queue.put_nowait(processed_bytes)
                        except queue.Full:
                            pass
                else:
                    time.sleep(0.15)
            except Exception:
                time.sleep(0.1)

            # Jaga kestabilan frame rate pada kisaran 10 - 15 FPS
            elapsed = time.time() - loop_start
            sleep_duration = max(0.01, target_interval - elapsed)
            time.sleep(sleep_duration)

    def _upload_loop(self):
        """Loop upload HTTP non-blocking ke Cloud VPS."""
        while not self.stop_event.is_set():
            try:
                # Ambil frame terbaru dari queue
                try:
                    frame_data = self.frame_queue.get(timeout=0.5)
                except queue.Empty:
                    continue

                # Upload frame ke VPS
                up_res = self.session.post(
                    self.upload_url,
                    data=frame_data,
                    headers={"Content-Type": "image/jpeg"},
                    timeout=2.0
                )

                if up_res.status_code == 200:
                    self.fps_counter += 1
                    try:
                        resp_data = up_res.json()
                        commands = resp_data.get("commands", [])
                        for cmd in commands:
                            threading.Thread(target=execute_command_async, args=(cmd,), daemon=True).start()
                    except Exception:
                        pass

                # Cetak statistik FPS setiap 5 detik
                now = time.time()
                if now - self.fps_timer >= 5.0:
                    calc_fps = self.fps_counter / (now - self.fps_timer)
                    print(f"[STREAM LIVE] {self.name} (OID {self.oid}): ~{calc_fps:.1f} FPS | 1280x720 HD (Quality {JPEG_QUALITY})")
                    self.fps_counter = 0
                    self.fps_timer = now

            except Exception:
                time.sleep(0.05)

class CameraStreamManager:
    """Mengelola seluruh instance CameraStreamWorker secara dinamis dan concurrency multi-kamera."""
    def __init__(self, vps_url, session):
        self.vps_url = vps_url
        self.session = session
        self.workers = {} # {oid: CameraStreamWorker}
        self.lock = threading.Lock()

    def update_cameras(self, camera_list):
        """Memperbarui daftar kamera aktif dan memastikan setiap OID memiliki thread independen."""
        with self.lock:
            active_oids = set()
            for cam in camera_list:
                oid = str(cam.get("oid") or "").strip()
                if not oid:
                    continue
                active_oids.add(oid)
                if oid not in self.workers:
                    worker = CameraStreamWorker(self.vps_url, cam, self.session)
                    self.workers[oid] = worker
                    worker.start()

            # Hentikan worker untuk kamera yang sudah tidak aktif
            stopped_oids = []
            for oid, worker in self.workers.items():
                if oid not in active_oids:
                    print(f"[*] Menonaktifkan worker kamera OID {oid}...")
                    worker.stop()
                    stopped_oids.append(oid)
            for oid in stopped_oids:
                del self.workers[oid]

    def stop_all(self):
        with self.lock:
            for oid, worker in self.workers.items():
                worker.stop()
            self.workers.clear()

def run_gateway(vps_url):
    vps_url = vps_url.rstrip('/')
    poll_url = f"{vps_url}/api/hardware/poll"

    print_banner(vps_url)

    # Jalankan koneksi MQTT Latar Belakang
    init_mqtt_background()

    session = requests.Session()
    session.headers.update({"User-Agent": "EdgeGateway-IoT/3.0"})

    manager = CameraStreamManager(vps_url, session)

    # Worker thread terpisah khusus polling perintah hardware
    poller_thread = threading.Thread(target=hardware_poll_worker, args=(poll_url, session), daemon=True)
    poller_thread.start()

    # Loop utama: supervisor sinkronisasi daftar kamera setiap 15 detik
    while True:
        try:
            # Ambil daftar seluruh kamera aktif dari VPS
            active_cams = []
            try:
                dev_res = session.get(f"{vps_url}/api/cctv-devices", timeout=3.0)
                if dev_res.status_code == 200:
                    dev_data = dev_res.json()
                    if isinstance(dev_data, list):
                        active_cams = [c for c in dev_data if c.get("is_active")]
                        # Jika list tidak memiliki filter is_active eksplisit, gunakan seluruh device
                        if not active_cams and dev_data:
                            active_cams = dev_data
            except Exception:
                pass

            # Fallback jika belum berhasil mengambil dari API VPS
            if not active_cams and not manager.workers:
                # Coba baca dari file lokal atau default ke OID 4 dan OID 5
                active_cams = [
                    {"id": 1, "name": "Kamera 4 (Tapo C200 Lab 2)", "oid": "4"},
                    {"id": 2, "name": "kamera 1 - lab otomasi", "oid": "5"}
                ]

            if active_cams:
                manager.update_cameras(active_cams)
                for cam in active_cams:
                    sync_active_camera(cam)

            time.sleep(15)

        except KeyboardInterrupt:
            print("\n[*] Edge Gateway dihentikan oleh pengguna.")
            manager.stop_all()
            mqtt_client.loop_stop()
            break
        except Exception as e:
            print(f"[!] Error supervisor gateway: {e}")
            time.sleep(3)

if __name__ == "__main__":
    target_vps = sys.argv[1] if len(sys.argv) > 1 else DEFAULT_VPS_URL
    run_gateway(target_vps)