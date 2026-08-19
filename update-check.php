<?php
require_once __DIR__ . '/auth/session.php';
require_login();

/*
  Manueller Update-Check fürs Menü (siehe assets/update-check.js). Fragt
  sofort bei GitHub nach, statt auf den Tages-Cache zu warten, und
  aktualisiert diesen dabei gleich mit - der passive Hinweis oben auf der
  Seite (partials/head.php) zeigt danach ohne weiteres Zutun den aktuellen
  Stand.
*/

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf_token'] ?? '')) {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit;
}

$result = wol_update_check_now();
if ($result === null) {
    echo json_encode(['ok' => false]);
    exit;
}

echo json_encode(['ok' => true] + $result);
