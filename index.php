<?php

// index.php (หรือหน้า login เดิมของคุณ)
declare(strict_types=1);
error_reporting(E_ALL & ~E_NOTICE);
header('Location: login.php');
exit;

require_once __DIR__ . '/connect_db.php';
date_default_timezone_set('Asia/Bangkok');

$prv = (int)($_GET['prv'] ?? 0);

// ดึงชื่อหน่วยงาน
$OfficeName = '';
$sql = "SELECT OfficeName FROM office LIMIT 1";
if ($result = $conn->query($sql)) {
  if ($row = $result->fetch_assoc()) {
    $OfficeName = (string)($row['OfficeName'] ?? '');
  }
}

// (option) ค่าเก่าที่พิมพ์ไว้
session_start();
$oldUser = $_SESSION['old_username'] ?? '';
unset($_SESSION['old_username']);
session_write_close();
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ระบบบริหารจัดการการเงินและบัญชี</title>
  <link rel="shortcut icon" type="image/x-icon" href="pic/fms.png" />

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">

  <style>
    body{ font-family:"Kanit",system-ui,sans-serif; }
    .bg-soft{
      min-height:100vh;
      background:
        radial-gradient(1100px 600px at 10% 10%, rgba(13,110,253,.20), transparent),
        radial-gradient(900px 500px at 90% 20%, rgba(25,135,84,.18), transparent),
        #f8fafc;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:24px;
    }
    .card{
      border:0;
      border-radius:18px;
      box-shadow:0 12px 36px rgba(0,0,0,.10);
    }
    .brand{
      display:flex; align-items:center; gap:12px;
    }
    .brand img{ width:64px; height:64px; border-radius:14px; }
    .muted{ color:#64748b; }
  </style>
</head>

<body>
<div class="bg-soft">
  <div class="container" style="max-width: 520px;">
    <div class="card">
      <div class="card-body p-4 p-md-5">

        <div class="brand mb-3">
          <img src="pic/fms.png" alt="logo">
          <div>
            <div class="text-primary fw-semibold">ระบบบริหารจัดการการเงินและบัญชี</div>
            <div class="muted"><?= htmlspecialchars($OfficeName) ?></div>
          </div>
        </div>

        <h5 class="mb-3">เข้าสู่ระบบ</h5>

        <?php if ($prv === 1): ?>
          <div class="alert alert-danger py-2 small">
            ชื่อผู้ใช้/รหัสผ่านไม่ถูกต้อง หรือท่านถูกระงับการใช้งาน โปรดติดต่อผู้ดูแลระบบ
          </div>
        <?php endif; ?>

        <form method="post" action="login_process.php" autocomplete="off" class="mt-3">
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input
              class="form-control"
              type="text"
              id="Username"
              name="Username"
              maxlength="50"
              required
              value="<?= htmlspecialchars($oldUser) ?>"
              placeholder="กรอกชื่อผู้ใช้"
            >
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group">
              <input class="form-control" type="password" id="Password" name="Password" required placeholder="••••••••">
              <button class="btn btn-outline-secondary" type="button" id="togglePw">แสดง</button>
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-100">เข้าสู่ระบบ</button>

          <div class="small muted mt-3">
            แนะนำ: ใช้งานผ่าน HTTPS และอย่าใช้รหัสผ่านร่วมกันหลายระบบ
          </div>
        </form>

      </div>
    </div>

    <div class="text-center small muted mt-3">
      <?php include __DIR__ . '/footer.php'; ?>
    </div>
  </div>
</div>

<script>
  const pw = document.getElementById('Password');
  const btn = document.getElementById('togglePw');
  btn.addEventListener('click', () => {
    const show = pw.type === 'password';
    pw.type = show ? 'text' : 'password';
    btn.textContent = show ? 'ซ่อน' : 'แสดง';
  });
</script>
</body>
</html>
