<?php
// logout.php
declare(strict_types=1);
error_reporting(E_ALL & ~E_NOTICE);

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
  $p = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool)$p['secure'], (bool)$p['httponly']);
}

session_destroy();

header('Location: login.php');
exit;
