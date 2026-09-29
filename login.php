<?php
// login.php
declare(strict_types=1);
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Bangkok');

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/connect_db.php';
require_once __DIR__ . '/provider_id_config.php';

// สร้าง state สำหรับ CSRF protection
$oauthState = bin2hex(random_bytes(16));
$_SESSION['pid_oauth_state'] = $oauthState;

// สร้าง URL สำหรับ Login with Provider ID
$providerLoginUrl = HEALTH_ID_URL . '/oauth/redirect?' . http_build_query([
    'client_id'     => HEALTH_ID_CLIENT_ID,
    'redirect_uri'  => HEALTH_ID_REDIRECT_URI,
    'response_type' => 'code',
    'state'         => $oauthState,
]);

$prv    = (int)($_GET['prv'] ?? 0);
$pidErr = trim($_GET['pid_err'] ?? '');

$OfficeName = '';
$res = $conn->query("SELECT OfficeName FROM office LIMIT 1");
if ($res && ($row = $res->fetch_assoc())) {
  $OfficeName = (string)($row['OfficeName'] ?? '');
}
$brandName = $OfficeName !== '' ? $OfficeName : 'ระบบบริหารจัดการการเงินและบัญชี';

$oldUser = $_SESSION['old_username'] ?? '';
unset($_SESSION['old_username']);

function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$curYear = date('Y') + 543;

