<?php
// header.php – MOPH Finance System · Sidebar Navigation
if (!defined('CONFIG_LOADED')) {
    require_once __DIR__ . '/config.php';
    define('CONFIG_LOADED', true);
}
if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

$Username   = $_SESSION['Username'] ?? '';
$Names      = $_SESSION['Names']    ?? '';
$OfficeName = '';
$resOffice  = $conn->query("SELECT OfficeName FROM office LIMIT 1");
if ($resOffice && ($rowOffice = $resOffice->fetch_assoc())) {
  $OfficeName = (string)($rowOffice['OfficeName'] ?? '');
}
$Position = ''; $TypeUser = '';
if ($Username !== '' && ($stmt = $conn->prepare("SELECT Names, Position, TypeUser FROM employee WHERE Username=? LIMIT 1"))) {
  $stmt->bind_param("s", $Username);
  $stmt->execute();
  $resEmp = $stmt->get_result();
  if ($resEmp && ($rowEmp = $resEmp->fetch_assoc())) {
    if ($Names === '' && !empty($rowEmp['Names'])) { $Names = (string)$rowEmp['Names']; $_SESSION['Names'] = $Names; }
    $Position = (string)($rowEmp['Position'] ?? '');
    $TypeUser = (string)($rowEmp['TypeUser'] ?? '');
  }
  $stmt->close();
}
$currentPage = basename($_SERVER['PHP_SELF']);
function navItem($href, $icon, $label, $current) {
  $active = (basename($href) === $current) ? ' active' : '';
  return '<a href="'.e($href).'" class="snav-item'.$active.'"><span class="msi">'.$icon.'</span><span>'.$label.'</span></a>';
}
?>
<!-- Google Fonts: Sarabun + Material Symbols Outlined -->
<link href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
<!-- SweetAlert2 -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css" rel="stylesheet">
<!-- MOPH Shared Theme -->
<link rel="stylesheet" href="css/theme.css">
<link rel="stylesheet" href="css/moph-font.css">
<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* ═══════════════════════════════════
   CSS Variables & Material Symbols
═══════════════════════════════════ */
:root {
  --sidebar-w: 240px;
  --topbar-h:  54px;
  --moph-primary:   #0B6E4F;
  --moph-secondary: #08A045;
  --moph-dark:      #1a4d2e;
  --moph-light:     #e8f5e9;
  --white: #fff;
  --gray-50: #f8f9fa; --gray-100: #f1f3f5; --gray-200: #e9ecef;
  --gray-300: #dee2e6; --gray-500: #adb5bd; --gray-600: #6c757d;
  --gray-700: #495057; --gray-800: #343a40;
}
.msi {
  font-family: 'Material Symbols Outlined';
  font-weight: normal; font-style: normal; font-size: 20px; line-height: 1;
  letter-spacing: normal; text-transform: none; display: inline-block;
  white-space: nowrap; direction: ltr; -webkit-font-smoothing: antialiased;
  flex-shrink: 0; user-select: none;
}
.msi-24 { font-size: 24px; }
.msi-28 { font-size: 28px; }
.msi-18 { font-size: 18px; }

/* Body offset — moves page content right of sidebar */
body {
  font-family: 'Sarabun', -apple-system, sans-serif !important;
  margin-left: var(--sidebar-w) !important;
  padding-top: var(--topbar-h) !important;
  min-height: 100vh;
  background: #f3f6fb;
  transition: margin-left .28s cubic-bezier(.4,0,.2,1);
}

