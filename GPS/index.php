<!doctype html>
<html lang="he" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>VAHABA GPS</title>

  <link rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

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
      top:14px;
      right:14px;
      z-index:1000;
      width:min(340px, calc(100vw - 28px));
      background:rgba(15,23,42,.95);
      color:white;
      border-radius:16px;
      padding:15px;
      box-shadow:0 10px 28px rgba(0,0,0,.35);
    }

    h1{
      margin:0 0 10px;
      font-size:20px;
    }

    .status{
      display:flex;
      align-items:center;
      gap:8px;
      margin-bottom:10px;
      color:#cbd5e1;
      font-size:14px;
    }

    .dot{
      width:10px;
      height:10px;
      border-radius:50%;
      background:#f59e0b;
      flex:0 0 auto;
    }

    .row{
      padding:8px 0;
      border-top:1px solid rgba(255,255,255,.09);
    }

    .label{
      display:block;
      color:#94a3b8;
      font-size:12px;
      margin-bottom:3px;
    }

    .value{
      font-weight:700;
      font-size:14px;
      overflow-wrap:anywhere;
    }
  </style>
</head>
<body>

  <div class="panel">
    <h1>VAHABA GPS</h1>

    <div class="status">
      <span id="dot" class="dot"></span>
      <span id="status">ממתין לדיווח מהלוח...</span>
    </div>

    <div class="row">
      <span class="label">התקן</span>
      <span id="device" class="value">—</span>
    </div>

    <div class="row">
      <span class="label">מיקום משוער</span>
      <span id="place" class="value">—</span>
    </div>

    <div class="row">
      <span class="label">קואורדינטות</span>
      <span id="coords" class="value">—</span>
    </div>

    <div class="row">
      <span class="label">רשת</span>
      <span id="network" class="value">—</span>
    </div>

    <div class="row">
      <span class="label">עדכון אחרון</span>
      <span id="updated" class="value">—</span>
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
    let firstFix = true;

    function setStatus(text, ok){
      document.getElementById('status').textContent = text;
      document.getElementById('dot').style.background = ok ? '#22c55e' : '#f59e0b';
    }

    function formatTime(value){
      if(!value) return '—';
      const d = new Date(value);
      return Number.isNaN(d.getTime()) ? String(value) : d.toLocaleString('he-IL');
    }

    async function refreshLocation(){
      try{
        const response = await fetch('latest.php?t=' + Date.now(), {cache:'no-store'});
        if(!response.ok) throw new Error('HTTP ' + response.status);

        const data = await response.json();

        if(!data.location){
          setStatus('ממתין לדיווח מהלוח...', false);
          return;
        }

        const p = data.location;
        const lat = Number(p.lat);
        const lon = Number(p.lon);

        document.getElementById('device').textContent = p.device_id || 'PET-001';
        document.getElementById('place').textContent =
          [p.city, p.country].filter(Boolean).join(', ') || '—';

        document.getElementById('coords').textContent =
          Number.isFinite(lat) && Number.isFinite(lon)
            ? lat.toFixed(6) + ', ' + lon.toFixed(6)
            : '—';

        document.getElementById('network').textContent =
          p.ssid
            ? p.ssid + (p.rssi !== null && p.rssi !== undefined ? ' (' + p.rssi + ' dBm)' : '')
            : '—';

        document.getElementById('updated').textContent = formatTime(p.timestamp);

        if(Number.isFinite(lat) && Number.isFinite(lon)){
          if(!marker){
            marker = L.marker([lat,lon]).addTo(map);
          }else{
            marker.setLatLng([lat,lon]);
          }

          marker.bindPopup(
            '<b>' + (p.device_id || 'PET-001') + '</b><br>' +
            (p.city || '') + ' ' + (p.country || '') + '<br>' +
            lat.toFixed(6) + ', ' + lon.toFixed(6)
          );

          if(firstFix){
            map.setView([lat,lon], 14);
            firstFix = false;
          }
        }

        setStatus('דיווח התקבל', true);

      }catch(err){
        console.error(err);
        setStatus('שגיאה בקריאת הנתונים', false);
      }
    }

    refreshLocation();
    setInterval(refreshLocation, 5000);
  </script>
</body>
</html>
