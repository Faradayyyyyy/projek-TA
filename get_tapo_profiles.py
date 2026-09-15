import test_tapo

def get_profile_tokens():
    url = "http://192.168.1.22:2020/onvif/media_service"
    header = test_tapo.generate_wsse() if hasattr(test_tapo, 'generate_wsse') else None
    
    import tapo_move
    header = tapo_move.generate_wsse()
    soap = f"""<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:trt="http://www.onvif.org/ver10/media/wsdl">
    {header}
    <s:Body>
        <trt:GetProfiles/>
    </s:Body>
</s:Envelope>"""
    
    headers = {
        "Content-Type": "text/xml; charset=utf-8",
        "SOAPAction": '"http://www.onvif.org/ver10/media/wsdl/GetProfiles"'
    }
    
    import requests
    res = requests.post(url, data=soap, headers=headers, timeout=2)
    print("GetProfiles Status:", res.status_code)
    print(res.text)

if __name__ == "__main__":
    get_profile_tokens()
