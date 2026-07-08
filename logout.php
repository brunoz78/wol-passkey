<?php
require_once __DIR__ . '/auth/session.php';

$_SESSION = [];
session_destroy();

// ?logout=1 unterdrückt die automatische Passkey-Abfrage auf der Loginseite.
header('Location: login.php?logout=1');
exit;
