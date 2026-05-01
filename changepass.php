<?php
declare(strict_types=1);
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Bangkok');
require_once __DIR__ . '/config.php';
checkAuth();

$prv = (int)($_GET['prv'] ?? 0);

function h($s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$Username = $_SESSION['Username'] ?? '';
$Names    = $_SESSION['Names']    ?? '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" type="image/x-icon" href="pic/fms.png">
  <title>เปลี่ยนรหัสผ่าน – MOPH</title>

  <link href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,300;0,400;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/moph-font.css">

  <style>
    body {
      background: linear-gradient(135deg, var(--moph-light) 0%, #fff 50%, var(--gray-50) 100%);
      min-height: 100vh;
    }
    .cp-wrapper {
      max-width: 480px;
      margin: 0 auto;
      padding: 32px 16px 48px;
    }
    .cp-card {
      background: var(--white);
      border-radius: 20px;
      box-shadow: var(--shadow-xl);
      overflow: hidden;
      border: 1px solid var(--gray-100);
    }
    .cp-header {
      background: linear-gradient(135deg, var(--moph-primary), var(--moph-secondary));
      padding: 28px 28px 24px;
      color: white;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .cp-header::before {
      content: '';
      position: absolute;
      top: -40%; right: -10%;
      width: 260px; height: 260px;
      background: radial-gradient(circle, rgba(255,255,255,.12), transparent 70%);
      border-radius: 50%;
      pointer-events: none;
    }
    .cp-header .icon-wrap {
      width: 64px; height: 64px;
      background: rgba(255,255,255,.2);
      border-radius: 16px;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 14px;
      font-size: 28px;
      position: relative; z-index: 1;
    }
    .cp-header h2 {
      font-size: 22px;
      font-weight: 700;
      margin: 0;
      position: relative; z-index: 1;
    }
    .cp-header p {
      font-size: 13px;
      opacity: .85;
      margin: 6px 0 0;
      position: relative; z-index: 1;
    }
    .cp-body { padding: 28px; }
    .field-group { margin-bottom: 20px; }
    .field-group label {
      font-weight: 600;
      font-size: 14px;
      color: var(--gray-700);
      margin-bottom: 7px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .field-group label i { color: var(--moph-primary); font-size: 16px; }
    .pass-wrap { position: relative; }
    .pass-wrap input {
      padding-right: 46px;
    }
    .pass-toggle {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--gray-400);
      font-size: 18px;
      cursor: pointer;
      padding: 0;
      line-height: 1;
      transition: color .2s;
    }
    .pass-toggle:hover { color: var(--moph-primary); }
    .pass-strength {
      height: 4px;
      border-radius: 99px;
      background: var(--gray-200);
      margin-top: 8px;
      overflow: hidden;
    }
    .pass-strength-bar {
      height: 100%;
      border-radius: 99px;
      width: 0%;
      transition: width .3s, background .3s;
    }
    .strength-weak   { width: 33%; background: var(--red); }
    .strength-medium { width: 66%; background: var(--orange); }
    .strength-strong { width: 100%; background: var(--moph-primary); }
    .strength-hint {
      font-size: 11px;
      color: var(--gray-500);
      margin-top: 4px;
    }
    .btn-submit {
      width: 100%;
      padding: 13px;
      font-size: 16px;
      font-weight: 700;
      background: linear-gradient(135deg, var(--moph-primary), var(--moph-secondary));
      color: white;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: all .2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      margin-top: 8px;
    }
    .btn-submit:hover { background: linear-gradient(135deg, var(--moph-dark), var(--moph-primary)); box-shadow: 0 6px 20px rgba(11,110,79,.3); }
    .btn-back {
      width: 100%;
      padding: 11px;
      font-size: 14px;
      font-weight: 600;
      background: var(--white);
      color: var(--gray-600);
      border: 1.5px solid var(--gray-200);
      border-radius: 12px;
      cursor: pointer;
      transition: all .2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      margin-top: 10px;
      text-decoration: none;
    }
    .btn-back:hover { background: var(--gray-50); color: var(--gray-800); border-color: var(--gray-300); }
    .user-badge {
      display: flex;
      align-items: center;
      gap: 10px;
      background: var(--gray-50);
      border: 1px solid var(--gray-200);
      border-radius: 12px;
      padding: 12px 14px;
      margin-bottom: 22px;
    }
    .user-badge i { font-size: 24px; color: var(--moph-primary); }
    .user-badge .ub-name { font-weight: 700; font-size: 15px; color: var(--gray-800); }
    .user-badge .ub-user { font-size: 12px; color: var(--gray-500); }
  </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="cp-wrapper">
  <div class="cp-card">
    <div class="cp-header">
      <div class="icon-wrap">
        <i class="bi bi-shield-lock-fill"></i>
      </div>
      <h2>เปลี่ยนรหัสผ่าน</h2>
      <p>กรุณากรอกรหัสผ่านเดิมและรหัสผ่านใหม่</p>
    </div>

    <div class="cp-body">
      <!-- User badge -->
      <div class="user-badge">
        <i class="bi bi-person-circle"></i>
        <div>
          <div class="ub-name"><?= h($Names ?: $Username) ?></div>
          <div class="ub-user">@<?= h($Username) ?></div>
        </div>
      </div>

      <form id="cpForm" action="editpass.php" method="post" autocomplete="off">
        <input type="hidden" name="Username" value="<?= h($Username) ?>">

        <!-- รหัสผ่านเดิม -->
        <div class="field-group">
          <label><i class="bi bi-key"></i> รหัสผ่านเดิม</label>
          <div class="pass-wrap">
            <input class="form-control" type="password" id="Password" name="Password" maxlength="30" placeholder="กรอกรหัสผ่านเดิม" autocomplete="current-password">
            <button type="button" class="pass-toggle" onclick="togglePass('Password', this)">
              <i class="bi bi-eye-slash"></i>
            </button>
          </div>
        </div>

        <!-- รหัสผ่านใหม่ -->
        <div class="field-group">
          <label><i class="bi bi-lock"></i> รหัสผ่านใหม่</label>
          <div class="pass-wrap">
            <input class="form-control" type="password" id="NewPassword" name="NewPassword" maxlength="30" placeholder="กรอกรหัสผ่านใหม่ (อย่างน้อย 6 ตัว)" autocomplete="new-password" oninput="checkStrength(this.value)">
            <button type="button" class="pass-toggle" onclick="togglePass('NewPassword', this)">
              <i class="bi bi-eye-slash"></i>
            </button>
          </div>
          <div class="pass-strength"><div class="pass-strength-bar" id="strengthBar"></div></div>
          <div class="strength-hint" id="strengthHint">ความยาวอย่างน้อย 6 ตัวอักษร</div>
        </div>

        <!-- ยืนยันรหัสผ่านใหม่ -->
        <div class="field-group">
          <label><i class="bi bi-lock-fill"></i> ยืนยันรหัสผ่านใหม่</label>
          <div class="pass-wrap">
            <input class="form-control" type="password" id="ConPassword" name="ConPassword" maxlength="30" placeholder="ยืนยันรหัสผ่านใหม่อีกครั้ง" autocomplete="new-password">
            <button type="button" class="pass-toggle" onclick="togglePass('ConPassword', this)">
              <i class="bi bi-eye-slash"></i>
            </button>
          </div>
        </div>

        <button type="button" class="btn-submit" id="btnSubmit">
          <i class="bi bi-check-circle-fill"></i> บันทึกรหัสผ่านใหม่
        </button>
      </form>

      <a href="main.php" class="btn-back">
        <i class="bi bi-arrow-left"></i> กลับหน้าหลัก
      </a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>

<script>
// ── Show result alerts from redirect ──
<?php if ($prv === 1): ?>
Swal.fire({
  icon: 'error', title: 'รหัสผ่านไม่ถูกต้อง',
  text: 'รหัสผ่านเดิมที่กรอกไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง',
  confirmButtonColor: '#0B6E4F', confirmButtonText: 'ลองใหม่'
});
<?php elseif ($prv === 2): ?>
Swal.fire({
  icon: 'warning', title: 'รหัสผ่านซ้ำกัน',
  text: 'รหัสผ่านใหม่เหมือนรหัสผ่านเดิม กรุณาตั้งรหัสผ่านใหม่',
  confirmButtonColor: '#0B6E4F', confirmButtonText: 'ตกลง'
});
<?php elseif ($prv === 3): ?>
Swal.fire({
  icon: 'error', title: 'รหัสผ่านไม่ตรงกัน',
  text: 'รหัสผ่านใหม่และการยืนยันไม่ตรงกัน กรุณากรอกใหม่',
  confirmButtonColor: '#0B6E4F', confirmButtonText: 'ลองใหม่'
});
<?php elseif ($prv === 4): ?>
Swal.fire({
  icon: 'success', title: 'เปลี่ยนรหัสผ่านสำเร็จ',
  text: 'รหัสผ่านของคุณได้รับการอัพเดทแล้ว กรุณาเข้าสู่ระบบด้วยรหัสผ่านใหม่',
  confirmButtonColor: '#0B6E4F', confirmButtonText: 'เข้าสู่ระบบ',
  timer: 3000, timerProgressBar: true
}).then(function() {
  window.location.href = 'logout.php';
});
<?php endif; ?>

// ── Toggle password visibility ──
function togglePass(id, btn) {
  var inp = document.getElementById(id);
  var icon = btn.querySelector('i');
  if (inp.type === 'password') {
    inp.type = 'text';
    icon.className = 'bi bi-eye';
  } else {
    inp.type = 'password';
    icon.className = 'bi bi-eye-slash';
  }
}

// ── Password strength meter ──
function checkStrength(val) {
  var bar  = document.getElementById('strengthBar');
  var hint = document.getElementById('strengthHint');
  if (!val) {
    bar.className = 'pass-strength-bar'; hint.textContent = 'ความยาวอย่างน้อย 6 ตัวอักษร'; return;
  }
  var score = 0;
  if (val.length >= 6)  score++;
  if (val.length >= 10) score++;
  if (/[A-Z]/.test(val) && /[0-9]/.test(val)) score++;
  if (score === 1) { bar.className = 'pass-strength-bar strength-weak';   hint.textContent = 'รหัสผ่านอ่อน'; }
  if (score === 2) { bar.className = 'pass-strength-bar strength-medium'; hint.textContent = 'รหัสผ่านปานกลาง'; }
  if (score === 3) { bar.className = 'pass-strength-bar strength-strong'; hint.textContent = 'รหัสผ่านแข็งแรง ✓'; }
}

// ── Form submit with validation ──
document.getElementById('btnSubmit').addEventListener('click', function() {
  var old    = document.getElementById('Password').value.trim();
  var newp   = document.getElementById('NewPassword').value;
  var conf   = document.getElementById('ConPassword').value;

  if (!old) {
    Swal.fire({ icon:'warning', title:'กรุณากรอกรหัสผ่านเดิม', confirmButtonColor:'#0B6E4F' }); return;
  }
  if (newp.length < 6) {
    Swal.fire({ icon:'warning', title:'รหัสผ่านสั้นเกินไป', text:'รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร', confirmButtonColor:'#0B6E4F' }); return;
  }
  if (newp !== conf) {
    Swal.fire({ icon:'error', title:'รหัสผ่านไม่ตรงกัน', text:'รหัสผ่านใหม่และการยืนยันต้องตรงกัน', confirmButtonColor:'#0B6E4F' }); return;
  }

  Swal.fire({
    icon: 'question',
    title: 'ยืนยันการเปลี่ยนรหัสผ่าน',
    text: 'คุณต้องการเปลี่ยนรหัสผ่านใช่หรือไม่?',
    showCancelButton: true,
    confirmButtonColor: '#0B6E4F',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="bi bi-check-circle"></i> ยืนยัน',
    cancelButtonText: '<i class="bi bi-x-circle"></i> ยกเลิก',
    reverseButtons: true
  }).then(function(result) {
    if (result.isConfirmed) {
      document.getElementById('cpForm').submit();
    }
  });
});
</script>
</body>
</html>
