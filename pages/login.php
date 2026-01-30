<?php
/**
 * Yasa LTD Task List - Login Page
 */

// Session FIRST before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('YASA_TASKLIST', true);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$sessionToken = $_COOKIE['yasa_session'] ?? $_SESSION['yasa_session'] ?? null;
$auth = new Auth();

if ($auth->validateSession($sessionToken)) {
    header('Location: ' . SITE_URL . '/pages/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username/email and password';
    } else {
        $result = $auth->authenticate($username, $password);
        
        if ($result['success']) {
            $_SESSION['yasa_session'] = $result['session_token'];
            $_SESSION['user'] = $result['user'];
            
            setcookie('yasa_session', $result['session_token'], [
                'expires' => time() + SESSION_LIFETIME,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            header('Location: ' . SITE_URL . '/pages/dashboard.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = 'Login';
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
                    <h1 class="auth-title">Task List</h1>
                    <p class="auth-subtitle">Sign in to your account</p>
                </div>
                
                <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>Registration successful! Please sign in.</span>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label" for="username">
                            <i class="fas fa-user"></i>
                            Username or Email
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-input" 
                            placeholder="Enter your username or email"
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password">
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <div class="password-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                placeholder="Enter your password"
                                required
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye" id="password-icon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </button>
                </form>
                
                <?php if (defined('ALLOW_REGISTRATION') && ALLOW_REGISTRATION): ?>
                <div class="auth-footer">
                    <p>Don't have an account? <a href="<?php echo SITE_URL; ?>/pages/register.php">Create one</a></p>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="auth-info">
                <p>&copy; <?php echo date('Y'); ?> Yasa LTD</p>
                <p><a href="https://yasa.fi" target="_blank"><i class="fas fa-external-link-alt"></i> yasa.fi</a></p>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('password-icon');
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
    </script>
</body>
</html>