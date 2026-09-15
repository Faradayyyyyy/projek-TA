import requests

def get_time():
    url = "http://192.168.1.22:2020/onvif/device_service"
    soap = """<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:tds="http://www.onvif.org/ver10/device/wsdl">
    <soap:Body>
        <tds:GetSystemDateAndTime/>
    </soap:Body>
</soap:Envelope>"""
    headers = {
        "Content-Type": "application/soap+xml; charset=utf-8; action=\"http://www.onvif.org/ver10/device/wsdl/GetSystemDateAndTime\""
    }
    res = requests.post(url, data=soap, headers=headers, timeout=2)
    print("Camera Time Response:\n", res.text)

if __name__ == "__main__":
    get_time()
