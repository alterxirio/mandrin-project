<?php
// Keep users signed in on the same browser for 30 days.
// This avoids storing usernames or passwords in browser storage.
$sessionLifetime = 60 * 60 * 24 * 30;

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', (string) $sessionLifetime);
    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
?>
