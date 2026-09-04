<?php
?><!doctype html>
<html lang="he" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>VAHABA GPS</title>

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

  <style>
    html,body{
      margin:0;
      width:100%;
      height:100%;
      font-family:Arial,Helvetica,sans-serif;
      background:#0f172a;
    }

    #map{
      width:100%;
      height:100%;
    }

    .panel{
      position:fixed;
      z-index:1000;
      top:15px;
      right:15px;
      width:min(330px, calc(100vw - 30px));
      background:rgba(15,23,42,.94);
      color:#fff;
      border-radius:16px;
      padding:16px;
      box-shadow:0 10px 30px rgba(0,0,0,.35);
    }

    .title{
      font-size:20px;
      font-weight:700;
      margin-bottom:12px;
    }

    .status{
      display:flex;
      align-items:center;
      gap:8px;
      margin-bottom:12px;
      color:#cbd5e1;
      font-size:14px;
    }

    .dot{
      width:10px;
      height:10px;
      border-radius:50%;
      background:#f59e0b;
    }

    .row{
      padding:8px 0;
      border-bottom:1px solid rgba(255,255,255,.1);
      font-size:14px;
    }

    .row:last-child{
      border-bottom:0;
    }

    .label{
      color:#94a3b8;
      font-size:12px;
      display:block;
      margin-bottom:3px;
    }

    .value{
      font-weight:700;
      word-break:break-word;
    }

    @media(max-width:600px){
      .panel{
        top:10px;
        right:10px;
        width:calc(100vw - 20px);
        padding:12px;
      }
    }
  </style>
</head>
<body>

<div class="panel">
  <div class="title">VAHABA GPS</div>

  <div class="status">
    <span class="dot" id="dot"></span>
    <span id="status">ממתין לדיווח מהלוח...</span>
  </div>

  <div class="row">
    <span class="label">מזהה לוח</span>
    <span class="value" id="device">—</span>
  </div>

  <div class="row">
    <span class="label">מיקום משוער</span>
    <span class="value" id="place">—</span>
  </div>

  <div class="row">
    <span class="label">קואורדינטות</span>
    <span class="value" id="coords">—</span>
  </div>

  <div class="row">
    <span class="label">רשת</span>
    <span class="value" id="network">—</span>
  </div>

  <div class="row">
    <span class="label">עדכון אחרון</span>
    <span class="value" id="updated">—</span>
  </div>
</div>

<div id="map"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
const map = L.map('map').setView([31.8, 34.9], 8);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

let marker = null;
let firstLocation = true;

function setStatus(text, ok){
  document.getElementById('status').textContent = text;
  document.getElementById('dot').style.background = ok ? '#22c55e' : '#f59e0b';
}

function formatDate(s){
  if(!s) return '—';
  const d = new Date(s);
  if(Number.isNaN(d.getTime())) return s;
  return d.toLocaleString('he-IL');
}

async function loadLocation(){
  try{
    const r = await fetch('latest.php?t=' + Date.now(), {cache:'no-store'});
    const data = await r.json();

    if(!data.ok || !data.location){
      setStatus('ממתין לדיווח מהלוח...', false);
      return;
    }

    const p = data.location;
    const lat = Number(p.lat);
    const lon = Number(p.lon);

    document.getElementById('device').textContent = p.device_id || 'ESP32';
    document.getElementById('place').textContent =
      [p.city, p.country].filter(Boolean).join(', ') || '—';

    document.getElementById('coords').textContent =
      Number.isFinite(lat) && Number.isFinite(lon)
      ? lat.toFixed(6) + ', ' + lon.toFixed(6)
      : '—';

    document.getElementById('network').textContent =
      p.ssid ? p.ssid + (p.rssi !== null && p.rssi !== undefined ? '  (' + p.rssi + ' dBm)' : '') : '—';

    document.getElementById('updated').textContent = formatDate(p.timestamp);

    if(Number.isFinite(lat) && Number.isFinite(lon)){
      if(!marker){
        marker = L.marker([lat,lon]).addTo(map);
      }else{
        marker.setLatLng([lat,lon]);
      }

      marker.bindPopup(
        '<b>' + (p.device_id || 'ESP32') + '</b><br>' +
        (p.city || '') + ' ' + (p.country || '') + '<br>' +
        lat.toFixed(6) + ', ' + lon.toFixed(6)
      );

      if(firstLocation){
        map.setView([lat,lon], 14);
        firstLocation = false;
      }
    }

    setStatus('דיווח התקבל', true);

  }catch(e){
    console.error(e);
    setStatus('שגיאה בקריאת הנתונים', false);
  }
}

loadLocation();
setInterval(loadLocation, 5000);
</script>

</body>
</html>
