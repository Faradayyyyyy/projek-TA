import sys
import time
import os
import base64
import hashlib
from datetime import datetime, timezone
import requests

# =========================================================================
# KONFIGURASI KAMERA 4 TAPO C200 (ONVIF)
# =========================================================================
IP = "10.32.72.46"
PORT = 2020
USER = "faradays"
PASS = "12345678"
PROFILE_TOKEN = "profile_1"

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
        print(f"Home status: {code}")
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
    print(f"Move '{direction}' status: {code}")
    
    # 2. Rotasi selama 0.4 detik lalu stop
    time.sleep(0.4)
    code_stop, _ = move_tapo("stop")
    print(f"Stop status: {code_stop}")
    return code, text

if __name__ == "__main__":
    direction = sys.argv[1].lower() if len(sys.argv) > 1 else "right"
    move_tapo(direction)
