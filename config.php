<?php
/**
 * config.php
 * Global configuration file
 * Include this file at the TOP of every PHP page
 */

// 1. Start output buffering (MUST BE FIRST)
if (!ob_get_level()) {
    ob_start();
}

// 2. Error reporting
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '1');

// 3. Timezone
date_default_timezone_set('Asia/Bangkok');

// 4. Start session
if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

// 5. Database connection
require_once __DIR__ . '/connect_db.php';

// 6. Helper function
if (!function_exists('e')) {
    function e($s) {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

// 7. Check if user is logged in (except for login pages)
function checkAuth() {
    $current_page = basename($_SERVER['PHP_SELF']);
    $public_pages = ['login.php', 'login_process.php', 'logout.php'];
    
    if (!in_array($current_page, $public_pages)) {
        if (!isset($_SESSION['Username']) || empty($_SESSION['Username'])) {
            header('Location: login.php');
            exit();
        }
    }
}