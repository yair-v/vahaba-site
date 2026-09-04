VAHABA GPS - גרסה פשוטה
=========================

מטרת המערכת:
הלוח שולח מיקום לשרת, והאתר מציג את המיקום האחרון על מפה.

העלה את תוכן התיקייה GPS ל:
https://vahaba.net/GPS/

קבצים:
index.php       - המפה והממשק
receive.php     - מקבל את הנתונים מה-ESP32
latest.php      - מחזיר את המיקום האחרון למפה
data/           - כאן נשמר latest.json
ESP32_SAMPLE.ino - דוגמת שליחה מהלוח

API KEY:
VHGPS-adee668dc2daca92

ה-ESP32 שולח POST ל:
https://vahaba.net/GPS/receive.php

מבנה JSON:
{
  "key": "VHGPS-adee668dc2daca92",
  "device_id": "PET-001",
  "lat": 32.123456,
  "lon": 34.987654,
  "city": "Kfar Saba",
  "country": "Israel",
  "ssid": "GuestWiFi",
  "rssi": -60,
  "source": "IP"
}

חשוב:
ל-PHP צריכה להיות הרשאת כתיבה לתיקיית:
GPS/data/

לאחר הדיווח הראשון, פתח:
https://vahaba.net/GPS/

המפה מתעדכנת אוטומטית כל 5 שניות.
