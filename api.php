<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// שנה למפתח משלך לפני העלאה לאתר
const API_KEY = 'CHANGE_ME_9f2d7c';

$dataDir = __DIR__ . '/data';
$latestFile = $dataDir . '/latest.json';
$historyFile = $dataDir . '/history.json';

if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

function reply(int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reply(405, ['ok' => false, 'error' => 'POST only']);
}

$input = file_get_contents('php://input');
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (stripos($contentType, 'application/json') !== false) {
    $data = json_decode($input, true);
    if (!is_array($data)) $data = [];
} else {
    $data = $_POST;
}

$key = (string)($data['key'] ?? '');
if (!hash_equals(API_KEY, $key)) {
    reply(401, ['ok' => false, 'error' => 'unauthorized']);
}

$lat = filter_var($data['lat'] ?? null, FILTER_VALIDATE_FLOAT);
$lon = filter_var($data['lon'] ?? null, FILTER_VALIDATE_FLOAT);

if ($lat === false || $lon === false || $lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
    reply(400, ['ok' => false, 'error' => 'invalid coordinates']);
}

$record = [
    'timestamp' => gmdate('c'),
    'device_id' => substr(trim((string)($data['device_id'] ?? 'ESP32-01')), 0, 64),
    'lat'       => (float)$lat,
    'lon'       => (float)$lon,
    'city'      => substr(trim((string)($data['city'] ?? '')), 0, 120),
    'country'   => substr(trim((string)($data['country'] ?? '')), 0, 120),
    'ssid'      => substr(trim((string)($data['ssid'] ?? '')), 0, 120),
    'rssi'      => isset($data['rssi']) ? (int)$data['rssi'] : null,
    'source'    => substr(trim((string)($data['source'] ?? 'IP')), 0, 20),
];

file_put_contents(
    $latestFile,
    json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    LOCK_EX
);

$history = [];
if (is_file($historyFile)) {
    $existing = json_decode((string)file_get_contents($historyFile), true);
    if (is_array($existing)) $history = $existing;
}

$history[] = $record;

// שומרים עד 1000 דיווחים אחרונים
if (count($history) > 1000) {
    $history = array_slice($history, -1000);
}

file_put_contents(
    $historyFile,
    json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    LOCK_EX
);

reply(200, ['ok' => true, 'saved' => $record]);
