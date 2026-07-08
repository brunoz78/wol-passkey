<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/store.php';
require_once __DIR__ . '/lib/webauthn/src/WebAuthn.php';

header('Content-Type: application/json');

try {
    $webauthn = new \lbuchs\WebAuthn\WebAuthn(WEBAUTHN_RP_NAME, WEBAUTHN_RP_ID);

    // Keine credentialIds angeben: der Browser sucht selbst nach einem
    // passenden, auf diesem Gerät gespeicherten Passkey (discoverable credential).
    $args = $webauthn->getGetArgs([], WEBAUTHN_TIMEOUT, true, true, true, true, true, true);

    // Nur den rohen Binärstring speichern, siehe Kommentar in webauthn-register-options.php.
    $_SESSION['webauthn_challenge'] = $webauthn->getChallenge()->getBinaryString();

    echo json_encode(['success' => true, 'options' => $args]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}
