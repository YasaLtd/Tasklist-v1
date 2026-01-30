<?php
/**
 * Yasa LTD Task List - Registration Page
 */

// Session FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('YASA_TASKLIST', true);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (!defined('ALLOW_REGISTRATION') || !ALLOW_REGISTRATION) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$sessionToken = $_COOKIE['yasa_session'] ?? $_SESSION['yasa_session'] ?? null;
$auth = new Auth();

if ($auth->validateSession($sessionToken)) {
    header('Location: ' . SITE_URL . '/pages/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $displayName = trim($_POST['display_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $result = $auth->register($username, $email, $password, $displayName ?: $username);
        
        if ($result['success']) {
            header('Location: ' . SITE_URL . '/pages/login.php?registered=1');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = 'Register';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/svg+xml" href="https://yasa.fi/wp-content/uploads/favicons/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Jost:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-background">
            <div class="auth-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
            </div>
        </div>
        
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <a href="https://yasa.fi" target="_blank" class="auth-logo">
                        <img src="https://yasa.fi/wp-content/uploads/2024/02/cropped-YASA_solution_black_svg.png" alt="Yasa LTD">
                    </a>
                    <h1 class="auth-title">Create Account</h1>
                    <p class="auth-subtitle">Sign up to get started</p>
                </div>
                
                <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label" for="username">
                            <i class="fas fa-user"></i>
                            Username *
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-input" 
                            placeholder="Choose a username"
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                            required
                            minlength="3"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="email">
                            <i class="fas fa-envelope"></i>
                            Email *
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input" 
                            placeholder="Enter your email"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="display_name">
                            <i class="fas fa-id-card"></i>
                            Display Name
                        </label>
                        <input 
                            type="text" 
                            id="display_name" 
                            name="display_name" 
                            class="form-input" 
                            placeholder="Your name (shown to others)"
                            value="<?php echo htmlspecialchars($_POST['display_name'] ?? ''); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password">
                            <i class="fas fa-lock"></i>
                            Password *
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input" 
                            placeholder="Create a password (min 6 chars)"
                            required
                            minlength="6"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">
                            <i class="fas fa-lock"></i>
                            Confirm Password *
                        </label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="form-input" 
                            placeholder="Confirm your password"
                            required
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-user-plus"></i>
                        Create Account
                    </button>
                </form>
                
                <div class="auth-footer">
                    <p>Already have an account? <a href="<?php echo SITE_URL; ?>/pages/login.php">Sign in</a></p>
                </div>
            </div>
            
            <div class="auth-info">
                <p>&copy; <?php echo date('Y'); ?> Yasa LTD</p>
                <p><a href="https://yasa.fi" target="_blank"><i class="fas fa-external-link-alt"></i> yasa.fi</a></p>
            </div>
        </div>
    </div>
</body>
</html>