// ข้อความแจ้งเตือน (แสดงในการ์ด)
$pidErrors = [
  'no_code'               => ['type' => 'error',   'title' => 'ไม่ได้รับ Code', 'text' => 'Health ID ไม่ส่ง authorization code กลับมา กรุณาลองใหม่อีกครั้ง'],
  'state_mismatch'        => ['type' => 'error',   'title' => 'คำขอไม่ถูกต้อง', 'text' => 'ตรวจพบการโจมตี CSRF — กรุณาลองเข้าสู่ระบบใหม่'],
  'health_token_failed'   => ['type' => 'error',   'title' => 'Health ID Error', 'text' => 'ไม่สามารถดึง Access Token จาก Health ID ได้ กรุณาติดต่อผู้ดูแลระบบ'],
  'not_provider'          => ['type' => 'warning', 'title' => 'ไม่พบข้อมูล Provider', 'text' => 'บัญชีนี้ไม่มีข้อมูล Provider ID กรุณาใช้ username/password เข้าสู่ระบบ'],
  'provider_token_failed' => ['type' => 'error',   'title' => 'Provider ID Error', 'text' => 'ไม่สามารถดึง Provider Token ได้ กรุณาลองใหม่หรือติดต่อผู้ดูแลระบบ'],
  'profile_failed'        => ['type' => 'error',   'title' => 'ดึงข้อมูลไม่สำเร็จ', 'text' => 'ไม่สามารถดึงข้อมูลโปรไฟล์ Provider ได้'],
  'no_employee'           => ['type' => 'warning', 'title' => 'ไม่พบบัญชีในระบบ', 'text' => 'ไม่พบข้อมูลของท่านในระบบนี้ กรุณาติดต่อผู้ดูแลระบบเพื่อเพิ่มสิทธิ์การใช้งาน'],
];
$alert = null;
if ($prv === 1) {
  $alert = ['type' => 'error', 'title' => 'เข้าสู่ระบบไม่สำเร็จ', 'text' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง หรือบัญชีถูกระงับการใช้งาน'];
} elseif ($pidErr !== '' && isset($pidErrors[$pidErr])) {
  $alert = $pidErrors[$pidErr];
}
?>
<!DOCTYPE html>
<html lang="th" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เข้าสู่ระบบ | ระบบบริหารจัดการการเงินและบัญชี</title>
<link rel="shortcut icon" type="image/x-icon" href="pic/fms.png">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css" rel="stylesheet">
<script>
  // ตั้งธีมก่อน render เพื่อไม่ให้หน้ากระพริบ
  (function(){
    var t;
    try { t = localStorage.getItem('fin-theme'); } catch (e) {}
    if (t !== 'dark' && t !== 'light') {
      t = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    document.documentElement.setAttribute('data-theme', t);
  })();
</script>
<style>
  :root{
    --bg:#FBFCFC; --surface:#FFFFFF; --surface-2:#FFFFFF;
    --bg-grad:linear-gradient(225deg, #FFFFFF 0%, #F4F6F5 45%, #E9ECEB 100%);
    --ink:#0F1A17; --muted:#6B7B76; --line:#E6ECEA; --ph:#AAB6B2;
    --accent:#12674F; --accent-2:#16856A; --accent-deep:#0B3B2E; --accent-soft:#EEF7F3;
    --icon-bg:rgba(18,103,79,.055);
    --shadow:0 1px 2px rgba(15,26,23,.04);
    --err-bg:#FDF1F0; --err-line:#F3C9C4; --err-ink:#9B2C1F;
    --warn-bg:#FFF8E6; --warn-line:#F2DC9C; --warn-ink:#7A5A00;
  }
  html[data-theme="dark"]{
    --bg:#080F0D; --surface:#0E1A16; --surface-2:#122320;
    --bg-grad:linear-gradient(#080F0D, #080F0D);
    --ink:#E8F1EE; --muted:#8FA39D; --line:#1D2F2A; --ph:#6B7F79;
    --accent:#2DBF95; --accent-2:#35D3A6; --accent-deep:#23A37F; --accent-soft:rgba(45,191,149,.10);
    --icon-bg:rgba(53,211,166,.07);
    --shadow:0 1px 2px rgba(0,0,0,.4);
    --err-bg:rgba(239,98,80,.10); --err-line:rgba(239,98,80,.35); --err-ink:#F2A196;
    --warn-bg:rgba(242,190,60,.10); --warn-line:rgba(242,190,60,.35); --warn-ink:#F0CF78;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  body{
    font-family:'IBM Plex Sans Thai',system-ui,sans-serif;
    background:var(--bg-grad) fixed, var(--bg); color:var(--ink); min-height:100vh;
    display:grid; grid-template-columns:1.05fr 1fr;
    transition:background .35s ease, color .35s ease;
  }

  /* ---------- ลายไอคอนการเงินด้านหลัง ---------- */
  .bg-icons{position:fixed;inset:0;z-index:0;overflow:hidden;pointer-events:none}
  .bg-icons svg{
    position:absolute; stroke:var(--accent-2); fill:none;
    stroke-width:1.2; opacity:.18; animation:float 14s ease-in-out infinite;
    transition:stroke .35s ease;
  }
  html[data-theme="dark"] .bg-icons svg{opacity:.16}
  @keyframes float{0%,100%{transform:translateY(0) rotate(0)}50%{transform:translateY(-16px) rotate(4deg)}}
  .i1{top:8%;left:5%;width:84px;height:84px;animation-delay:0s}
  .i2{top:26%;left:22%;width:54px;height:54px;animation-delay:1.4s}
  .i3{top:62%;left:8%;width:96px;height:96px;animation-delay:2.6s}
  .i4{top:84%;left:28%;width:60px;height:60px;animation-delay:.8s}
  .i5{top:14%;left:38%;width:46px;height:46px;animation-delay:3.4s}
  .i6{top:46%;left:33%;width:70px;height:70px;animation-delay:2s}
  .i7{top:10%;right:6%;width:88px;height:88px;animation-delay:1.1s}
  .i8{top:40%;right:3%;width:52px;height:52px;animation-delay:3s}
  .i9{top:72%;right:9%;width:104px;height:104px;animation-delay:.4s}
  .i10{top:90%;right:30%;width:48px;height:48px;animation-delay:2.3s}
  .i11{top:56%;right:26%;width:40px;height:40px;animation-delay:4s}
  .i12{top:4%;left:60%;width:58px;height:58px;animation-delay:1.8s}
  @media (prefers-reduced-motion:reduce){.bg-icons svg{animation:none}}

  /* ---------- ซ้าย: แบรนด์ ---------- */
  .brand{
    position:relative;z-index:1;
    padding:52px 60px; display:flex;flex-direction:column;justify-content:space-between;
    border-right:1px solid var(--line);
    background:color-mix(in srgb, var(--surface) 55%, transparent);
    backdrop-filter:blur(3px);
    transition:background .35s ease,border-color .35s ease;
  }
  .logo{display:flex;align-items:center;gap:12px}
  .logo-img{width:44px;height:44px;flex:none;object-fit:contain}
  html[data-theme="dark"] .logo-img{background:#fff;border-radius:50%}
  .logo-text{font-size:14px;font-weight:600}
  .logo-text span{display:block;font-size:12px;font-weight:400;color:var(--muted)}

  .brand-body{max-width:450px}
  .eyebrow{display:inline-flex;align-items:center;gap:7px;font-size:12px;color:var(--accent);
    font-weight:500;background:var(--accent-soft);padding:6px 12px;border-radius:99px;margin-bottom:22px}
  .dot{width:6px;height:6px;border-radius:50%;background:var(--accent-2)}
  h1{font-size:34px;line-height:1.35;font-weight:600;letter-spacing:-.3px}
  h1 em{font-style:normal;color:var(--accent)}
  .lede{margin-top:16px;color:var(--muted);font-size:15px;line-height:1.75;font-weight:300}

  .features{margin-top:28px;display:flex;flex-direction:column}
  .feature{display:flex;align-items:center;gap:14px;padding:11px 0;border-top:1px solid var(--line);font-size:14px}
  .feature:last-child{border-bottom:1px solid var(--line)}
  .feature>svg{flex:none;stroke:var(--accent-2);fill:none}
  .feature small{display:block;color:var(--muted);font-size:12px;font-weight:300;margin-top:2px}
  .brand-foot{font-size:12px;color:var(--muted);font-weight:300}

  /* ---------- ขวา: ฟอร์ม ---------- */
  .pane{position:relative;z-index:1;display:grid;place-items:center;padding:40px}
  .pane-inner{width:100%;max-width:392px}
  .m-brand{display:none;margin-bottom:20px}
  .card{
    width:100%;background:var(--surface);
    border:1px solid var(--line);border-radius:20px;padding:38px 34px;
    box-shadow:var(--shadow);transition:background .35s ease,border-color .35s ease;
  }
  .card h2{font-size:24px;font-weight:600;letter-spacing:-.2px}
  .card .sub{color:var(--muted);font-size:14px;font-weight:300;margin:6px 0 24px}

  /* แถบแจ้งเตือน */
  .alert{display:flex;gap:10px;align-items:flex-start;padding:12px 14px;border-radius:12px;
    border:1px solid var(--err-line);background:var(--err-bg);color:var(--err-ink);
    font-size:13px;line-height:1.55;margin-bottom:20px}
  .alert[hidden]{display:none}
  .alert.warning{border-color:var(--warn-line);background:var(--warn-bg);color:var(--warn-ink)}
  .alert svg{flex:none;margin-top:1px;stroke:currentColor;fill:none}
  .alert strong{display:block;font-weight:600}

  label{display:block;font-size:13px;font-weight:500;margin-bottom:8px}
  .field{position:relative;margin-bottom:18px}
  .field input{
    width:100%;height:50px;border:1px solid var(--line);border-radius:12px;
    background:var(--surface-2);padding:0 44px 0 16px;
    font-family:inherit;font-size:14px;color:var(--ink);
    transition:border-color .18s,box-shadow .18s,background .35s ease;
  }
  .field input::placeholder{color:var(--ph);font-weight:300}
  .field input:focus{outline:none;border-color:var(--accent-2);
    box-shadow:0 0 0 4px color-mix(in srgb, var(--accent-2) 16%, transparent)}
  .toggle{position:absolute;right:6px;bottom:6px;width:38px;height:38px;border:0;background:none;
    cursor:pointer;display:grid;place-items:center;border-radius:9px;color:var(--ph)}
  .toggle:hover{background:var(--accent-soft);color:var(--accent)}
  .toggle.on{color:var(--accent)}
  .toggle .ico-eye-off,.toggle.on .ico-eye{display:none}
  .toggle.on .ico-eye-off{display:block}

  .btn{width:100%;height:50px;border:0;border-radius:12px;cursor:pointer;margin-top:6px;
    background:var(--accent);color:#fff;font-family:inherit;font-size:15px;font-weight:600;
    display:flex;align-items:center;justify-content:center;gap:9px;transition:background .18s,transform .06s}
  html[data-theme="dark"] .btn{color:#07120F}
  .btn:hover{background:var(--accent-deep)}
  .btn:active{transform:translateY(1px)}

  .divider{display:flex;align-items:center;gap:14px;margin:24px 0;color:var(--ph);font-size:12px;font-weight:300}
  .divider::before,.divider::after{content:"";flex:1;height:1px;background:var(--line)}

  .btn-ghost{width:100%;height:50px;border:1px solid var(--line);border-radius:12px;cursor:pointer;
    background:var(--surface-2);color:var(--ink);font-family:inherit;font-size:14px;font-weight:500;
    display:flex;align-items:center;justify-content:center;gap:10px;text-decoration:none;
    transition:border-color .18s,background .18s}
  .btn-ghost:hover{border-color:var(--accent-2);background:var(--accent-soft)}
  .btn-ghost img{height:26px;width:auto;max-width:80px;object-fit:contain}
  html[data-theme="dark"] .btn-ghost img{background:#fff;border-radius:6px;padding:2px 5px;box-sizing:content-box}

  .is-loading{opacity:.7;pointer-events:none}
  .spin{animation:spin .7s linear infinite}
  @keyframes spin{to{transform:rotate(360deg)}}

  .foot{margin-top:26px;display:flex;justify-content:center;gap:22px;font-size:13px}
  .foot a{color:var(--muted);text-decoration:none;display:flex;align-items:center;gap:6px}
  .foot a:hover{color:var(--accent)}
  .ver{margin-top:18px;text-align:center;font-size:11px;color:var(--ph);font-weight:300}

  /* ---------- ปุ่มสลับธีม ---------- */
  .theme-btn{
    position:fixed;top:22px;right:24px;z-index:5;
    height:40px;padding:0 14px;border-radius:99px;cursor:pointer;
    border:1px solid var(--line);background:var(--surface);color:var(--ink);
    font-family:inherit;font-size:13px;font-weight:500;
    display:flex;align-items:center;gap:8px;box-shadow:var(--shadow);
    transition:border-color .18s,background .35s ease,transform .06s;
  }
  .theme-btn:hover{border-color:var(--accent-2)}
  .theme-btn:active{transform:scale(.96)}
  .theme-btn svg{stroke:var(--accent-2);fill:none;stroke-width:1.8}
  html[data-theme="dark"] .ico-moon,html[data-theme="light"] .ico-sun{display:none}

  /* SweetAlert */
  .swal2-popup{border-radius:16px !important;font-family:'IBM Plex Sans Thai',system-ui,sans-serif !important}
  .swal2-confirm{border-radius:10px !important;font-weight:600 !important}

  /* จอเตี้ย (laptop 1366×768) ให้แผงซ้ายพอดีจอ */
  @media (min-width:981px) and (max-height:820px){
    .brand{padding:28px 60px}
    .eyebrow{margin-bottom:14px}
    h1{font-size:28px}
    .lede{margin-top:10px;font-size:14px;line-height:1.65}
    .features{margin-top:18px}
    .feature{padding:7px 0;font-size:13.5px}
    .pane{padding:16px 40px}
    .card{padding:28px 34px}
    .card .sub{margin-bottom:18px}
    .divider{margin:16px 0}
    .foot{margin-top:18px}
    .ver{margin-top:12px}
  }
  @media (min-width:981px) and (max-height:700px){ .lede{display:none} }

  @media (max-width:980px){
    body{grid-template-columns:1fr}
    .brand{display:none}
    .m-brand{display:flex}
    .pane{min-height:100vh;padding:72px 24px 24px}
    .theme-btn{top:16px;right:16px}
  }
  @media (max-width:420px){
    .pane{padding:72px 16px 16px}
    .card{padding:28px 22px}
  }
</style>
</head>
<body>

<!-- ลายไอคอนการเงิน/บัญชี -->
<div class="bg-icons" aria-hidden="true">
  <svg class="i1" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 6v12M15 9.2c-.5-1-1.7-1.5-3-1.5s-2.7.6-2.7 1.9 1.3 1.7 2.7 2 3 .8 3 2.2-1.4 2.2-3 2.2-2.6-.6-3-1.6"/></svg>
  <svg class="i2" viewBox="0 0 24 24"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>
  <svg class="i3" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2.5"/><rect x="8" y="5" width="8" height="3.5" rx="1"/><circle cx="9" cy="13" r=".9"/><circle cx="12" cy="13" r=".9"/><circle cx="15" cy="13" r=".9"/><circle cx="9" cy="17" r=".9"/><circle cx="12" cy="17" r=".9"/><circle cx="15" cy="17" r=".9"/></svg>
  <svg class="i4" viewBox="0 0 24 24"><path d="M2 8h20v10a2 2 0 01-2 2H4a2 2 0 01-2-2V8zM2 8l3-4h14l3 4"/><path d="M16 14h3"/></svg>
  <svg class="i5" viewBox="0 0 24 24"><path d="M19 5L5 19"/><circle cx="7" cy="7" r="2.6"/><circle cx="17" cy="17" r="2.6"/></svg>
  <svg class="i6" viewBox="0 0 24 24"><path d="M6 2h9l5 5v15a1 1 0 01-1 1H6a1 1 0 01-1-1V3a1 1 0 011-1z"/><path d="M15 2v5h5M9 13h6M9 17h4"/></svg>
  <svg class="i7" viewBox="0 0 24 24"><ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v6c0 1.7 3.6 3 8 3s8-1.3 8-3V6M4 12v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/></svg>
  <svg class="i8" viewBox="0 0 24 24"><path d="M3 6a2 2 0 012-2h13v4M3 6v12a2 2 0 002 2h14a2 2 0 002-2V8H5a2 2 0 01-2-2z"/><circle cx="17" cy="14" r="1.4"/></svg>
  <svg class="i9" viewBox="0 0 24 24"><path d="M12 2v20M6 7h9a3 3 0 010 6H6h10a3 3 0 010 6H6"/></svg>
  <svg class="i10" viewBox="0 0 24 24"><path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z"/><path d="M9 8h6M9 12h6M9 16h3"/></svg>
  <svg class="i11" viewBox="0 0 24 24"><path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/></svg>
  <svg class="i12" viewBox="0 0 24 24"><path d="M12 3l8 4v5c0 5-3.4 8.3-8 9-4.6-.7-8-4-8-9V7l8-4z"/><path d="M9.5 12l1.8 1.8L15 10"/></svg>
</div>

<!-- ปุ่มสลับธีม -->
<button class="theme-btn" type="button" onclick="switchTheme()" aria-label="สลับโหมดสี">
  <svg class="ico-moon" width="17" height="17" viewBox="0 0 24 24"><path d="M21 13A9 9 0 1111 3a7 7 0 1010 10z"/></svg>
  <svg class="ico-sun" width="17" height="17" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
  <span id="themeLabel">โหมดมืด</span>
</button>

<!-- ซ้าย -->
<aside class="brand">
  <div class="logo">
    <img class="logo-img" src="images/logo_ck_256.png" alt="">
    <div class="logo-text"><?= h($brandName) ?><span>สำนักงานปลัดกระทรวงสาธารณสุข</span></div>
  </div>

  <div class="brand-body">
    <div class="eyebrow"><span class="dot"></span> ระบบภายในองค์กร · v1.12</div>
    <h1>ระบบบริหารจัดการ<br><em>การเงินและบัญชี</em></h1>
    <p class="lede">จัดการงานบัญชีและการเงินได้ครบถ้วนในที่เดียว รวดเร็ว โปร่งใส และปลอดภัยตามมาตรฐานกระทรวงสาธารณสุข</p>

    <div class="features">
      <div class="feature">
        <svg width="18" height="18" viewBox="0 0 24 24" stroke-width="1.8"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
        <div>บัญชีเจ้าหนี้และรายการสั่งจ่าย<small>บันทึก ตรวจสอบ และติดตามสถานะ</small></div>
      </div>
      <div class="feature">
        <svg width="18" height="18" viewBox="0 0 24 24" stroke-width="1.8"><path d="M20 6L9 17l-5-5"/></svg>
        <div>อนุมัติและเบิกจ่ายออนไลน์<small>ลดเอกสาร ลดเวลารออนุมัติ</small></div>
      </div>
      <div class="feature">
        <svg width="18" height="18" viewBox="0 0 24 24" stroke-width="1.8"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>
        <div>รายงานและสถิติรายละเอียด<small>สรุปผลแบบเรียลไทม์</small></div>
      </div>
      <div class="feature">
        <svg width="18" height="18" viewBox="0 0 24 24" stroke-width="1.8"><path d="M12 3l8 4v5c0 5-3.4 8.3-8 9-4.6-.7-8-4-8-9V7l8-4z"/></svg>
        <div>ยืนยันตัวตนด้วย Provider ID<small>ปกป้องข้อมูลการเงินขององค์กร</small></div>
      </div>
      <div class="feature">
        <svg width="18" height="18" viewBox="0 0 24 24" stroke-width="1.8"><path d="M6 8a6 6 0 0112 0c0 7 3 9 3 9H3s3-2 3-9M10.3 21a1.9 1.9 0 003.4 0"/></svg>
        <div>ระบบแจ้งเตือนสถานะการเบิกจ่าย<small>แจ้งเตือนผ่าน MOPH ALERT ทันทีเมื่อสถานะเปลี่ยน</small></div>
      </div>
      <div class="feature">
        <svg width="18" height="18" viewBox="0 0 24 24" stroke-width="1.8"><rect x="2" y="6" width="20" height="12" rx="2"/><path d="M6 14h5M15 14h3M6 10h12"/></svg>
        <div>จัดทำเช็ค<small>พิมพ์เช็คกรุงไทย ออมสิน และ ธ.ก.ส.</small></div>
      </div>
    </div>
  </div>

  <p class="brand-foot">© <?= $curYear ?> กระทรวงสาธารณสุข · สงวนสิทธิ์การใช้งานเฉพาะบุคลากร</p>
</aside>

<!-- ขวา -->
<main class="pane">
  <div class="pane-inner">
    <!-- แบรนด์แบบย่อ (มือถือ) -->
    <div class="logo m-brand">
      <img class="logo-img" src="images/logo_ck_256.png" alt="">
      <div class="logo-text"><?= h($brandName) ?><span>สำนักงานปลัดกระทรวงสาธารณสุข</span></div>
    </div>

    <div class="card">
      <h2>เข้าสู่ระบบ</h2>
      <p class="sub">กรอกชื่อผู้ใช้งานและรหัสผ่านของท่านเพื่อดำเนินการต่อ</p>

      <div class="alert<?= ($alert && $alert['type'] === 'warning') ? ' warning' : '' ?>" id="formAlert" role="alert"<?= $alert ? '' : ' hidden' ?>>
        <svg width="18" height="18" viewBox="0 0 24 24" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7.5v5.5M12 16.5h.01"/></svg>
        <div><strong id="alertTitle"><?= h($alert['title'] ?? '') ?></strong><span id="alertText"><?= h($alert['text'] ?? '') ?></span></div>
      </div>

      <form method="post" action="login_process.php" id="loginForm" novalidate>
        <div class="field">
          <label for="Username">ชื่อผู้ใช้งาน</label>
          <input id="Username" name="Username" type="text" value="<?= h($oldUser) ?>" placeholder="กรอกชื่อผู้ใช้งาน" autocomplete="username" required>
        </div>

        <div class="field">
          <label for="Password">รหัสผ่าน</label>
          <input id="Password" name="Password" type="password" placeholder="กรอกรหัสผ่าน" autocomplete="current-password" required>
          <button class="toggle" id="toggleEye" type="button" aria-label="แสดงรหัสผ่าน" aria-pressed="false">
            <svg class="ico-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="ico-eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 3l18 18M10.6 5.1A10.4 10.4 0 0112 5c6.4 0 10 7 10 7a17.6 17.6 0 01-3.2 4.1M6.6 6.6C3.8 8.4 2 12 2 12s3.6 7 10 7c1.8 0 3.4-.5 4.8-1.3M9.9 9.9a3 3 0 004.2 4.2"/></svg>
          </button>
        </div>

        <button class="btn" type="submit" id="loginBtn">เข้าสู่ระบบ
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
      </form>

      <div class="divider">หรือเข้าสู่ระบบด้วย</div>
      <a class="btn-ghost" id="btnProviderLogin" href="<?= h($providerLoginUrl) ?>">
        <img src="pic/provider-id.png" alt=""> <span>เข้าสู่ระบบด้วย Provider ID</span>
      </a>

      <div class="foot">
        <a href="#" onclick="showHelp(); return false;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 113.5 2.3c-.6.3-1 .9-1 1.7M12 17h.01"/></svg> ช่วยเหลือ</a>
        <a href="#" onclick="showContact(); return false;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v12H7l-3 3V5z"/></svg> ติดต่อเจ้าหน้าที่ IT</a>
      </div>

      <p class="ver">การเชื่อมต่อเข้ารหัส HTTPS · version 1.12</p>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
<script>
var SPINNER = '<svg class="spin" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 11-6.2-8.6"/></svg>';

// ---------- ธีม ----------
function setTheme(t){
  document.documentElement.setAttribute('data-theme', t);
  document.getElementById('themeLabel').textContent = (t === 'dark') ? 'โหมดสว่าง' : 'โหมดมืด';
}
function switchTheme(){
  var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
  setTheme(next);
  try { localStorage.setItem('fin-theme', next); } catch (e) {}
}
setTheme(document.documentElement.getAttribute('data-theme'));

// ---------- แสดง/ซ่อนรหัสผ่าน ----------
document.getElementById('toggleEye').addEventListener('click', function(){
  var inp = document.getElementById('Password');
  var show = inp.type === 'password';
  inp.type = show ? 'text' : 'password';
  this.classList.toggle('on', show);
  this.setAttribute('aria-pressed', show ? 'true' : 'false');
  this.setAttribute('aria-label', show ? 'ซ่อนรหัสผ่าน' : 'แสดงรหัสผ่าน');
});

// ---------- แถบแจ้งเตือน ----------
function showAlert(type, title, text){
  var el = document.getElementById('formAlert');
  el.classList.toggle('warning', type === 'warning');
  document.getElementById('alertTitle').textContent = title;
  document.getElementById('alertText').textContent = text;
  el.hidden = false;
}

// ---------- ส่งฟอร์ม ----------
document.getElementById('loginForm').addEventListener('submit', function(e){
  var u = document.getElementById('Username');
  var p = document.getElementById('Password');
  if (!u.value.trim() || !p.value) {
    e.preventDefault();
    showAlert('warning', 'กรุณากรอกข้อมูล', 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน');
    (u.value.trim() ? p : u).focus();
    return;
  }
  var btn = document.getElementById('loginBtn');
  btn.classList.add('is-loading');
  btn.innerHTML = SPINNER + ' กำลังเข้าสู่ระบบ...';
});

// ---------- Provider ID ----------
document.getElementById('btnProviderLogin').addEventListener('click', function(e){
  // เปิดในแท็บใหม่ (Ctrl/Cmd/Shift/กลางเมาส์) ไม่ต้องล็อกปุ่มในแท็บนี้
  if (e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;
  this.classList.add('is-loading');
  this.innerHTML = SPINNER + '<span>กำลังเชื่อมต่อ Health ID...</span>';
});

// กลับมาหน้านี้ด้วยปุ่ม Back (bfcache) ให้ปุ่มกลับเป็นปกติ
window.addEventListener('pageshow', function(e){
  if (e.persisted) window.location.reload();
});

// ---------- Auto focus ----------
(function(){
  var u = document.getElementById('Username');
  if (u.value) document.getElementById('Password').focus();
  else u.focus();
})();

<?php if ($alert): ?>
// ล้าง query string เพื่อไม่ให้ refresh แล้วแจ้งเตือนซ้ำ
if (window.history && history.replaceState) history.replaceState(null, '', 'login.php');
<?php endif; ?>

// ---------- ช่วยเหลือ ----------
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

// ---------- ติดต่อ IT ----------
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
</script>
</body>
</html>
