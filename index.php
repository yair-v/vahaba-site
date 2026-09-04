<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
?>
<!doctype html>
<html lang="he" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>VAHABA GPS</title>

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIINfQ3yn5HaorvqxxBVVZ6yYgup1nIhS6Q="
        crossorigin="">

  <style>
    :root{
      --bg:#0d1117;
      --panel:#161b22;
      --panel2:#1f2630;
      --text:#f3f4f6;
      --muted:#9ca3af;
      --line:#2d3748;
      --ok:#22c55e;
      --warn:#f59e0b;
      --bad:#ef4444;
      --accent:#60a5fa;
    }
    *{box-sizing:border-box}
    body{
      margin:0;
      font-family:Arial,Helvetica,sans-serif;
      background:var(--bg);
      color:var(--text);
    }
    .wrap{
      max-width:1200px;
      margin:auto;
      padding:16px;
    }
    .top{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:14px;
      flex-wrap:wrap;
      margin-bottom:14px;
    }
    .title h1{margin:0;font-size:24px}
    .title p{margin:5px 0 0;color:var(--muted);font-size:14px}
    .badge{
      display:inline-flex;
      align-items:center;
      gap:8px;
      background:var(--panel);
      border:1px solid var(--line);
      padding:9px 12px;
      border-radius:999px;
      font-size:13px;
    }
    .dot{
      width:10px;height:10px;border-radius:50%;
      background:var(--warn);
    }
    .grid{
      display:grid;
      grid-template-columns: 1.6fr .9fr;
      gap:14px;
    }
    .card{
      background:var(--panel);
      border:1px solid var(--line);
      border-radius:16px;
      overflow:hidden;
    }
    #map{
      height:620px;
      min-height:420px;
      direction:ltr;
    }
    .side{padding:14px}
    .section-title{
      font-size:14px;
      color:var(--muted);
      margin:2px 0 10px;
    }
    .metric{
      background:var(--panel2);
      border:1px solid var(--line);
      border-radius:12px;
      padding:12px;
      margin-bottom:10px;
    }
    .metric .k{color:var(--muted);font-size:12px;margin-bottom:5px}
    .metric .v{font-size:18px;font-weight:700;word-break:break-word}
    .coords{
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:10px;
    }
    .small{font-size:13px;color:var(--muted)}
    .history{
      margin-top:14px;
      background:var(--panel);
      border:1px solid var(--line);
      border-radius:16px;
      overflow:hidden;
    }
    .history-head{
      padding:12px 14px;
      border-bottom:1px solid var(--line);
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:10px;
    }
    .table-wrap{overflow:auto;max-height:310px}
    table{width:100%;border-collapse:collapse;font-size:13px}
    th,td{padding:10px 12px;border-bottom:1px solid var(--line);white-space:nowrap;text-align:right}
    th{color:var(--muted);font-weight:600;background:#131920;position:sticky;top:0}
    button{
      background:var(--accent);
      color:#07111f;
      border:0;
      border-radius:10px;
      padding:9px 12px;
      cursor:pointer;
      font-weight:700;
    }
    .empty{padding:20px;color:var(--muted);text-align:center}
    @media (max-width:800px){
      .grid{grid-template-columns:1fr}
      #map{height:470px}
    }
  </style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <div class="title">
      <h1>VAHABA GPS</h1>
      <p>מיקום אחרון של התג — בשלב זה לפי כתובת IP ולכן המיקום משוער.</p>
    </div>
    <div class="badge">
      <span class="dot" id="statusDot"></span>
      <span id="statusText">ממתין לנתונים</span>
    </div>
  </div>

  <div class="grid">
    <div class="card">
      <div id="map"></div>
    </div>

    <div class="card side">
      <div class="section-title">נתוני מיקום אחרונים</div>

      <div class="metric">
        <div class="k">מזהה התקן</div>
        <div class="v" id="device">—</div>
      </div>

      <div class="metric">
        <div class="k">מיקום משוער</div>
        <div class="v" id="place">—</div>
      </div>

      <div class="coords">
        <div class="metric">
          <div class="k">Latitude</div>
          <div class="v" id="lat">—</div>
        </div>
        <div class="metric">
          <div class="k">Longitude</div>
          <div class="v" id="lon">—</div>
        </div>
      </div>

      <div class="metric">
        <div class="k">רשת Wi‑Fi</div>
        <div class="v" id="ssid">—</div>
        <div class="small" id="rssi"></div>
      </div>

      <div class="metric">
        <div class="k">מקור מיקום</div>
        <div class="v" id="source">—</div>
      </div>

      <div class="metric">
        <div class="k">עדכון אחרון</div>
        <div class="v" id="updated">—</div>
      </div>
    </div>
  </div>

  <div class="history">
    <div class="history-head">
      <strong>היסטוריית דיווחים</strong>
      <button type="button" id="refreshBtn">רענן</button>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>זמן</th>
            <th>התקן</th>
            <th>עיר</th>
            <th>Latitude</th>
            <th>Longitude</th>
            <th>SSID</th>
            <th>RSSI</th>
          </tr>
        </thead>
        <tbody id="historyBody"></tbody>
      </table>
      <div class="empty" id="emptyHistory">עדיין לא התקבלו דיווחים</div>
    </div>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
<script>
  const map = L.map('map').setView([31.8, 34.9], 8);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  let marker = null;
  let firstFix = true;

  const $ = id => document.getElementById(id);

  function safe(v, fallback='—'){
    return (v === undefined || v === null || v === '') ? fallback : String(v);
  }

  function setStatus(ok, text){
    $('statusText').textContent = text;
    $('statusDot').style.background = ok ? 'var(--ok)' : 'var(--warn)';
  }

  function formatTime(value){
    if(!value) return '—';
    const d = new Date(value);
    return Number.isNaN(d.getTime()) ? value : d.toLocaleString('he-IL');
  }

  function updateLatest(d){
    $('device').textContent = safe(d.device_id);
    $('place').textContent = [d.city, d.country].filter(Boolean).join(', ') || '—';
    $('lat').textContent = d.lat != null ? Number(d.lat).toFixed(6) : '—';
    $('lon').textContent = d.lon != null ? Number(d.lon).toFixed(6) : '—';
    $('ssid').textContent = safe(d.ssid);
    $('rssi').textContent = d.rssi != null ? `עוצמת קליטה: ${d.rssi} dBm` : '';
    $('source').textContent = safe(d.source, 'IP');
    $('updated').textContent = formatTime(d.timestamp);

    const lat = Number(d.lat);
    const lon = Number(d.lon);

    if(Number.isFinite(lat) && Number.isFinite(lon)){
      if(marker) marker.setLatLng([lat, lon]);
      else marker = L.marker([lat, lon]).addTo(map);

      marker.bindPopup(
        `<b>${safe(d.device_id,'ESP32')}</b><br>` +
        `${safe(d.city,'')} ${safe(d.country,'')}<br>` +
        `${lat.toFixed(6)}, ${lon.toFixed(6)}`
      );

      map.setView([lat, lon], firstFix ? 14 : map.getZoom());
      firstFix = false;
    }
  }

  function updateHistory(rows){
    const body = $('historyBody');
    body.innerHTML = '';

    $('emptyHistory').style.display = rows.length ? 'none' : 'block';

    rows.slice().reverse().slice(0,100).forEach(d => {
      const tr = document.createElement('tr');
      [
        formatTime(d.timestamp),
        safe(d.device_id),
        safe(d.city),
        d.lat != null ? Number(d.lat).toFixed(6) : '—',
        d.lon != null ? Number(d.lon).toFixed(6) : '—',
        safe(d.ssid),
        d.rssi != null ? `${d.rssi}` : '—'
      ].forEach(v => {
        const td = document.createElement('td');
        td.textContent = v;
        tr.appendChild(td);
      });
      body.appendChild(tr);
    });
  }

  async function loadData(){
    try{
      const r = await fetch('data.php?t=' + Date.now(), {cache:'no-store'});
      if(!r.ok) throw new Error('HTTP ' + r.status);
      const data = await r.json();

      if(data.latest){
        updateLatest(data.latest);
        setStatus(true, 'נתונים התקבלו');
      }else{
        setStatus(false, 'ממתין לדיווח ראשון');
      }

      updateHistory(Array.isArray(data.history) ? data.history : []);
    }catch(e){
      console.error(e);
      setStatus(false, 'שגיאה בקריאת הנתונים');
    }
  }

  $('refreshBtn').addEventListener('click', loadData);
  loadData();
  setInterval(loadData, 10000);
</script>
</body>
</html>
