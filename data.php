<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$dataDir = __DIR__ . '/data';
$latestFile = $dataDir . '/latest.json';
$historyFile = $dataDir . '/history.json';

$latest = null;
$history = [];

if (is_file($latestFile)) {
    $tmp = json_decode((string)file_get_contents($latestFile), true);
    if (is_array($tmp)) $latest = $tmp;
}

if (is_file($historyFile)) {
    $tmp = json_decode((string)file_get_contents($historyFile), true);
    if (is_array($tmp)) $history = $tmp;
}

echo json_encode(
    ['latest' => $latest, 'history' => $history],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
