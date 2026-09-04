<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$file = __DIR__ . '/data/latest.json';

if (!is_file($file)) {
    echo json_encode([
        'ok' => true,
        'location' => null
    ]);
    exit;
}

$data = json_decode((string)file_get_contents($file), true);

echo json_encode([
    'ok' => true,
    'location' => is_array($data) ? $data : null
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