/* ── Sidebar ── */
.moph-sidebar {
  position: fixed;
  top: 0; left: 0; bottom: 0;
  width: var(--sidebar-w);
  background: linear-gradient(180deg, #0d4f36 0%, #0B6E4F 45%, #0a6045 100%);
  z-index: 1050;
  display: flex;
  flex-direction: column;
  box-shadow: 3px 0 20px rgba(0,0,0,.15);
  transition: transform .28s cubic-bezier(.4,0,.2,1);
  overflow: hidden;
}
/* Sidebar Header */
.sb-header {
  padding: 16px 14px 12px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid rgba(255,255,255,.10);
  flex-shrink: 0;
}
.sb-brand {
  display: flex; align-items: center; gap: 10px; text-decoration: none;
}
.sb-brand img {
  width: 38px; height: 38px; border-radius: 9px;
  background: rgba(255,255,255,.15); padding: 3px; flex-shrink: 0;
}
.sb-brand-title { color: #fff; font-size: 14px; font-weight: 700; line-height: 1.2; }
.sb-brand-sub   { color: rgba(255,255,255,.55); font-size: 10px; }
.sb-close-btn {
  display: none; background: none; border: none; color: rgba(255,255,255,.55);
  cursor: pointer; padding: 4px; border-radius: 6px; transition: all .2s;
}
.sb-close-btn:hover { background: rgba(255,255,255,.1); color: #fff; }
/* User */
.sb-user {
  padding: 12px 14px;
  display: flex; align-items: center; gap: 9px;
  border-bottom: 1px solid rgba(255,255,255,.08);
  flex-shrink: 0;
}
.sb-user .msi { font-size: 32px; color: rgba(255,255,255,.65); }
.sb-user-name { font-size: 13px; font-weight: 700; color: #fff; line-height: 1.3;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sb-user-role { font-size: 10px; color: rgba(255,255,255,.5); }
/* Nav */
.sb-nav {
  flex: 1; overflow-y: auto; padding: 8px 8px 4px;
  scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.2) transparent;
}
.sb-nav::-webkit-scrollbar { width: 3px; }
.sb-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 3px; }
.snav-label {
  font-size: 9px; font-weight: 700; letter-spacing: 1.1px; text-transform: uppercase;
  color: rgba(255,255,255,.35); padding: 12px 8px 5px;
}
.snav-item {
  display: flex; align-items: center; gap: 9px;
  padding: 9px 11px; border-radius: 9px; color: rgba(255,255,255,.75);
  text-decoration: none; font-size: 13.5px; font-weight: 500; margin-bottom: 1px;
  transition: background .16s, color .16s; cursor: pointer; border: none;
  background: none; width: 100%; text-align: left; font-family: 'Sarabun', sans-serif;
}
.snav-item:hover { background: rgba(255,255,255,.11); color: #fff; text-decoration: none; }
.snav-item.active { background: rgba(255,255,255,.17); color: #fff; font-weight: 700; }
.snav-item.active .msi { color: #86efac; }
.snav-logout { color: rgba(255,160,160,.85) !important; }
.snav-logout:hover { background: rgba(220,53,69,.18) !important; color: #ffb3b3 !important; }
/* Footer */
.sb-footer {
  padding: 10px 14px; border-top: 1px solid rgba(255,255,255,.08); flex-shrink: 0;
}
.sb-office { font-size: 10px; color: rgba(255,255,255,.45); white-space: nowrap;
  overflow: hidden; text-overflow: ellipsis; margin-bottom: 1px; }
.sb-ver { font-size: 9px; color: rgba(255,255,255,.28); }

/* ── Topbar ── */
.moph-topbar {
  position: fixed;
  top: 0; left: var(--sidebar-w); right: 0;
  height: var(--topbar-h);
  background: #fff;
  z-index: 1040;
  display: flex; align-items: center;
  padding: 0 16px 0 18px;
  border-bottom: 1px solid var(--gray-200);
  box-shadow: 0 1px 8px rgba(0,0,0,.07);
  transition: left .28s cubic-bezier(.4,0,.2,1);
  gap: 10px;
}
.topbar-ham {
  display: none; background: none; border: none; color: var(--gray-700);
  cursor: pointer; padding: 5px; border-radius: 8px; transition: background .18s; flex-shrink: 0;
}
.topbar-ham:hover { background: var(--gray-100); }
.topbar-ham .msi { font-size: 22px; }
.topbar-title {
  flex: 1; display: flex; align-items: center; gap: 7px;
  font-size: 14px; font-weight: 700; color: var(--moph-dark);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0;
}
.topbar-title .msi { color: var(--moph-primary); font-size: 20px; flex-shrink: 0; }
.topbar-user {
  display: flex; align-items: center; gap: 6px;
  background: var(--gray-50); border: 1px solid var(--gray-200);
  border-radius: 9px; padding: 6px 12px;
  cursor: pointer; transition: all .18s; color: var(--gray-700);
  font-size: 13px; font-weight: 600; font-family: 'Sarabun', sans-serif;
  flex-shrink: 0; white-space: nowrap;
}
.topbar-user:hover { background: var(--moph-light); border-color: var(--moph-primary); color: var(--moph-dark); }
.topbar-user .msi { color: var(--moph-primary); font-size: 20px; }
.topbar-uname { max-width: 130px; overflow: hidden; text-overflow: ellipsis; }

/* ── Overlay (mobile) ── */
.sb-overlay {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,.4); z-index: 1045; backdrop-filter: blur(2px);
}
.sb-overlay.show { display: block; }

/* ── Responsive ── */
@media (max-width: 900px) {
  body { margin-left: 0 !important; }
  .moph-sidebar { transform: translateX(-100%); }
  .moph-sidebar.show { transform: translateX(0); }
  .sb-close-btn { display: flex; align-items: center; }
  .moph-topbar { left: 0; }
  .topbar-ham { display: flex; }
  .topbar-uname { display: none; }
}

/* ── Modal ── */
.modal-content { border-radius: 18px; border: none; box-shadow: 0 16px 48px rgba(0,0,0,.15); }
.modal-header {
  background: linear-gradient(135deg, var(--moph-primary), var(--moph-secondary));
  color: #fff; border-radius: 18px 18px 0 0; padding: 18px 22px; border: none;
}
.modal-header .modal-title { font-weight: 700; font-size: 17px; display: flex; align-items: center; gap: 7px; }
.modal-body { padding: 22px; }
.modal-body .form-group { margin-bottom: 14px; }
.modal-body label { font-weight: 600; color: var(--gray-700); margin-bottom: 5px; font-size: 13px; display: flex; align-items: center; gap: 5px; }
.modal-body .form-control { border-radius: 9px; border: 1.5px solid var(--gray-200); padding: 9px 12px; font-size: 14px; font-family: 'Sarabun', sans-serif; }
.modal-body .form-control:focus { border-color: var(--moph-primary); box-shadow: 0 0 0 3px rgba(11,110,79,.1); outline: none; }
.modal-footer { padding: 14px 22px; border-top: 1px solid var(--gray-100); gap: 8px; }
.modal-footer .btn { border-radius: 9px; padding: 8px 20px; font-weight: 600; font-size: 14px; font-family: 'Sarabun', sans-serif; }
.modal-footer .btn-primary { background: var(--moph-primary); border: none; }
.modal-footer .btn-primary:hover { background: var(--moph-dark); }

/* SweetAlert2 */
.swal2-popup { border-radius: 16px !important; font-family: 'Sarabun', sans-serif !important; }
.swal2-confirm { background: var(--moph-primary) !important; border-radius: 10px !important; font-weight: 600 !important; }
.swal2-cancel  { border-radius: 10px !important; }

/* ── Go Back button (shared) ── */
.btn-go-back {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 7px 16px; border-radius: 9px;
  background: #fff; border: 1.5px solid var(--gray-300);
  color: var(--gray-700); font-size: 13px; font-weight: 600;
  text-decoration: none; cursor: pointer; transition: all .18s;
  font-family: 'Sarabun', sans-serif;
}
.btn-go-back:hover { background: var(--gray-50); border-color: var(--gray-400); color: var(--gray-900); text-decoration: none; }
.btn-go-back .msi { font-size: 18px; }
</style>

<!-- Sidebar Overlay -->
<div class="sb-overlay" id="sbOverlay"></div>

<!-- ═══════ SIDEBAR ═══════ -->
<aside class="moph-sidebar" id="mophSidebar">
  <div class="sb-header">
    <a href="main.php" class="sb-brand">
      <img src="pic/fms.png" alt="MOPH">
      <div>
        <div class="sb-brand-title">ระบบการเงิน</div>
        <div class="sb-brand-sub">MOPH Finance</div>
      </div>
    </a>
    <button class="sb-close-btn" id="sbCloseBtn" title="ปิดเมนู">
      <span class="msi">close</span>
    </button>
  </div>

  <div class="sb-user">
    <span class="msi msi-28">account_circle</span>
    <div style="min-width:0;">
      <div class="sb-user-name"><?= e($Names ?: $Username) ?></div>
      <div class="sb-user-role"><?= e($Position ?: ($TypeUser ?: 'ผู้ใช้งาน')) ?></div>
    </div>
  </div>

  <nav class="sb-nav">
    <div class="snav-label">เมนูหลัก</div>
    <?= navItem('dashboard.php',  'dashboard',    'แดชบอร์ด',                $currentPage) ?>
    <?= navItem('main.php',       'home',         'หน้าหลัก',                $currentPage) ?>
    <?= navItem('accounting.php', 'receipt_long', 'บัญชีเจ้าหนี้/สั่งจ่าย', $currentPage) ?>
    <?= navItem('finance.php',    'payments',     'อนุมัติจ่าย',             $currentPage) ?>
    <?= navItem('cheque.php',     'credit_card',  'จัดทำเช็ค',               $currentPage) ?>
    <?= navItem('paid.php',       'task_alt',     'บันทึกจ่าย',              $currentPage) ?>

    <div class="snav-label">รายงาน</div>
    <?= navItem('statistics.php', 'bar_chart',      'รายงานสถิติ',  $currentPage) ?>
    <?= navItem('daily.php',      'calendar_today', 'รายงานรายวัน', $currentPage) ?>
    <?= navItem('control.php',    'tune',           'ควบคุมระบบ',   $currentPage) ?>

    <div class="snav-label">ระบบ</div>
    <?= navItem('backup.php',              'backup',               'สำรองข้อมูล',         $currentPage) ?>
    <?= navItem('moph_alert_settings.php', 'notifications_active', 'ตั้งค่า MOPH ALERT',  $currentPage) ?>

    <div class="snav-label">บัญชีผู้ใช้</div>
    <button class="snav-item" onclick="openUserModal()">
      <span class="msi">manage_accounts</span><span>ข้อมูลผู้ใช้</span>
    </button>
    <?= navItem('changepass.php', 'lock', 'เปลี่ยนรหัสผ่าน', $currentPage) ?>
    <button class="snav-item snav-logout" onclick="confirmLogout()">
      <span class="msi">logout</span><span>ออกจากระบบ</span>
    </button>
  </nav>

  <div class="sb-footer">
    <div class="sb-office"><?= e($OfficeName) ?></div>
    <div class="sb-ver">version 1.12</div>
  </div>
</aside>

<!-- ═══════ TOPBAR ═══════ -->
<header class="moph-topbar">
  <button class="topbar-ham" id="topbarHam" title="เมนู">
    <span class="msi">menu</span>
  </button>
  <div class="topbar-title">
    <span class="msi">account_balance</span>
    <span>ระบบบริหารจัดการการเงินและบัญชี</span>
  </div>
  <button class="topbar-user" onclick="openUserModal()" title="ข้อมูลผู้ใช้">
    <span class="msi">account_circle</span>
    <span class="topbar-uname"><?= e($Names ?: $Username) ?></span>
  </button>
</header>

<!-- ─── User Data Modal ─── -->
<div class="modal fade" id="mydata" tabindex="-1" aria-labelledby="mydataLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mydataLabel">
          <span class="msi">badge</span> ข้อมูลผู้ใช้งาน
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="get" action="editconfig8.php" id="userDataForm">
        <div class="modal-body">
          <div class="form-group">
            <label><span class="msi msi-18">person</span> Username</label>
            <input type="text" class="form-control" value="<?= e($Username) ?>" disabled>
            <input type="hidden" name="Username" value="<?= e($Username) ?>">
          </div>
          <div class="form-group">
            <label><span class="msi msi-18">badge</span> ชื่อ - สกุล</label>
            <input type="text" class="form-control" name="Names" value="<?= e($Names) ?>" placeholder="กรอกชื่อ-สกุล">
          </div>
          <div class="form-group">
            <label><span class="msi msi-18">work</span> ตำแหน่ง</label>
            <input type="text" class="form-control" name="Position" value="<?= e($Position) ?>" placeholder="กรอกตำแหน่ง">
          </div>
          <div class="form-group">
            <label><span class="msi msi-18">shield</span> สิทธิ์การใช้งาน</label>
            <input type="text" class="form-control" value="<?= e($TypeUser) ?>" disabled>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-primary">
            <span class="msi msi-18">save</span> บันทึก
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>

<script>
(function(){
  var sb      = document.getElementById('mophSidebar');
  var overlay = document.getElementById('sbOverlay');
  var closeBtn= document.getElementById('sbCloseBtn');
  var hamBtn  = document.getElementById('topbarHam');

  function isMobile(){ return window.innerWidth <= 900; }
  function openSb(){  sb.classList.add('show'); overlay.classList.add('show'); }
  function closeSb(){ sb.classList.remove('show'); overlay.classList.remove('show'); }

  if (hamBtn)   hamBtn.addEventListener('click', function(){ isMobile() ? (sb.classList.contains('show') ? closeSb() : openSb()) : null; });
  if (closeBtn) closeBtn.addEventListener('click', closeSb);
  if (overlay)  overlay.addEventListener('click', closeSb);

  // Close on mobile nav click
  document.querySelectorAll('.snav-item[href]').forEach(function(a){
    a.addEventListener('click', function(){ if(isMobile()) closeSb(); });
  });
})();

function openUserModal() {
  var el = document.getElementById('mydata');
  if (el) { var m = bootstrap.Modal.getOrCreateInstance(el); m.show(); }
}

function confirmLogout() {
  Swal.fire({
    title: 'ยืนยันการออกจากระบบ', text: 'คุณต้องการออกจากระบบใช่หรือไม่?',
    icon: 'question', showCancelButton: true,
    confirmButtonColor: '#0B6E4F', cancelButtonColor: '#6c757d',
    confirmButtonText: 'ยืนยัน', cancelButtonText: 'ยกเลิก', reverseButtons: true
  }).then(function(r){
    if (r.isConfirmed) {
      Swal.fire({ title: 'กำลังออกจากระบบ...', allowOutsideClick: false, allowEscapeKey: false, didOpen: function(){ Swal.showLoading(); } });
      setTimeout(function(){ window.location.href = 'logout.php'; }, 400);
    }
  });
}

document.getElementById('userDataForm').addEventListener('submit', function(e){
  e.preventDefault(); var form = this;
  Swal.fire({
    title:'ยืนยันการแก้ไข', text:'คุณต้องการบันทึกการแก้ไขข้อมูลใช่หรือไม่?', icon:'question',
    showCancelButton:true, confirmButtonColor:'#0B6E4F', cancelButtonColor:'#6c757d',
    confirmButtonText:'ยืนยัน', cancelButtonText:'ยกเลิก', reverseButtons:true
  }).then(function(r){ if(r.isConfirmed) form.submit(); });
});

<?php if (isset($_GET['updated']) && $_GET['updated'] === 'success'): ?>
Swal.fire({ icon:'success', title:'บันทึกสำเร็จ', text:'ข้อมูลได้รับการอัพเดทแล้ว', confirmButtonColor:'#0B6E4F', confirmButtonText:'ตกลง' });
<?php endif; ?>
</script>
