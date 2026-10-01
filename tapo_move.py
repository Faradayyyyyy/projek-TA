import sys
import time
import os
import json
import base64
import hashlib
from datetime import datetime, timezone
import requests

# =========================================================================
# KONFIGURASI KAMERA TAPO C200 (ONVIF)
# =========================================================================
IP = "10.32.72.177"
PORT = 2020
USER = "faradays"
PASS = "12345678"
PROFILE_TOKEN = "profile_1"

# Mapping konfigurasi kamera Tapo / ONVIF per OID (Pertahankan Kamera 4 & dukung Kamera 1)
CAMERA_DEFAULTS = {
    "4": {
        "ip": "10.32.72.177",
        "port": 2020,
        "user": "faradays",
        "pass": "12345678",
        "name": "kamera 2"
    },
    "5": {
        "ip": "10.32.72.68",
        "port": 2020,
        "user": "viggos",
        "pass": "12345678",
        "name": "Kamera 1 - Mesin Air Blast"
    }
}

def safe_print(*args, **kwargs):
    try:
        print(*args, **kwargs)
        sys.stdout.flush()
    except Exception:
        pass

def load_config(target_oid="4"):
    global IP, PORT, USER, PASS
    oid_str = str(target_oid or "4").strip()
    
    # 0. Set default berdasarkan OID yang diminta
    cfg = CAMERA_DEFAULTS.get(oid_str)
    if not cfg:
        # Fallback jika cam_id 1 atau 2 dikirim
        if oid_str in ["1", "5"]:
            cfg = CAMERA_DEFAULTS["5"]
            oid_str = "5"
        else:
            cfg = CAMERA_DEFAULTS["4"]
            oid_str = "4"
            
    IP = cfg["ip"]
    PORT = cfg["port"]
    USER = cfg["user"]
    PASS = cfg["pass"]

    # 1. Coba baca IP dari live configuration Agent DVR jika ada
    found_in_objects = False
    try:
        agent_objects = r"C:\Program Files\Agent\Media\XML\objects.json"
        if os.path.exists(agent_objects):
            for enc in ['utf-8-sig', 'utf-8']:
                try:
                    with open(agent_objects, 'r', encoding=enc) as f:
                        dvr_data = json.load(f)
                        cameras = dvr_data.get('cameras', [])
                        cam = next((c for c in cameras if str(c.get('id')) == oid_str or c.get('name') == cfg.get('name')), None)
                        if cam and 'settings' in cam:
                            settings = cam['settings']
                            onvifident = settings.get('onvifident', '')
                            if '://' in onvifident:
                                host_part = onvifident.split('://', 1)[1].split('/')[0]
                                if ':' in host_part:
                                    ip_cand, port_cand = host_part.split(':')
                                    IP = ip_cand
                                    PORT = int(port_cand)
                                else:
                                    IP = host_part
                            if settings.get('login'):
                                USER = settings.get('login')
                            if settings.get('password'):
                                PASS = settings.get('password')
                            found_in_objects = True
                        break
                except Exception:
                    continue
    except Exception:
        pass

    # 2. Coba baca dari storage/app/cctv_devices.json jika belum ditemukan di objects.json
    if not found_in_objects:
        try:
            script_dir = os.path.dirname(os.path.abspath(__file__))
            cfg_path = os.path.join(script_dir, 'storage', 'app', 'cctv_devices.json')
            if os.path.exists(cfg_path):
                with open(cfg_path, 'r', encoding='utf-8') as f:
                    devices = json.load(f)
                    active = next((d for d in devices if str(d.get('oid')) == oid_str or str(d.get('id')) == oid_str), None)
                    if active:
                        if active.get('ip') and active.get('ip') != "10.32.72.78":
                            IP = active.get('ip')
                        if active.get('port'):
                            PORT = int(active.get('port'))
                        if active.get('user'):
                            USER = active.get('user')
                        if active.get('pass'):
                            PASS = active.get('pass')
        except Exception:
            pass

