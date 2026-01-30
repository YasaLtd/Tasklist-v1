<?php
/**
 * Yasa LTD Task List - Header
 */

if (!defined('YASA_TASKLIST')) {
    die('Direct access not permitted');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Task List'); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/svg+xml" href="https://yasa.fi/wp-content/uploads/favicons/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Jost:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="header-container">
            <div class="header-left">
                <button class="mobile-menu-btn" id="mobile-menu-btn" onclick="toggleMobileMenu()">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </button>
                
                <a href="<?php echo SITE_URL; ?>/pages/dashboard.php" class="logo">
                    <img src="https://yasa.fi/wp-content/uploads/2024/02/cropped-YASA_solution_black_svg.png" alt="Yasa LTD" class="logo-img">
                    <span class="logo-text">Task List</span>
                </a>
            </div>
            
            <?php if (isset($currentUser) && $currentUser): ?>
            <nav class="header-nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/pages/dashboard.php" class="nav-link <?php echo ($currentPage ?? '') === 'dashboard' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/pages/projects.php" class="nav-link <?php echo ($currentPage ?? '') === 'projects' ? 'active' : ''; ?>">
                            <i class="fas fa-folder"></i>
                            <span>Projects</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/pages/tasks.php" class="nav-link <?php echo ($currentPage ?? '') === 'tasks' ? 'active' : ''; ?>">
                            <i class="fas fa-tasks"></i>
                            <span>Tasks</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="header-right">
                <div class="user-menu" id="user-menu">
                    <button class="user-menu-trigger" onclick="toggleUserMenu()">
                        <span class="user-avatar" style="background-color: <?php echo $currentUser['avatar_color'] ?? '#37505d'; ?>">
                            <?php echo strtoupper(substr($currentUser['display_name'] ?? 'U', 0, 1)); ?>
                        </span>
                        <span class="user-name"><?php echo sanitize($currentUser['display_name']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-dropdown">
                        <div class="dropdown-header">
                            <span class="dropdown-name"><?php echo sanitize($currentUser['display_name']); ?></span>
                            <span class="dropdown-email"><?php echo sanitize($currentUser['email']); ?></span>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo SITE_URL; ?>/pages/logout.php" class="dropdown-item dropdown-item-danger">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </header>
    
    <main class="main-content">

<script>
function toggleUserMenu() {
    document.getElementById('user-menu').classList.toggle('open');
}

function toggleMobileMenu() {
    document.getElementById('mobile-menu-btn').classList.toggle('active');
    document.body.classList.toggle('mobile-menu-open');
}

document.addEventListener('click', function(e) {
    const userMenu = document.getElementById('user-menu');
    if (userMenu && !userMenu.contains(e.target)) {
        userMenu.classList.remove('open');
    }
});
</script>