<?php
/**
 * provider_id_callback.php
 * =====================================================================
 * OAuth Callback สำหรับรับ code จาก Health ID แล้วดำเนินการ:
 *   1. แลก code → Health ID access_token
 *   2. แลก Health ID token → Provider ID access_token
 *   3. ดึงข้อมูลโปรไฟล์ Provider
 *   4. จับคู่กับพนักงานในระบบ → สร้าง Session → redirect main.php
 * =====================================================================
 */
declare(strict_types=1);
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Bangkok');

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/connect_db.php';
require_once __DIR__ . '/provider_id_config.php';

/* ─────────────────────────────────────────
   Helper: cURL POST (JSON body)
───────────────────────────────────────── */
function curlPost(string $url, array $payload, array $headers = [], bool $formEncoded = false): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $formEncoded
        ? http_build_query($payload)
        : json_encode($payload, JSON_UNESCAPED_UNICODE));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(
        [$formEncoded ? 'Content-Type: application/x-www-form-urlencoded' : 'Content-Type: application/json'],
        $headers
    ));
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, PROVIDER_ID_CONNECT_TIMEOUT);
    curl_setopt($ch, CURLOPT_TIMEOUT, PROVIDER_ID_TIMEOUT);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $body   = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $errno  = curl_errno($ch);
    curl_close($ch);

    if ($errno !== 0 || $body === false) {
        return ['http_status' => 0, 'body' => null, 'error' => 'cURL error ' . $errno];
    }
    return ['http_status' => $status, 'body' => json_decode((string)$body, true), 'error' => null];
}

/* ─────────────────────────────────────────
   Helper: cURL GET with headers
───────────────────────────────────────── */
function curlGet(string $url, array $headers = [], array $params = []): array {
    if ($params) $url .= '?' . http_build_query($params);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, PROVIDER_ID_CONNECT_TIMEOUT);
    curl_setopt($ch, CURLOPT_TIMEOUT, PROVIDER_ID_TIMEOUT);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $body   = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $errno  = curl_errno($ch);
    curl_close($ch);

    if ($errno !== 0 || $body === false) {
        return ['http_status' => 0, 'body' => null, 'error' => 'cURL error ' . $errno];
    }
    return ['http_status' => $status, 'body' => json_decode((string)$body, true), 'error' => null];
}

/* ─────────────────────────────────────────
   Helper: Redirect พร้อม error
───────────────────────────────────────── */
function redirectError(string $code): never {
    header('Location: login.php?pid_err=' . urlencode($code));
    exit;
}

/* ─────────────────────────────────────────
   ตรวจสอบ state (CSRF protection)
───────────────────────────────────────── */
$receivedState = trim($_GET['state'] ?? '');
$savedState    = $_SESSION['pid_oauth_state'] ?? '';
if ($savedState !== '' && ($receivedState === '' || !hash_equals($savedState, $receivedState))) {
    redirectError('state_mismatch');
}
unset($_SESSION['pid_oauth_state']);

/* ─────────────────────────────────────────
   ขั้นตอน 1: รับ code จาก Health ID
───────────────────────────────────────── */
$code = trim($_GET['code'] ?? '');
if ($code === '') {
    redirectError('no_code');
}

/* ─────────────────────────────────────────
   ขั้นตอน 2: แลก code → Health ID access_token
───────────────────────────────────────── */
$healthTokenRes = curlPost(
    HEALTH_ID_URL . '/api/v1/token',
    [
        'grant_type'    => 'authorization_code',
        'code'          => $code,
        'redirect_uri'  => HEALTH_ID_REDIRECT_URI,
        'client_id'     => HEALTH_ID_CLIENT_ID,
        'client_secret' => HEALTH_ID_CLIENT_SECRET,
    ],
    [],
    true // form-encoded
);

if ($healthTokenRes['http_status'] !== 200 || empty($healthTokenRes['body']['data']['access_token'])) {
    error_log('[Provider ID] Health ID token failed: ' . json_encode($healthTokenRes));
    redirectError('health_token_failed');
}

$healthAccessToken = (string) $healthTokenRes['body']['data']['access_token'];

/* ─────────────────────────────────────────
   ขั้นตอน 3: แลก Health ID token → Provider ID access_token
───────────────────────────────────────── */
$providerTokenRes = curlPost(
    PROVIDER_ID_URL . '/api/v1/services/token',
    [
        'client_id'  => PROVIDER_ID_CLIENT_ID,
        'secret_key' => PROVIDER_ID_SECRET_KEY,
        'token_by'   => 'Health ID',
        'token'      => $healthAccessToken,
    ]
);

if ($providerTokenRes['http_status'] === 400) {
    // บุคคลนี้ไม่มี Provider ID
    redirectError('not_provider');
}
if ($providerTokenRes['http_status'] !== 200 || empty($providerTokenRes['body']['data']['access_token'])) {
    error_log('[Provider ID] Provider token failed: ' . json_encode($providerTokenRes));
    redirectError('provider_token_failed');
}

$providerAccessToken = (string) $providerTokenRes['body']['data']['access_token'];
$providerUsername    = (string) ($providerTokenRes['body']['data']['username'] ?? '');

