<?php
require_once __DIR__ . '/header.php';
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Bangkok');

/* =========================
   Helper: Thai date
========================= */
function DateThai($strDate){
  if (!$strDate || $strDate === '0000-00-00') return '-';
  $ts = strtotime($strDate);
  if ($ts === false) return '-';
  $strYear  = date("Y", $ts) + 543;
  $strMonth = (int)date("n", $ts);
  $strDay   = (int)date("j", $ts);
  $strMonthCut  = ["","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค."];
  $strMonthThai = $strMonthCut[$strMonth] ?? '';
  return "$strDay&nbsp;&nbsp;$strMonthThai&nbsp;&nbsp;$strYear";
}

/* =========================
   Inputs
========================= */
$Keyword = trim($_GET['Keyword'] ?? '');
$TypeKey = (int)($_GET['TypeKey'] ?? 1);
$pn      = max(1, (int)($_GET['pn'] ?? 1));
$page_rows = 10;

// หา MaxId / LastId (แสดงล่าสุด 50)
$MaxId  = 0;
$rowmax = $conn->query("SELECT MAX(PayId) AS MaxId FROM payment")->fetch_assoc();
$MaxId  = (int)($rowmax['MaxId'] ?? 0);
$LastId = max(0, $MaxId - 49);

/* =========================
   Pre-load Dropdown Data
========================= */
// หมวด → table types
$typesList = [];
$stmtT = $conn->prepare("SELECT TypesId, TypesName FROM types ORDER BY TypesId");
$stmtT->execute();
$resT  = $stmtT->get_result();
while ($r = $resT->fetch_assoc()) { $typesList[] = $r; }
$stmtT->close();

// ตัดจ่ายจากแผน → table planpay
$planpayList = [];
$stmtP = $conn->prepare("SELECT PlanPayId, PlanPayName FROM planpay ORDER BY PlanPayId");
$stmtP->execute();
$resP  = $stmtP->get_result();
while ($r = $resP->fetch_assoc()) { $planpayList[] = $r; }
$stmtP->close();

/* =========================
   Build WHERE + Params
========================= */
$where  = "";
$params = [];
$types  = "";
$noResult = false;

if ($Keyword === '') {
  $where  = " WHERE PayId BETWEEN ? AND ? ";
  $types  = "ii";
  $params = [$LastId, $MaxId];
} else {
  if ($TypeKey === 1) {
    $kw = "%" . $Keyword . "%";
    $stmtC = $conn->prepare("SELECT CompanyId FROM company WHERE CompanyName LIKE ? LIMIT 1");
    $stmtC->bind_param("s", $kw);
    $stmtC->execute();
    $resC  = $stmtC->get_result();
    if ($resC && $resC->num_rows > 0) {
      $CompanyId = (int)$resC->fetch_assoc()['CompanyId'];
      $where  = " WHERE CompanyId = ? ";
      $types  = "i";
      $params = [$CompanyId];
    } else {
      $noResult = true;
    }
    $stmtC->close();
  } elseif ($TypeKey === 2) {
    $where  = " WHERE Detail LIKE ? ";
    $types  = "s";
    $params = ["%" . $Keyword . "%"];
  } else {
    $payid  = (int)$Keyword;
    $where  = " WHERE PayId = ? ";
    $types  = "i";
    $params = [$payid];
  }
}

/* =========================
   Count rows for pagination
========================= */
$total_rows = 0;
if (!$noResult) {
  $sqlCount  = "SELECT COUNT(*) AS cnt FROM payment " . $where;
  $stmtCount = $conn->prepare($sqlCount);
  if ($types !== '') $stmtCount->bind_param($types, ...$params);
  $stmtCount->execute();
  $total_rows = (int)($stmtCount->get_result()->fetch_assoc()['cnt'] ?? 0);
  $stmtCount->close();
}

$last   = max(1, (int)ceil($total_rows / $page_rows));
if ($pn > $last) $pn = $last;
$offset = ($pn - 1) * $page_rows;

/* =========================
   Fetch page rows
========================= */
$rowsData = [];
if (!$noResult && $total_rows > 0) {
  $sqlData  = "SELECT * FROM payment " . $where . " ORDER BY PayId DESC LIMIT ?, ?";
  $stmtData = $conn->prepare($sqlData);
  if ($types === '') {
    $stmtData->bind_param("ii", $offset, $page_rows);
  } else {
    $stmtData->bind_param($types . "ii", ...[...$params, $offset, $page_rows]);
  }
  $stmtData->execute();
  $resData = $stmtData->get_result();
  while ($r = $resData->fetch_assoc()) { $rowsData[] = $r; }
  $stmtData->close();
}

/* =========================
   Pagination helper
========================= */
function buildQuery(array $extra): string {
  $base = $_GET;
  foreach ($extra as $k => $v) $base[$k] = $v;
  return http_build_query($base);
}

