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
IP = "10.32.72.78"
PORT = 2020
USER = "faradays"
PASS = "12345678"
PROFILE_TOKEN = "profile_1"

def safe_print(*args, **kwargs):
    try:
        print(*args, **kwargs)
        sys.stdout.flush()
    except Exception:
        pass

def load_config():
    global IP, PORT, USER, PASS
    try:
        script_dir = os.path.dirname(os.path.abspath(__file__))
        cfg_path = os.path.join(script_dir, 'storage', 'app', 'cctv_devices.json')
        if os.path.exists(cfg_path):
            with open(cfg_path, 'r', encoding='utf-8') as f:
                devices = json.load(f)
                active = next((d for d in devices if d.get('is_active')), devices[0] if devices else None)
                if active:
                    if active.get('ip'):
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

def move_tapo(direction):
    load_config()
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
    
    # 2. Rotasi selama 0.4 detik lalu stop
    time.sleep(0.4)
    code_stop, _ = move_tapo("stop")
    safe_print(f"Stop status: {code_stop}")
    return code, text

if __name__ == "__main__":
    load_config()
    
    direction = sys.argv[1].lower() if len(sys.argv) > 1 else "right"
    if len(sys.argv) > 2 and sys.argv[2]:
        IP = sys.argv[2]
    if len(sys.argv) > 3 and sys.argv[3]:
        USER = sys.argv[3]
    if len(sys.argv) > 4 and sys.argv[4]:
        PASS = sys.argv[4]
        
    move_tapo(direction)
