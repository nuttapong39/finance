<?php
// moph_alert_settings.php — จัดการ MOPH ALERT Token Keys
declare(strict_types=1);
date_default_timezone_set('Asia/Bangkok');
ini_set('display_errors', '1');
error_reporting(E_ALL);

$__header_html = '';
ob_start();
@include __DIR__ . '/header.php';
$__header_html = ob_get_clean();

require_once __DIR__ . '/connect_db.php';
require_once __DIR__ . '/moph_alert_config.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('ไม่สามารถเชื่อมต่อฐานข้อมูลได้');
}
@mysqli_set_charset($conn, 'utf8mb4');

function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// ── สร้างตารางถ้ายังไม่มี ──
$conn->query("
    CREATE TABLE IF NOT EXISTS moph_alert_tokens (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        name        VARCHAR(100)  NOT NULL DEFAULT '',
        client_key  VARCHAR(255)  NOT NULL DEFAULT '',
        secret_key  VARCHAR(255)  NOT NULL DEFAULT '',
        is_active   TINYINT(1)    NOT NULL DEFAULT 1,
        note        VARCHAR(255)           DEFAULT '',
        created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// ── Pre-populate จาก config.php ถ้าตารางว่าง ──
$cnt = $conn->query("SELECT COUNT(*) AS c FROM moph_alert_tokens")->fetch_assoc()['c'] ?? 0;
if ((int)$cnt === 0 && defined('MOPH_CLIENT_KEY') && MOPH_CLIENT_KEY !== '') {
    $ck = MOPH_CLIENT_KEY; $sk = MOPH_SECRET_KEY;
    $stmt = $conn->prepare("INSERT INTO moph_alert_tokens (name, client_key, secret_key, is_active) VALUES (?, ?, ?, 1)");
    $name = 'ค่าเริ่มต้น (จาก config)';
    $stmt->bind_param("sss", $name, $ck, $sk);
    $stmt->execute();
    $stmt->close();
}

// ── Helper: ส่ง MOPH ALERT ด้วย key คู่ที่ระบุ ──
function send_test_alert(string $clientKey, string $secretKey): array {
    $dt  = new DateTimeImmutable('now', new DateTimeZone('Asia/Bangkok'));
    $msg = [
        ["type" => "text", "text" =>
            "🔔 ทดสอบการแจ้งเตือน MOPH ALERT\n" .
            " ──────────────────────\n" .
            "✅ เชื่อมต่อสำเร็จ!\n" .
            "📅 วันที่: " . $dt->format('Y-m-d') . "\n" .
            "⏰ เวลา: "   . $dt->format('H:i:s') . "\n" .
            " ──────────────────────\n" .
            "ระบบบริหารจัดการการเงิน MOPH"
        ]
    ];
    $json = json_encode(['messages' => $msg], JSON_UNESCAPED_UNICODE);
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => MOPH_ALERT_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $json,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'client-key: ' . $clientKey,
            'secret-key: ' . $secretKey,
        ],
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 8,
    ]);
    $resp     = curl_exec($ch);
    $errno    = curl_errno($ch);
    $errStr   = curl_error($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $ok = ($errno === 0 && $httpCode >= 200 && $httpCode < 300);
    return ['ok' => $ok, 'http' => $httpCode, 'errno' => $errno, 'err' => $errStr, 'resp' => substr((string)$resp, 0, 300)];
}

// ── Handle AJAX test ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'test_ajax') {
    header('Content-Type: application/json');
    $id = (int)($_POST['id'] ?? 0);
    $row = null;
    if ($id > 0) {
        $s = $conn->prepare("SELECT client_key, secret_key FROM moph_alert_tokens WHERE id=? LIMIT 1");
        $s->bind_param("i", $id);
        $s->execute();
        $row = $s->get_result()->fetch_assoc();
        $s->close();
    }
    if (!$row) { echo json_encode(['ok' => false, 'msg' => 'ไม่พบ Token']); exit; }
    $result = send_test_alert((string)$row['client_key'], (string)$row['secret_key']);
    $result['msg'] = $result['ok'] ? '✅ ส่งสำเร็จ! HTTP ' . $result['http'] : '❌ ส่งไม่สำเร็จ HTTP ' . $result['http'] . ' | ' . $result['err'];
    echo json_encode($result);
    exit;
}

