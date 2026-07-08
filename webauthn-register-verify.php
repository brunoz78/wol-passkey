<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/store.php';
require_once __DIR__ . '/lib/webauthn/src/WebAuthn.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'msg' => 'Nicht angemeldet.']);
    exit;
}

try {
    $post = json_decode(file_get_contents('php://input'));
    if (!$post) {
        throw new Exception('Ungültige Anfrage.');
    }

    $clientDataJSON = !empty($post->clientDataJSON) ? base64_decode($post->clientDataJSON) : null;
    $attestationObject = !empty($post->attestationObject) ? base64_decode($post->attestationObject) : null;
    $challenge = $_SESSION['webauthn_challenge'] ?? null;
    $deviceName = isset($post->deviceName) && is_string($post->deviceName) ? substr(trim($post->deviceName), 0, 60) : 'Unbenanntes Gerät';

    if (!$challenge) {
        throw new Exception('Keine aktive Registrierungsanfrage. Bitte Seite neu laden.');
    }

    $webauthn = new \lbuchs\WebAuthn\WebAuthn(WEBAUTHN_RP_NAME, WEBAUTHN_RP_ID);
    $result = $webauthn->processCreate($clientDataJSON, $attestationObject, $challenge, true, true, false, false);

    $data = auth_load();
    $data['credentials'][] = [
        'id' => base64_encode($result->credentialId),
        'publicKey' => base64_encode($result->credentialPublicKey),
        'signCount' => $result->signatureCounter ?? 0,
        'name' => $deviceName !== '' ? $deviceName : 'Unbenanntes Gerät',
        'createdAt' => date('Y-m-d H:i'),
    ];
    if (!auth_save($data)) {
        throw new Exception('Speichern fehlgeschlagen: Ordner "auth" ist für den Webserver nicht beschreibbar.');
    }

    unset($_SESSION['webauthn_challenge']);

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}
