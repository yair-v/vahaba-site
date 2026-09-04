const cfg = window.GPS_CONFIG || {};
const DATA_URL = (cfg.DATA_URL || "").trim();
const REFRESH_MS = Number(cfg.REFRESH_MS || 10000);

const $ = id => document.getElementById(id);

const map = L.map("map").setView([31.9, 34.85], 8);
L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
  maxZoom: 19,
  attribution: "&copy; OpenStreetMap contributors"
}).addTo(map);

let marker = null;
let firstFix = true;

function safe(v, fallback = "—"){
  return (v === null || v === undefined || v === "") ? fallback : String(v);
}

function formatTime(v){
  if(!v) return "—";
  const d = new Date(v);
  return Number.isNaN(d.getTime()) ? String(v) : d.toLocaleString("he-IL");
}

function setStatus(type, text){
  $("statusText").textContent = text;
  $("statusDot").className = "dot " + type;
}

function showLatest(d){
  $("device").textContent = safe(d.device_id);
  $("place").textContent = [d.city, d.country].filter(Boolean).join(", ") || "—";
  $("lat").textContent = d.lat != null ? Number(d.lat).toFixed(6) : "—";
  $("lon").textContent = d.lon != null ? Number(d.lon).toFixed(6) : "—";
  $("ssid").textContent = safe(d.ssid);
  $("rssi").textContent = d.rssi != null ? `עוצמת קליטה: ${d.rssi} dBm` : "";
  $("source").textContent = safe(d.source, "IP");
  $("updated").textContent = formatTime(d.timestamp);

  const lat = Number(d.lat);
  const lon = Number(d.lon);

  if(Number.isFinite(lat) && Number.isFinite(lon)){
    if(marker){
      marker.setLatLng([lat, lon]);
    }else{
      marker = L.marker([lat, lon]).addTo(map);
    }

    marker.bindPopup(
      `<b>${safe(d.device_id, "ESP32")}</b><br>` +
      `${safe(d.city, "")} ${safe(d.country, "")}<br>` +
      `${lat.toFixed(6)}, ${lon.toFixed(6)}`
    );

    if(firstFix){
      map.setView([lat, lon], 14);
      firstFix = false;
    }
  }
}

function showHistory(rows){
  const body = $("historyBody");
  body.innerHTML = "";

  const arr = Array.isArray(rows) ? rows : [];
  $("countText").textContent = `${arr.length} דיווחים`;
  $("emptyState").style.display = arr.length ? "none" : "block";

  arr.slice().reverse().slice(0,100).forEach(d => {
    const tr = document.createElement("tr");
    const values = [
      formatTime(d.timestamp),
      safe(d.device_id),
      safe(d.city),
      d.lat != null ? Number(d.lat).toFixed(6) : "—",
      d.lon != null ? Number(d.lon).toFixed(6) : "—",
      safe(d.ssid),
      d.rssi != null ? String(d.rssi) : "—"
    ];

    values.forEach(v => {
      const td = document.createElement("td");
      td.textContent = v;
      tr.appendChild(td);
    });

    body.appendChild(tr);
  });
}

async function loadData(){
  if(!DATA_URL){
    setStatus("warn", "נדרש להגדיר Backend");
    return;
  }

  try{
    const joiner = DATA_URL.includes("?") ? "&" : "?";
    const response = await fetch(`${DATA_URL}${joiner}t=${Date.now()}`, {
      cache: "no-store"
    });

    if(!response.ok) throw new Error(`HTTP ${response.status}`);

    const data = await response.json();

    if(data.latest){
      showLatest(data.latest);
      setStatus("ok", "מחובר — התקבל מיקום");
    }else{
      setStatus("warn", "מחובר — ממתין לדיווח");
    }

    showHistory(data.history || []);
  }catch(err){
    console.error(err);
    setStatus("bad", "שגיאה בחיבור לשרת");
  }
}

$("refreshBtn").addEventListener("click", loadData);
loadData();

if(REFRESH_MS >= 3000){
  setInterval(loadData, REFRESH_MS);
}
