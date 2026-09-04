<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

const API_KEY = 'VHGPS-adee668dc2daca92';

function reply(int $code, array $data): never {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reply(405, ['ok' => false, 'error' => 'POST only']);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    $data = $_POST;
}

$key = (string)($data['key'] ?? '');

if (!hash_equals(API_KEY, $key)) {
    reply(401, ['ok' => false, 'error' => 'unauthorized']);
}

$lat = filter_var($data['lat'] ?? null, FILTER_VALIDATE_FLOAT);
$lon = filter_var($data['lon'] ?? null, FILTER_VALIDATE_FLOAT);

if ($lat === false || $lon === false) {
    reply(400, ['ok' => false, 'error' => 'lat/lon required']);
}

if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
    reply(400, ['ok' => false, 'error' => 'invalid coordinates']);
}

$location = [
    'device_id' => substr(trim((string)($data['device_id'] ?? 'ESP32')), 0, 64),
    'lat'       => (float)$lat,
    'lon'       => (float)$lon,
    'city'      => substr(trim((string)($data['city'] ?? '')), 0, 100),
    'country'   => substr(trim((string)($data['country'] ?? '')), 0, 100),
    'ssid'      => substr(trim((string)($data['ssid'] ?? '')), 0, 100),
    'rssi'      => isset($data['rssi']) ? (int)$data['rssi'] : null,
    'source'    => substr(trim((string)($data['source'] ?? 'IP')), 0, 20),
    'timestamp' => gmdate('c')
];

$file = __DIR__ . '/data/latest.json';

$result = file_put_contents(
    $file,
    json_encode($location, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    LOCK_EX
);

if ($result === false) {
    reply(500, ['ok' => false, 'error' => 'cannot write data/latest.json']);
}

reply(200, ['ok' => true, 'location' => $location]);
