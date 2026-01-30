<?php
/**
 * Yasa LTD Task List - Entry Point
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('YASA_TASKLIST', true);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$sessionToken = $_COOKIE['yasa_session'] ?? $_SESSION['yasa_session'] ?? null;
$auth = new Auth();
$currentUser = $auth->validateSession($sessionToken);

if ($currentUser) {
    header('Location: ' . SITE_URL . '/pages/dashboard.php');
} else {
    header('Location: ' . SITE_URL . '/pages/login.php');
}
exit;