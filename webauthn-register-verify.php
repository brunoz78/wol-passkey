<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/store.php';
require_once __DIR__ . '/auth/log.php';
require_once __DIR__ . '/lib/webauthn/src/WebAuthn.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'msg' => t('wa.not_logged_in')]);
    exit;
}

try {
    $post = json_decode(file_get_contents('php://input'));
    if (!$post) {
        throw new WolUserMessage(t('wa.bad_request'));
    }

    $clientDataJSON = !empty($post->clientDataJSON) ? base64_decode($post->clientDataJSON) : null;
    $attestationObject = !empty($post->attestationObject) ? base64_decode($post->attestationObject) : null;
    $challenge = $_SESSION['webauthn_challenge'] ?? null;
    $deviceName = isset($post->deviceName) && is_string($post->deviceName) ? substr(trim($post->deviceName), 0, 60) : t('passkey.default_device');

    if (!$challenge) {
        throw new WolUserMessage(t('wa.no_reg_request'));
    }

    $webauthn = new \lbuchs\WebAuthn\WebAuthn(WEBAUTHN_RP_NAME, WEBAUTHN_RP_ID);
    $result = $webauthn->processCreate($clientDataJSON, $attestationObject, $challenge, true, true, false, false);

    $entry = [
        'id' => base64_encode($result->credentialId),
        'publicKey' => base64_encode($result->credentialPublicKey),
        'signCount' => $result->signatureCounter ?? 0,
        'name' => $deviceName !== '' ? $deviceName : t('passkey.default_device'),
        'createdAt' => date('Y-m-d H:i'),
    ];

    // Anhängen unter der Sperre (siehe auth_update in auth/store.php), damit
    // eine parallele Änderung den neuen Passkey nicht überschreibt.
    $saved = auth_update(function ($data) use ($entry) {
        $data['credentials'][] = $entry;
        return $data;
    });
    if (!$saved) {
        throw new WolUserMessage(t('wa.save_failed'));
    }

    unset($_SESSION['webauthn_challenge']);
    wol_log('passkey_added', ['name' => $entry['name'], 'ip' => wol_client_ip()]);

    echo json_encode(['success' => true]);
} catch (WolUserMessage $e) {
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
} catch (Throwable $e) {
    // Unerwartete Ausnahme: Wortlaut nur ins Fehlerprotokoll, nicht zum Browser.
    error_log('wol-passkey: ' . $e->getMessage());
    echo json_encode(['success' => false, 'msg' => t('wa.failed')]);
}
