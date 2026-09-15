import requests
import onvif_ptz

def get_profiles():
    media_url = "http://192.168.1.22:2020/onvif/media_service"
    action = "http://www.onvif.org/ver10/media/wsdl/GetProfiles"
    body = "<trt:GetProfiles xmlns:trt=\"http://www.onvif.org/ver10/media/wsdl\"/>"
    
    code, text = onvif_ptz.send_onvif_soap(media_url, action, body)
    print(f"GetProfiles status: {code}")
    print("Response snippet:", text[:500])

if __name__ == "__main__":
    get_profiles()
