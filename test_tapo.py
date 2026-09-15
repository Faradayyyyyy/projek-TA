import os
import base64
import hashlib
from datetime import datetime, timezone
import requests

IP = "192.168.1.22"
PORT = 2020
USER = "viggos"
PASS = "viggo1234"

def test_tapo_auth():
    url = f"http://{IP}:{PORT}/onvif/device_service"
    created = datetime.now(timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ")
    nonce_raw = os.urandom(16)
    nonce_b64 = base64.b64encode(nonce_raw).decode('utf-8')
    
    sha = hashlib.sha1()
    sha.update(nonce_raw)
    sha.update(created.encode('utf-8'))
    sha.update(PASS.encode('utf-8'))
    password_digest = base64.b64encode(sha.digest()).decode('utf-8')
    
    soap = f"""<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
    <s:Header>
        <Security xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <UsernameToken>
                <Username>{USER}</Username>
                <Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">{password_digest}</Password>
                <Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">{nonce_b64}</Nonce>
                <Created xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">{created}</Created>
            </UsernameToken>
        </Security>
    </s:Header>
    <s:Body xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
        <GetDeviceInformation xmlns="http://www.onvif.org/ver10/device/wsdl"/>
    </s:Body>
</s:Envelope>"""
    
    headers = {
        "Content-Type": "text/xml; charset=utf-8",
        "SOAPAction": '"http://www.onvif.org/ver10/device/wsdl/GetDeviceInformation"'
    }
    
    res = requests.post(url, data=soap, headers=headers, timeout=2)
    print(f"Tapo C200 Status: HTTP {res.status_code}")
    print("Response:\n", res.text)

if __name__ == "__main__":
    test_tapo_auth()
