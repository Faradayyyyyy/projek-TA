import requests
import tapo_move

url = 'http://192.168.1.22:2020/onvif/media_service'
soap = f"""<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:trt="http://www.onvif.org/ver10/media/wsdl">
    {tapo_move.generate_wsse()}
    <s:Body>
        <trt:GetProfiles/>
    </s:Body>
</s:Envelope>"""

headers = {
    'Content-Type': 'text/xml; charset=utf-8',
    'SOAPAction': '"http://www.onvif.org/ver10/media/wsdl/GetProfiles"'
}

res = requests.post(url, data=soap, headers=headers)
body_idx = res.text.find('<SOAP-ENV:Body>')
print("Body content:\n", res.text[body_idx:body_idx+1200])
