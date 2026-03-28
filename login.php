<?php
// login.php
declare(strict_types=1);
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Bangkok');

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/connect_db.php';

$prv = (int)($_GET['prv'] ?? 0);

$OfficeName = '';
$res = $conn->query("SELECT OfficeName FROM office LIMIT 1");
if ($res && ($row = $res->fetch_assoc())) {
  $OfficeName = (string)($row['OfficeName'] ?? '');
}

$oldUser = $_SESSION['old_username'] ?? '';
unset($_SESSION['old_username']);

function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$curYear = date('Y') + 543;
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>เข้าสู่ระบบ – ระบบบริหารจัดการการเงินและบัญชี</title>
  <link rel="shortcut icon" type="image/x-icon" href="pic/fms.png">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css" rel="stylesheet">

  <style>
    /* ── Variables ── */
    :root {
      --green:    #0B6E4F;
      --green2:   #08A045;
      --green-d:  #1a4d2e;
      --green-l:  #e8f5e9;
      --gold:     #FFB81C;
      --white:    #ffffff;
      --g50:  #f8f9fa; --g100: #f1f3f5; --g200: #e9ecef;
      --g300: #dee2e6; --g400: #ced4da; --g500: #adb5bd;
      --g600: #6c757d; --g700: #495057; --g800: #343a40;
    }

    /* Material Symbols */
    .msi {
      font-family: 'Material Symbols Outlined';
      font-weight: normal; font-style: normal; font-size: 22px; line-height: 1;
      letter-spacing: normal; text-transform: none; display: inline-block;
      white-space: nowrap; direction: ltr; -webkit-font-smoothing: antialiased;
      vertical-align: middle; user-select: none;
    }
    .msi-lg { font-size: 48px; }
    .msi-xl { font-size: 64px; }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body { height: 100%; }

    body {
      font-family: 'Sarabun', -apple-system, sans-serif;
      background: var(--g50);
      display: flex;
      min-height: 100vh;
      overflow-x: hidden;
    }

    /* ════════════════════════
       LEFT PANEL (Decorative)
    ════════════════════════ */
    .login-left {
      width: 46%;
      min-height: 100vh;
      background: linear-gradient(150deg, var(--green-d) 0%, var(--green) 45%, var(--green2) 100%);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 48px 40px;
      position: relative;
      overflow: hidden;
      flex-shrink: 0;
    }

    /* Decorative circles */
    .login-left::before {
      content: '';
      position: absolute;
      top: -120px; right: -120px;
      width: 400px; height: 400px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(255,255,255,.1) 0%, transparent 70%);
    }
    .login-left::after {
      content: '';
      position: absolute;
      bottom: -100px; left: -80px;
      width: 320px; height: 320px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(255,255,255,.07) 0%, transparent 70%);
    }
    .login-left .ring {
      position: absolute;
      border-radius: 50%;
      border: 1px solid rgba(255,255,255,.08);
    }
    .ring-1 { width: 300px; height: 300px; top: 10%; left: -80px; }
    .ring-2 { width: 200px; height: 200px; bottom: 15%; right: -50px; }
    .ring-3 { width: 140px; height: 140px; top: 55%; left: 30%; }

    .left-content { position: relative; z-index: 1; text-align: center; max-width: 320px; }

    .left-logo-wrap {
      width: 100px; height: 100px;
      background: rgba(255,255,255,.15);
      border-radius: 24px;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 24px;
      backdrop-filter: blur(8px);
      box-shadow: 0 8px 32px rgba(0,0,0,.2);
      border: 1px solid rgba(255,255,255,.2);
    }
    .left-logo-wrap img { width: 68px; height: 68px; object-fit: contain; }

    .left-title {
      color: #fff;
      font-size: 22px;
      font-weight: 800;
      line-height: 1.3;
      margin-bottom: 10px;
      text-shadow: 0 2px 8px rgba(0,0,0,.15);
    }
    .left-sub {
      color: rgba(255,255,255,.75);
      font-size: 14px;
      line-height: 1.6;
      margin-bottom: 28px;
    }
    .left-office {
      background: rgba(255,255,255,.12);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255,255,255,.18);
      border-radius: 12px;
      padding: 12px 20px;
      color: rgba(255,255,255,.9);
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 32px;
    }

    .left-features { display: flex; flex-direction: column; gap: 12px; }
    .feat-item {
      display: flex; align-items: center; gap: 12px;
      background: rgba(255,255,255,.09);
      border-radius: 10px; padding: 11px 16px;
      color: rgba(255,255,255,.85); font-size: 13px;
      border: 1px solid rgba(255,255,255,.08);
    }
    .feat-item .msi { color: #86efac; font-size: 20px; flex-shrink: 0; }

    /* ════════════════════════
       RIGHT PANEL (Form)
    ════════════════════════ */
    .login-right {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px 24px;
      background: var(--g50);
    }

    .login-form-wrap {
      width: 100%;
      max-width: 420px;
    }

    /* Header */
    .form-hd {
      margin-bottom: 32px;
    }
    .form-hd h2 {
      font-size: 26px;
      font-weight: 800;
      color: var(--g800);
      margin-bottom: 6px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .form-hd h2 .msi { color: var(--green); font-size: 28px; }
    .form-hd p { color: var(--g600); font-size: 14px; margin: 0; }

    /* Card */
    .form-card {
      background: var(--white);
      border-radius: 20px;
      box-shadow: 0 8px 32px rgba(0,0,0,.08), 0 1px 4px rgba(0,0,0,.04);
      padding: 32px;
      border: 1px solid var(--g200);
    }

    /* Fields */
    .field-block { margin-bottom: 20px; }
    .field-block label {
      display: flex; align-items: center; gap: 6px;
      font-size: 13.5px; font-weight: 700; color: var(--g700);
      margin-bottom: 7px;
    }
    .field-block label .msi { color: var(--green); font-size: 18px; }

    .input-wrap { position: relative; }
    .input-prefix {
      position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
      color: var(--g400); font-size: 20px; pointer-events: none; z-index: 2;
    }
    .field-block input {
      width: 100%;
      height: 50px;
      border: 1.5px solid var(--g300);
      border-radius: 12px;
      padding: 0 16px 0 48px;
      font-size: 15px;
      font-family: 'Sarabun', sans-serif;
      background: var(--g50);
      color: var(--g800);
      transition: border-color .2s, box-shadow .2s, background .2s;
      outline: none;
    }
    .field-block input:focus {
      border-color: var(--green);
      background: var(--white);
      box-shadow: 0 0 0 3px rgba(11,110,79,.12);
    }
    .field-block input::placeholder { color: var(--g400); }

    .eye-btn {
      position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
      background: none; border: none; color: var(--g400); cursor: pointer;
      padding: 4px; border-radius: 6px; transition: color .18s;
      display: flex; align-items: center;
    }
    .eye-btn:hover { color: var(--green); }

    /* Btn */
    .btn-login {
      width: 100%; height: 50px;
      background: linear-gradient(135deg, var(--green) 0%, var(--green2) 100%);
      border: none; border-radius: 12px; color: #fff;
      font-size: 16px; font-weight: 700; font-family: 'Sarabun', sans-serif;
      cursor: pointer; transition: all .22s;
      display: flex; align-items: center; justify-content: center; gap: 8px;
      box-shadow: 0 4px 14px rgba(11,110,79,.3);
      margin-top: 8px;
    }
    .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(11,110,79,.4); }
    .btn-login:active { transform: translateY(0); }
    .btn-login .msi { font-size: 20px; }

    /* Links */
    .form-links {
      display: flex; justify-content: center; gap: 20px;
      margin-top: 20px;
    }
    .form-links a {
      color: var(--g600); font-size: 13px; text-decoration: none;
      display: flex; align-items: center; gap: 5px;
      transition: color .18s;
    }
    .form-links a:hover { color: var(--green); }
    .form-links a .msi { font-size: 16px; }

    .form-footer {
      text-align: center; margin-top: 20px;
      padding-top: 16px; border-top: 1px solid var(--g100);
      color: var(--g500); font-size: 12px;
    }

    /* Security badge */
    .sec-badge {
      display: flex; align-items: center; justify-content: center; gap: 6px;
      color: var(--green); font-size: 12.5px; font-weight: 600; margin-bottom: 20px;
    }
    .sec-badge .msi { font-size: 16px; }

    /* SweetAlert */
    .swal2-popup { border-radius: 16px !important; font-family: 'Sarabun', sans-serif !important; }
    .swal2-confirm { background: var(--green) !important; border-radius: 10px !important; font-weight: 600 !important; }

    /* Loading */
    .btn-login.loading { opacity: .7; pointer-events: none; }

    /* Responsive */
    @media (max-width: 820px) {
      body { flex-direction: column; }
      .login-left { width: 100%; min-height: auto; padding: 32px 24px; }
      .left-features { display: none; }
      .left-logo-wrap { width: 72px; height: 72px; }
      .left-logo-wrap img { width: 48px; height: 48px; }
      .left-title { font-size: 18px; }
      .left-sub { display: none; }
      .left-office { margin-bottom: 0; }
      .login-right { padding: 24px 16px; }
      .form-card { padding: 24px 20px; }
    }
  </style>
</head>
<body>

  <!-- ═══════ LEFT PANEL ═══════ -->
  <div class="login-left">
    <div class="ring ring-1"></div>
    <div class="ring ring-2"></div>
    <div class="ring ring-3"></div>

    <div class="left-content">
      <div class="left-logo-wrap">
        <img src="pic/fms.png" alt="MOPH">
      </div>
      <h2 class="left-title">ระบบบริหารจัดการ<br>การเงินและบัญชี</h2>
      <p class="left-sub">ระบบจัดการงานบัญชีและการเงินที่ครบถ้วน รวดเร็ว และปลอดภัย สำหรับบุคลากรกระทรวงสาธารณสุข</p>

      <?php if ($OfficeName): ?>
      <div class="left-office">
        <span class="msi" style="font-size:16px; margin-right:6px;">location_on</span>
        <?= h($OfficeName) ?>
      </div>
      <?php endif; ?>

      <div class="left-features">
        <div class="feat-item">
          <span class="msi">receipt_long</span>
          <span>จัดการบัญชีเจ้าหนี้และรายการสั่งจ่าย</span>
        </div>
        <div class="feat-item">
          <span class="msi">payments</span>
          <span>ระบบอนุมัติและเบิกจ่าย</span>
        </div>
        <div class="feat-item">
          <span class="msi">bar_chart</span>
          <span>รายงานและสถิติรายละเอียด</span>
        </div>
        <div class="feat-item">
          <span class="msi">security</span>
          <span>ระบบปลอดภัยด้วยการยืนยันตัวตน</span>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════ RIGHT PANEL ═══════ -->
  <div class="login-right">
    <div class="login-form-wrap">
      <div class="form-hd">
        <h2>
          <span class="msi">login</span>
          เข้าสู่ระบบ
        </h2>
        <p>กรุณากรอกชื่อผู้ใช้งานและรหัสผ่านของท่าน</p>
      </div>

      <div class="sec-badge">
        <span class="msi">lock</span>
        การเชื่อมต่อปลอดภัย · HTTPS Secured
      </div>

      <div class="form-card">
        <form method="post" action="login_process.php" autocomplete="off" id="loginForm">

          <!-- Username -->
          <div class="field-block">
            <label for="Username">
              <span class="msi">person</span>
              ชื่อผู้ใช้งาน
            </label>
            <div class="input-wrap">
              <span class="input-prefix msi">account_circle</span>
              <input
                type="text" id="Username" name="Username"
                value="<?= h($oldUser) ?>"
                placeholder="กรอกชื่อผู้ใช้งาน"
                autocomplete="username"
                required>
            </div>
          </div>

          <!-- Password -->
          <div class="field-block">
            <label for="Password">
              <span class="msi">lock</span>
              รหัสผ่าน
            </label>
            <div class="input-wrap">
              <span class="input-prefix msi">key</span>
              <input
                type="password" id="Password" name="Password"
                placeholder="กรอกรหัสผ่าน"
                autocomplete="current-password"
                style="padding-right:46px;"
                required>
              <button type="button" class="eye-btn" id="toggleEye" title="แสดง/ซ่อนรหัสผ่าน">
                <span class="msi" id="eyeIcon">visibility</span>
              </button>
            </div>
          </div>

          <!-- Submit -->
          <button type="submit" class="btn-login" id="loginBtn">
            <span class="msi">login</span>
            เข้าสู่ระบบ
          </button>

        </form>

        <!-- Links -->
        <div class="form-links">
          <a href="#" onclick="showHelp(); return false;">
            <span class="msi">help_outline</span> ช่วยเหลือ
          </a>
          <a href="#" onclick="showContact(); return false;">
            <span class="msi">support_agent</span> ติดต่อเจ้าหน้าที่ it
          </a>
        </div>

        <div class="form-footer">
          © <?= $curYear ?> กระทรวงสาธารณสุข &nbsp;·&nbsp; version 1.12
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>

  <script>
  // Eye toggle
  document.getElementById('toggleEye').addEventListener('click', function(){
    var inp = document.getElementById('Password');
    var ico = document.getElementById('eyeIcon');
    if (inp.type === 'password') { inp.type = 'text'; ico.textContent = 'visibility_off'; }
    else { inp.type = 'password'; ico.textContent = 'visibility'; }
  });

  // Login failed alert
  <?php if ($prv === 1): ?>
  Swal.fire({
    icon: 'error',
    title: 'เข้าสู่ระบบไม่สำเร็จ',
    html: '<p>ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง</p><p style="margin-top:6px; font-size:13px; color:#6c757d;">หรือบัญชีถูกระงับการใช้งาน</p>',
    confirmButtonText: 'ลองอีกครั้ง',
    confirmButtonColor: '#0B6E4F'
  });
  <?php endif; ?>

  // Form submit
  document.getElementById('loginForm').addEventListener('submit', function(e){
    var u = document.getElementById('Username').value.trim();
    var p = document.getElementById('Password').value;
    if (!u || !p) {
      e.preventDefault();
      Swal.fire({ icon:'warning', title:'กรุณากรอกข้อมูล',
        text:'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน',
        confirmButtonText:'ตรวจสอบ', confirmButtonColor:'#0B6E4F' });
      return;
    }
    var btn = document.getElementById('loginBtn');
    btn.classList.add('loading');
    btn.innerHTML = '<span class="msi" style="animation:spin .6s linear infinite">progress_activity</span> กำลังเข้าสู่ระบบ...';
  });

  // Enter key
  document.getElementById('Password').addEventListener('keydown', function(e){
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('loginForm').requestSubmit(); }
  });

  // Auto focus
  document.addEventListener('DOMContentLoaded', function(){
    var u = document.getElementById('Username');
    if (u && !u.value) u.focus();
    else document.getElementById('Password').focus();
  });

  // Help
  function showHelp() {
    Swal.fire({
      icon: 'info', title: 'ช่วยเหลือการใช้งาน',
      html: '<div style="text-align:left; font-size:14px;">' +
        '<p style="margin-bottom:10px;"><strong>วิธีการเข้าสู่ระบบ:</strong></p>' +
        '<ol style="padding-left:18px; line-height:2;">' +
        '<li>กรอกชื่อผู้ใช้งานที่ได้รับจากเจ้าหน้าที่</li>' +
        '<li>กรอกรหัสผ่านของท่าน</li>' +
        '<li>คลิกปุ่ม "เข้าสู่ระบบ"</li>' +
        '</ol>' +
        '<p style="margin-top:12px; color:#6c757d; font-size:13px;">หากลืมรหัสผ่าน กรุณาติดต่อเจ้าหน้าที่ผู้ดูแลระบบ</p>' +
        '</div>',
      confirmButtonText: 'เข้าใจแล้ว', confirmButtonColor: '#0B6E4F'
    });
  }

  // Contact
  function showContact() {
    Swal.fire({
      icon: 'question', title: 'ติดต่อเจ้าหน้าที่ไอที ',
      html: '<div style="text-align:left; font-size:14px;">' +
        '<p style="margin-bottom:14px;">ติดต่อ: นายณัฐพงษ์ นิลคง นักวิชาการคอมพิวเตอร์ </p>' +
        '<div style="background:#f8f9fa; padding:14px; border-radius:10px; margin-bottom:10px;">' +
        '<p style="margin:0;"><strong>โทรศัพท์:</strong> 095-671-6233</p>' +
        '</div>' +
        '<div style="background:#f8f9fa; padding:14px; border-radius:10px;">' +
        '<p style="margin:0;"><strong>อีเมล:</strong> itckhosptial@gmail.com</p>' +
        '</div></div>',
      confirmButtonText: 'ปิด', confirmButtonColor: '#0B6E4F'
    });
  }

  // Spin animation for loading
  var style = document.createElement('style');
  style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
  document.head.appendChild(style);
  </script>
</body>
</html>
