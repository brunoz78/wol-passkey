<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/store.php';
require_once __DIR__ . '/auth/log.php';
require_once __DIR__ . '/lib/webauthn/src/WebAuthn.php';

header('Content-Type: application/json');

try {
    $post = json_decode(file_get_contents('php://input'));
    if (!$post) {
        throw new WolUserMessage(t('wa.bad_request'));
    }

    $id = !empty($post->id) ? base64_decode($post->id) : null;
    $clientDataJSON = !empty($post->clientDataJSON) ? base64_decode($post->clientDataJSON) : null;
    $authenticatorData = !empty($post->authenticatorData) ? base64_decode($post->authenticatorData) : null;
    $signature = !empty($post->signature) ? base64_decode($post->signature) : null;
    $challenge = $_SESSION['webauthn_challenge'] ?? null;

    if (!$challenge) {
        throw new WolUserMessage(t('wa.no_login_request'));
    }
    if (!$id) {
        throw new WolUserMessage(t('wa.no_passkey'));
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
        throw new WolUserMessage(t('wa.unknown_passkey'));
    }

    $cred = $data['credentials'][$credIndex];
    $publicKey = base64_decode($cred['publicKey']);

    $webauthn = new \lbuchs\WebAuthn\WebAuthn(WEBAUTHN_RP_NAME, WEBAUTHN_RP_ID);
    $webauthn->processGet($clientDataJSON, $authenticatorData, $signature, $publicKey, $challenge, (int)$cred['signCount'], true);

    /*
      Signaturzähler und Zeitstempel unter der Sperre fortschreiben. Der
      Eintrag wird darin über seine ID neu gesucht statt über den oben
      ermittelten Index: Wurde zwischenzeitlich ein anderer Passkey gelöscht,
      zeigt der alte Index auf den falschen Eintrag.
    */
    $newCounter = $webauthn->getSignatureCounter();
    $credId = $cred['id'];
    auth_update(function ($data) use ($credId, $newCounter) {
        foreach ($data['credentials'] as $i => $c) {
            if (($c['id'] ?? '') !== $credId) {
                continue;
            }
            if ($newCounter !== null) {
                $data['credentials'][$i]['signCount'] = $newCounter;
            }
            $data['credentials'][$i]['lastUsed'] = date('Y-m-d H:i');
            return $data;
        }
        return null; // Eintrag inzwischen entfernt - nichts zu schreiben
    });

    unset($_SESSION['webauthn_challenge']);
    $_SESSION['authenticated'] = true;
    session_regenerate_id(true);
    wol_log('login', ['method' => 'passkey', 'name' => (string)($cred['name'] ?? ''), 'ip' => wol_client_ip()]);

    echo json_encode(['success' => true, 'redirect' => 'index.php']);
} catch (WolUserMessage $e) {
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
} catch (Throwable $e) {
    // Unerwartete Ausnahme: Wortlaut nur ins Fehlerprotokoll, nicht zum Browser.
    error_log('wol-passkey: ' . $e->getMessage());
    echo json_encode(['success' => false, 'msg' => t('wa.failed')]);
}
