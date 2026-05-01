<?php
// finance.php
if (!ob_get_level()) { ob_start(); }
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Bangkok');

$__header_html = '';
ob_start();
require_once __DIR__ . '/header.php';
$__header_html = ob_get_clean();

require_once __DIR__ . '/connect_db.php';
if (!isset($conn) || !($conn instanceof mysqli)) { $conn = null; }
if ($conn) { @mysqli_set_charset($conn, 'utf8mb4'); }

/* ========= Helpers ========= */
if (!function_exists('h')) {
  function h($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
  }
}

function thaiDateShort(?string $ymd): string {
  if (!$ymd || $ymd === '0000-00-00') return '-';
  $ts = strtotime($ymd);
  if (!$ts) return '-';
  $y = (int)date('Y', $ts) + 543;
  $m = (int)date('n', $ts);
  $d = (int)date('j', $ts);
  $mcut = ["","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค."];
  return $d . "&nbsp;&nbsp;" . ($mcut[$m] ?? '') . "&nbsp;&nbsp;" . $y;
}

function buildQuery(array $extra = [], array $base = null): string {
  $q = $base ?? ($_GET ?? []);
  foreach ($extra as $k => $v) $q[$k] = $v;
  return http_build_query($q);
}

function bindParams(mysqli_stmt $stmt, string $types, array $values): bool {
  $bind = [];
  $bind[] = &$types;
  foreach ($values as $k => $v) { $bind[] = &$values[$k]; }
  return call_user_func_array([$stmt, 'bind_param'], $bind);
}

/* ========= SweetAlert from query ========= */
$sw       = strtolower(trim($_GET['sw'] ?? ''));
$swMsg    = trim($_GET['msg'] ?? '');
$swRedirect = trim($_GET['redirect'] ?? '');

/* ========= Inputs ========= */
$Keyword  = trim($_GET['Keyword'] ?? '');
$TypeKey  = (int)($_GET['TypeKey'] ?? 0);
$Keyword2 = trim($_GET['Keyword2'] ?? '');
$TypeKey2 = (int)($_GET['TypeKey2'] ?? 0);
$pn       = max(1, (int)($_GET['pn'] ?? 1));
$pn2      = max(1, (int)($_GET['pn2'] ?? 1));

/* ========= Prefetch dropdown data ========= */
$db_ok     = (bool)$conn;
$banks     = [];
$typebs    = [];
$employees = [];

if ($db_ok) {
  $stmt = $conn->prepare("SELECT BankId, BankName FROM bank ORDER BY BankName");
  $stmt->execute();
  $rs = $stmt->get_result();
  while ($rs && ($r = $rs->fetch_assoc())) $banks[] = $r;
  $stmt->close();

  $stmt = $conn->prepare("SELECT TypebId, TypebName FROM typeb ORDER BY TypebId");
  $stmt->execute();
  $rs = $stmt->get_result();
  while ($rs && ($r = $rs->fetch_assoc())) $typebs[] = $r;
  $stmt->close();

  $stmt = $conn->prepare("SELECT Username, Names FROM employee ORDER BY Names");
  $stmt->execute();
  $rs = $stmt->get_result();
  while ($rs && ($r = $rs->fetch_assoc())) $employees[] = $r;
  $stmt->close();
}

/* ========= LIST #1 ========= */
$today   = date('Y-m-d');
$where1  = [];
$params1 = [];
$types1  = '';

$joinCompany = " LEFT JOIN company c ON p.CompanyId = c.CompanyId ";

$where1[]  = "(p.DateApprove IS NULL OR p.DateApprove='' OR p.DateApprove='0000-00-00' OR p.DateApprove=?)";
$types1   .= "s";
$params1[] = $today;

if ($Keyword !== '') {
  switch ($TypeKey) {
    case 1:
      $where1[]  = "c.CompanyName LIKE ?";
      $types1   .= "s";
      $params1[] = "%{$Keyword}%";
      break;
    case 2:
      $where1[]  = "p.Detail LIKE ?";
      $types1   .= "s";
      $params1[] = "%{$Keyword}%";
      break;
    case 3:
    default:
      $where1[]  = "p.PayId = ?";
      $types1   .= "i";
      $params1[] = (int)$Keyword;
      break;
  }
}

$whereSql1 = $where1 ? (" WHERE " . implode(" AND ", $where1)) : "";

$total1 = 0;
if ($db_ok) {
  $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM payment p {$joinCompany} {$whereSql1}");
  bindParams($stmt, $types1, $params1);
  $stmt->execute();
  $rs     = $stmt->get_result();
  $total1 = (int)(($rs ? $rs->fetch_assoc() : [])['cnt'] ?? 0);
  $stmt->close();
}

$page_rows1 = 10;
$last1      = max(1, (int)ceil($total1 / $page_rows1));
if ($pn > $last1) $pn = $last1;
$offset1    = ($pn - 1) * $page_rows1;

