import requests
import onvif_ptz

def check_device():
    url = "http://192.168.1.22:2020/onvif/device_service"
    action = "http://www.onvif.org/ver10/device/wsdl/GetDeviceInformation"
    body = "<tds:GetDeviceInformation xmlns:tds=\"http://www.onvif.org/ver10/device/wsdl\"/>"
    
    code, text = onvif_ptz.send_onvif_soap(url, action, body)
    print(f"GetDeviceInformation status: {code}")
    print("Full Response:\n", text)

if __name__ == "__main__":
    check_device()
