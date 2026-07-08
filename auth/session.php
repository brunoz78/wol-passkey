<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name(AUTH_SESSION_NAME);
    session_set_cookie_params([
        // Bewusst kurz: nach Ablauf muss der Passkey/das Passwort erneut
        // bestätigt werden, statt dauerhaft eingeloggt zu bleiben.
        'lifetime' => 60 * 30, // 30 Minuten
        'path' => '/',
        'domain' => '',
        // secure nur wenn via HTTPS aufgerufen - erlaubt lokales Testen über http.
        // Hinter dem Reverse Proxy kommt die Verbindung intern als HTTP an,
        // deshalb zählt auch der X-Forwarded-Proto-Header des Proxys.
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                 || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function is_logged_in() {
    return !empty($_SESSION['authenticated']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check($token) {
    return !empty($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}
