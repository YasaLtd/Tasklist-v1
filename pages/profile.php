<?php
/**
 * Yasa LTD Task List - Profile Page
 */

define('YASA_TASKLIST', true);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sessionToken = $_COOKIE['yasa_session'] ?? $_SESSION['yasa_session'] ?? null;
$auth = new Auth();
$currentUser = $auth->validateSession($sessionToken);

if (!$currentUser) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$pageTitle = 'Profile Settings';
$currentPage = 'profile';

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $displayName = trim($_POST['display_name'] ?? '');
        $avatarColor = $_POST['avatar_color'] ?? '#37505d';
        
        if (empty($displayName)) {
            $error = 'Display name is required';
        } else {
            $db = Database::getInstance();
            try {
                $stmt = $db->prepare("UPDATE users SET display_name = :name, avatar_color = :color, updated_at = NOW() WHERE id = :id");
                $stmt->execute([
                    ':name' => $displayName,
                    ':color' => $avatarColor,
                    ':id' => $currentUser['id']
                ]);
                $success = 'Profile updated successfully';
                $currentUser['display_name'] = $displayName;
                $currentUser['avatar_color'] = $avatarColor;
                $_SESSION['user'] = $currentUser;
            } catch (PDOException $e) {
                $error = 'Failed to update profile';
            }
        }
    } elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword)) {
            $error = 'All password fields are required';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match';
        } elseif (strlen($newPassword) < 6) {
            $error = 'New password must be at least 6 characters';
        } else {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute([':id' => $currentUser['id']]);
            $user = $stmt->fetch();
            
            if (!password_verify($currentPassword, $user['password'])) {
                $error = 'Current password is incorrect';
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                try {
                    $stmt = $db->prepare("UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id");
                    $stmt->execute([':password' => $hashedPassword, ':id' => $currentUser['id']]);
                    $success = 'Password changed successfully';
                } catch (PDOException $e) {
                    $error = 'Failed to change password';
                }
            }
        }
    }
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="profile-container">
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title"><i class="fas fa-user-cog"></i> Profile Settings</h1>
            <p class="page-subtitle">Manage your account settings</p>
        </div>
    </div>
    
    <?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span><?php echo htmlspecialchars($success); ?></span>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <span><?php echo htmlspecialchars($error); ?></span>
    </div>
    <?php endif; ?>
    
    <div class="profile-grid">
        <!-- Profile Info Card -->
        <div class="profile-card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-user"></i> Profile Information</h2>
            </div>
            <form method="POST" class="card-body">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="profile-avatar-section">
                    <div class="profile-avatar-large" id="preview-avatar" style="background-color: <?php echo $currentUser['avatar_color'] ?? '#37505d'; ?>">
                        <?php echo strtoupper(substr($currentUser['display_name'], 0, 1)); ?>
                    </div>
                    <div class="avatar-color-picker">
                        <label class="form-label">Avatar Color</label>
                        <div class="color-options">
                            <?php
                            $colors = ['#1C2930', '#37505d', '#4A90D9', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'];
                            foreach ($colors as $color):
                            ?>
                            <label class="color-option">
                                <input type="radio" name="avatar_color" value="<?php echo $color; ?>" 
                                       <?php echo ($currentUser['avatar_color'] ?? '#37505d') === $color ? 'checked' : ''; ?>
                                       onchange="updateAvatarPreview('<?php echo $color; ?>')">
                                <span class="color-swatch" style="background-color: <?php echo $color; ?>"></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" class="form-input" value="<?php echo sanitize($currentUser['username']); ?>" disabled>
                    <small class="form-hint">Username cannot be changed</small>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" class="form-input" value="<?php echo sanitize($currentUser['email']); ?>" disabled>
                    <small class="form-hint">Email cannot be changed</small>
                </div>
                
                <div class="form-group">
                    <label for="display_name" class="form-label">Display Name</label>
                    <input type="text" id="display_name" name="display_name" class="form-input" 
                           value="<?php echo sanitize($currentUser['display_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Account Type</label>
                    <div class="badge badge-<?php echo $currentUser['is_admin'] ? 'admin' : 'user'; ?> badge-lg">
                        <?php echo $currentUser['is_admin'] ? 'Administrator' : 'User'; ?>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Change Password Card -->
        <div class="profile-card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-lock"></i> Change Password</h2>
            </div>
            <form method="POST" class="card-body" id="password-form">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-group">
                    <label for="current_password" class="form-label">Current Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="current_password" name="current_password" class="form-input" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                            <i class="fas fa-eye" id="current_password-icon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="new_password" class="form-label">New Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="new_password" name="new_password" class="form-input" required minlength="6">
                        <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                            <i class="fas fa-eye" id="new_password-icon"></i>
                        </button>
                    </div>
                    <small class="form-hint">Minimum 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye" id="confirm_password-icon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + '-icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function updateAvatarPreview(color) {
    document.getElementById('preview-avatar').style.backgroundColor = color;
}

// Password confirmation validation
document.getElementById('password-form').addEventListener('submit', function(e) {
    const newPass = document.getElementById('new_password').value;
    const confirmPass = document.getElementById('confirm_password').value;
    
    if (newPass !== confirmPass) {
        e.preventDefault();
        showToast('Passwords do not match', 'error');
    }
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>