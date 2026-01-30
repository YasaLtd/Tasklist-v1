<?php
/**
 * Yasa LTD Task List - Authentication System
 * 
 * @package YasaTaskList
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('YASA_TASKLIST')) {
    die('Direct access not permitted');
}

/**
 * Authentication Class
 */
class Auth {
    private $wpdb;
    private $taskdb;
    private $wpPrefix;
    
    public function __construct() {
        $this->wpdb = Database::getWPDB();
        $this->taskdb = Database::getTaskDB();
        $this->wpPrefix = DB_WP_PREFIX; // Tämä on nyt 'pyx_'
    }
    
    /**
     * Authenticate user against WordPress database
     */
    public function authenticate($username, $password) {
        try {
            // Get user from WordPress database
            $sql = "SELECT ID, user_login, user_pass, user_email, display_name, user_status 
                    FROM {$this->wpPrefix}users 
                    WHERE user_login = :username OR user_email = :email 
                    LIMIT 1";
            
            $stmt = $this->wpdb->prepare($sql);
            $stmt->execute([
                ':username' => $username,
                ':email' => $username
            ]);
            
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid username or password'];
            }
            
            // Check if user is active
            if ($user['user_status'] == 1) {
                return ['success' => false, 'message' => 'Account is disabled'];
            }
            
            // Verify password using WordPress password hashing
            if (!$this->verifyPassword($password, $user['user_pass'])) {
                return ['success' => false, 'message' => 'Invalid username or password'];
            }
            
            // Get user role
            $role = $this->getUserRole($user['ID']);
            
            // Create session
            $sessionToken = $this->createSession($user['ID']);
            
            return [
                'success' => true,
                'user' => [
                    'id' => $user['ID'],
                    'username' => $user['user_login'],
                    'email' => $user['user_email'],
                    'display_name' => $user['display_name'],
                    'role' => $role,
                    'is_admin' => $this->isAdmin($role)
                ],
                'session_token' => $sessionToken
            ];
            
        } catch (PDOException $e) {
            error_log("Authentication Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Authentication system error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Verify WordPress password hash
     */
    private function verifyPassword($password, $hash) {
        // WordPress uses phpass library for password hashing
        require_once __DIR__ . '/class-phpass.php';
        $hasher = new PasswordHash(8, true);
        return $hasher->CheckPassword($password, $hash);
    }
    
    /**
     * Get user role from WordPress
     */
    private function getUserRole($userId) {
        try {
            $metaKey = $this->wpPrefix . 'capabilities';
            
            $sql = "SELECT meta_value FROM {$this->wpPrefix}usermeta 
                    WHERE user_id = :user_id AND meta_key = :meta_key";
            
            $stmt = $this->wpdb->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':meta_key' => $metaKey
            ]);
            
            $result = $stmt->fetch();
            
            if ($result && $result['meta_value']) {
                $capabilities = @unserialize($result['meta_value']);
                if (is_array($capabilities)) {
                    return array_keys($capabilities)[0] ?? 'subscriber';
                }
            }
            
            return 'subscriber';
        } catch (PDOException $e) {
            error_log("Get User Role Error: " . $e->getMessage());
            return 'subscriber';
        }
    }
    
    /**
     * Check if user is admin
     */
    private function isAdmin($role) {
        $adminRoles = ['administrator', 'editor'];
        return in_array($role, $adminRoles);
    }
    
    /**
     * Create user session
     */
    private function createSession($userId) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
        
        try {
            $stmt = $this->taskdb->prepare(
                "INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) 
                 VALUES (:user_id, :token, :ip, :ua, :expires)"
            );
            $stmt->execute([
                ':user_id' => $userId,
                ':token' => $token,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                ':expires' => $expiresAt
            ]);
        } catch (PDOException $e) {
            error_log("Create Session Error: " . $e->getMessage());
        }
        
        return $token;
    }
    
    /**
     * Validate session token
     */
    public function validateSession($token) {
        if (empty($token)) {
            return false;
        }
        
        try {
            // Check session in task database
            $stmt = $this->taskdb->prepare(
                "SELECT user_id FROM user_sessions 
                 WHERE session_token = :token AND expires_at > NOW()"
            );
            $stmt->execute([':token' => $token]);
            $session = $stmt->fetch();
            
            if (!$session) {
                return false;
            }
            
            // Get user data from WordPress
            $sql = "SELECT ID, user_login, user_email, display_name 
                    FROM {$this->wpPrefix}users WHERE ID = :id";
            $stmt = $this->wpdb->prepare($sql);
            $stmt->execute([':id' => $session['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return false;
            }
            
            $role = $this->getUserRole($user['ID']);
            
            return [
                'id' => $user['ID'],
                'username' => $user['user_login'],
                'email' => $user['user_email'],
                'display_name' => $user['display_name'],
                'role' => $role,
                'is_admin' => $this->isAdmin($role)
            ];
            
        } catch (PDOException $e) {
            error_log("Session Validation Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Destroy session
     */
    public function logout($token) {
        try {
            $stmt = $this->taskdb->prepare(
                "DELETE FROM user_sessions WHERE session_token = :token"
            );
            $stmt->execute([':token' => $token]);
            return true;
        } catch (PDOException $e) {
            error_log("Logout Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clean expired sessions
     */
    public function cleanExpiredSessions() {
        try {
            $stmt = $this->taskdb->prepare(
                "DELETE FROM user_sessions WHERE expires_at < NOW()"
            );
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Session Cleanup Error: " . $e->getMessage());
        }
    }
    
    /**
     * Get all WordPress users (for admin)
     */
    public function getAllUsers() {
        try {
            $sql = "SELECT ID, user_login, user_email, display_name 
                    FROM {$this->wpPrefix}users ORDER BY display_name ASC";
            $stmt = $this->wpdb->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll();
            
            foreach ($users as &$user) {
                $user['role'] = $this->getUserRole($user['ID']);
            }
            
            return $users;
        } catch (PDOException $e) {
            error_log("Get Users Error: " . $e->getMessage());
            return [];
        }
    }
}