$list1 = [];
if ($db_ok && $total1 > 0) {
  $sqlD1 = "SELECT p.PayId, p.DateApprove, p.Detail, p.CompanyId, p.Price, p.Amount, p.ReceiveNo, c.CompanyName
            FROM payment p {$joinCompany} {$whereSql1}
            ORDER BY p.DateApprove ASC, p.PayId DESC LIMIT ?, ?";
  $stmt = $conn->prepare($sqlD1);
  bindParams($stmt, $types1 . "ii", array_merge($params1, [$offset1, $page_rows1]));
  $stmt->execute();
  $rs = $stmt->get_result();
  while ($rs && ($r = $rs->fetch_assoc())) $list1[] = $r;
  $stmt->close();
}

// Pagination 1
$pagination1 = '';
if ($last1 > 1) {
  $pagination1 = '<nav><ul class="pagination" style="margin:8px 0;">';
  if ($pn > 1) {
    $pagination1 .= '<li><a href="?' . h(buildQuery(['pn'=>1])) . '">&laquo;&laquo;</a></li>';
    $pagination1 .= '<li><a href="?' . h(buildQuery(['pn'=>$pn-1])) . '">&laquo;</a></li>';
  }
  for ($i = max(1,$pn-2); $i <= min($last1,$pn+2); $i++) {
    $act = ($i===$pn) ? ' class="active"' : '';
    $pagination1 .= '<li'.$act.'><a href="?'.h(buildQuery(['pn'=>$i])).'">'.  $i.'</a></li>';
  }
  if ($pn < $last1) {
    $pagination1 .= '<li><a href="?' . h(buildQuery(['pn'=>$pn+1])) . '">&raquo;</a></li>';
    $pagination1 .= '<li><a href="?' . h(buildQuery(['pn'=>$last1])) . '">&raquo;&raquo;</a></li>';
  }
  $pagination1 .= '</ul></nav>';
}

/* ========= LIST #2 ========= */
$dateNow  = date('Y-m-d');
$datePass = date('Y-m-d', strtotime('-30 days'));

$where2  = [];
$params2 = [];
$types2  = '';

$where2[]  = "(p.DateApprove IS NOT NULL AND p.DateApprove<>'' AND p.DateApprove<>'0000-00-00')";
$where2[]  = "(p.DateApprove BETWEEN ? AND ?)";
$types2   .= "ss";
$params2[] = $datePass;
$params2[] = $dateNow;

if ($Keyword2 !== '') {
  if ($TypeKey2 === 1) {
    $where2[]  = "p.PayId = ?";
    $types2   .= "i";
    $params2[] = (int)$Keyword2;
  } else {
    $where2[]  = "p.ReceiveNo = ?";
    $types2   .= "s";
    $params2[] = $Keyword2;
  }
}

$whereSql2 = $where2 ? (" WHERE " . implode(" AND ", $where2)) : "";

$total2 = 0;
if ($db_ok) {
  $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM (SELECT p.ReceiveNo FROM payment p {$whereSql2} GROUP BY p.ReceiveNo) x");
  bindParams($stmt, $types2, $params2);
  $stmt->execute();
  $rs     = $stmt->get_result();
  $total2 = (int)(($rs ? $rs->fetch_assoc() : [])['cnt'] ?? 0);
  $stmt->close();
}

$page_rows2 = 20;
$last2      = max(1, (int)ceil($total2 / $page_rows2));
if ($pn2 > $last2) $pn2 = $last2;
$offset2    = ($pn2 - 1) * $page_rows2;

$list2 = [];
if ($db_ok && $total2 > 0) {
  $sqlD2 = "SELECT p.ReceiveNo, MAX(p.DateApprove) AS DateApprove, MAX(p.CompanyId) AS CompanyId, SUM(p.Amount) AS Amounts
            FROM payment p {$whereSql2} GROUP BY p.ReceiveNo ORDER BY MAX(p.DateApprove) DESC LIMIT ?, ?";
  $stmt = $conn->prepare($sqlD2);
  bindParams($stmt, $types2 . "ii", array_merge($params2, [$offset2, $page_rows2]));
  $stmt->execute();
  $rs = $stmt->get_result();
  while ($rs && ($r = $rs->fetch_assoc())) $list2[] = $r;
  $stmt->close();
}

// Pagination 2
$base2 = $_GET ?? [];
$pagination2 = '';
if ($last2 > 1) {
  $pagination2 = '<nav><ul class="pagination" style="margin:8px 0;">';
  if ($pn2 > 1) {
    $pagination2 .= '<li><a href="?' . h(buildQuery(['pn2'=>1], $base2)) . '">&laquo;&laquo;</a></li>';
    $pagination2 .= '<li><a href="?' . h(buildQuery(['pn2'=>$pn2-1], $base2)) . '">&laquo;</a></li>';
  }
  for ($i = max(1,$pn2-2); $i <= min($last2,$pn2+2); $i++) {
    $act = ($i===$pn2) ? ' class="active"' : '';
    $pagination2 .= '<li'.$act.'><a href="?'.h(buildQuery(['pn2'=>$i], $base2)).'">'.$i.'</a></li>';
  }
  if ($pn2 < $last2) {
    $pagination2 .= '<li><a href="?' . h(buildQuery(['pn2'=>$pn2+1], $base2)) . '">&raquo;</a></li>';
    $pagination2 .= '<li><a href="?' . h(buildQuery(['pn2'=>$last2], $base2)) . '">&raquo;&raquo;</a></li>';
  }
  $pagination2 .= '</ul></nav>';
}

