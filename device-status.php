<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/devices.php';
require_once __DIR__ . '/auth/reachability.php';
require_login();

/*
  Liefert den Online-Status eines Geräts als JSON, wird per JS von
  assets/device-status.js für jede Gerätekachel mit hinterlegter IP
  aufgerufen (siehe index.php). Der Check läuft serverseitig, weil nur der
  Server im selben Netz wie die Zielgeräte steht - das Handy des Nutzers oft
  nicht (z.B. unterwegs über Mobilfunk auf den per Reverse Proxy erreichbaren
  Server).
*/

header('Content-Type: application/json; charset=utf-8');

$name = $_GET['name'] ?? '';
$devices = devices_load();

if (!is_string($name) || !isset($devices[$name]) || $devices[$name]['ip'] === '') {
    echo json_encode(['status' => 'unknown']);
    exit;
}

$online = device_is_reachable($devices[$name]['ip']);
echo json_encode(['status' => $online ? 'online' : 'offline']);