// ── Handle POST actions ──
$flash = ['type' => '', 'msg' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name  = trim($_POST['name']       ?? '');
        $ck    = trim($_POST['client_key'] ?? '');
        $sk    = trim($_POST['secret_key'] ?? '');
        $note  = trim($_POST['note']       ?? '');
        $active = (int)($_POST['is_active'] ?? 1);
        if ($name === '' || $ck === '' || $sk === '') {
            $flash = ['type' => 'error', 'msg' => 'กรุณากรอกข้อมูลให้ครบถ้วน'];
        } else {
            $s = $conn->prepare("INSERT INTO moph_alert_tokens (name, client_key, secret_key, note, is_active) VALUES (?,?,?,?,?)");
            $s->bind_param("ssssi", $name, $ck, $sk, $note, $active);
            $s->execute();
            $s->close();
            $flash = ['type' => 'success', 'msg' => 'เพิ่ม Token สำเร็จแล้ว'];
        }
    }

    elseif ($action === 'edit') {
        $id    = (int)($_POST['id']         ?? 0);
        $name  = trim($_POST['name']        ?? '');
        $ck    = trim($_POST['client_key']  ?? '');
        $sk    = trim($_POST['secret_key']  ?? '');
        $note  = trim($_POST['note']        ?? '');
        $active = (int)($_POST['is_active'] ?? 1);
        if ($id < 1 || $name === '' || $ck === '' || $sk === '') {
            $flash = ['type' => 'error', 'msg' => 'กรุณากรอกข้อมูลให้ครบถ้วน'];
        } else {
            $s = $conn->prepare("UPDATE moph_alert_tokens SET name=?, client_key=?, secret_key=?, note=?, is_active=? WHERE id=?");
            $s->bind_param("ssssii", $name, $ck, $sk, $note, $active, $id);
            $s->execute();
            $s->close();
            $flash = ['type' => 'success', 'msg' => 'แก้ไข Token สำเร็จแล้ว'];
        }
    }

    elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $s = $conn->prepare("DELETE FROM moph_alert_tokens WHERE id=?");
            $s->bind_param("i", $id);
            $s->execute();
            $s->close();
            $flash = ['type' => 'success', 'msg' => 'ลบ Token เรียบร้อยแล้ว'];
        }
    }

    elseif ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $conn->query("UPDATE moph_alert_tokens SET is_active = IF(is_active=1,0,1) WHERE id={$id}");
            $flash = ['type' => 'success', 'msg' => 'เปลี่ยนสถานะเรียบร้อยแล้ว'];
        }
    }

    if ($flash['type'] !== '') {
        header('Location: moph_alert_settings.php?sw=' . $flash['type'] . '&msg=' . urlencode($flash['msg']));
        exit;
    }
}