/* ─────────────────────────────────────────
   ขั้นตอน 4: ดึงข้อมูลโปรไฟล์ Provider
───────────────────────────────────────── */
$profileRes = curlGet(
    PROVIDER_ID_URL . '/api/v1/services/profile',
    [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $providerAccessToken,
        'client-id: '   . PROVIDER_ID_CLIENT_ID,
        'secret-key: '  . PROVIDER_ID_SECRET_KEY,
    ]
);

if ($profileRes['http_status'] !== 200 || empty($profileRes['body']['data'])) {
    error_log('[Provider ID] Profile failed: ' . json_encode($profileRes));
    redirectError('profile_failed');
}

$profile       = $profileRes['body']['data'];
$providerId    = (string) ($profile['provider_id']  ?? '');
$nameTh        = (string) ($profile['name_th']      ?? '');
$specialTitle  = (string) ($profile['special_title_th'] ?? '');
$org           = $profile['organization'][0] ?? [];
$hcode         = (string) ($org['hcode']    ?? '');
$position      = (string) ($org['position'] ?? '');
$hnameTh       = (string) ($org['hname_th'] ?? '');

/* ─────────────────────────────────────────
   ขั้นตอน 5: จับคู่กับพนักงานในระบบ
   ลำดับการค้นหา:
     1) ค้นหาจาก username ที่ได้จาก Provider ID token
     2) ค้นหาจาก provider_id field (ถ้ามีคอลัมน์นี้ในตาราง)
     3) ค้นหาจากชื่อ-สกุล (name_th)
───────────────────────────────────────── */
$empRow = null;

// 1) จาก username ของ Provider ID
if ($providerUsername !== '') {
    $s = $conn->prepare("SELECT Username, Names, Position, TypeUser FROM employee WHERE Username = ? AND Status = '1' LIMIT 1");
    $s->bind_param("s", $providerUsername);
    $s->execute();
    $r = $s->get_result()->fetch_assoc();
    $s->close();
    if ($r) $empRow = $r;
}

// 2) จากชื่อ-สกุล (name_th ตัดคำนำหน้า)
if (!$empRow && $nameTh !== '') {
    $searchName = trim(str_replace($specialTitle, '', $nameTh));
    $like       = '%' . $searchName . '%';
    $s = $conn->prepare("SELECT Username, Names, Position, TypeUser FROM employee WHERE Names LIKE ? AND Status = '1' LIMIT 1");
    $s->bind_param("s", $like);
    $s->execute();
    $r = $s->get_result()->fetch_assoc();
    $s->close();
    if ($r) $empRow = $r;
}

/* ─────────────────────────────────────────
   ขั้นตอน 5b: ถ้าไม่พบ → Auto-register
   สร้างบัญชีใหม่จากข้อมูล Provider ID
───────────────────────────────────────── */
if (!$empRow) {
    // กำหนด username = provider_id หรือ providerUsername หรือ account_id
    $newUsername = $providerUsername !== ''
        ? $providerUsername
        : ($providerId !== '' ? $providerId : (string)($healthTokenRes['body']['data']['account_id'] ?? uniqid('pid_')));

    // ตรวจซ้ำด้วย username นี้
    $chk = $conn->prepare("SELECT Username FROM employee WHERE Username = ? LIMIT 1");
    $chk->bind_param("s", $newUsername);
    $chk->execute();
    $exists = $chk->get_result()->num_rows > 0;
    $chk->close();

    if ($exists) {
        // username ซ้ำ → ดึงข้อมูลเดิม
        $s = $conn->prepare("SELECT Username, Names, Position, TypeUser FROM employee WHERE Username = ? LIMIT 1");
        $s->bind_param("s", $newUsername);
        $s->execute();
        $empRow = $s->get_result()->fetch_assoc();
        $s->close();
    } else {
        // สร้างบัญชีใหม่
        $newNames    = $nameTh ?: $newUsername;
        $newPosition = $position ?: ($specialTitle ? $specialTitle . 'Provider' : 'Provider');
        $newTypeUser = 'user';
        $newPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT); // random, ใช้ OAuth เท่านั้น

        $ins = $conn->prepare(
            "INSERT INTO employee (Username, Password, Names, Position, TypeUser, Status) VALUES (?, ?, ?, ?, ?, '1')"
        );
        $ins->bind_param("sssss", $newUsername, $newPassword, $newNames, $newPosition, $newTypeUser);
        $ins->execute();
        $ins->close();

        $empRow = [
            'Username' => $newUsername,
            'Names'    => $newNames,
            'Position' => $newPosition,
            'TypeUser' => $newTypeUser,
        ];
    }

    // ถ้ายังไม่ได้ empRow (กรณีหายาก)
    if (!$empRow) {
        error_log('[Provider ID] Auto-register failed for provider_id=' . $providerId);
        redirectError('no_employee');
    }
}

/* ─────────────────────────────────────────
   ขั้นตอน 6: สร้าง Session และ Redirect
───────────────────────────────────────── */
session_regenerate_id(true);
$_SESSION['Username']         = (string) $empRow['Username'];
$_SESSION['Names']            = (string) $empRow['Names'];
$_SESSION['Position']         = (string) ($empRow['Position'] ?: $position);
$_SESSION['TypeUser']         = (string) $empRow['TypeUser'];
$_SESSION['login_by']         = 'provider_id';
$_SESSION['provider_id']      = $providerId;
$_SESSION['provider_hcode']   = $hcode;
$_SESSION['provider_hname']   = $hnameTh;

header('Location: main.php');
exit;
