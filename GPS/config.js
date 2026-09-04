/*
  שנה את הכתובת הבאה לכתובת ה-Backend שלך.

  ה-Backend צריך להחזיר JSON בצורה:
  {
    "latest": {
      "timestamp": "2026-09-04T08:30:00Z",
      "device_id": "TAG-01",
      "lat": 32.178,
      "lon": 34.907,
      "city": "Kfar Saba",
      "country": "Israel",
      "ssid": "GuestWiFi",
      "rssi": -58,
      "source": "IP"
    },
    "history": [ ... ]
  }

  לדוגמה אם נקים Backend ב-Render:
  DATA_URL: "https://YOUR-SERVICE.onrender.com/api/locations"
*/
window.GPS_CONFIG = {
  DATA_URL: "",
  REFRESH_MS: 10000
};