// ── Load tokens ──
$tokens = [];
$rs = $conn->query("SELECT * FROM moph_alert_tokens ORDER BY id ASC");
if ($rs) { while ($r = $rs->fetch_assoc()) $tokens[] = $r; }
$totalTokens  = count($tokens);
$activeTokens = count(array_filter($tokens, fn($t) => (int)$t['is_active'] === 1));
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>ตั้งค่า MOPH ALERT — ระบบการเงิน</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" type="image/x-icon" href="pic/fms.png">

  <style>
    body {
      font-family: 'Sarabun', sans-serif;
      background:
        radial-gradient(1100px 600px at 12% 15%, rgba(91,155,213,.2), transparent 60%),
        radial-gradient(900px 520px at 90% 8%,  rgba(0,176,80,.12),  transparent 55%),
        linear-gradient(180deg, #f8fbff, #f4f7fc);
    }
    .container { max-width: 1100px; }

    /* ── Title bar ── */
    .page-titlebar {
      margin: 14px 0 16px;
      border-radius: 18px;
      padding: 16px 22px;
      background: rgba(255,255,255,.95);
      border: 1px solid #e9eef6;
      box-shadow: 0 10px 28px rgba(13,27,62,.08);
      display: flex; align-items: center; justify-content: space-between; gap: 12px;
    }
    .page-titlebar h3 {
      margin: 0; font-weight: 800; color: #1f2a44; font-size: 20px;
      display: flex; align-items: center; gap: 9px;
    }
    .page-titlebar .sub { color: #6b778c; margin-top: 4px; font-size: 13px; }

    /* ── Stats cards ── */
    .stats-row { display: grid; grid-template-columns: repeat(3,1fr); gap: 12px; margin-bottom: 16px; }
    @media(max-width:600px){ .stats-row { grid-template-columns: 1fr 1fr; } }
    .stat-card {
      border-radius: 16px; padding: 16px 18px;
      background: #fff; border: 1px solid #e9eef6;
      box-shadow: 0 4px 14px rgba(13,27,62,.06);
      border-left: 4px solid transparent;
      display: flex; align-items: center; gap: 14px;
    }
    .stat-card-blue  { border-left-color: #3B82F6; }
    .stat-card-green { border-left-color: #16A34A; }
    .stat-card-amber { border-left-color: #F59E0B; }
    .stat-icon {
      width: 44px; height: 44px; border-radius: 12px;
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .si-blue  { background: #EFF6FF; color: #3B82F6; }
    .si-green { background: #F0FDF4; color: #16A34A; }
    .si-amber { background: #FFFBEB; color: #F59E0B; }
    .stat-num   { font-size: 26px; font-weight: 900; color: #1f2a44; line-height: 1; }
    .stat-label { font-size: 12px; color: #6b778c; font-weight: 600; margin-top: 2px; }

    /* ── Card panel ── */
    .card-panel {
      border-radius: 18px; border: 1px solid #e9eef6;
      background: rgba(255,255,255,.97);
      box-shadow: 0 8px 24px rgba(13,27,62,.07);
      overflow: hidden; margin-bottom: 16px;
    }
    .card-head {
      padding: 13px 18px; border-bottom: 1px solid #e9eef6;
      font-weight: 800; color: #1f2a44;
      background: linear-gradient(135deg, rgba(0,176,80,.12), rgba(0,176,80,.04));
      display: flex; align-items: center; justify-content: space-between; gap: 10px;
    }
    .card-head-title { display: flex; align-items: center; gap: 8px; font-size: 15px; }

    /* ── Table ── */
    .table { margin-bottom: 0; font-size: 14px; }
    .table thead th {
      background: #e9f7ee; color: #1f2a44; font-weight: 800;
      border-bottom: 2px solid #d6f0df !important;
      vertical-align: middle !important; padding: 11px 14px; white-space: nowrap;
    }
    .table td { vertical-align: middle !important; padding: 11px 14px; }
    .table tbody tr:hover { background: #f8fafc; }

    /* ── Token key masking ── */
    .key-masked { font-family: monospace; font-size: 13px; color: #374151; }
    .key-show-btn {
      background: none; border: none; color: #6b778c; cursor: pointer;
      padding: 2px 6px; border-radius: 6px; font-size: 12px; font-family: 'Sarabun', sans-serif;
      transition: background .15s;
    }
    .key-show-btn:hover { background: #f1f5f9; color: #1f2a44; }

    /* ── Status badge ── */
    .badge-active   { display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;background:#dcfce7;color:#166534; }
    .badge-inactive { display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;background:#fee2e2;color:#991b1b; }

    /* ── Action buttons ── */
    .btn-act {
      display: inline-flex; align-items: center; justify-content: center;
      width: 32px; height: 32px; border-radius: 8px; border: none;
      cursor: pointer; transition: all .15s; text-decoration: none;
    }
    .btn-act-test   { background: #EFF6FF; color: #3B82F6; }
    .btn-act-test:hover   { background: #DBEAFE; }
    .btn-act-edit   { background: #FFF7ED; color: #D97706; }
    .btn-act-edit:hover   { background: #FDE68A; }
    .btn-act-toggle-on  { background: #FEF2F2; color: #DC2626; }
    .btn-act-toggle-on:hover  { background: #FEE2E2; }
    .btn-act-toggle-off { background: #F0FDF4; color: #16A34A; }
    .btn-act-toggle-off:hover { background: #DCFCE7; }
    .btn-act-delete { background: #FEF2F2; color: #DC2626; }
    .btn-act-delete:hover { background: #FEE2E2; }

    /* ── Test result inline ── */
    .test-result {
      display: none; margin-top: 6px;
      padding: 6px 10px; border-radius: 8px;
      font-size: 12px; font-weight: 600;
    }
    .test-result.ok  { background: #dcfce7; color: #166534; }
    .test-result.err { background: #fee2e2; color: #991b1b; }

    /* ── Add button ── */
    .btn-add {
      display: inline-flex; align-items: center; gap: 7px;
      padding: 8px 18px; border-radius: 12px;
      background: linear-gradient(135deg, #0B6E4F, #08A045);
      color: #fff; border: none; font-family: 'Sarabun', sans-serif;
      font-weight: 700; font-size: 14px; cursor: pointer;
      box-shadow: 0 6px 18px rgba(11,110,79,.22);
      transition: opacity .15s;
    }
    .btn-add:hover { opacity: .88; }

    /* ── Modal ── */
    .modal-content { border-radius: 20px !important; overflow: hidden; border: none !important; box-shadow: 0 20px 60px rgba(0,0,0,.18) !important; }
    .modal-header {
      background: linear-gradient(135deg, #0B6E4F, #08A045) !important;
      border: none !important; padding: 16px 22px !important;
    }
    .modal-header .modal-title { color: #fff !important; font-weight: 800; display:flex;align-items:center;gap:8px; }
    .modal-header .btn-close { filter: invert(1); opacity: .8; }
    .modal-body { padding: 22px; }
    .modal-footer { border-top: 1px solid #e9eef6; background: #f8fafc; padding: 14px 22px; }
    .modal-label { font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 6px; display: block; }
    .modal-input {
      width: 100%; height: 42px; border-radius: 12px;
      border: 1.5px solid #dfe7f3; padding: 0 14px;
      font-family: 'Sarabun', sans-serif; font-size: 14px; color: #1f2a44;
      transition: border-color .2s;
    }
    .modal-input:focus { border-color: #0B6E4F; outline: none; box-shadow: 0 0 0 3px rgba(11,110,79,.12); }
    .modal-input.font-mono { font-family: 'Courier New', monospace; font-size: 13px; }
    .input-with-eye { position: relative; }
    .input-with-eye .modal-input { padding-right: 44px; }
    .eye-btn {
      position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
      background: none; border: none; color: #94a3b8; cursor: pointer; padding: 4px;
      border-radius: 6px; transition: color .15s;
    }
    .eye-btn:hover { color: #374151; }
    .form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    @media(max-width:540px){ .form-row-2 { grid-template-columns: 1fr; } }
    .btn-modal-cancel { border-radius: 10px; font-family: 'Sarabun', sans-serif; font-weight: 700; border: 1.5px solid #e2e8f0; background: #fff; color: #374151; padding: 8px 18px; }
    .btn-modal-save   { border-radius: 10px; font-family: 'Sarabun', sans-serif; font-weight: 700; background: linear-gradient(135deg,#0B6E4F,#08A045); color: #fff; border: none; padding: 8px 22px; box-shadow: 0 4px 14px rgba(11,110,79,.22); display:inline-flex;align-items:center;gap:6px; }

    /* ── Info box ── */
    .info-box {
      background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 14px;
      padding: 14px 18px; margin-bottom: 16px;
      display: flex; gap: 12px; align-items: flex-start;
    }
    .info-box .msi { color: #16A34A; flex-shrink: 0; margin-top: 1px; }
    .info-box-text { font-size: 13px; color: #166534; line-height: 1.6; }
    .info-box-text b { color: #14532d; }

    /* ── Empty state ── */
    .empty-state { text-align: center; padding: 52px 20px; color: #94a3b8; }
    .empty-state .msi { font-size: 52px; color: #d1d5db; display: block; margin-bottom: 10px; }
    .empty-state p { margin: 0; font-size: 15px; }

    /* ── Go back ── */
    .btn-go-back {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 7px 16px; border-radius: 10px;
      background: #f1f5f9; border: 1px solid #e2e8f0;
      color: #475569; font-weight: 700; font-size: 13px;
      text-decoration: none; transition: background .15s;
    }
    .btn-go-back:hover { background: #e2e8f0; color: #1e293b; }
  </style>
</head>
<body>
  <?php echo $__header_html; ?>

  <div class="container">

    <!-- Title bar -->
    <div class="page-titlebar">
      <div>
        <h3>
          <span class="msi msi-24" style="color:#0B6E4F;">notifications_active</span>
          ตั้งค่า MOPH ALERT Token
        </h3>
        <div class="sub">จัดการ Client Key / Secret Key สำหรับแจ้งเตือน LINE รองรับหลาย Token</div>
      </div>
      <a href="backup.php" class="btn-go-back">
        <span class="msi">arrow_back</span> กลับ
      </a>
    </div>

    <!-- Info box -->
    <div class="info-box">
      <span class="msi msi-24">info</span>
      <div class="info-box-text">
        <b>วิธีใช้:</b> เพิ่ม Token หลายชุดได้ ระบบจะ <b>broadcast</b> แจ้งเตือนไปยังทุก Token ที่เปิดใช้งานอยู่
        · Token ที่ปิดใช้งาน (Inactive) จะถูกข้ามไป
        · กด <b>"ทดสอบ"</b> เพื่อส่งข้อความทดสอบไปยัง LINE ของ Token นั้น ๆ
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-row">
      <div class="stat-card stat-card-blue">
        <div class="stat-icon si-blue"><span class="msi msi-24">key</span></div>
        <div>
          <div class="stat-num"><?= $totalTokens ?></div>
          <div class="stat-label">Token ทั้งหมด</div>
        </div>
      </div>
      <div class="stat-card stat-card-green">
        <div class="stat-icon si-green"><span class="msi msi-24">check_circle</span></div>
        <div>
          <div class="stat-num"><?= $activeTokens ?></div>
          <div class="stat-label">Token ที่เปิดใช้งาน</div>
        </div>
      </div>
      <div class="stat-card stat-card-amber">
        <div class="stat-icon si-amber"><span class="msi msi-24">notifications_off</span></div>
        <div>
          <div class="stat-num"><?= $totalTokens - $activeTokens ?></div>
          <div class="stat-label">Token ที่ปิดใช้งาน</div>
        </div>
      </div>
    </div>

    <!-- Token table -->
    <div class="card-panel">
      <div class="card-head">
        <div class="card-head-title">
          <span class="msi msi-18" style="color:#0B6E4F;">vpn_key</span>
          รายการ Token Keys
        </div>
        <button class="btn-add" data-bs-toggle="modal" data-bs-target="#modalAdd">
          <span class="msi msi-18">add</span> เพิ่ม Token
        </button>
      </div>

      <?php if (empty($tokens)): ?>
        <div class="empty-state">
          <span class="msi">notifications_off</span>
          <p>ยังไม่มี Token — กด "เพิ่ม Token" เพื่อเริ่มต้น</p>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th style="width:44px;">#</th>
                <th>ชื่อ / กลุ่ม LINE</th>
                <th>Client Key</th>
                <th>Secret Key</th>
                <th>หมายเหตุ</th>
                <th style="width:100px;">สถานะ</th>
                <th style="width:160px;">อัปเดตล่าสุด</th>
                <th style="width:140px; text-align:center;">จัดการ</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tokens as $i => $tok): ?>
              <?php $isActive = (int)$tok['is_active'] === 1; ?>
              <tr id="row-<?= (int)$tok['id'] ?>">
                <td style="color:#94a3b8; font-weight:700;"><?= $i+1 ?></td>
                <td>
                  <div style="font-weight:700; color:#1f2a44;"><?= h($tok['name']) ?></div>
                </td>
                <td>
                  <div class="key-masked" id="ck-<?= (int)$tok['id'] ?>">
                    <?= h(substr($tok['client_key'],0,8)) ?>••••••••••••••••••••
                  </div>
                  <button class="key-show-btn" onclick="toggleKey(<?= (int)$tok['id'] ?>,'ck','<?= h(addslashes($tok['client_key'])) ?>')">
                    <span class="msi msi-18">visibility</span>
                  </button>
                </td>
                <td>
                  <div class="key-masked" id="sk-<?= (int)$tok['id'] ?>">
                    <?= h(substr($tok['secret_key'],0,4)) ?>••••••••••••
                  </div>
                  <button class="key-show-btn" onclick="toggleKey(<?= (int)$tok['id'] ?>,'sk','<?= h(addslashes($tok['secret_key'])) ?>')">
                    <span class="msi msi-18">visibility</span>
                  </button>
                </td>
                <td style="color:#64748b; font-size:13px;"><?= h($tok['note'] ?? '') ?></td>
                <td>
                  <?php if ($isActive): ?>
                    <span class="badge-active"><span class="msi msi-18">radio_button_checked</span>ใช้งาน</span>
                  <?php else: ?>
                    <span class="badge-inactive"><span class="msi msi-18">radio_button_unchecked</span>ปิดอยู่</span>
                  <?php endif; ?>
                </td>
                <td style="font-size:12px; color:#64748b;">
                  <?= h(substr($tok['updated_at'],0,16)) ?>
                </td>
                <td>
                  <div style="display:flex;align-items:center;gap:5px;justify-content:center;">

                    <!-- Test -->
                    <button class="btn-act btn-act-test"
                            onclick="testToken(<?= (int)$tok['id'] ?>,this)"
                            title="ทดสอบส่งแจ้งเตือน">
                      <span class="msi msi-18">send</span>
                    </button>

                    <!-- Edit -->
                    <button class="btn-act btn-act-edit"
                            onclick="openEdit(<?= (int)$tok['id'] ?>,<?= h(json_encode($tok)) ?>)"
                            title="แก้ไข">
                      <span class="msi msi-18">edit</span>
                    </button>

                    <!-- Toggle active -->
                    <form method="post" style="margin:0;">
                      <input type="hidden" name="action" value="toggle">
                      <input type="hidden" name="id" value="<?= (int)$tok['id'] ?>">
                      <button type="submit"
                              class="btn-act <?= $isActive ? 'btn-act-toggle-on' : 'btn-act-toggle-off' ?>"
                              title="<?= $isActive ? 'ปิดใช้งาน' : 'เปิดใช้งาน' ?>">
                        <span class="msi msi-18"><?= $isActive ? 'toggle_on' : 'toggle_off' ?></span>
                      </button>
                    </form>

                    <!-- Delete -->
                    <button class="btn-act btn-act-delete"
                            onclick="confirmDelete(<?= (int)$tok['id'] ?>, '<?= h(addslashes($tok['name'])) ?>')"
                            title="ลบ">
                      <span class="msi msi-18">delete</span>
                    </button>

                  </div>
                  <!-- Test result inline -->
                  <div class="test-result" id="tr-<?= (int)$tok['id'] ?>"></div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <div style="height:20px;"></div>
  </div>

  <!-- ══════════ Modal: Add ══════════ -->
  <div class="modal fade" id="modalAdd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <span class="msi msi-18">add_circle</span> เพิ่ม Token ใหม่
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="post">
          <input type="hidden" name="action" value="add">
          <div class="modal-body">

            <div style="margin-bottom:14px;">
              <label class="modal-label"><span class="msi msi-18">label</span> ชื่อ / กลุ่ม LINE <span style="color:#dc2626;">*</span></label>
              <input class="modal-input" name="name" type="text" placeholder="เช่น กลุ่มการเงิน, ห้องผู้อำนวยการ" required>
            </div>

            <div style="margin-bottom:14px;">
              <label class="modal-label"><span class="msi msi-18">vpn_key</span> Client Key <span style="color:#dc2626;">*</span></label>
              <div class="input-with-eye">
                <input class="modal-input font-mono" name="client_key" id="add-ck" type="password" placeholder="client-key" required>
                <button type="button" class="eye-btn" onclick="toggleEye('add-ck',this)">
                  <span class="msi msi-18">visibility</span>
                </button>
              </div>
            </div>

            <div style="margin-bottom:14px;">
              <label class="modal-label"><span class="msi msi-18">password</span> Secret Key <span style="color:#dc2626;">*</span></label>
              <div class="input-with-eye">
                <input class="modal-input font-mono" name="secret_key" id="add-sk" type="password" placeholder="secret-key" required>
                <button type="button" class="eye-btn" onclick="toggleEye('add-sk',this)">
                  <span class="msi msi-18">visibility</span>
                </button>
              </div>
            </div>

            <div class="form-row-2">
              <div>
                <label class="modal-label"><span class="msi msi-18">note</span> หมายเหตุ</label>
                <input class="modal-input" name="note" type="text" placeholder="อธิบายเพิ่มเติม (ไม่บังคับ)">
              </div>
              <div>
                <label class="modal-label"><span class="msi msi-18">toggle_on</span> สถานะ</label>
                <select class="modal-input" name="is_active" style="cursor:pointer;">
                  <option value="1">เปิดใช้งาน</option>
                  <option value="0">ปิดใช้งาน</option>
                </select>
              </div>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-modal-cancel" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="submit" class="btn btn-modal-save">
              <span class="msi msi-18">save</span> บันทึก Token
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ══════════ Modal: Edit ══════════ -->
  <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <span class="msi msi-18">edit</span> แก้ไข Token
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="post">
          <input type="hidden" name="action" value="edit">
          <input type="hidden" name="id" id="edit-id">
          <div class="modal-body">

            <div style="margin-bottom:14px;">
              <label class="modal-label"><span class="msi msi-18">label</span> ชื่อ / กลุ่ม LINE <span style="color:#dc2626;">*</span></label>
              <input class="modal-input" name="name" id="edit-name" type="text" required>
            </div>

            <div style="margin-bottom:14px;">
              <label class="modal-label"><span class="msi msi-18">vpn_key</span> Client Key <span style="color:#dc2626;">*</span></label>
              <div class="input-with-eye">
                <input class="modal-input font-mono" name="client_key" id="edit-ck" type="password" required>
                <button type="button" class="eye-btn" onclick="toggleEye('edit-ck',this)">
                  <span class="msi msi-18">visibility</span>
                </button>
              </div>
            </div>

            <div style="margin-bottom:14px;">
              <label class="modal-label"><span class="msi msi-18">password</span> Secret Key <span style="color:#dc2626;">*</span></label>
              <div class="input-with-eye">
                <input class="modal-input font-mono" name="secret_key" id="edit-sk" type="password" required>
                <button type="button" class="eye-btn" onclick="toggleEye('edit-sk',this)">
                  <span class="msi msi-18">visibility</span>
                </button>
              </div>
            </div>

            <div class="form-row-2">
              <div>
                <label class="modal-label"><span class="msi msi-18">note</span> หมายเหตุ</label>
                <input class="modal-input" name="note" id="edit-note" type="text">
              </div>
              <div>
                <label class="modal-label"><span class="msi msi-18">toggle_on</span> สถานะ</label>
                <select class="modal-input" name="is_active" id="edit-active" style="cursor:pointer;">
                  <option value="1">เปิดใช้งาน</option>
                  <option value="0">ปิดใช้งาน</option>
                </select>
              </div>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-modal-cancel" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="submit" class="btn btn-modal-save">
              <span class="msi msi-18">save</span> บันทึกการแก้ไข
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Delete form (hidden) -->
  <form method="post" id="deleteForm" style="display:none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
  </form>

  <script>
  // ── Toggle show/hide key in table ──
  const keyState = {};
  function toggleKey(id, type, fullVal) {
    const el = document.getElementById(type + '-' + id);
    const key = type + id;
    if (keyState[key]) {
      el.textContent = fullVal.substring(0, type === 'ck' ? 8 : 4) + '••••••••••••';
      keyState[key] = false;
    } else {
      el.textContent = fullVal;
      keyState[key] = true;
    }
  }

  // ── Toggle eye button in modal inputs ──
  function toggleEye(inputId, btn) {
    const inp = document.getElementById(inputId);
    const icon = btn.querySelector('.msi');
    if (inp.type === 'password') {
      inp.type = 'text';
      icon.textContent = 'visibility_off';
    } else {
      inp.type = 'password';
      icon.textContent = 'visibility';
    }
  }

  // ── Open edit modal ──
  function openEdit(id, data) {
    document.getElementById('edit-id').value     = id;
    document.getElementById('edit-name').value   = data.name   || '';
    document.getElementById('edit-ck').value     = data.client_key || '';
    document.getElementById('edit-sk').value     = data.secret_key || '';
    document.getElementById('edit-note').value   = data.note   || '';
    document.getElementById('edit-active').value = data.is_active == 1 ? '1' : '0';
    // Reset eye buttons
    document.getElementById('edit-ck').type = 'password';
    document.getElementById('edit-sk').type = 'password';
    document.querySelectorAll('#modalEdit .eye-btn .msi').forEach(i => i.textContent = 'visibility');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEdit')).show();
  }

  // ── Confirm delete ──
  function confirmDelete(id, name) {
    Swal.fire({
      icon: 'warning',
      title: 'ยืนยันการลบ?',
      html: 'ต้องการลบ Token <b>' + name + '</b> ใช่หรือไม่?<br><small style="color:#dc2626;">การลบไม่สามารถกู้คืนได้</small>',
      showCancelButton: true,
      confirmButtonColor: '#DC2626',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'ลบเลย',
      cancelButtonText: 'ยกเลิก'
    }).then(r => {
      if (r.isConfirmed) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
      }
    });
  }

  // ── Test token via AJAX ──
  function testToken(id, btn) {
    const resultEl = document.getElementById('tr-' + id);
    btn.disabled = true;
    btn.innerHTML = '<span class="msi msi-18" style="animation:spin .7s linear infinite">refresh</span>';
    resultEl.style.display = 'none';
    resultEl.className = 'test-result';

    const fd = new FormData();
    fd.append('action', 'test_ajax');
    fd.append('id', id);

    fetch('moph_alert_settings.php', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(data => {
        resultEl.textContent = data.msg;
        resultEl.classList.add(data.ok ? 'ok' : 'err');
        resultEl.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '<span class="msi msi-18">send</span>';
        setTimeout(() => { resultEl.style.display = 'none'; }, 5000);
      })
      .catch(() => {
        resultEl.textContent = '❌ เชื่อมต่อระบบไม่ได้';
        resultEl.classList.add('err');
        resultEl.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '<span class="msi msi-18">send</span>';
      });
  }

  // ── Spin animation ──
  const style = document.createElement('style');
  style.textContent = '@keyframes spin{ to{ transform:rotate(360deg) } }';
  document.head.appendChild(style);

  // ── Flash messages ──
  (function(){
    const params = new URLSearchParams(location.search);
    const sw  = params.get('sw');
    const msg = params.get('msg');
    if (!sw || typeof Swal === 'undefined') return;
    if (sw === 'success') {
      Swal.fire({ icon:'success', title:'สำเร็จ', text: msg || 'ดำเนินการเรียบร้อย', timer:1800, showConfirmButton:false });
    } else if (sw === 'error') {
      Swal.fire({ icon:'error', title:'เกิดข้อผิดพลาด', text: msg || 'ไม่สำเร็จ' });
    }
    // Clean URL
    const url = new URL(location.href);
    url.searchParams.delete('sw'); url.searchParams.delete('msg');
    history.replaceState({}, '', url);
  })();
  </script>

</body>
</html>
