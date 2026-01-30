<?php
/**
 * Yasa LTD Task List - Logout
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('YASA_TASKLIST', true);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$sessionToken = $_COOKIE['yasa_session'] ?? $_SESSION['yasa_session'] ?? null;

if ($sessionToken) {
    $auth = new Auth();
    $auth->logout($sessionToken);
}

$_SESSION = [];
session_destroy();

setcookie('yasa_session', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

header('Location: ' . SITE_URL . '/pages/login.php');
exit;