// Stats for summary cards
$statTotal  = 0; $statAmount = 0.0;
$statPend   = 0; $statDone   = 0;
$rStat = $conn->query("SELECT COUNT(*) AS cnt, COALESCE(SUM(Amount),0) AS amt FROM payment");
if ($rStat && ($rS = $rStat->fetch_assoc())) {
  $statTotal  = (int)$rS['cnt'];
  $statAmount = (float)$rS['amt'];
}
$rPend = $conn->query("SELECT COUNT(*) AS cnt FROM payment WHERE (DateApprove IS NULL OR DateApprove='' OR DateApprove='0000-00-00')");
if ($rPend && ($rP = $rPend->fetch_assoc())) $statPend = (int)$rP['cnt'];
$statDone = $statTotal - $statPend;

$paginationCtrls = '';
if ($last > 1) {
  $paginationCtrls = '<nav><ul class="pagination">';
  if ($pn > 1) {
    $paginationCtrls .= '<li><a href="?' . buildQuery(['pn' => 1]) . '">&laquo;&laquo;</a></li>';
    $paginationCtrls .= '<li><a href="?' . buildQuery(['pn' => $pn - 1]) . '">&laquo;</a></li>';
  }
  $start = max(1, $pn - 2);
  $end   = min($last, $pn + 2);
  for ($i = $start; $i <= $end; $i++) {
    $active = ($i === $pn) ? ' class="active"' : '';
    $paginationCtrls .= '<li' . $active . '><a href="?' . buildQuery(['pn' => $i]) . '">' . $i . '</a></li>';
  }
  if ($pn < $last) {
    $paginationCtrls .= '<li><a href="?' . buildQuery(['pn' => $pn + 1]) . '">&raquo;</a></li>';
    $paginationCtrls .= '<li><a href="?' . buildQuery(['pn' => $last]) . '">&raquo;&raquo;</a></li>';
  }
  $paginationCtrls .= '</ul></nav>';
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

  <!-- Flatpickr (Modern Datepicker) -->
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
    .container { max-width: 1100px; }

    /* ─── Page Title ─── */
    .page-titlebar {
      margin: 14px 0 16px;
      border-radius: 18px;
      padding: 14px 20px;
      background: rgba(255,255,255,.88);
      border: 1px solid #e9eef6;
      box-shadow: 0 12px 30px rgba(13,27,62,.08);
      text-align: center;
    }
    .page-titlebar h3 {
      margin: 2px 0 0;
      font-weight: 800;
      color: #1f2a44;
      font-size: 20px;
    }
    .page-titlebar .sub {
      color: #6b778c;
      margin-top: 6px;
      font-size: 13px;
    }

    /* ─── Card Panel ─── */
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
      background: linear-gradient(135deg, rgba(91,155,213,.16), rgba(91,155,213,.06));
    }
    .card-body { padding: 16px 16px 6px; }

    /* ─── Form Controls ─── */
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
      border-color: rgba(91,155,213,.65);
      box-shadow: 0 0 0 3px rgba(91,155,213,.18);
    }
    select.form-control {
      appearance: none;
      -webkit-appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b778c' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
      padding-right: 36px;
    }
    .input-group-addon {
      border-radius: 12px;
      border: 1px solid #dfe7f3;
      background: #fff;
    }

    /* ─── Buttons ─── */
    .btn {
      border-radius: 12px;
      font-weight: 700;
      font-family: 'Sarabun', sans-serif;
      padding: 9px 14px;
      transition: transform .15s, box-shadow .15s;
    }
    .btn:active { transform: scale(.96); }
    .btn-primary  { box-shadow: 0 10px 22px rgba(13,110,253,.18); }
    .btn-info     { box-shadow: 0 10px 22px rgba(91,155,213,.18); }
    .btn-warning  { box-shadow: 0 10px 22px rgba(240,173,78,.16); }

    .help-note {
      color: #6b778c;
      font-size: 12px;
      margin-top: 10px;
    }

    /* ─── Table ─── */
    .table {
      background: #fff;
      border-radius: 14px;
      overflow: hidden;
      margin-bottom: 8px;
    }
    .table tr.info td {
      background: #eef5ff !important;
      font-weight: 800;
      color: #1f2a44;
    }
    .table td { vertical-align: middle !important; }

    /* ── Table Row Hover ── */
    .table tbody tr {
      transition: background .18s ease, transform .18s ease, box-shadow .18s ease;
    }
    .table tbody tr:hover {
      background: linear-gradient(90deg, #eef7ff, #f4f9ff) !important;
      transform: scale(1.005);
      box-shadow: 0 4px 14px rgba(91,155,213,.22);
      position: relative;
      z-index: 2;
    }
    .table tbody tr:hover td:first-child {
      border-left: 3px solid #5b9bd5;
      border-radius: 8px 0 0 8px;
    }
    .table tbody tr:hover td:last-child {
      border-radius: 0 8px 8px 0;
    }
    .table tbody tr:hover .badge-status {
      transform: scale(1.05);
    }

    /* ─── Status Badges ─── */
    .badge-status {
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
    .st-wait    { background: #fff7ed; border-color: #fed7aa; color: #9a3412; }
    .st-approve { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
    .st-check   { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
    .st-paid    { background: #f1f5f9; border-color: #e2e8f0; color: #0f172a; }

    /* ─── Pagination ─── */
    .pagination > li > a,
    .pagination > li > span {
      border-radius: 10px !important;
      margin: 0 4px;
      border: 1px solid #e2e8f0;
      color: #1f2a44;
      transition: background .2s;
    }
    .pagination > .active > a {
      background: #5b9bd5;
      border-color: #5b9bd5;
    }
    .pagination > li > a:hover {
      background: #dbeafe;
      border-color: #93c5fd;
    }

    .control-label { font-weight: 700; color: #1f2a44; }

    /* ─── Pay Cards Grid ─── */
    .pay-cards-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 14px;
      padding: 4px 0 8px;
    }
    .pay-card {
      background: #fff;
      border-radius: 14px;
      border: 1px solid #e2e8f0;
      padding: 16px;
      box-shadow: 0 2px 10px rgba(13,27,62,.06);
      transition: transform .18s, box-shadow .18s;
      display: flex; flex-direction: column; gap: 10px;
    }
    .pay-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(13,27,62,.10); }
    .pay-card-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .pay-card-id {
      font-size: 12px; font-weight: 800; color: #94a3b8;
      background: #f8fafc; border: 1px solid #e2e8f0;
      padding: 3px 9px; border-radius: 99px; letter-spacing: .4px;
    }
    .pay-card-company {
      font-size: 15px; font-weight: 700; color: #1e293b;
      display: flex; align-items: center; gap: 6px;
    }
    .pay-card-detail {
      font-size: 13px; color: #64748b; display: flex; align-items: center; gap: 6px;
    }
    .pay-card-row {
      display: flex; align-items: center; justify-content: space-between; gap: 8px;
      padding-top: 6px; border-top: 1px solid #f1f5f9;
    }
    .pay-card-date { font-size: 12px; color: #64748b; display: flex; align-items: center; gap: 5px; }
    .pay-card-amount {
      font-size: 17px; font-weight: 800; color: #0B6E4F;
      letter-spacing: -.3px;
    }
    .pay-card-actions { display: flex; gap: 8px; }
    .pay-card-btn {
      flex: 1; display: flex; align-items: center; justify-content: center; gap: 5px;
      padding: 8px 12px; border-radius: 9px; font-size: 13px; font-weight: 600;
      font-family: 'Sarabun', sans-serif; cursor: pointer; transition: all .16s;
      text-decoration: none; border: none;
    }
    .pay-card-edit {
      background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;
    }
    .pay-card-edit:hover { background: #dbeafe; color: #1e40af; text-decoration: none; }
    .pay-card-del {
      background: #fff1f2; color: #be123c; border: 1px solid #fecdd3;
    }
    .pay-card-del:hover { background: #ffe4e6; color: #9f1239; }

    /* ─── Modern Form Sections ─── */
    .form-section {
      padding: 20px 0 8px;
      border-top: 1px solid #f1f5f9;
    }
    .form-section:first-child { border-top: none; padding-top: 4px; }
    .form-section-title {
      display: flex; align-items: center; gap: 7px;
      font-size: 13px; font-weight: 800; color: #0B6E4F;
      text-transform: uppercase; letter-spacing: .6px;
      margin-bottom: 14px;
    }
    .form-section-title .msi { font-size: 17px; }

    .form-label-custom {
      display: block; font-size: 13px; font-weight: 700; color: #374151;
      margin-bottom: 6px;
    }
    .req { color: #ef4444; margin-left: 2px; }

    .radio-group-row {
      display: flex; flex-wrap: wrap; align-items: center; gap: 20px;
      padding: 10px 14px; background: #f8fafc; border-radius: 12px;
      border: 1px solid #e2e8f0; height: 42px;
    }
    .radio-group-row label {
      display: flex; align-items: center; gap: 6px;
      font-weight: 700; font-size: 14px; color: #374151; cursor: pointer; margin: 0;
    }
    .radio-group-row input[type="radio"] {
      accent-color: #0B6E4F; width: 16px; height: 16px; cursor: pointer;
    }

    .input-group-text {
      background: #f1f5f9; border: 1px solid #dfe7f3;
      color: #64748b; font-family: 'Sarabun', sans-serif;
      font-weight: 700; font-size: 13px; border-radius: 0 12px 12px 0 !important;
    }
    .input-group .form-control:first-child {
      border-radius: 12px 0 0 12px !important;
    }
    .input-group .form-control:only-child {
      border-radius: 12px !important;
    }

    .vat-result-field {
      background: linear-gradient(135deg, #f0fdf4, #ecfdf5) !important;
      border-color: #bbf7d0 !important;
      color: #166534 !important;
      font-weight: 700 !important;
    }

    .form-actions {
      display: flex; align-items: center; justify-content: center;
      gap: 12px; padding: 20px 0 8px;
      border-top: 1px solid #f1f5f9; margin-top: 8px;
    }
    .btn-save {
      background: linear-gradient(135deg, #0B6E4F, #08A045);
      color: #fff; border: none; padding: 11px 32px;
      border-radius: 12px; font-weight: 700; font-size: 15px;
      font-family: 'Sarabun', sans-serif;
      display: flex; align-items: center; gap: 8px;
      box-shadow: 0 4px 14px rgba(11,110,79,.3);
      transition: all .2s; cursor: pointer;
    }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(11,110,79,.4); }
    .btn-save:active { transform: scale(.97); }
    .btn-reset {
      background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;
      padding: 11px 24px; border-radius: 12px; font-weight: 700; font-size: 15px;
      font-family: 'Sarabun', sans-serif;
      display: flex; align-items: center; gap: 8px;
      transition: all .2s; cursor: pointer;
    }
    .btn-reset:hover { background: #e2e8f0; color: #374151; }

    /* ─── SweetAlert2 tweaks ─── */
    .swal2-popup         { border-radius: 16px; font-family: 'Sarabun', sans-serif; }
    .swal2-title         { color: #1f2a44; font-weight: 800; }
    .swal2-confirm       { border-radius: 10px !important; font-weight: 700; }
    .swal2-cancel        { border-radius: 10px !important; font-weight: 700; }
    .swal2-confirm.swal-btn-danger { background: #ef4444 !important; }

    /* Flatpickr calendar tweak */
    .flatpickr-calendar { font-family: 'Sarabun', sans-serif !important; border-radius: 14px !important; box-shadow: 0 8px 28px rgba(0,0,0,.15) !important; }
    .flatpickr-day.selected { background: #0B6E4F !important; border-color: #0B6E4F !important; }
    .flatpickr-day:hover { background: #e8f5e9 !important; }
    .numInputWrapper input { font-family: 'Sarabun', sans-serif !important; }
  </style>
</head>

<body>
  <div class="container">
    <div class="page-titlebar" style="display:flex; align-items:center; justify-content:space-between; gap:12px; text-align:left;">
      <div>
        <h3 style="margin:0; display:flex; align-items:center; gap:8px;">
          <span class="msi msi-24" style="color:#0B6E4F;">receipt_long</span>
          บันทึกเจ้าหนี้การค้า / รายการสั่งจ่าย
        </h3>
        <div class="sub">จัดการข้อมูลการรับเอกสาร การบันทึก และติดตามสถานะรายการล่าสุด</div>
      </div>
      <a href="main.php" class="btn-go-back">
        <span class="msi">arrow_back</span> กลับหน้าหลัก
      </a>
    </div>

    <!-- Stats Cards -->
    <div class="stats-row">
      <div class="stat-card stat-green">
        <div class="stat-icon"><span class="msi msi-24">receipt_long</span></div>
        <div class="stat-body">
          <div class="stat-label">รายการทั้งหมด</div>
          <div class="stat-value"><?= number_format($statTotal) ?></div>
          <div class="stat-sub">รายการในระบบ</div>
        </div>
      </div>
      <div class="stat-card stat-orange">
        <div class="stat-icon"><span class="msi msi-24">pending</span></div>
        <div class="stat-body">
          <div class="stat-label">รออนุมัติ</div>
          <div class="stat-value"><?= number_format($statPend) ?></div>
          <div class="stat-sub">รายการยังไม่ได้อนุมัติ</div>
        </div>
      </div>
      <div class="stat-card stat-blue">
        <div class="stat-icon"><span class="msi msi-24">check_circle</span></div>
        <div class="stat-body">
          <div class="stat-label">ดำเนินการแล้ว</div>
          <div class="stat-value"><?= number_format($statDone) ?></div>
          <div class="stat-sub">รายการผ่านอนุมัติ</div>
        </div>
      </div>
      <div class="stat-card stat-teal">
        <div class="stat-icon"><span class="msi msi-24">payments</span></div>
        <div class="stat-body">
          <div class="stat-label">ยอดรวมทั้งหมด</div>
          <div class="stat-value"><?= number_format($statAmount, 2) ?></div>
          <div class="stat-sub">บาท (รวมทุกรายการ)</div>
        </div>
      </div>
    </div>

    <!-- =======================
         บันทึกเจ้าหนี้การค้า
    ======================== -->
    <div class="card-panel">
      <div class="card-head">
        <span class="msi msi-18">edit_note</span> บันทึกเจ้าหนี้การค้า/รายการสั่งจ่าย
      </div>
      <div class="card-body">
        <form id="form1" method="get" action="save.php" target="_self">

          <!-- ── Section 1: ข้อมูลเอกสาร ── -->
          <div class="form-section">
            <div class="form-section-title">
              <span class="msi msi-18">folder_open</span> ข้อมูลเอกสาร
            </div>
            <div class="row g-3">
              <div class="col-md-8">
                <label class="form-label-custom">กลุ่มงานที่ส่งหลักฐาน <span class="req">*</span></label>
                <select class="form-control" name="Dept" id="Dept">
                  <option value="">-- เลือกกลุ่มงาน/งาน --</option>
                  <?php
                    $stmtD = $conn->prepare("SELECT DeptId, DeptName FROM department ORDER BY DeptId");
                    $stmtD->execute();
                    $resD  = $stmtD->get_result();
                    while ($r = $resD->fetch_assoc()) {
                      echo '<option value="' . htmlspecialchars($r['DeptId']) . '">' . htmlspecialchars($r['DeptName']) . '</option>';
                    }
                    $stmtD->close();
                  ?>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label-custom">วันที่รับเอกสาร <span class="req">*</span></label>
                <div class="input-group">
                  <input class="form-control date-th-picker" id="DateIn" type="text" name="DateIn"
                    value="<?php
                      $ts = time();
                      echo date("d/m/", $ts) . (date("Y", $ts) + 543);
                    ?>" placeholder="วว/ดด/ปปปป" autocomplete="off">
                  <span class="input-group-text" style="cursor:pointer;" onclick="document.getElementById('DateIn')._flatpickr && document.getElementById('DateIn')._flatpickr.open()">
                    <span class="msi msi-18" style="color:#0B6E4F;">calendar_month</span>
                  </span>
                </div>
              </div>
            </div>
            <div class="row g-3 mt-2">
              <div class="col-md-8">
                <label class="form-label-custom">หมวด <span class="req">*</span></label>
                <select class="form-control" name="Types" id="Types">
                  <option value="">-- เลือกหมวด --</option>
                  <?php foreach ($typesList as $t): ?>
                    <option value="<?php echo htmlspecialchars($t['TypesId']); ?>">
                      <?php echo htmlspecialchars($t['TypesName']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="row g-3 mt-2">
              <div class="col-md-8">
                <label class="form-label-custom">ตัดจ่ายจากแผน <span class="req">*</span></label>
                <select class="form-control" name="PlanPay" id="PlanPay">
                  <option value="">-- เลือกแผน --</option>
                  <?php foreach ($planpayList as $p): ?>
                    <option value="<?php echo htmlspecialchars($p['PlanPayId']); ?>">
                      <?php echo htmlspecialchars($p['PlanPayId'] . '. ' . $p['PlanPayName']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label-custom">แหล่งเงิน</label>
                <div class="radio-group-row">
                  <label>
                    <input type="radio" name="source" value="1" checked> เงินบำรุง
                  </label>
                  <label>
                    <input type="radio" name="source" value="2"> งบประมาณ
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- ── Section 2: รายละเอียดรายการ ── -->
          <div class="form-section">
            <div class="form-section-title">
              <span class="msi msi-18">description</span> รายละเอียดรายการ
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label-custom">เลขที่ใบส่งของ <span class="req">*</span></label>
                <input class="form-control" name="Detail" id="Detail" type="text" placeholder="กรอกเลขที่ใบส่งของ">
              </div>
              <div class="col-md-6">
                <label class="form-label-custom">รายการ/บริษัท <span class="req">*</span></label>
                <select class="form-control" name="Company" id="Company">
                  <option value="">-- เลือกบริษัท/ร้านค้า --</option>
                  <?php
                    $stmtCo = $conn->prepare("SELECT CompanyId, CompanyName FROM company ORDER BY CompanyName");
                    $stmtCo->execute();
                    $resCo  = $stmtCo->get_result();
                    while ($r = $resCo->fetch_assoc()) {
                      echo '<option value="' . htmlspecialchars($r['CompanyId']) . '">' . htmlspecialchars($r['CompanyName']) . '</option>';
                    }
                    $stmtCo->close();
                  ?>
                </select>
              </div>
            </div>
          </div>

          <!-- ── Section 3: ข้อมูลการเงิน ── -->
          <div class="form-section">
            <div class="form-section-title">
              <span class="msi msi-18">payments</span> ข้อมูลการเงิน
            </div>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label-custom">จำนวน <span class="req">*</span></label>
                <div class="input-group">
                  <input class="form-control" name="NumList" id="NumList" type="text" placeholder="0">
                  <span class="input-group-text">รายการ</span>
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label-custom">จำนวนเงิน <span class="req">*</span></label>
                <div class="input-group">
                  <input class="form-control" name="Price" id="Price" type="text" placeholder="0.00">
                  <span class="input-group-text">บาท</span>
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label-custom">VAT</label>
                <div class="radio-group-row">
                  <label>
                    <input type="radio" name="vatOption" id="vatOn" value="on"> คำนวณ VAT 7%
                  </label>
                  <label>
                    <input type="radio" name="vatOption" id="vatOff" value="off" checked> ไม่คำนวณ
                  </label>
                </div>
              </div>
            </div>
            <div class="row g-3 mt-2">
              <div class="col-md-4">
                <label class="form-label-custom">VAT (บาท)</label>
                <input class="form-control vat-result-field" name="Vat" id="Vat" type="text" value="0.00" readonly>
              </div>
              <div class="col-md-4">
                <label class="form-label-custom">รวม VAT (บาท)</label>
                <input class="form-control vat-result-field" name="Amount" id="Amount" type="text" value="0.00" readonly>
              </div>
            </div>
          </div>

          <!-- ── Actions ── -->
          <div class="form-actions">
            <button type="button" class="btn-save" id="btnSave">
              <span class="msi">check_circle</span> บันทึก
            </button>
            <button type="button" class="btn-reset" id="btnReset">
              <span class="msi">refresh</span> ล้างข้อมูล
            </button>
          </div>

        </form>
      </div>
    </div>

    <!-- =======================
         ค้นหา
    ======================== -->
    <div class="card-panel">
      <div class="card-head">
        <span class="msi msi-18">search</span> ค้นหารายการ
      </div>
      <div class="card-body">
        <form class="form-horizontal" id="formSearch" method="get" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
          <div class="form-group">
            <label class="col-md-2 control-label">ค้นด้วย</label>
            <div class="col-md-3">
              <select class="form-control" name="TypeKey" id="TypeKey">
                <option value="1" <?php echo ($TypeKey===1?'selected':''); ?>>บริษัท/ร้านค้า</option>
                <option value="2" <?php echo ($TypeKey===2?'selected':''); ?>>เลขที่ใบส่งของ</option>
                <option value="3" <?php echo ($TypeKey===3?'selected':''); ?>>รหัสรายการ</option>
              </select>
            </div>

            <label class="col-md-2 control-label">คำค้น</label>
            <div class="col-md-4">
              <input class="form-control" name="Keyword" type="text" id="Keyword" value="<?php echo htmlspecialchars($Keyword); ?>" placeholder="พิมพ์คำค้นที่นี่...">
            </div>

            <div class="col-md-1">
              <button type="submit" class="btn btn-info">
                <span class="msi msi-18">search</span> ค้นหา
              </button>
            </div>
          </div>
        </form>
        <div class="help-note">* หากไม่กรอกคำค้น ระบบจะแสดงรายการล่าสุด 50 รายการ (แบ่งหน้า)</div>
      </div>
    </div>

    <!-- =======================
         รายการเจ้าหนี้ (Card Layout)
    ======================== -->
    <div class="card-panel">
      <div class="card-head" style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
        <span style="display:flex; align-items:center; gap:7px;">
          <span class="msi msi-18">receipt_long</span>
          รายการเจ้าหนี้ค้างจ่าย/รายการสั่งจ่าย
        </span>
        <span style="font-size:12px; font-weight:500; color:#64748b; background:#f1f5f9; padding:3px 10px; border-radius:99px;">
          แสดงล่าสุด 50 รายการ
        </span>
      </div>

      <div class="card-body">
        <?php if ($noResult || $total_rows === 0): ?>
          <div class="empty-state" style="text-align:center; padding:40px 20px; color:#64748b;">
            <span class="msi" style="font-size:48px; opacity:.35;">inbox</span>
            <p style="margin-top:12px; font-size:15px;">ไม่พบข้อมูลที่ตรงกับเกณฑ์ค้นหา</p>
          </div>
        <?php else: ?>
          <div class="pay-cards-grid">
            <?php foreach ($rowsData as $row):
              $PayId       = (int)$row['PayId'];
              $DateIn      = $row['DateIn'] ?? '';
              $CompanyId   = (int)($row['CompanyId'] ?? 0);
              $Amount      = (float)($row['Amount'] ?? 0);
              $DateApprove = $row['DateApprove'] ?? '';
              $DateReceive = $row['DateReceive'] ?? '';
              $DatePay     = $row['DatePay'] ?? '';
              $Detail      = $row['Detail'] ?? '';

              $CompanyName = '';
              $stmtCN = $conn->prepare("SELECT CompanyName FROM company WHERE CompanyId=? LIMIT 1");
              $stmtCN->bind_param("i", $CompanyId);
              $stmtCN->execute();
              $rCN = $stmtCN->get_result()->fetch_assoc();
              $stmtCN->close();
              if ($rCN) $CompanyName = (string)($rCN['CompanyName'] ?? '');

              $statusText  = "ดำเนินการเบิกจ่าย";
              $statusClass = "st-paid";
              $statusIcon  = "payments";
              $isZeroDate  = fn($d) => ($d === '0000-00-00' || $d === '' || $d === null);
              if ($isZeroDate($DatePay)) {
                if ($isZeroDate($DateApprove)) {
                  if ($isZeroDate($DateReceive)) {
                    $statusText = "รอรับเอกสารการเงิน"; $statusClass = "st-wait"; $statusIcon = "pending";
                  } else {
                    $statusText = "อยู่ระหว่างขออนุมัติ"; $statusClass = "st-approve"; $statusIcon = "hourglass_empty";
                  }
                } else {
                  $statusText = "อยู่ระหว่างจัดทำเช็ค"; $statusClass = "st-check"; $statusIcon = "edit_document";
                }
              }
            ?>
            <div class="pay-card">
              <div class="pay-card-top">
                <div class="pay-card-id">#<?= $PayId ?></div>
                <span class="badge-status <?= $statusClass ?>" style="display:flex; align-items:center; gap:4px;">
                  <span class="msi" style="font-size:13px;"><?= $statusIcon ?></span>
                  <?= $statusText ?>
                </span>
              </div>
              <div class="pay-card-company">
                <span class="msi msi-18" style="color:#0B6E4F;">store</span>
                <?= htmlspecialchars($CompanyName ?: '—') ?>
              </div>
              <div class="pay-card-detail">
                <span class="msi msi-18" style="color:#64748b;">description</span>
                <?= htmlspecialchars($Detail ?: '—') ?>
              </div>
              <div class="pay-card-row">
                <div class="pay-card-date">
                  <span class="msi msi-18" style="color:#64748b;">calendar_today</span>
                  <?= DateThai($DateIn) ?>
                </div>
                <div class="pay-card-amount">
                  <?= number_format($Amount, 2) ?> <span style="font-size:11px; opacity:.7;">บาท</span>
                </div>
              </div>
              <div class="pay-card-actions">
                <a href="editform.php?PayId=<?= $PayId ?>" class="pay-card-btn pay-card-edit" title="แก้ไข">
                  <span class="msi msi-18">edit</span> แก้ไข
                </a>
                <button class="pay-card-btn pay-card-del" onclick="confirmDelete(<?= $PayId ?>)" title="ลบ">
                  <span class="msi msi-18">delete</span> ลบ
                </button>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <div style="text-align:center; margin-top:16px;">
            <?php echo $paginationCtrls; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div style="height:16px;"></div>
  </div>

  <!-- Flatpickr -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

  <script>
  // ── Thai Buddhist Calendar Flatpickr ──
  function initThaiDatepicker(selector) {
    var elements = document.querySelectorAll(selector);
    elements.forEach(function(el) {
      var initVal = el.value;
      var initDate = null;
      if (initVal) {
        var parts = initVal.split('/');
        if (parts.length === 3) {
          var y = parseInt(parts[2]); if (y > 2500) y -= 543;
          initDate = new Date(y, parseInt(parts[1])-1, parseInt(parts[0]));
        }
      }
      flatpickr(el, {
        locale: 'th',
        dateFormat: 'd/m/Y',
        defaultDate: initDate || undefined,
        disableMobile: false,
        allowInput: true,
        onReady: function(sd, ds, instance) {
          if (initDate) {
            var d = initDate;
            var be = d.getFullYear() + 543;
            var mm = String(d.getMonth()+1).padStart(2,'0');
            var dd = String(d.getDate()).padStart(2,'0');
            instance.element.value = dd+'/'+mm+'/'+be;
          }
        },
        onChange: function(sd, ds, instance) {
          if (sd[0]) {
            var d = sd[0];
            var be = d.getFullYear() + 543;
            var mm = String(d.getMonth()+1).padStart(2,'0');
            var dd = String(d.getDate()).padStart(2,'0');
            instance.element.value = dd+'/'+mm+'/'+be;
          }
        },
        parseDate: function(s) {
          if (!s) return null;
          var p = s.split('/');
          if (p.length !== 3) return null;
          var y = parseInt(p[2]); if (y > 2500) y -= 543;
          return new Date(y, parseInt(p[1])-1, parseInt(p[0]));
        }
      });
    });
  }

  $(function(){
    // ── Datepicker ──
    initThaiDatepicker('.date-th-picker');

    // ── VAT Calculation ──
    function recalcVat() {
      var price = parseFloat($('#Price').val()) || 0;
      var useVat = $('#vatOn').is(':checked');
      var vat    = useVat ? +(price * 7 / 100).toFixed(2) : 0;
      var total  = +(price + vat).toFixed(2);
      $('#Vat').val(vat.toFixed(2));
      $('#Amount').val(total.toFixed(2));
    }
    $('#Price').on('input', recalcVat);
    $('input[name="vatOption"]').on('change', recalcVat);

    // ── Form Validation + Submit ──
    $('#btnSave').on('click', function(){
      // เรียง validation ตามลำดับ field
      var checks = [
        { el: '#Dept',     msg: 'กลุ่มงานที่ส่งหลักฐาน' },
        { el: '#Types',    msg: 'หมวด' },
        { el: '#PlanPay',  msg: 'แผนที่ตัดจ่าย' },
        { el: '#Detail',   msg: 'เลขที่ใบส่งของ' },
        { el: '#Company',  msg: 'บริษัท/ร้านค้า' },
        { el: '#NumList',  msg: 'จำนวน' },
        { el: '#Price',    msg: 'จำนวนเงิน' }
      ];

      for (var i = 0; i < checks.length; i++) {
        var val = $(checks[i].el).val();
        if (!val || val.trim() === '') {
          Swal.fire({
            icon: 'warning',
            title: 'กรุณาตรวจสอบข้อมูล',
            html: 'กรุณากรอก <strong>' + checks[i].msg + '</strong> ให้ครบถ้วน',
            confirmButtonText: 'เข้าใจ',
            confirmButtonColor: '#5b9bd5'
          }).then(function(){ $(checks[i].el).focus(); });
          return;
        }
      }

      // เงินจำนวน validation
      var priceVal = parseFloat($('#Price').val());
      if (isNaN(priceVal) || priceVal <= 0) {
        Swal.fire({
          icon: 'warning',
          title: 'กรุณาตรวจสอบข้อมูล',
          html: '<strong>จำนวนเงิน</strong> ต้องเป็นตัวเลขมากกว่า 0',
          confirmButtonText: 'เข้าใจ',
          confirmButtonColor: '#5b9bd5'
        }).then(function(){ $('#Price').focus(); });
        return;
      }

      // ยืนยัน → บันทึก
      Swal.fire({
        title: 'ยืนยันการบันทึก',
        html: 'คุณต้องการบันทึกรายการเจ้าหนี้การค้า<br>จำนวน <strong>' + $('#Amount').val() + '</strong> บาท ใช่หรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-circle"></i> บันทึก',
        cancelButtonText:  '<i class="bi bi-x-circle"></i> ยกเลิก',
        confirmButtonColor: '#0B6E4F',
        cancelButtonColor:  '#6b778c'
      }).then(function(result){
        if (result.isConfirmed) {
          Swal.fire({
            title: 'กำลังบันทึกข้อมูล...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: function(){ Swal.showLoading(); }
          });
          document.getElementById('form1').submit();
        }
      });
    });

    // ── Reset Form ──
    $('#btnReset').on('click', function(){
      Swal.fire({
        title: 'ยกเลิกการบันทึก',
        text: 'คุณต้องการล้างข้อมูลในฟอร์มใช่หรือไม่?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ล้างเลย',
        cancelButtonText:  'ยกเลิก',
        confirmButtonColor: '#ef4444',
        cancelButtonColor:  '#6b778c'
      }).then(function(result){
        if (result.isConfirmed) {
          document.getElementById('form1').reset();
          $('#Vat').val('0.00');
          $('#Amount').val('0.00');
          Swal.fire({
            icon: 'success',
            title: 'ล้างข้อมูลเรียบร้อย',
            timer: 1200,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
          });
        }
      });
    });
  });

  // ── ลบรายการ (แทน Bootstrap Modal) ──
  function confirmDelete(payId) {
    Swal.fire({
      title: 'ยืนยันการลบ',
      html: 'คุณต้องการลบรายการ รหัส <strong>' + payId + '</strong> ใช่หรือไม่?<br><span style="color:#ef4444; font-size:13px;">การกระทำนี้ไม่สามารถเปลี่ยนกลับได้</span>',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-trash3"></i> ลบ',
      cancelButtonText:  '<i class="bi bi-x-circle"></i> ยกเลิก',
      confirmButtonColor: '#ef4444',
      cancelButtonColor:  '#6b778c',
      confirmButtonClass: 'swal-btn-danger'
    }).then(function(result){
      if (result.isConfirmed) {
        Swal.fire({
          title: 'กำลังลบข้อมูล...',
          allowOutsideClick: false,
          showConfirmButton: false,
          didOpen: function(){ Swal.showLoading(); }
        });
        window.location.href = 'delete.php?PayId=' + payId;
      }
    });
  }
  </script>
</body>
</html>