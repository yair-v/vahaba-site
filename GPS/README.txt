VAHABA GPS - GitHub Pages
==========================

מיועד להעלאה לתיקיית:
vahaba-site/GPS/

קבצים:
- index.html
- style.css
- app.js
- config.js

לאחר ההעלאה:
https://vahaba.net/GPS/

חשוב:
GitHub Pages הוא אתר סטטי ולכן הוא לא יכול לקבל ולשמור דיווחי ESP32 בעצמו.

צריך Backend קטן נפרד.
לאחר שנקים אותו, יש לשנות רק את השורה DATA_URL בתוך config.js.

לדוגמה:
window.GPS_CONFIG = {
  DATA_URL: "https://YOUR-SERVICE.onrender.com/api/locations",
  REFRESH_MS: 10000
};

העמוד מצפה לקבל:
{
  "latest": {...},
  "history": [...]
}

המפה מבוססת Leaflet + OpenStreetMap.
