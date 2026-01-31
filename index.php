<?php
/**
 * Yasa LTD Task List - Landing Page
 * Professional Task Management Solution
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

// If logged in, redirect to dashboard
if ($currentUser) {
    header('Location: ' . SITE_URL . '/pages/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Yasa LTD Task List - Professional task management solution for teams and individuals. Organize projects, track progress, and collaborate seamlessly.">
    <meta name="keywords" content="task management, project management, team collaboration, productivity, Yasa LTD">
    <meta name="author" content="Yasa LTD">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo SITE_URL; ?>">
    <meta property="og:title" content="Yasa LTD Task List - Professional Task Management">
    <meta property="og:description" content="Organize projects, track progress, and collaborate seamlessly with your team.">
    <meta property="og:image" content="https://yasa.fi/wp-content/uploads/2024/02/cropped-YASA_solution_black_svg.png">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Yasa LTD Task List">
    <meta name="twitter:description" content="Professional task management solution for teams and individuals.">
    
    <title>Yasa LTD Task List - Professional Task Management Solution</title>
    
<!-- Favicon tags added by Favicon Fixer plugin - https://www.yasa.fi -->
<meta name="application-name" content="Task list - Yasa Ltd.">
<meta name="apple-mobile-web-app-title" content="Task list - Yasa Ltd.">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="https://yasa.fi/wp-content/uploads/favicons/mstile-144x144.png">
<meta name="theme-color" content="#ffffff">
<link rel="icon" type="image/svg+xml" href="https://yasa.fi/wp-content/uploads/favicons/favicon.svg">
<link rel="icon" type="image/png" sizes="16x16" href="https://yasa.fi/wp-content/uploads/favicons/favicon-16x16.png">
<link rel="icon" type="image/png" sizes="32x32" href="https://yasa.fi/wp-content/uploads/favicons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="48x48" href="https://yasa.fi/wp-content/uploads/favicons/favicon-48x48.png">
<link rel="icon" type="image/png" sizes="96x96" href="https://yasa.fi/wp-content/uploads/favicons/favicon-96x96.png">
<link rel="apple-touch-icon" sizes="180x180" href="https://yasa.fi/wp-content/uploads/favicons/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="192x192" href="https://yasa.fi/wp-content/uploads/favicons/android-chrome-192x192.png">
<link rel="icon" type="image/png" sizes="512x512" href="https://yasa.fi/wp-content/uploads/favicons/android-chrome-512x512.png">
<link rel="shortcut icon" href="https://yasa.fi/wp-content/uploads/favicons/favicon.ico">
<link rel="manifest" href="https://yasa.fi/wp-content/uploads/favicons/site.webmanifest">
<!-- End Favicon Fixer tags -->

    
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Jost:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        /* =============================================
           CSS Variables & Reset
           ============================================= */
        :root {
            --color-primary: #1C2930;
            --color-primary-dark: #141d22;
            --color-primary-light: #37505d;
            --color-secondary: #4A90D9;
            --color-accent: #10B981;
            --color-accent-light: #34D399;
            
            --color-success: #10B981;
            --color-warning: #F59E0B;
            --color-danger: #EF4444;
            --color-info: #3B82F6;
            
            --color-text: #1C2930;
            --color-text-light: #64748b;
            --color-text-muted: #94a3b8;
            
            --color-bg: #f8fafc;
            --color-bg-alt: #f1f5f9;
            --color-white: #ffffff;
            --color-border: #e2e8f0;
            
            --gradient-primary: linear-gradient(135deg, #1C2930 0%, #37505d 100%);
            --gradient-accent: linear-gradient(135deg, #10B981 0%, #34D399 100%);
            --gradient-hero: linear-gradient(135deg, #1C2930 0%, #2d4a5a 50%, #37505d 100%);
            
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            
            --radius-sm: 4px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            --radius-2xl: 24px;
            --radius-full: 9999px;
            
            --transition-fast: 150ms ease;
            --transition-normal: 250ms ease;
            --transition-slow: 350ms ease;
            
            --font-sans: 'Jost', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --font-heading: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            font-size: 16px;
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-sans);
            font-size: 1rem;
            line-height: 1.6;
            color: var(--color-text);
            background-color: var(--color-white);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        img {
            max-width: 100%;
            height: auto;
        }

        /* =============================================
           Header / Navigation
           ============================================= */
        .landing-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 1rem 2rem;
            transition: all var(--transition-normal);
        }

        .landing-header.scrolled {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow-md);
            padding: 0.75rem 2rem;
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo-img {
            height: 36px;
            width: auto;
            transition: filter var(--transition-normal);
        }



        .logo-text {
            font-family: var(--font-heading);
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--color-white);
            transition: color var(--transition-normal);
        }

        .landing-header.scrolled .logo-text {
            color: var(--color-primary);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem;
            list-style: none;
        }

        .nav-link {
            font-weight: 500;
            font-size: 0.9375rem;
            color: rgba(255, 255, 255, 0.9);
            transition: all var(--transition-fast);
            position: relative;
        }

        .landing-header.scrolled .nav-link {
            color: var(--color-text);
        }

        .nav-link:hover {
            color: var(--color-accent-light);
        }

        .landing-header.scrolled .nav-link:hover {
            color: var(--color-accent);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--color-accent);
            transition: width var(--transition-normal);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.625rem 1.5rem;
            font-family: var(--font-heading);
            font-weight: 600;
            font-size: 0.9375rem;
            border-radius: var(--radius-md);
            border: 2px solid transparent;
            cursor: pointer;
            transition: all var(--transition-fast);
            white-space: nowrap;
        }

        .btn-ghost {
            background: transparent;
            color: var(--color-white);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .landing-header.scrolled .btn-ghost {
            color: var(--color-text);
            border-color: var(--color-border);
        }

        .landing-header.scrolled .btn-ghost:hover {
            background: var(--color-bg-alt);
            border-color: var(--color-text-muted);
        }

        .btn-accent {
            background: var(--gradient-accent);
            color: var(--color-white);
            border: none;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
        }

        .btn-accent:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
        }

        .btn-primary {
            background: var(--color-primary);
            color: var(--color-white);
        }

        .btn-primary:hover {
            background: var(--color-primary-dark);
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: var(--color-primary);
            border-color: var(--color-primary);
        }

        .btn-outline:hover {
            background: var(--color-primary);
            color: var(--color-white);
        }

        .btn-lg {
            padding: 0.875rem 2rem;
            font-size: 1rem;
        }

        .btn-xl {
            padding: 1rem 2.5rem;
            font-size: 1.125rem;
        }

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            flex-direction: column;
            gap: 5px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
        }

        .mobile-menu-btn span {
            display: block;
            width: 24px;
            height: 2px;
            background: var(--color-white);
            transition: all var(--transition-fast);
        }

        .landing-header.scrolled .mobile-menu-btn span {
            background: var(--color-text);
        }

        /* =============================================
           Hero Section
           ============================================= */
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gradient-hero);
            overflow: hidden;
            padding: 6rem 2rem 4rem;
        }

        .hero-bg-shapes {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            z-index: 0;
        }

        .hero-shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
            animation: float 20s infinite ease-in-out;
        }

        .hero-shape-1 {
            width: 600px;
            height: 600px;
            top: -200px;
            right: -150px;
            animation-delay: 0s;
        }

        .hero-shape-2 {
            width: 400px;
            height: 400px;
            bottom: -100px;
            left: -100px;
            animation-delay: -5s;
        }

        .hero-shape-3 {
            width: 300px;
            height: 300px;
            top: 50%;
            left: 20%;
            animation-delay: -10s;
        }

        .hero-shape-4 {
            width: 200px;
            height: 200px;
            bottom: 20%;
            right: 15%;
            animation-delay: -15s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(30px, -30px) rotate(5deg); }
            50% { transform: translate(-20px, 20px) rotate(-5deg); }
            75% { transform: translate(20px, 30px) rotate(3deg); }
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .hero-text {
            color: var(--color-white);
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: var(--radius-full);
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-accent-light);
            margin-bottom: 1.5rem;
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
        }

        .hero-badge i {
            font-size: 0.75rem;
        }

        .hero-title {
            font-family: var(--font-heading);
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            animation: fadeInUp 0.6s ease 0.1s forwards;
            opacity: 0;
        }

        .hero-title span {
            background: linear-gradient(135deg, var(--color-accent-light), #6EE7B7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-description {
            font-size: 1.25rem;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 2rem;
            animation: fadeInUp 0.6s ease 0.2s forwards;
            opacity: 0;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            animation: fadeInUp 0.6s ease 0.3s forwards;
            opacity: 0;
        }

        .hero-stats {
            display: flex;
            gap: 3rem;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            animation: fadeInUp 0.6s ease 0.4s forwards;
            opacity: 0;
        }

        .hero-stat {
            text-align: left;
        }

        .hero-stat-value {
            font-family: var(--font-heading);
            font-size: 2rem;
            font-weight: 700;
            color: var(--color-white);
        }

        .hero-stat-label {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .hero-visual {
            position: relative;
            animation: fadeInRight 0.8s ease 0.3s forwards;
            opacity: 0;
        }

        .hero-mockup {
            position: relative;
            background: var(--color-white);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-2xl);
            overflow: hidden;
            transform: perspective(1000px) rotateY(-5deg) rotateX(5deg);
            transition: transform var(--transition-slow);
        }

        .hero-mockup:hover {
            transform: perspective(1000px) rotateY(0deg) rotateX(0deg);
        }

        .mockup-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: var(--color-bg-alt);
            border-bottom: 1px solid var(--color-border);
        }

        .mockup-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .mockup-dot-red { background: #EF4444; }
        .mockup-dot-yellow { background: #F59E0B; }
        .mockup-dot-green { background: #10B981; }

        .mockup-body {
            padding: 1.5rem;
        }

        .mockup-card {
            background: var(--color-bg-alt);
            border-radius: var(--radius-lg);
            padding: 1rem;
            margin-bottom: 0.75rem;
            animation: mockupPulse 3s infinite ease-in-out;
        }

        .mockup-card:nth-child(2) { animation-delay: 0.5s; }
        .mockup-card:nth-child(3) { animation-delay: 1s; }

        @keyframes mockupPulse {
            0%, 100% { opacity: 1; transform: translateX(0); }
            50% { opacity: 0.8; transform: translateX(5px); }
        }

        .mockup-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .mockup-card-title {
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--color-text);
        }

        .mockup-badge {
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-full);
            font-size: 0.625rem;
            font-weight: 600;
        }

        .mockup-badge-green {
            background: #D1FAE5;
            color: #059669;
        }

        .mockup-badge-blue {
            background: #DBEAFE;
            color: #2563EB;
        }

        .mockup-badge-yellow {
            background: #FEF3C7;
            color: #D97706;
        }

        .mockup-progress {
            height: 4px;
            background: var(--color-border);
            border-radius: var(--radius-full);
            overflow: hidden;
        }

        .mockup-progress-fill {
            height: 100%;
            background: var(--color-accent);
            border-radius: var(--radius-full);
        }

        .hero-floating-card {
            position: absolute;
            background: var(--color-white);
            border-radius: var(--radius-lg);
            padding: 1rem;
            box-shadow: var(--shadow-xl);
            animation: floatCard 4s infinite ease-in-out;
        }

        .floating-card-1 {
            top: -20px;
            right: -30px;
            animation-delay: 0s;
        }

        .floating-card-2 {
            bottom: 40px;
            left: -40px;
            animation-delay: -2s;
        }

        @keyframes floatCard {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        .floating-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }

        .floating-icon-green {
            background: #D1FAE5;
            color: #059669;
        }

        .floating-icon-blue {
            background: #DBEAFE;
            color: #2563EB;
        }

        .floating-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--color-text);
        }

        .floating-value {
            font-size: 0.6875rem;
            color: var(--color-text-muted);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* =============================================
           Features Section
           ============================================= */
        .features {
            padding: 6rem 2rem;
            background: var(--color-white);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 4rem;
        }

        .section-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--color-bg-alt);
            border-radius: var(--radius-full);
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--color-accent);
            margin-bottom: 1rem;
        }

        .section-title {
            font-family: var(--font-heading);
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--color-text);
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .section-subtitle {
            font-size: 1.125rem;
            color: var(--color-text-light);
            line-height: 1.7;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .feature-card {
            background: var(--color-white);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-xl);
            padding: 2rem;
            transition: all var(--transition-normal);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-accent);
            transform: scaleX(0);
            transition: transform var(--transition-normal);
        }

        .feature-card:hover {
            border-color: transparent;
            box-shadow: var(--shadow-xl);
            transform: translateY(-5px);
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all var(--transition-normal);
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1);
        }

        .feature-icon-1 { background: #DBEAFE; color: #2563EB; }
        .feature-icon-2 { background: #D1FAE5; color: #059669; }
        .feature-icon-3 { background: #FEF3C7; color: #D97706; }
        .feature-icon-4 { background: #EDE9FE; color: #7C3AED; }
        .feature-icon-5 { background: #FCE7F3; color: #DB2777; }
        .feature-icon-6 { background: #CFFAFE; color: #0891B2; }

        .feature-title {
            font-family: var(--font-heading);
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--color-text);
            margin-bottom: 0.75rem;
        }

        .feature-description {
            font-size: 0.9375rem;
            color: var(--color-text-light);
            line-height: 1.7;
        }

        /* =============================================
           How It Works Section
           ============================================= */
        .how-it-works {
            padding: 6rem 2rem;
            background: var(--color-bg);
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            position: relative;
        }

        .steps-grid::before {
            content: '';
            position: absolute;
            top: 40px;
            left: 12.5%;
            right: 12.5%;
            height: 2px;
            background: linear-gradient(90deg, var(--color-accent), var(--color-secondary), var(--color-accent));
            z-index: 0;
        }

        .step-card {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .step-number {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--color-white);
            border: 3px solid var(--color-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-family: var(--font-heading);
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--color-accent);
            transition: all var(--transition-normal);
        }

        .step-card:hover .step-number {
            background: var(--color-accent);
            color: var(--color-white);
            transform: scale(1.1);
        }

        .step-title {
            font-family: var(--font-heading);
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--color-text);
            margin-bottom: 0.5rem;
        }

        .step-description {
            font-size: 0.9375rem;
            color: var(--color-text-light);
            line-height: 1.6;
        }

        /* =============================================
           Benefits Section
           ============================================= */
        .benefits {
            padding: 6rem 2rem;
            background: var(--color-white);
        }

        .benefits-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .benefits-image {
            position: relative;
        }

        .benefits-visual {
            background: var(--gradient-primary);
            border-radius: var(--radius-2xl);
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }

        .benefits-visual::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        .benefits-chart {
            display: flex;
            align-items: flex-end;
            gap: 1rem;
            height: 200px;
            justify-content: center;
        }

        .chart-bar {
            width: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-md) var(--radius-md) 0 0;
            position: relative;
            transition: all var(--transition-normal);
        }

        .chart-bar::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--color-accent);
            border-radius: var(--radius-md) var(--radius-md) 0 0;
            transition: height 1s ease;
        }

        .chart-bar-1::after { height: 60%; }
        .chart-bar-2::after { height: 80%; }
        .chart-bar-3::after { height: 50%; }
        .chart-bar-4::after { height: 90%; }
        .chart-bar-5::after { height: 70%; }

        .benefits-stat-card {
            position: absolute;
            background: var(--color-white);
            border-radius: var(--radius-lg);
            padding: 1rem 1.5rem;
            box-shadow: var(--shadow-xl);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .benefits-stat-card-1 {
            bottom: -20px;
            right: -20px;
        }

        .benefits-stat-card-2 {
            top: 50%;
            left: -30px;
            transform: translateY(-50%);
        }

        .stat-card-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-card-icon-green {
            background: #D1FAE5;
            color: #059669;
        }

        .stat-card-icon-blue {
            background: #DBEAFE;
            color: #2563EB;
        }

        .stat-card-value {
            font-family: var(--font-heading);
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-text);
        }

        .stat-card-label {
            font-size: 0.8125rem;
            color: var(--color-text-muted);
        }

        .benefits-list {
            list-style: none;
        }

        .benefit-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            background: var(--color-bg);
            border-radius: var(--radius-lg);
            transition: all var(--transition-normal);
        }

        .benefit-item:hover {
            background: var(--color-white);
            box-shadow: var(--shadow-lg);
            transform: translateX(10px);
        }

        .benefit-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-md);
            background: var(--gradient-accent);
            color: var(--color-white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .benefit-content h4 {
            font-family: var(--font-heading);
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--color-text);
            margin-bottom: 0.25rem;
        }

        .benefit-content p {
            font-size: 0.9375rem;
            color: var(--color-text-light);
        }

        /* =============================================
           Testimonials Section
           ============================================= */
        .testimonials {
            padding: 6rem 2rem;
            background: var(--gradient-primary);
            position: relative;
            overflow: hidden;
        }

        .testimonials::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        .testimonials .section-header {
            color: var(--color-white);
        }

        .testimonials .section-badge {
            background: rgba(255, 255, 255, 0.1);
            color: var(--color-accent-light);
        }

        .testimonials .section-title {
            color: var(--color-white);
        }

        .testimonials .section-subtitle {
            color: rgba(255, 255, 255, 0.7);
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .testimonial-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-xl);
            padding: 2rem;
            transition: all var(--transition-normal);
        }

        .testimonial-card:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-5px);
        }

        .testimonial-stars {
            color: #FBBF24;
            margin-bottom: 1rem;
        }

        .testimonial-text {
            font-size: 1rem;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 1.5rem;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .testimonial-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--gradient-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--color-white);
        }

        .testimonial-name {
            font-weight: 600;
            color: var(--color-white);
        }

        .testimonial-role {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.6);
        }

        /* =============================================
           CTA Section
           ============================================= */
        .cta {
            padding: 6rem 2rem;
            background: var(--color-bg);
        }

        .cta-card {
            background: var(--color-white);
            border-radius: var(--radius-2xl);
            padding: 4rem;
            text-align: center;
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
        }

        .cta-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: var(--gradient-accent);
        }

        .cta-title {
            font-family: var(--font-heading);
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--color-text);
            margin-bottom: 1rem;
        }

        .cta-description {
            font-size: 1.125rem;
            color: var(--color-text-light);
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .cta-features {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .cta-feature {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--color-text-muted);
        }

        .cta-feature i {
            color: var(--color-accent);
        }

        /* =============================================
           Footer
           ============================================= */
        .landing-footer {
            background: var(--color-primary);
            color: var(--color-white);
            padding: 4rem 2rem 2rem;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-brand {
            max-width: 300px;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .footer-logo img {
            height: 32px;

        }

        .footer-logo span {
            font-family: var(--font-heading);
            font-weight: 700;
            font-size: 1.25rem;
        }

        .footer-description {
            font-size: 0.9375rem;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .footer-social {
            display: flex;
            gap: 0.75rem;
        }

        .footer-social a {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-md);
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-fast);
        }

        .footer-social a:hover {
            background: var(--color-accent);
            transform: translateY(-3px);
        }

        .footer-column h4 {
            font-family: var(--font-heading);
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.75rem;
        }

        .footer-links a {
            font-size: 0.9375rem;
            color: rgba(255, 255, 255, 0.7);
            transition: all var(--transition-fast);
        }

        .footer-links a:hover {
            color: var(--color-accent-light);
            padding-left: 5px;
        }

        .footer-bottom {
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-bottom-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .footer-copyright {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .footer-company-info {
            text-align: right;
        }

        .footer-company-info p {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 0.25rem;
        }

        .footer-company-info strong {
            color: var(--color-white);
        }

        .footer-company-info a {
            color: var(--color-accent-light);
        }

        .footer-company-info a:hover {
            text-decoration: underline;
        }

        /* =============================================
           Animations & Scroll Effects
           ============================================= */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .fade-in-left {
            opacity: 0;
            transform: translateX(-30px);
            transition: all 0.6s ease;
        }

        .fade-in-left.visible {
            opacity: 1;
            transform: translateX(0);
        }

        .fade-in-right {
            opacity: 0;
            transform: translateX(30px);
            transition: all 0.6s ease;
        }

        .fade-in-right.visible {
            opacity: 1;
            transform: translateX(0);
        }

        /* =============================================
           Responsive Design
           ============================================= */
        @media (max-width: 1024px) {
            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-text {
                order: 1;
            }

            .hero-visual {
                order: 2;
                max-width: 500px;
                margin: 0 auto;
            }

            .hero-buttons {
                justify-content: center;
            }

            .hero-stats {
                justify-content: center;
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .steps-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .steps-grid::before {
                display: none;
            }

            .benefits-content {
                grid-template-columns: 1fr;
            }

            .testimonials-grid {
                grid-template-columns: 1fr;
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .mobile-menu-btn {
                display: flex;
            }

            .hero {
                padding: 5rem 1.5rem 3rem;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-description {
                font-size: 1.125rem;
            }

            .hero-stats {
                flex-direction: column;
                gap: 1.5rem;
                align-items: center;
            }

            .hero-stat {
                text-align: center;
            }

            .hero-floating-card {
                display: none;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .steps-grid {
                grid-template-columns: 1fr;
            }

            .section-title {
                font-size: 2rem;
            }

            .cta-card {
                padding: 2rem;
            }

            .cta-title {
                font-size: 1.75rem;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .footer-brand {
                max-width: 100%;
            }

            .footer-social {
                justify-content: center;
            }

            .footer-bottom-content {
                flex-direction: column;
                text-align: center;
            }

            .footer-company-info {
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 2rem;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn-xl {
                width: 100%;
            }

            .cta-buttons {
                flex-direction: column;
            }

            .cta-buttons .btn {
                width: 100%;
            }
        }

        /* =============================================
           Loading Animation
           ============================================= */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--color-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        .page-loader.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .loader-content {
            text-align: center;
        }

        .loader-logo {
            height: 48px;
            margin-bottom: 1.5rem;
            animation: pulse 1.5s infinite ease-in-out;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(0.95); }
        }

        .loader-bar {
            width: 200px;
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-full);
            overflow: hidden;
        }

        .loader-progress {
            height: 100%;
            background: var(--color-accent);
            border-radius: var(--radius-full);
            animation: loading 1.5s infinite ease-in-out;
        }

        @keyframes loading {
            0% { width: 0; margin-left: 0; }
            50% { width: 70%; margin-left: 15%; }
            100% { width: 0; margin-left: 100%; }
        }
    </style>
</head>
<body>
    <!-- Page Loader -->
    <div class="page-loader" id="page-loader">
        <div class="loader-content">
            <img src="https://yasa.fi/wp-content/uploads/2024/02/cropped-YASA_solution_black_svg.png" alt="Yasa LTD" class="loader-logo">
            <div class="loader-bar">
                <div class="loader-progress"></div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="landing-header" id="header">
        <div class="header-container">
            <a href="<?php echo SITE_URL; ?>" class="logo">
                <img src="https://yasa.fi/wp-content/uploads/2024/02/cropped-YASA_solution_black_svg.png" alt="Yasa LTD" class="logo-img">
            </a>

            <nav>
                <ul class="nav-links">
                    <li><a href="#features" class="nav-link">Features</a></li>
                    <li><a href="#how-it-works" class="nav-link">How It Works</a></li>
                    <li><a href="#benefits" class="nav-link">Benefits</a></li>
                </ul>
            </nav>

            <div class="header-actions">
                <a href="<?php echo SITE_URL; ?>/pages/login.php" class="btn btn-ghost">Sign In</a>
                <a href="<?php echo SITE_URL; ?>/pages/register.php" class="btn btn-accent">Get Started Free</a>
            </div>

        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-bg-shapes">
            <div class="hero-shape hero-shape-1"></div>
            <div class="hero-shape hero-shape-2"></div>
            <div class="hero-shape hero-shape-3"></div>
            <div class="hero-shape hero-shape-4"></div>
        </div>
<br>
        <div class="hero-content">
            <div class="hero-text">
                <div class="hero-badge">
                    <i class="fas fa-rocket"></i>
                    <span>Boost Your Productivity Today</span>
                </div>

                <h1 class="hero-title">
                    Yasa LTD Task List
                    <span>Organize,<br>
                    Achieve More</span>
                </h1>

                <p class="hero-description">
                    The professional task management solution that helps teams and individuals 
                    organize projects, track progress, and collaborate seamlessly. Start managing 
                    your tasks smarter, not harder.
                </p>

                <div class="hero-buttons">
                    <a href="<?php echo SITE_URL; ?>/pages/register.php" class="btn btn-accent btn-xl">
                        <i class="fas fa-arrow-right"></i>
                        Start now - it's 100% free!
                    </a>
                    <a href="#features" class="btn btn-ghost btn-xl">
                        <i class="fas fa-play-circle"></i>
                        See How It Works
                    </a>
                </div>

                <div class="hero-stats">
                    <div class="hero-stat">
                        <div class="hero-stat-value">100% Free</div>
                        <div class="hero-stat-label">In development</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-value">No limits</div>
                        <div class="hero-stat-label">Add as many tasks and projects</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-value">99.9%</div>
                        <div class="hero-stat-label">Uptime</div>
                    </div>
                </div>
            </div>

            <div class="hero-visual">
                <div class="hero-mockup">
                    <div class="mockup-header">
                        <span class="mockup-dot mockup-dot-red"></span>
                        <span class="mockup-dot mockup-dot-yellow"></span>
                        <span class="mockup-dot mockup-dot-green"></span>
                    </div>
                    <div class="mockup-body">
                        <div class="mockup-card">
                            <div class="mockup-card-header">
                                <span class="mockup-card-title">Website Redesign</span>
                                <span class="mockup-badge mockup-badge-green">Active</span>
                            </div>
                            <div class="mockup-progress">
                                <div class="mockup-progress-fill" style="width: 75%;"></div>
                            </div>
                        </div>
                        <div class="mockup-card">
                            <div class="mockup-card-header">
                                <span class="mockup-card-title">Mobile App Development</span>
                                <span class="mockup-badge mockup-badge-blue">In Progress</span>
                            </div>
                            <div class="mockup-progress">
                                <div class="mockup-progress-fill" style="width: 45%;"></div>
                            </div>
                        </div>
                        <div class="mockup-card">
                            <div class="mockup-card-header">
                                <span class="mockup-card-title">Marketing Campaign</span>
                                <span class="mockup-badge mockup-badge-yellow">Pending</span>
                            </div>
                            <div class="mockup-progress">
                                <div class="mockup-progress-fill" style="width: 20%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hero-floating-card floating-card-1">
                    <div class="floating-icon floating-icon-green">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="floating-label">Task Completed</div>
                    <div class="floating-value">Just now</div>
                </div>

                <div class="hero-floating-card floating-card-2">
                    <div class="floating-icon floating-icon-blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="floating-label">Team Members</div>
                    <div class="floating-value">5 Online</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-header fade-in">
                <div class="section-badge">
                    <i class="fas fa-star"></i>
                    Features
                </div>
                <h2 class="section-title">Everything You Need to Stay Organized</h2>
                <p class="section-subtitle">
                    Powerful features designed to help you manage projects efficiently, 
                    collaborate with your team, and achieve your goals faster.
                </p>
            </div>

            <div class="features-grid">
                <div class="feature-card fade-in">
                    <div class="feature-icon feature-icon-1">
                        <i class="fas fa-folder-tree"></i>
                    </div>
                    <h3 class="feature-title">Project Management</h3>
                    <p class="feature-description">
                        Organize your work into projects with custom colors, deadlines, and priorities. 
                        Keep everything structured and easy to find.
                    </p>
                </div>

                <div class="feature-card fade-in">
                    <div class="feature-icon feature-icon-2">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3 class="feature-title">Task Tracking</h3>
                    <p class="feature-description">
                        Create, assign, and track tasks with detailed descriptions, deadlines, 
                        and status updates. Never miss a deadline again.
                    </p>
                </div>

                <div class="feature-card fade-in">
                    <div class="feature-icon feature-icon-3">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h3 class="feature-title">Team Collaboration</h3>
                    <p class="feature-description">
                        Share projects with team members, assign tasks, and work together 
                        seamlessly. Real-time updates keep everyone in sync.
                    </p>
                </div>

                <div class="feature-card fade-in">
                    <div class="feature-icon feature-icon-4">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <h3 class="feature-title">Recurring Tasks</h3>
                    <p class="feature-description">
                        Set up recurring tasks for daily, weekly, or monthly activities. 
                        Automate your routine and focus on what matters.
                    </p>
                </div>

                <div class="feature-card fade-in">
                    <div class="feature-icon feature-icon-5">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="feature-title">Progress Analytics</h3>
                    <p class="feature-description">
                        Track your productivity with visual progress bars and completion stats. 
                        See how much you've accomplished at a glance.
                    </p>
                </div>

                <div class="feature-card fade-in">
                    <div class="feature-icon feature-icon-6">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3 class="feature-title">Smart Reminders</h3>
                    <p class="feature-description">
                        Get notified about upcoming deadlines and overdue tasks. 
                        Stay on top of your work without constant checking.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <div class="section-header fade-in">
                <div class="section-badge">
                    <i class="fas fa-cogs"></i>
                    How It Works
                </div>
                <h2 class="section-title">Get Started in Minutes</h2>
                <p class="section-subtitle">
                    Simple setup, powerful results. Start organizing your work in just four easy steps.
                </p>
            </div>

            <div class="steps-grid">
                <div class="step-card fade-in">
                    <div class="step-number">1</div>
                    <h3 class="step-title">Create Account</h3>
                    <p class="step-description">
                        Sign up for free in seconds. No credit card required to get started.
                    </p>
                </div>

                <div class="step-card fade-in">
                    <div class="step-number">2</div>
                    <h3 class="step-title">Create Projects</h3>
                    <p class="step-description">
                        Organize your work into projects with custom settings and team access.
                    </p>
                </div>

                <div class="step-card fade-in">
                    <div class="step-number">3</div>
                    <h3 class="step-title">Add Tasks</h3>
                    <p class="step-description">
                        Break down your projects into manageable tasks with deadlines and priorities.
                    </p>
                </div>

                <div class="step-card fade-in">
                    <div class="step-number">4</div>
                    <h3 class="step-title">Track Progress</h3>
                    <p class="step-description">
                        Monitor your progress, complete tasks, and celebrate your achievements.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits" id="benefits">
        <div class="container">
            <div class="benefits-content">
                <div class="benefits-image fade-in-left">
                    <div class="benefits-visual">
                        <div class="benefits-chart">
                            <div class="chart-bar chart-bar-1" style="height: 100%;"></div>
                            <div class="chart-bar chart-bar-2" style="height: 100%;"></div>
                            <div class="chart-bar chart-bar-3" style="height: 100%;"></div>
                            <div class="chart-bar chart-bar-4" style="height: 100%;"></div>
                            <div class="chart-bar chart-bar-5" style="height: 100%;"></div>
                        </div>
                    </div>

                    <div class="benefits-stat-card benefits-stat-card-1">
                        <div class="stat-card-icon stat-card-icon-green">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <div>
                            <div class="stat-card-value">+40%</div>
                            <div class="stat-card-label">Productivity</div>
                        </div>
                    </div>

                    <div class="benefits-stat-card benefits-stat-card-2">
                        <div class="stat-card-icon stat-card-icon-blue">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <div class="stat-card-value">10h</div>
                            <div class="stat-card-label">Saved Weekly</div>
                        </div>
                    </div>
                </div>

                <div class="fade-in-right">
                    <div class="section-badge">
                        <i class="fas fa-trophy"></i>
                        Benefits
                    </div>
                    <h2 class="section-title">Why Teams Choose Yasa Task List</h2>
                    <p class="section-subtitle" style="margin-bottom: 2rem;">
                        Join thousands of professionals who have transformed their workflow 
                        and achieved more with less stress.
                    </p>

                    <ul class="benefits-list">
                        <li class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <div class="benefit-content">
                                <h4>Boost Productivity</h4>
                                <p>Get more done in less time with organized workflows and clear priorities.</p>
                            </div>
                        </li>

                        <li class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <div class="benefit-content">
                                <h4>Better Collaboration</h4>
                                <p>Keep your team aligned with shared projects and real-time updates.</p>
                            </div>
                        </li>

                        <li class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-brain"></i>
                            </div>
                            <div class="benefit-content">
                                <h4>Reduce Stress</h4>
                                <p>Never forget important tasks with smart reminders and deadlines.</p>
                            </div>
                        </li>

                        <li class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="benefit-content">
                                <h4>Access Anywhere</h4>
                                <p>Manage your tasks from any device with our responsive web interface.</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

  
    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-card fade-in">
                <h2 class="cta-title">Ready to Get Organized?</h2>
                <p class="cta-description">
                    Join thousands of professionals who trust Yasa Task List to manage their work. 
                    Start your free trial today — no credit card required.
                </p>

                <div class="cta-buttons">
                    <a href="<?php echo SITE_URL; ?>/pages/register.php" class="btn btn-accent btn-xl">
                        <i class="fas fa-rocket"></i>
                        Start now!
                    </a>
                    <a href="<?php echo SITE_URL; ?>/pages/login.php" class="btn btn-outline btn-xl">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </a>
                </div>

                <div class="cta-features">
                    <div class="cta-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Free forever plan</span>
                    </div>
                    <div class="cta-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>No credit card required</span>
                    </div>
                    <div class="cta-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Cancel anytime</span>
                    </div>
                    <div class="cta-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>24/7 support</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="footer-logo">
                        <img src="https://yasa.fi/wp-content/uploads/2024/02/cropped-YASA_solution_black_svg.png" alt="Yasa LTD">
                        <span>Task List</span>
                    </div>
                    <p class="footer-description">
                        Professional task management solution for teams and individuals. 
                        Organize, collaborate, and achieve more with Yasa Task List.
                    </p>
                    <div class="footer-social">
                        <a href="https://www.linkedin.com/in/anter-yasa/" target="_blank" rel="noopener" aria-label="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>

                <div class="footer-column">
                    <h4>Product</h4>
                    <ul class="footer-links">
                        <li><a href="#features">Features</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="#benefits">Benefits</a></li>

                    </ul>
                </div>

                <div class="footer-column">
                    <h4>Account</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo SITE_URL; ?>/pages/login.php">Sign In</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/register.php">Create Account</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/dashboard.php">Dashboard</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4>Company</h4>
                    <ul class="footer-links">
                        <li><a href="https://yasa.fi" target="_blank">About Yasa LTD</a></li>
                        <li><a href="https://yasa.fi/contact" target="_blank">Contact Us</a></li>
                        <li><a href="https://yasa.fi/privacy" target="_blank">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <div class="footer-copyright">
                        &copy; 2026 Yasa LTD. All rights reserved.
                    </div>
                    <div class="footer-company-info">
                        <p><strong>YASA LTD</strong></p>
                        <p>Business ID: FI33819864</p>
                        <p><strong>Fredrikinkatu 61, 00100 Helsinki, Finland</strong></p>
                        <p><a href="mailto:info@yasa.fi"><strong>info@yasa.fi</strong></a></p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Page Loader
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('page-loader').classList.add('hidden');
            }, 800);
        });

        // Header Scroll Effect
        const header = document.getElementById('header');
        let lastScroll = 0;

        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;

            if (currentScroll > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }

            lastScroll = currentScroll;
        });

        // Smooth Scroll for Anchor Links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const headerHeight = header.offsetHeight;
                    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerHeight;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Scroll Animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in, .fade-in-left, .fade-in-right').forEach(el => {
            observer.observe(el);
        });

        // Staggered animation for feature cards
        const featureCards = document.querySelectorAll('.feature-card');
        featureCards.forEach((card, index) => {
            card.style.transitionDelay = `${index * 0.1}s`;
        });

        // Staggered animation for step cards
        const stepCards = document.querySelectorAll('.step-card');
        stepCards.forEach((card, index) => {
            card.style.transitionDelay = `${index * 0.15}s`;
        });

        // Staggered animation for testimonial cards
        const testimonialCards = document.querySelectorAll('.testimonial-card');
        testimonialCards.forEach((card, index) => {
            card.style.transitionDelay = `${index * 0.1}s`;
        });

        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        let mobileMenuOpen = false;

        mobileMenuBtn.addEventListener('click', function() {
            mobileMenuOpen = !mobileMenuOpen;
            this.classList.toggle('active');
            
            // You can add mobile menu logic here
            // For now, it's just a visual toggle
        });

        // Animate chart bars on scroll
        const benefitsSection = document.getElementById('benefits');
        const chartBars = document.querySelectorAll('.chart-bar');

        const chartObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    chartBars.forEach((bar, index) => {
                        setTimeout(() => {
                            bar.style.opacity = '1';
                        }, index * 100);
                    });
                }
            });
        }, { threshold: 0.5 });

        if (benefitsSection) {
            chartObserver.observe(benefitsSection);
        }

        // Counter Animation for Hero Stats
        function animateCounter(element, target, duration = 2000) {
            let start = 0;
            const increment = target / (duration / 16);
            
            function updateCounter() {
                start += increment;
                if (start < target) {
                    element.textContent = Math.floor(start).toLocaleString() + (element.dataset.suffix || '');
                    requestAnimationFrame(updateCounter);
                } else {
                    element.textContent = target.toLocaleString() + (element.dataset.suffix || '');
                }
            }
            
            updateCounter();
        }

        // Initialize counters when hero is visible
        const heroStats = document.querySelectorAll('.hero-stat-value');
        let countersAnimated = false;

        const heroObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting && !countersAnimated) {
                    countersAnimated = true;
                    // Note: For simplicity, keeping the static text
                    // You can implement dynamic counters if needed
                }
            });
        }, { threshold: 0.5 });

        const heroSection = document.querySelector('.hero');
        if (heroSection) {
            heroObserver.observe(heroSection);
        }

        // Parallax effect for hero shapes
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const shapes = document.querySelectorAll('.hero-shape');
            
            shapes.forEach((shape, index) => {
                const speed = 0.1 + (index * 0.05);
                shape.style.transform = `translateY(${scrolled * speed}px)`;
            });
        });

        // Add hover sound effect (optional - visual feedback only)
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Console easter egg
        console.log('%c 🚀 Yasa LTD Task List', 'font-size: 24px; font-weight: bold; color: #10B981;');
        console.log('%c Professional task management solution', 'font-size: 14px; color: #64748b;');
        console.log('%c Visit: https://yasa.fi', 'font-size: 12px; color: #4A90D9;');
    </script>
</body>
</html>
