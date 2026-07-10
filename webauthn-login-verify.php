<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/store.php';
require_once __DIR__ . '/lib/webauthn/src/WebAuthn.php';

header('Content-Type: application/json');

try {
    $post = json_decode(file_get_contents('php://input'));
    if (!$post) {
        throw new Exception(t('wa.bad_request'));
    }

    $id = !empty($post->id) ? base64_decode($post->id) : null;
    $clientDataJSON = !empty($post->clientDataJSON) ? base64_decode($post->clientDataJSON) : null;
    $authenticatorData = !empty($post->authenticatorData) ? base64_decode($post->authenticatorData) : null;
    $signature = !empty($post->signature) ? base64_decode($post->signature) : null;
    $challenge = $_SESSION['webauthn_challenge'] ?? null;

    if (!$challenge) {
        throw new Exception(t('wa.no_login_request'));
    }
    if (!$id) {
        throw new Exception(t('wa.no_passkey'));
    }

    $data = auth_load();
    $credIndex = null;
    foreach ($data['credentials'] as $i => $cred) {
        if (hash_equals($cred['id'], base64_encode($id))) {
            $credIndex = $i;
            break;
        }
    }

    if ($credIndex === null) {
        throw new Exception(t('wa.unknown_passkey'));
    }

    $cred = $data['credentials'][$credIndex];
    $publicKey = base64_decode($cred['publicKey']);

    $webauthn = new \lbuchs\WebAuthn\WebAuthn(WEBAUTHN_RP_NAME, WEBAUTHN_RP_ID);
    $webauthn->processGet($clientDataJSON, $authenticatorData, $signature, $publicKey, $challenge, (int)$cred['signCount'], true);

    $newCounter = $webauthn->getSignatureCounter();
    if ($newCounter !== null) {
        $data['credentials'][$credIndex]['signCount'] = $newCounter;
    }
    $data['credentials'][$credIndex]['lastUsed'] = date('Y-m-d H:i');
    auth_save($data);

    unset($_SESSION['webauthn_challenge']);
    $_SESSION['authenticated'] = true;
    session_regenerate_id(true);

    echo json_encode(['success' => true, 'redirect' => 'index.php']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}