// CompanyMap for list2
$companyMap = [];
if ($db_ok) {
  $needIds = [];
  foreach ($list2 as $r) { $cid = (int)($r['CompanyId'] ?? 0); if ($cid > 0) $needIds[$cid] = true; }
  if ($needIds) {
    $ids = array_keys($needIds);
    $ph  = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $conn->prepare("SELECT CompanyId, CompanyName FROM company WHERE CompanyId IN ($ph)");
    bindParams($stmt, str_repeat('i', count($ids)), $ids);
    $stmt->execute();
    $rs = $stmt->get_result();
    while ($rs && ($r = $rs->fetch_assoc())) $companyMap[(int)$r['CompanyId']] = (string)$r['CompanyName'];
    $stmt->close();
  }
}

/* ========= Build option HTML (reuse in JS) ========= */
$bankOptions = '';
foreach ($banks as $b) {
  $bankOptions .= '<option value="'.(int)$b['BankId'].'">'.h($b['BankName']).'</option>';
}
$typebOptions = '';
foreach ($typebs as $tb) {
  $typebOptions .= '<option value="'.(int)$tb['TypebId'].'">'.h((int)$tb['TypebId'].'. '.$tb['TypebName']).'</option>';
}
$empOptions = '';
foreach ($employees as $em) {
  $empOptions .= '<option value="'.h($em['Username']).'">'.h($em['Names']).'</option>';
}

