<?php
// File: config/session.php
// Inicia sessão segura
session_start([
    'cookie_lifetime' => 86400,
    'cookie_httponly' => true,
    'cookie_secure' => true, // Ative apenas em HTTPS
    'use_strict_mode' => true,
]);

// Regenera ID da sessão periodicamente
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}
?>