import urllib.request
import re
url = "https://pixabay.com/videos/search/empty%20room/"
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    html = urllib.request.urlopen(req).read().decode('utf-8')
    links = re.findall(r'https://cdn.pixabay.com/video/[^\&\"\' ]+\.mp4', html)
    print(list(set(links))[:5])
except Exception as e:
    print(e)
