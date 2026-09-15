import sys
import time
import base64
import hashlib
import os
from datetime import datetime, timezone
import requests

IP = "192.168.1.22"
PORT = 2020
USER = "kevinp"
PASS = "AlphaCentaury"

def generate_wsse_header(username, password):
    created = datetime.now(timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ")
    nonce_raw = os.urandom(16)
    nonce_b64 = base64.b64encode(nonce_raw).decode('utf-8')
    
    # Password Digest = Base64(SHA1(Nonce + Created + Password))
    sha = hashlib.sha1()
    sha.update(nonce_raw)
    sha.update(created.encode('utf-8'))
    sha.update(password.encode('utf-8'))
    password_digest = base64.b64encode(sha.digest()).decode('utf-8')
    
    return f"""<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsse:UsernameToken>
            <wsse:Username>{username}</wsse:Username>
            <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">{password_digest}</wsse:Password>
            <wsse:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">{nonce_b64}</wsse:Nonce>
            <wsu:Created>{created}</wsu:Created>
        </wsse:UsernameToken>
    </wsse:Security>"""

def send_onvif_soap(service_url, action, body_xml):
    header_xml = generate_wsse_header(USER, PASS)
    soap_envelope = f"""<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:tptz="http://www.onvif.org/ver20/ptz/wsdl" xmlns:tt="http://www.onvif.org/ver10/schema">
    <soap:Header>
        {header_xml}
    </soap:Header>
    <soap:Body>
        {body_xml}
    </soap:Body>
</soap:Envelope>"""
    
    headers = {
        "Content-Type": "application/soap+xml; charset=utf-8; action=\"" + action + "\"",
        "Accept": "application/soap+xml, application/xml"
    }
    
    try:
        res = requests.post(service_url, data=soap_envelope, headers=headers, timeout=2.0)
        return res.status_code, res.text
    except Exception as e:
        return 500, str(e)

def move_ptz(direction):
    ptz_url = f"http://{IP}:{PORT}/onvif/ptz_service"
    
    # Values for Pan (x) and Tilt (y)
    x = 0.0
    y = 0.0
    
    if direction == "up":
        y = 0.6
    elif direction == "down":
        y = -0.6
    elif direction == "left":
        x = -0.6
    elif direction == "right":
        x = 0.6
    elif direction == "stop":
        x = 0.0
        y = 0.0
        
    if direction == "stop":
        body = """<tptz:Stop>
            <tptz:ProfileToken>Profile_1</tptz:ProfileToken>
            <tptz:PanTilt>true</tptz:PanTilt>
            <tptz:Zoom>true</tptz:Zoom>
        </tptz:Stop>"""
        action = "http://www.onvif.org/ver20/ptz/wsdl/Stop"
        code, text = send_onvif_soap(ptz_url, action, body)
        print(f"Stop response: {code}")
        return
        
    body = f"""<tptz:ContinuousMove>
        <tptz:ProfileToken>Profile_1</tptz:ProfileToken>
        <tptz:Velocity>
            <tt:PanTilt x="{x}" y="{y}"/>
        </tptz:Velocity>
    </tptz:ContinuousMove>"""
    
    action = "http://www.onvif.org/ver20/ptz/wsdl/ContinuousMove"
    code, text = send_onvif_soap(ptz_url, action, body)
    print(f"Move response for {direction}: {code}")
    
    # Auto stop after 0.5 seconds
    time.sleep(0.5)
    move_ptz("stop")

if __name__ == "__main__":
    direction = sys.argv[1] if len(sys.argv) > 1 else "right"
    move_ptz(direction)