// Stats for summary cards
$statPending = $total1;  // รออนุมัติ/อนุมัติวันนี้
$statApproved = $total2; // อนุมัติแล้ว (30 วัน)
$statAmtPend = 0.0; $statAmtApproved = 0.0;
if ($db_ok) {
  $rr = $conn->query("SELECT COALESCE(SUM(Amount),0) AS s FROM payment WHERE (DateApprove IS NULL OR DateApprove='' OR DateApprove='0000-00-00')");
  if ($rr && ($rw = $rr->fetch_assoc())) $statAmtPend = (float)$rw['s'];
  $d30 = date('Y-m-d', strtotime('-30 days'));
  $rr2 = $conn->query("SELECT COALESCE(SUM(Amount),0) AS s FROM payment WHERE DateApprove BETWEEN '$d30' AND '".date('Y-m-d')."'");
  if ($rr2 && ($rw2 = $rr2->fetch_assoc())) $statAmtApproved = (float)$rw2['s'];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" type="image/x-icon" href="pic/fms.png"/>
  <title>ระบบบริหารจัดการการเงินและบัญชี</title>

  <!-- Bootstrap Icons & Flatpickr (Bootstrap 5, SweetAlert, theme loaded by header.php) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">

  <style>
    body {
      font-family: 'Sarabun', sans-serif;
      background:
        radial-gradient(1100px 600px at 12% 15%, rgba(91,155,213,.22), transparent 60%),
        radial-gradient(900px 520px at 92% 10%, rgba(0,176,80,.14), transparent 55%),
        linear-gradient(180deg, #f8fbff, #f6f8fc);
    }
    .container { max-width: 1200px; }

    /* ─── Page Title ─── */
    .page-titlebar {
      margin: 14px 0 16px;
      border-radius: 18px;
      padding: 14px 16px;
      background: rgba(255,255,255,.88);
      border: 1px solid #e9eef6;
      box-shadow: 0 12px 30px rgba(13,27,62,.08);
      text-align: center;
    }
    .page-titlebar h3 { margin: 2px 0 0; font-weight: 800; color: #1f2a44; font-size: 20px; }
    .page-titlebar .sub { color: #6b778c; margin-top: 6px; font-size: 13px; }

    /* ─── Card ─── */
    .card-panel {
      border-radius: 18px;
      border: 1px solid #e9eef6;
      background: rgba(255,255,255,.92);
      box-shadow: 0 12px 30px rgba(13,27,62,.08);
      overflow: hidden;
      margin-bottom: 14px;
    }
    .card-head {
      padding: 12px 14px;
      border-bottom: 1px solid #e9eef6;
      font-weight: 800;
      color: #1f2a44;
      background: linear-gradient(135deg, rgba(0,176,80,.16), rgba(0,176,80,.06));
    }
    .card-body { padding: 14px 16px; }


    /* ─── Form ─── */
    .form-control {
      height: 42px;
      border-radius: 12px;
      border: 1px solid #dfe7f3;
      box-shadow: none;
      font-family: 'Sarabun', sans-serif;
      font-size: 14px;
      transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus {
      border-color: rgba(0,176,80,.55);
      box-shadow: 0 0 0 3px rgba(0,176,80,.18);
    }
    select.form-control {
      appearance: none;
      -webkit-appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b778c' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
      padding-right: 36px;
    }
    .control-label { font-weight: 700; color: #1f2a44; font-size: 13px; }

    /* ─── Buttons ─── */
    .btn {
      border-radius: 12px;
      font-weight: 700;
      font-family: 'Sarabun', sans-serif;
      padding: 9px 14px;
      transition: transform .15s, box-shadow .15s;
    }
    .btn:active { transform: scale(.96); }
    .btn-success { box-shadow: 0 10px 22px rgba(0,176,80,.18); }
    .btn-primary { box-shadow: 0 10px 22px rgba(13,110,253,.18); }
    .btn-action {
      width: 36px; height: 36px; padding: 0;
      display: inline-flex; align-items: center; justify-content: center;
    }
    .btn-action:hover { transform: translateY(-1px); }

    /* ─── Tables ─── */
    .table {
      background: #fff;
      border-radius: 14px;
      overflow: hidden;
      margin-bottom: 8px;
    }
    .table thead th {
      background: #e9f7ee;
      color: #1f2a44;
      font-weight: 800;
      border-bottom: 1px solid #d6f0df !important;
      vertical-align: middle !important;
    }
    .table td { vertical-align: middle !important; }

    /* ── Table Row Hover ── */
    .table tbody tr {
      transition: background .18s ease, transform .18s ease, box-shadow .18s ease;
    }
    .table tbody tr:hover {
      background: linear-gradient(90deg, #eef7ff, #f4f9ff) !important;
      transform: scale(1.005);
      box-shadow: 0 4px 14px rgba(0,176,80,.22);
      position: relative;
      z-index: 2;
    }
    .table tbody tr:hover td:first-child {
      border-left: 3px solid #00b050;
      border-radius: 8px 0 0 8px;
    }
    .table tbody tr:hover td:last-child {
      border-radius: 0 8px 8px 0;
    }
    .table tbody tr:hover .badge-pill { transform: scale(1.05); }

    /* ─── Badges ─── */
    .badge-pill {
      display: inline-block;
      padding: 6px 10px;
      border-radius: 999px;
      font-weight: 800;
      font-size: 12px;
      border: 1px solid #e2e8f0;
      background: #f8fafc;
      color: #334155;
      transition: transform .15s;
    }
    .bd-wait { background: #fff7ed; border-color: #fed7aa; color: #9a3412; }
    .bd-ok   { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }

    /* ─── Pagination ─── */
    .pagination > li > a, .pagination > li > span {
      border-radius: 10px !important;
      margin: 0 4px;
      border: 1px solid #e2e8f0;
      color: #1f2a44;
      transition: background .2s;
    }
    .pagination > .active > a { background: #00b050; border-color: #00b050; }
    .pagination > li > a:hover { background: #d6f0df; border-color: #00b050; }

    .help-note { color: #6b778c; font-size: 12px; margin-top: 8px; }

    /* ─── SweetAlert2 Tweaks ─── */
    .swal2-popup         { border-radius: 20px; font-family: 'Sarabun', sans-serif; }
    .swal2-title         { color: #1f2a44; font-weight: 800; }
    .swal2-confirm       { border-radius: 12px !important; font-weight: 700; }
    .swal2-cancel        { border-radius: 12px !important; font-weight: 700; }

    /* ─── Swal Form Styles ─── */
    .swal-form .form-group { margin-bottom: 14px; text-align: left; }
    .swal-form .form-group label {
      font-weight: 700; color: #1f2a44; font-size: 13px;
      display: block; margin-bottom: 5px;
    }
    .swal-form select, .swal-form input {
      width: 100%; height: 40px;
      border-radius: 10px;
      border: 1px solid #dfe7f3;
      font-family: 'Sarabun', sans-serif;
      font-size: 14px;
      padding: 0 12px;
      box-sizing: border-box;
      appearance: none; -webkit-appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b778c' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 10px center;
      padding-right: 32px;
    }
    .swal-form select:focus, .swal-form input:focus {
      outline: none;
      border-color: #00b050;
      box-shadow: 0 0 0 3px rgba(0,176,80,.18);
    }
    .swal-form input[type="number"] {
      background-image: none;
      padding-right: 12px;
    }
  </style>
</head>

<body>
  <?php echo $__header_html; ?>

  <div class="container">
    <div class="page-titlebar" style="display:flex; align-items:center; justify-content:space-between; gap:12px; text-align:left;">
      <div>
        <h3 style="margin:0; display:flex; align-items:center; gap:8px;">
          <span class="msi msi-24" style="color:#0B6E4F;">payments</span>
          ขออนุมัติรายการจ่าย
        </h3>
        <div class="sub">ค้นหารายการรออนุมัติ/อนุมัติวันนี้ และจัดพิมพ์หนังสือขออนุมัติย้อนหลัง 30 วัน</div>
      </div>
      <a href="main.php" class="btn-go-back">
        <span class="msi">arrow_back</span> กลับหน้าหลัก
      </a>
    </div>

    <!-- Stats Cards -->
    <div class="stats-row">
      <div class="stat-card stat-orange">
        <div class="stat-icon"><span class="msi msi-24">pending</span></div>
        <div class="stat-body">
          <div class="stat-label">รออนุมัติ/วันนี้</div>
          <div class="stat-value"><?= number_format($statPending) ?></div>
          <div class="stat-sub">รายการ</div>
        </div>
      </div>
      <div class="stat-card stat-red">
        <div class="stat-icon"><span class="msi msi-24">account_balance_wallet</span></div>
        <div class="stat-body">
          <div class="stat-label">ยอดรออนุมัติ</div>
          <div class="stat-value"><?= number_format($statAmtPend, 2) ?></div>
          <div class="stat-sub">บาท</div>
        </div>
      </div>
      <div class="stat-card stat-green">
        <div class="stat-icon"><span class="msi msi-24">check_circle</span></div>
        <div class="stat-body">
          <div class="stat-label">อนุมัติแล้ว (30 วัน)</div>
          <div class="stat-value"><?= number_format($statApproved) ?></div>
          <div class="stat-sub">ใบสั่งจ่าย</div>
        </div>
      </div>
      <div class="stat-card stat-blue">
        <div class="stat-icon"><span class="msi msi-24">account_balance</span></div>
        <div class="stat-body">
          <div class="stat-label">ยอดอนุมัติ (30 วัน)</div>
          <div class="stat-value"><?= number_format($statAmtApproved, 2) ?></div>
          <div class="stat-sub">บาท</div>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="card-panel">
      <div class="card-body" style="padding-bottom:0;">
        <ul class="nav-tabs-modern">
          <li><a href="receive.php"><i class="bi bi-inbox"></i> ลงรับเอกสาร</a></li>
          <li class="active"><a href="finance.php"><i class="bi bi-check2-circle"></i> ขออนุมัติ</a></li>
          <li><a href="cheque.php"><i class="bi bi-credit-card"></i> จัดทำเช็ค</a></li>
          <li><a href="printcheque.php"><i class="bi bi-printer"></i> พิมพ์เช็ค</a></li>
          <li><a href="control.php"><i class="bi bi-journal-text"></i> ทะเบียนคุม</a></li>
          <li><a href="paidment.php"><i class="bi bi-book"></i> ใบสำคัญ</a></li>
          <li><a href="paid.php"><i class="bi bi-check-square"></i> ตัดจ่ายเช็ค</a></li>
          <li><a href="daily.php"><i class="bi bi-calendar3"></i> รายงานประจำวัน</a></li>
          <li><a href="findpay.php"><i class="bi bi-search"></i> ค้นหารายการ</a></li>
        </ul>
      </div>
    </div>

    <!-- Search list1 -->
    <div class="card-panel">
      <div class="card-head">
        <i class="bi bi-search"></i> ค้นหารายการอนุมัติ (รออนุมัติ/อนุมัติวันนี้)
      </div>
      <div class="card-body">
        <form id="formSearch1" method="get" action="<?= h($_SERVER['PHP_SELF']) ?>">
          <div class="row" style="margin:0;">
            <div class="col-md-3" style="padding-left:0;">
              <label style="font-weight:800;color:#1f2a44;">ค้นด้วย</label>
              <select class="form-control" name="TypeKey" id="TypeKey">
                <option value="0">เลือกประเภทการค้น</option>
                <option value="1" <?= $TypeKey===1?'selected':''; ?>>บริษัท/ร้านค้า</option>
                <option value="2" <?= $TypeKey===2?'selected':''; ?>>เลขที่ใบส่งของ</option>
                <option value="3" <?= $TypeKey===3?'selected':''; ?>>รหัสรายการ</option>
              </select>
            </div>
            <div class="col-md-7">
              <label style="font-weight:800;color:#1f2a44;">คำค้น</label>
              <input class="form-control" name="Keyword" id="Keyword" type="text"
                     value="<?= h($Keyword) ?>" placeholder="ระบุคำค้น...">
              <div class="help-note">* หากไม่กรอกคำค้น ระบบจะแสดงเฉพาะรายการ "รออนุมัติ/อนุมัติวันนี้"</div>
            </div>
            <div class="col-md-2" style="padding-right:0; padding-top:26px;">
              <button type="submit" class="btn btn-success" style="width:100%;">
                <i class="bi bi-search"></i> ค้นหา
              </button>
            </div>
          </div>
          <input type="hidden" name="pn"       value="1">
          <input type="hidden" name="TypeKey2" value="<?= (int)$TypeKey2 ?>">
          <input type="hidden" name="Keyword2" value="<?= h($Keyword2) ?>">
          <input type="hidden" name="pn2"      value="<?= (int)$pn2 ?>">
        </form>
      </div>
    </div>

    <!-- List1 -->
    <div class="card-panel">
      <div class="card-head">
        <i class="bi bi-list-ul"></i> รายการอนุมัติ
        <span style="font-weight:600;color:#64748b;">(ทั้งหมด <?= (int)$total1 ?> รายการ)</span>
      </div>
      <div class="card-body">
        <?php if (!$db_ok): ?>
          <div class="alert alert-danger" style="border-radius:14px;">ไม่สามารถเชื่อมต่อฐานข้อมูลได้</div>
        <?php elseif ($total1 <= 0): ?>
          <div class="alert alert-warning" style="border-radius:14px;text-align:center;">
            <i class="bi bi-info-circle"></i> ไม่พบข้อมูล
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th style="width:80px;  text-align:center;">รหัสรายการ</th>
                  <th style="width:160px; text-align:center;">วันที่อนุมัติ</th>
                  <th style="width:260px; text-align:center;">เลขที่ใบส่งของ</th>
                  <th>รายการ/บริษัท</th>
                  <th style="width:140px; text-align:right;">จำนวนเงิน</th>
                  <th style="width:110px; text-align:center;">ขออนุมัติ</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($list1 as $r): ?>
                <?php
                  $PayId       = (int)($r['PayId'] ?? 0);
                  $DateApprove = (string)($r['DateApprove'] ?? '');
                  $Detail      = (string)($r['Detail'] ?? '');
                  $CompanyName = (string)($r['CompanyName'] ?? '');
                  $Price       = (float)($r['Price'] ?? 0);
                  $Amount      = (float)($r['Amount'] ?? 0);
                  $isApproved  = ($DateApprove && $DateApprove !== '0000-00-00');
                  $badge       = $isApproved
                    ? '<span class="badge-pill bd-ok">'.thaiDateShort($DateApprove).'</span>'
                    : '<span class="badge-pill bd-wait">รออนุมัติ</span>';
                ?>
                <tr>
                  <td style="text-align:center;"><?= $PayId ?></td>
                  <td style="text-align:center;"><?= $badge ?></td>
                  <td style="text-align:center;"><?= h($Detail) ?></td>
                  <td><?= h($CompanyName) ?></td>
                  <td style="text-align:right;"><?= number_format($Amount, 2) ?></td>
                  <td style="text-align:center;">
                    <button type="button" class="btn btn-success btn-action"
                      onclick="openApprove(<?= $PayId ?>, <?= number_format($Price,2,'.','') ?>, <?= number_format($Amount,2,'.','') ?>)"
                      title="ขออนุมัติ">
                      <i class="bi bi-check2-circle"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div style="text-align:center; margin-top:6px;"><?= $pagination1 ?></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- List2 -->
    <div class="card-panel">
      <div class="card-head">
        <i class="bi bi-list-ul"></i> รายการพิมพ์หนังสือขออนุมัติ (ย้อนหลัง 30 วัน)
        <span style="font-weight:600;color:#64748b;">(ทั้งหมด <?= (int)$total2 ?> เลขรับ)</span>
      </div>
      <div class="card-body">
        <div style="text-align:right; margin-bottom:10px;">
          <button type="button" class="btn btn-success" onclick="openSearch2()" title="ค้นหนังสือขออนุมัติ">
            <i class="bi bi-search"></i> ค้นหนังสือขออนุมัติ
          </button>
        </div>

        <?php if (!$db_ok): ?>
          <div class="alert alert-danger" style="border-radius:14px;">ไม่สามารถเชื่อมต่อฐานข้อมูลได้</div>
        <?php elseif ($total2 <= 0): ?>
          <div class="alert alert-warning" style="border-radius:14px;text-align:center;">
            <i class="bi bi-info-circle"></i> ไม่พบข้อมูล
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th style="width:220px; text-align:center;">เลขที่รับเอกสาร</th>
                  <th style="width:160px; text-align:center;">วันที่อนุมัติ</th>
                  <th>รายการ/บริษัท</th>
                  <th style="width:150px; text-align:right;">จำนวนเงิน</th>
                  <th style="width:110px; text-align:center;">พิมพ์</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($list2 as $r): ?>
                <?php
                  $ReceiveNo   = (string)($r['ReceiveNo'] ?? '');
                  $DateApprove2 = (string)($r['DateApprove'] ?? '');
                  $CompanyId2  = (int)($r['CompanyId'] ?? 0);
                  $Amounts     = (float)($r['Amounts'] ?? 0);
                  $CompanyName2 = $companyMap[$CompanyId2] ?? '-';
                ?>
                <tr>
                  <td style="text-align:center;"><?= h($ReceiveNo) ?></td>
                  <td style="text-align:center;"><?= thaiDateShort($DateApprove2) ?></td>
                  <td><?= h($CompanyName2) ?></td>
                  <td style="text-align:right;"><?= number_format($Amounts, 2) ?></td>
                  <td style="text-align:center;">
                    <button type="button" class="btn btn-primary btn-action"
                      onclick="openPrint('<?= addslashes(h($ReceiveNo)) ?>')"
                      title="พิมพ์หนังสือ">
                      <i class="bi bi-printer"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div style="text-align:center; margin-top:6px;"><?= $pagination2 ?></div>
        <?php endif; ?>
      </div>
    </div>

    <div style="height:16px;"></div>
  </div>

  <!-- Scripts -->
  <!-- jQuery, Bootstrap 5 JS, SweetAlert loaded by header.php -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

  <script>
  // ─── Option HTML (from PHP) ───
  var bankOptions = '<?= addslashes($bankOptions) ?>';
  var typebOptions = '<?= addslashes($typebOptions) ?>';
  var empOptions = '<?= addslashes($empOptions) ?>';

  $(function(){
    // ─── SweetAlert from query (sw=success|error) ───
    var sw       = <?= json_encode($sw) ?>;
    var swMsg    = <?= json_encode($swMsg) ?>;
    var swRedir  = <?= json_encode($swRedirect) ?>;

    if (sw === 'success' || sw === 'error') {
      Swal.fire({
        icon: sw,
        title: sw === 'success' ? 'สำเร็จ' : 'ไม่สำเร็จ',
        text: swMsg || (sw === 'success' ? 'ดำเนินการเรียบร้อย' : 'เกิดข้อผิดพลาด'),
        confirmButtonText: 'ตกลง',
        confirmButtonColor: '#00b050'
      }).then(function(){
        if (swRedir) { window.location.href = swRedir; return; }
        var url = new URL(window.location.href);
        url.searchParams.delete('sw');
        url.searchParams.delete('msg');
        url.searchParams.delete('redirect');
        window.history.replaceState({}, '', url.toString());
      });
    }
  });

  // ─── Modal: ขออนุมัติ ───
  function openApprove(payId, price, amount) {
    Swal.fire({
      title: 'ขออนุมัติรายการ #' + payId,
      showConfirmButton: false,
      showCloseButton: true,
      width: '480px',
      customClass: { popup: 'swal-approve-popup' },
      html:
        '<div class="swal-form" style="padding:0 10px;">' +
          '<div class="form-group">' +
            '<label>เลือกธนาคาร/เงินสด</label>' +
            '<select id="swal-bank" class="form-control">' +
              '<option value="">-- เลือกธนาคาร/เงินสด --</option>' + bankOptions +
            '</select>' +
          '</div>' +
          '<div class="form-group">' +
            '<label>จ่ายจากเงิน (หมวดงบ)</label>' +
            '<select id="swal-typeb" class="form-control">' +
              '<option value="">-- เลือกหมวดงบ --</option>' + typebOptions +
            '</select>' +
          '</div>' +
          '<div class="form-group">' +
            '<label>หักภาษี (%)</label>' +
            '<input type="number" step="0.01" min="0" id="swal-percent" class="form-control" placeholder="เช่น 1, 3, 5, 10">' +
          '</div>' +
          '<div style="display:flex; gap:10px; justify-content:center; margin-top:20px;">' +
            '<button class="btn btn-success" onclick="submitApprove(' + payId + ',' + price + ',' + amount + ')">' +
              '<i class="bi bi-check-circle"></i> บันทึก</button>' +
            '<button class="btn btn-secondary" onclick="Swal.close()">' +
              '<i class="bi bi-x-circle"></i> ยกเลิก</button>' +
          '</div>' +
        '</div>',
      didOpen: function() {
        // style selects inside swal
        Swal.getContent().querySelectorAll('select').forEach(function(el) {
          el.style.appearance = 'auto';
        });
      }
    });
  }

  function submitApprove(payId, price, amount) {
    var bank    = document.getElementById('swal-bank').value;
    var typeb   = document.getElementById('swal-typeb').value;
    var percent = document.getElementById('swal-percent').value;

    // Validate
    if (!bank) {
      Swal.fire({ icon:'warning', title:'กรุณาเลือกธนาคาร/เงินสด', confirmButtonText:'เข้าใจ', confirmButtonColor:'#5b9bd5' });
      return;
    }
    if (!typeb) {
      Swal.fire({ icon:'warning', title:'กรุณาเลือกหมวดงบ', confirmButtonText:'เข้าใจ', confirmButtonColor:'#5b9bd5' });
      return;
    }

    // ยืนยัน
    Swal.fire({
      icon: 'question',
      title: 'ยืนยันการขออนุมัติ?',
      html: 'รายการ <strong>#' + payId + '</strong> จำนวน <strong>' + amount + '</strong> บาท<br>' +
            '<span style="color:#6b778c; font-size:13px;">ตรวจสอบธนาคาร/หมวดงบ/เปอร์เซ็นต์ภาษีให้ถูกต้อง</span>',
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-check-circle"></i> บันทึก',
      cancelButtonText:  '<i class="bi bi-x-circle"></i> ยกเลิก',
      confirmButtonColor: '#00b050',
      cancelButtonColor:  '#6b778c'
    }).then(function(result) {
      if (result.isConfirmed) {
        Swal.fire({ title:'กำลังบันทึก...', allowOutsideClick:false, showConfirmButton:false,
          didOpen: function(){ Swal.showLoading(); }
        });
        var returnUrl = 'finance.php?' + '<?= h(buildQuery(['pn'=>$pn])) ?>';
        var url = 'approved.php?PayId=' + payId
                + '&Bank=' + bank
                + '&Typeb=' + typeb
                + '&Price=' + price
                + '&Amount=' + amount
                + '&return=' + encodeURIComponent(returnUrl);
        if (percent !== '') url += '&Percent=' + percent;
        window.location.href = url;
      }
    });
  }

  // ─── Modal: พิมพ์หนังสือขออนุมัติ ───
  function openPrint(receiveNo) {
    Swal.fire({
      title: 'ระบุเจ้าหน้าที่ผู้ปฏิบัติงาน',
      showConfirmButton: false,
      showCloseButton: true,
      width: '480px',
      html:
        '<div class="swal-form" style="padding:0 10px;">' +
          '<div class="form-group">' +
            '<label>1. บันทึกเจ้าหนี้</label>' +
            '<select id="swal-w1" class="form-control"><option value="">-- เลือกผู้บันทึกเจ้าหนี้ --</option>' + empOptions + '</select>' +
          '</div>' +
          '<div class="form-group">' +
            '<label>2. ผู้จัดทำ</label>' +
            '<select id="swal-w2" class="form-control"><option value="">-- เลือกผู้จัดทำ --</option>' + empOptions + '</select>' +
          '</div>' +
          '<div class="form-group">' +
            '<label>3. ผู้ตรวจสอบ</label>' +
            '<select id="swal-w3" class="form-control"><option value="">-- เลือกผู้ตรวจสอบ --</option>' + empOptions + '</select>' +
          '</div>' +
          '<div class="form-group">' +
            '<label>4. ผู้ตรวจสอบ</label>' +
            '<select id="swal-w4" class="form-control"><option value="">-- เลือกผู้ตรวจสอบ --</option>' + empOptions + '</select>' +
          '</div>' +
          '<div style="display:flex; gap:10px; justify-content:center; margin-top:20px;">' +
            '<button class="btn btn-primary" onclick="submitPrint(\'' + receiveNo + '\')">' +
              '<i class="bi bi-printer"></i> พิมพ์</button>' +
            '<button class="btn btn-secondary" onclick="Swal.close()">' +
              '<i class="bi bi-x-circle"></i> ยกเลิก</button>' +
          '</div>' +
        '</div>'
    });
  }

  function submitPrint(receiveNo) {
    var w1 = document.getElementById('swal-w1').value;
    var w2 = document.getElementById('swal-w2').value;
    var w3 = document.getElementById('swal-w3').value;
    var w4 = document.getElementById('swal-w4').value;

    var checks = [
      { id:'swal-w1', msg:'ผู้บันทึกเจ้าหนี้' },
      { id:'swal-w2', msg:'ผู้จัดทำ' },
      { id:'swal-w3', msg:'ผู้ตรวจสอบ (3)' },
      { id:'swal-w4', msg:'ผู้ตรวจสอบ (4)' }
    ];
    for (var i = 0; i < checks.length; i++) {
      if (!document.getElementById(checks[i].id).value) {
        Swal.fire({
          icon: 'warning',
          title: 'กรุณาตรวจสอบข้อมูล',
          html: 'กรุณาเลือก <strong>' + checks[i].msg + '</strong>',
          confirmButtonText: 'เข้าใจ',
          confirmButtonColor: '#5b9bd5'
        });
        return;
      }
    }

    // เปิดใน tab ใหม่
    var url = 'printapproved.php?ReceiveNo=' + encodeURIComponent(receiveNo)
            + '&Worker1=' + encodeURIComponent(w1)
            + '&Worker2=' + encodeURIComponent(w2)
            + '&Worker3=' + encodeURIComponent(w3)
            + '&Worker4=' + encodeURIComponent(w4);
    window.open(url, '_blank');
    Swal.close();
  }

  // ─── Modal: ค้นหนังสือขออนุมัติ ───
  function openSearch2() {
    Swal.fire({
      title: 'ค้นหนังสือขออนุมัติ',
      showConfirmButton: false,
      showCloseButton: true,
      width: '420px',
      html:
        '<div class="swal-form" style="padding:0 10px;">' +
          '<div class="form-group">' +
            '<label>ค้นด้วย</label>' +
            '<select id="swal-typekey2" class="form-control">' +
              '<option value="1" <?= $TypeKey2===1?'selected':'' ?>>รหัสรายการ</option>' +
              '<option value="2" <?= $TypeKey2===2?'selected':'' ?>>เลขที่รับเอกสาร</option>' +
            '</select>' +
          '</div>' +
          '<div class="form-group">' +
            '<label>ระบุคำค้น</label>' +
            '<input type="text" id="swal-keyword2" class="form-control" value="<?= h($Keyword2) ?>" placeholder="ระบุคำค้น...">' +
          '</div>' +
          '<div style="display:flex; gap:10px; justify-content:center; margin-top:20px;">' +
            '<button class="btn btn-success" onclick="submitSearch2()">' +
              '<i class="bi bi-search"></i> ค้นหา</button>' +
            '<button class="btn btn-secondary" onclick="Swal.close()">' +
              '<i class="bi bi-x-circle"></i> ยกเลิก</button>' +
          '</div>' +
        '</div>'
    });
  }

  function submitSearch2() {
    var tk2 = document.getElementById('swal-typekey2').value;
    var kw2 = document.getElementById('swal-keyword2').value;
    var params = new URLSearchParams(window.location.search);
    params.set('TypeKey2', tk2);
    params.set('Keyword2', kw2);
    params.set('pn2', '1');
    window.location.href = 'finance.php?' + params.toString();
  }
  </script>
</body>
</html>