/*
  דוגמת שליחה בלבד.
  שלב אותה בקוד הקיים שלך אחרי שכבר קיבלת lat/lon.
*/

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

const char* SERVER_URL = "https://vahaba.net/GPS/receive.php";
const char* API_KEY    = "VHGPS-adee668dc2daca92";

bool sendLocation(
  float lat,
  float lon,
  const String& city,
  const String& country,
  const String& ssid,
  int rssi
) {
  if (WiFi.status() != WL_CONNECTED) return false;

  HTTPClient http;
  http.begin(SERVER_URL);
  http.addHeader("Content-Type", "application/json");

  StaticJsonDocument<512> doc;

  doc["key"] = API_KEY;
  doc["device_id"] = "PET-001";
  doc["lat"] = lat;
  doc["lon"] = lon;
  doc["city"] = city;
  doc["country"] = country;
  doc["ssid"] = ssid;
  doc["rssi"] = rssi;
  doc["source"] = "IP";

  String body;
  serializeJson(doc, body);

  int code = http.POST(body);
  String response = http.getString();

  Serial.printf("GPS server HTTP: %d\n", code);
  Serial.println(response);

  http.end();

  return code == 200;
}
