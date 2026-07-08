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
    $data = auth_load();

    $webauthn = new \lbuchs\WebAuthn\WebAuthn(WEBAUTHN_RP_NAME, WEBAUTHN_RP_ID);

    $excludeIds = [];
    foreach ($data['credentials'] as $cred) {
        $excludeIds[] = base64_decode($cred['id']);
    }

    $args = $webauthn->getCreateArgs(
        hex2bin($data['user_id']),
        'bruno',
        'Bruno WOL Login',
        WEBAUTHN_TIMEOUT,
        true,   // requireResidentKey (Passkey)
        true,   // requireUserVerification (Biometrie/PIN)
        null,   // crossPlatformAttachment: sowohl platform als auch cross-platform erlauben
        $excludeIds
    );

    // Nur den rohen Binärstring in der Session speichern (nicht das ByteBuffer-Objekt):
    // session_start() läuft vor dem require der WebAuthn-Klassen, ein serialisiertes
    // Objekt würde beim Auslesen sonst zu __PHP_Incomplete_Class werden.
    $_SESSION['webauthn_challenge'] = $webauthn->getChallenge()->getBinaryString();

    echo json_encode(['success' => true, 'options' => $args]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}