def generate_wsse():
    created = datetime.now(timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ")
    nonce_raw = os.urandom(16)
    nonce_b64 = base64.b64encode(nonce_raw).decode('utf-8')
    
    sha = hashlib.sha1()
    sha.update(nonce_raw)
    sha.update(created.encode('utf-8'))
    sha.update(PASS.encode('utf-8'))
    password_digest = base64.b64encode(sha.digest()).decode('utf-8')
    
    return f"""<s:Header>
        <Security xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <UsernameToken>
                <Username>{USER}</Username>
                <Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">{password_digest}</Password>
                <Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">{nonce_b64}</Nonce>
                <Created xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-security-utility-1.0.xsd">{created}</Created>
            </UsernameToken>
        </Security>
    </s:Header>"""

def send_soap(action, body_inner):
    url = f"http://{IP}:{PORT}/onvif/ptz_service"
    header = generate_wsse()
    soap = f"""<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tptz="http://www.onvif.org/ver20/ptz/wsdl" xmlns:tt="http://www.onvif.org/ver10/schema">
    {header}
    <s:Body>
        {body_inner}
    </s:Body>
</s:Envelope>"""
    
    headers = {
        "Content-Type": "text/xml; charset=utf-8",
        "SOAPAction": f'"{action}"'
    }
    
    try:
        res = requests.post(url, data=soap, headers=headers, timeout=3.0)
        return res.status_code, res.text
    except Exception as e:
        return 500, str(e)

def move_tapo(direction, target_oid="4"):
    load_config(target_oid)
    safe_print(f"[*] Mengirim PTZ '{direction}' ke {IP}:{PORT} (User: {USER}, OID: {target_oid})...")
    x = 0.0
    y = 0.0
    z = 0.0
    
    if direction == "up":
        y = 0.6
    elif direction == "down":
        y = -0.6
    elif direction == "left":
        x = -0.6
    elif direction == "right":
        x = 0.6
    elif direction == "zoomin":
        z = 0.5
    elif direction == "zoomout":
        z = -0.5
    elif direction == "home":
        # Kirim perintah AbsoluteMove ke Home/Pusat
        body_home = f"""<tptz:AbsoluteMove>
            <tptz:ProfileToken>{PROFILE_TOKEN}</tptz:ProfileToken>
            <tptz:Position>
                <tt:PanTilt x="0.0" y="0.0"/>
            </tptz:Position>
        </tptz:AbsoluteMove>"""
        code, text = send_soap("http://www.onvif.org/ver20/ptz/wsdl/AbsoluteMove", body_home)
        safe_print(f"Home status: {code}")
        return code, text
    elif direction == "stop":
        body = f"""<tptz:Stop>
            <tptz:ProfileToken>{PROFILE_TOKEN}</tptz:ProfileToken>
            <tptz:PanTilt>true</tptz:PanTilt>
            <tptz:Zoom>true</tptz:Zoom>
        </tptz:Stop>"""
        return send_soap("http://www.onvif.org/ver20/ptz/wsdl/Stop", body)
        
    # 1. Start Continuous Move
    body = f"""<tptz:ContinuousMove>
        <tptz:ProfileToken>{PROFILE_TOKEN}</tptz:ProfileToken>
        <tptz:Velocity>
            <tt:PanTilt x="{x}" y="{y}"/>
            <tt:Zoom x="{z}"/>
        </tptz:Velocity>
    </tptz:ContinuousMove>"""
    
    code, text = send_soap("http://www.onvif.org/ver20/ptz/wsdl/ContinuousMove", body)
    safe_print(f"Move '{direction}' status: {code}")
    
    if code != 200:
        # Fallback otomatis ke API Agent DVR lokal (Port 8090)
        try:
            ispy_map = {'up': 'ispydir_3', 'down': 'ispydir_7', 'left': 'ispydir_1', 'right': 'ispydir_5', 'home': 'home', 'stop': 'ispydir_11'}
            action_code = ispy_map.get(direction, direction)
            base_q = "http://localhost:8090/q.json"
            fallback_oid = str(target_oid) if str(target_oid).isdigit() else "4"
            requests.get(base_q, params={'cmd': 'ptzcommand', 'field': 'ptz', 'value': action_code, 'command': action_code, 'oid': fallback_oid, 'ot': 2}, timeout=1.5)
            time.sleep(0.35)
            requests.get(base_q, params={'cmd': 'ptzcommand', 'field': 'ptz', 'value': 'ispydir_11', 'command': 'ispydir_11', 'oid': fallback_oid, 'ot': 2}, timeout=1.5)
            safe_print(f"Fallback ke Agent DVR lokal berhasil untuk arah '{direction}' (OID {fallback_oid})")
            return 200, "Agent DVR Fallback OK"
        except Exception as e_fb:
            safe_print(f"Agent DVR fallback gagal: {e_fb}")

    # 2. Rotasi selama 0.4 detik lalu stop
    time.sleep(0.4)
    code_stop, _ = move_tapo("stop", target_oid=target_oid)
    safe_print(f"Stop status: {code_stop}")
    return code, text

if __name__ == "__main__":
    direction = sys.argv[1].lower() if len(sys.argv) > 1 else "right"
    target_oid = "4"
    if len(sys.argv) > 2 and sys.argv[2]:
        arg2 = sys.argv[2]
        if "." in arg2:
            IP = arg2
        else:
            target_oid = arg2
    if len(sys.argv) > 3 and sys.argv[3]:
        USER = sys.argv[3]
    if len(sys.argv) > 4 and sys.argv[4]:
        PASS = sys.argv[4]
        
    move_tapo(direction, target_oid=target_oid)
