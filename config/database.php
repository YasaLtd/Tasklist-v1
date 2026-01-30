<?php
/**
 * Yasa LTD Task List - Database Configuration
 */

if (!defined('YASA_TASKLIST')) {
    die('Direct access not permitted');
}

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'dby7ivib3lmpre');
define('DB_USER', 'uf9au0ihzcx3l');
define('DB_PASS', 't[^446h2l~wF');
define('DB_CHARSET', 'utf8mb4');

define('SITE_URL', 'https://tasklist.yasa.fi');
define('SITE_NAME', 'Yasa LTD Task List');
define('SESSION_LIFETIME', 2592000);
define('TIMEZONE', 'Europe/Helsinki');
define('ALLOW_REGISTRATION', true);

date_default_timezone_set(TIMEZONE);

class Database {
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
            } catch (PDOException $e) {
                error_log("Database Error: " . $e->getMessage());
                die("Database connection failed");
            }
        }
        return self::$instance;
    }
}