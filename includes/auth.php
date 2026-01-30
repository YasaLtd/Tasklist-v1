<?php
/**
 * Yasa LTD Task List - Authentication
 */

if (!defined('YASA_TASKLIST')) {
    die('Direct access not permitted');
}

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function register($username, $email, $password, $displayName = null) {
        $username = trim(strtolower($username));
        $email = trim(strtolower($email));
        $displayName = $displayName ? trim($displayName) : $username;
        
        if (strlen($username) < 3) {
            return ['success' => false, 'message' => 'Username must be at least 3 characters'];
        }
        
        if (!preg_match('/^[a-z0-9_]+$/', $username)) {
            return ['success' => false, 'message' => 'Username can only contain letters, numbers and underscores'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }
        
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters'];
        }
        
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
        
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $colors = ['#1C2930', '#37505d', '#4A90D9', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'];
        $avatarColor = $colors[array_rand($colors)];
        
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO users (username, email, password, display_name, role, status, avatar_color) 
                 VALUES (?, ?, ?, ?, 'user', 'active', ?)"
            );
            $stmt->execute([$username, $email, $hashedPassword, $displayName, $avatarColor]);
            
            return [
                'success' => true,
                'message' => 'Registration successful',
                'user_id' => $this->db->lastInsertId()
            ];
        } catch (PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }
    
    public function authenticate($username, $password) {
        $username = trim($username);
        
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active' LIMIT 1"
        );
        $stmt->execute([strtolower($username), strtolower($username)]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }
        
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }
        
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
        
        $stmt = $this->db->prepare(
            "INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $user['id'],
            $token,
            $_SERVER['REMOTE_ADDR'] ?? '',
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            $expires
        ]);
        
        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'display_name' => $user['display_name'],
                'role' => $user['role'],
                'is_admin' => $user['role'] === 'admin',
                'avatar_color' => $user['avatar_color'] ?? '#37505d'
            ],
            'session_token' => $token
        ];
    }
    
    public function validateSession($token) {
        if (empty($token)) return false;
        
        $stmt = $this->db->prepare(
            "SELECT u.* FROM user_sessions s
             JOIN users u ON s.user_id = u.id
             WHERE s.session_token = ? AND s.expires_at > NOW() AND u.status = 'active'"
        );
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if (!$user) return false;
        
        return [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'display_name' => $user['display_name'],
            'role' => $user['role'],
            'is_admin' => $user['role'] === 'admin',
            'avatar_color' => $user['avatar_color'] ?? '#37505d'
        ];
    }
    
    public function logout($token) {
        $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE session_token = ?");
        $stmt->execute([$token]);
        return true;
    }
    
    public function findUser($identifier) {
        $stmt = $this->db->prepare(
            "SELECT id, username, email, display_name, avatar_color 
             FROM users WHERE (username = ? OR email = ?) AND status = 'active' LIMIT 1"
        );
        $stmt->execute([strtolower(trim($identifier)), strtolower(trim($identifier))]);
        return $stmt->fetch() ?: null;
    }
    
    public function getUserById($userId) {
        $stmt = $this->db->prepare(
            "SELECT id, username, email, display_name, role, avatar_color 
             FROM users WHERE id = ? AND status = 'active'"
        );
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }
}