<?php
/**
 * Yasa LTD Task List - Users API
 */

define('YASA_TASKLIST', true);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Authenticate
$sessionToken = $_COOKIE['yasa_session'] ?? $_SESSION['yasa_session'] ?? null;
$auth = new Auth();
$currentUser = $auth->validateSession($sessionToken);

if (!$currentUser) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;

switch ($method) {
    case 'GET':
        if ($action === 'search') {
            // Search for user by email/username
            $query = $_GET['q'] ?? '';
            
            if (strlen($query) < 2) {
                jsonResponse(['success' => false, 'message' => 'Query too short'], 400);
            }
            
            $user = $auth->findUser($query);
            
            if ($user) {
                jsonResponse([
                    'success' => true, 
                    'data' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'display_name' => $user['display_name'],
                        'avatar_color' => $user['avatar_color']
                    ]
                ]);
            } else {
                jsonResponse(['success' => false, 'message' => 'User not found'], 404);
            }
        } elseif ($action === 'me') {
            // Get current user info
            jsonResponse([
                'success' => true,
                'data' => $currentUser
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }
        break;
        
    case 'PUT':
        if ($action === 'profile') {
            // Update current user's profile
            $data = json_decode(file_get_contents('php://input'), true);
            
            $db = Database::getInstance();
            
            $fields = [];
            $params = [':id' => $currentUser['id']];
            
            if (!empty($data['display_name'])) {
                $fields[] = 'display_name = :display_name';
                $params[':display_name'] = trim($data['display_name']);
            }
            
            if (!empty($data['avatar_color'])) {
                $fields[] = 'avatar_color = :avatar_color';
                $params[':avatar_color'] = $data['avatar_color'];
            }
            
            if (empty($fields)) {
                jsonResponse(['success' => false, 'message' => 'Nothing to update'], 400);
            }
            
            try {
                $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                
                jsonResponse(['success' => true, 'message' => 'Profile updated']);
            } catch (PDOException $e) {
                jsonResponse(['success' => false, 'message' => 'Update failed'], 500);
            }
        } elseif ($action === 'password') {
            // Change password
            $data = json_decode(file_get_contents('php://input'), true);
            
            $currentPassword = $data['current_password'] ?? '';
            $newPassword = $data['new_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword)) {
                jsonResponse(['success' => false, 'message' => 'Both passwords required'], 400);
            }
            
            if (strlen($newPassword) < 6) {
                jsonResponse(['success' => false, 'message' => 'New password must be at least 6 characters'], 400);
            }
            
            $db = Database::getInstance();
            
            // Verify current password
            $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute([':id' => $currentUser['id']]);
            $user = $stmt->fetch();
            
            if (!password_verify($currentPassword, $user['password'])) {
                jsonResponse(['success' => false, 'message' => 'Current password is incorrect'], 400);
            }
            
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            
            try {
                $stmt = $db->prepare("UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id");
                $stmt->execute([':password' => $hashedPassword, ':id' => $currentUser['id']]);
                
                jsonResponse(['success' => true, 'message' => 'Password changed']);
            } catch (PDOException $e) {
                jsonResponse(['success' => false, 'message' => 'Failed to change password'], 500);
            }
        } else {
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}