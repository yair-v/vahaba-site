<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/config.php';

function respond(int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['ok' => false, 'error' => 'POST only']);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '', true);

if (!is_array($data)) {
    $data = $_POST;
}

$key = (string)($data['key'] ?? '');
if (!hash_equals(GPS_API_KEY, $key)) {
    respond(401, ['ok' => false, 'error' => 'unauthorized']);
}

$lat = filter_var($data['lat'] ?? null, FILTER_VALIDATE_FLOAT);
$lon = filter_var($data['lon'] ?? null, FILTER_VALIDATE_FLOAT);

if ($lat === false || $lon === false) {
    respond(400, ['ok' => false, 'error' => 'lat/lon required']);
}

if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
    respond(400, ['ok' => false, 'error' => 'invalid coordinates']);
}

$record = [
    'device_id' => substr(trim((string)($data['device_id'] ?? 'PET-001')), 0, 64),
    'lat'       => (float)$lat,
    'lon'       => (float)$lon,
    'city'      => substr(trim((string)($data['city'] ?? '')), 0, 120),
    'country'   => substr(trim((string)($data['country'] ?? '')), 0, 120),
    'ssid'      => substr(trim((string)($data['ssid'] ?? '')), 0, 120),
    'rssi'      => isset($data['rssi']) ? (int)$data['rssi'] : null,
    'source'    => substr(trim((string)($data['source'] ?? 'IP')), 0, 20),
    'timestamp' => gmdate('c')
];

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir) && !mkdir($dataDir, 0755, true)) {
    respond(500, ['ok' => false, 'error' => 'cannot create data directory']);
}

$file = $dataDir . '/latest.json';

$json = json_encode(
    $record,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);

if ($json === false || file_put_contents($file, $json, LOCK_EX) === false) {
    respond(500, ['ok' => false, 'error' => 'cannot write data/latest.json']);
}

respond(200, ['ok' => true, 'location' => $record]);
