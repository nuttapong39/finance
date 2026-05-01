<?php
declare(strict_types=1);
error_reporting(E_ALL & ~E_NOTICE);

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
include 'connect_db.php';

$Username    = (string)($_SESSION['Username'] ?? '');
$Password    = (string)($_GET['Password']    ?? '');
$NewPassword = (string)($_GET['NewPassword'] ?? '');
$ConPassword = (string)($_GET['ConPassword'] ?? '');

date_default_timezone_set('Asia/Bangkok');

if ($Username === '') {
    header('location:changepass.php?prv=1');
    exit;
}

// ดึง hash ที่เก็บไว้ด้วย prepared statement
$stmt = $conn->prepare("SELECT Password FROM employee WHERE Username = ? LIMIT 1");
$stmt->bind_param("s", $Username);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
    header('location:changepass.php?prv=1');
    exit;
}

$stored = (string)($row['Password'] ?? '');

// ตรวจสอบรหัสเดิม รองรับทั้ง bcrypt hash และ plain text เดิม
$oldOk = false;
if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2a$') || str_starts_with($stored, '$argon2')) {
    $oldOk = password_verify($Password, $stored);
} else {
    $oldOk = hash_equals($stored, $Password);
}

if (!$oldOk) {
    header('location:changepass.php?prv=1'); // รหัสผ่านเดิมไม่ถูก
    exit;
}

if ($NewPassword !== $ConPassword) {
    header('location:changepass.php?prv=3'); // รหัสผ่านใหม่ไม่ตรงกัน
    exit;
}

if ($NewPassword === $Password) {
    header('location:changepass.php?prv=2'); // รหัสผ่านใหม่ซ้ำเดิม
    exit;
}

// บันทึกรหัสใหม่เป็น hash เสมอ
$newHash = password_hash($NewPassword, PASSWORD_DEFAULT);
$upd = $conn->prepare("UPDATE employee SET Password = ? WHERE Username = ? LIMIT 1");
$upd->bind_param("ss", $newHash, $Username);
$upd->execute();
$upd->close();

session_destroy();
$_SESSION = [];

header('location:changepass.php?prv=4'); // เปลี่ยนรหัสผ่านสำเร็จ
exit;
?>
