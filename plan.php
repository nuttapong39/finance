<?php
// plan.php (Fixed warnings + Modern Card UI)

date_default_timezone_set('Asia/Bangkok');
ini_set('display_errors', '1');
error_reporting(E_ALL);

// --- Capture header.php output (กัน header.php พ่น HTML ก่อน <head>) ---
$__header_html = '';
ob_start();
@include __DIR__ . '/header.php';
$__header_html = ob_get_clean();

require_once __DIR__ . '/connect_db.php';
if (!isset($conn) || !($conn instanceof mysqli)) {
  $conn = null;
} else {
  @mysqli_set_charset($conn, 'utf8mb4');
}

// ---------------- Helpers ----------------
function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function fiscalYearBE(): int {
  // ปีงบประมาณเริ่ม 1 ต.ค. -> ถ้าเดือน >= 10 ให้เป็นปีถัดไป
  $y = (int)date('Y');
  $m = (int)date('n');
  $be = $y + 543;
  if ($m >= 10) $be += 1;
  return $be;
}

function ymd_to_th_dmy(string $ymd): string {
  $ts = strtotime($ymd);
  if (!$ts) return '';
  $d = date('d', $ts);
  $m = date('m', $ts);
  $y = (int)date('Y', $ts) + 543;
  return $d . '/' . $m . '/' . $y;
}

// ---------------- Inputs (กัน Warning) ----------------
$Qyear = trim($_GET['Qyear'] ?? '');
if ($Qyear === '' || !preg_match('/^\d{4}$/', $Qyear)) {
  $Qyear = (string)fiscalYearBE();
}

$QyearInt = (int)$Qyear;
$yearbe   = $QyearInt - 544;          // เช่น 2568 -> 2024
$yearthis = $QyearInt - 543;          // 2568 -> 2025
$DatePaystart = sprintf('%04d-10-01', $yearbe);
$DatePayend   = sprintf('%04d-09-30', $yearthis);

// สำหรับ modal export (ค่าเริ่มต้นเป็นช่วงปีงบ)
$DatePaystart_th = ymd_to_th_dmy($DatePaystart);
$DatePayend_th   = ymd_to_th_dmy($DatePayend);

// ---------------- DB queries (รวมให้เบา + กัน null) ----------------
$db_ok = (bool)$conn;

$Amountset = 0.0; // รวมประมาณการทั้งสิ้น (planpayset)
$Amounts   = 0.0; // จ่ายตามแผน (DateIn)
$Amounts2  = 0.0; // จ่ายจริง (DatePaid)

$types = [];        // [typeId => PlanName]
$itemsByType = [];  // [typeId => [ [PlanPayId, PlanPayName], ... ]]
$planAmountMap = []; // [PlanPayId => Amount planned]
$sumInMap = [];      // [PlanPayId => sum Amount by DateIn]
$sumPaidMap = [];    // [PlanPayId => sum Amount by DatePaid]

if ($db_ok) {
  // 1) รวมจ่ายตามแผน (DateIn)
  if ($stmt = $conn->prepare("SELECT COALESCE(SUM(Amount),0) AS s FROM payment WHERE DateIn BETWEEN ? AND ? AND Source='1'")) {
    $stmt->bind_param("ss", $DatePaystart, $DatePayend);
    if ($stmt->execute()) {
      $rs = $stmt->get_result();
      $row = $rs ? $rs->fetch_assoc() : null;
      $Amounts = (float)($row['s'] ?? 0);
    }
    $stmt->close();
  }

  // 2) รวมจ่ายจริง (DatePaid)
  if ($stmt = $conn->prepare("SELECT COALESCE(SUM(Amount),0) AS s FROM payment WHERE DatePaid BETWEEN ? AND ? AND Source='1'")) {
    $stmt->bind_param("ss", $DatePaystart, $DatePayend);
    if ($stmt->execute()) {
      $rs = $stmt->get_result();
      $row = $rs ? $rs->fetch_assoc() : null;
      $Amounts2 = (float)($row['s'] ?? 0);
    }
    $stmt->close();
  }

  // 3) รวมประมาณการทั้งหมด
  if ($stmt = $conn->prepare("SELECT COALESCE(SUM(Amount),0) AS s FROM planpayset WHERE Qyear=?")) {
    $stmt->bind_param("i", $QyearInt);
    if ($stmt->execute()) {
      $rs = $stmt->get_result();
      $row = $rs ? $rs->fetch_assoc() : null;
      $Amountset = (float)($row['s'] ?? 0);
    }
    $stmt->close();
  }

  // 4) ดึงประเภทแผน (planpaytype)
  $rs = $conn->query("SELECT PlanPayTypeId, PlanName FROM planpaytype ORDER BY PlanPayTypeId");
  if ($rs) {
    while ($r = $rs->fetch_assoc()) {
      $tid = (int)($r['PlanPayTypeId'] ?? 0);
      if ($tid > 0) $types[$tid] = (string)($r['PlanName'] ?? '');
    }
  }

  // 5) ดึงรายการย่อย (planpay)
  $rs = $conn->query("SELECT PlanPayId, PlanPayTypeId, PlanPayName FROM planpay ORDER BY PlanPayTypeId, PlanPayId");
  if ($rs) {
    while ($r = $rs->fetch_assoc()) {
      $tid = (int)($r['PlanPayTypeId'] ?? 0);
      $pid = (int)($r['PlanPayId'] ?? 0);
      $pname = (string)($r['PlanPayName'] ?? '');
      if ($tid > 0 && $pid > 0) {
        if (!isset($itemsByType[$tid])) $itemsByType[$tid] = [];
        $itemsByType[$tid][] = ['PlanPayId'=>$pid, 'PlanPayName'=>$pname];
      }
    }
  }

  // 6) map ประมาณการต่อรายการ (planpayset)
  if ($stmt = $conn->prepare("SELECT PlanPayId, Amount FROM planpayset WHERE Qyear=?")) {
    $stmt->bind_param("i", $QyearInt);
    if ($stmt->execute()) {
      $rs = $stmt->get_result();
      while ($rs && ($r = $rs->fetch_assoc())) {
        $pid = (int)($r['PlanPayId'] ?? 0);
        $amt = (float)($r['Amount'] ?? 0);
        if ($pid > 0) $planAmountMap[$pid] = $amt;
      }
    }
    $stmt->close();
  }

  // 7) sum จ่ายตามแผน per PlanPayId (payment DateIn)
  if ($stmt = $conn->prepare("
      SELECT PlanPayId, COALESCE(SUM(Amount),0) AS s
      FROM payment
      WHERE DateIn BETWEEN ? AND ? AND Source='1'
      GROUP BY PlanPayId
  ")) {
    $stmt->bind_param("ss", $DatePaystart, $DatePayend);
    if ($stmt->execute()) {
      $rs = $stmt->get_result();
      while ($rs && ($r = $rs->fetch_assoc())) {
        $pid = (int)($r['PlanPayId'] ?? 0);
        $sum = (float)($r['s'] ?? 0);
        if ($pid > 0) $sumInMap[$pid] = $sum;
      }
    }
    $stmt->close();
  }

  // 8) sum จ่ายจริง per PlanPayId (payment DatePaid)
  if ($stmt = $conn->prepare("
      SELECT PlanPayId, COALESCE(SUM(Amount),0) AS s
      FROM payment
      WHERE DatePaid BETWEEN ? AND ? AND Source='1'
      GROUP BY PlanPayId
  ")) {
    $stmt->bind_param("ss", $DatePaystart, $DatePayend);
    if ($stmt->execute()) {
      $rs = $stmt->get_result();
      while ($rs && ($r = $rs->fetch_assoc())) {
        $pid = (int)($r['PlanPayId'] ?? 0);
        $sum = (float)($r['s'] ?? 0);
        if ($pid > 0) $sumPaidMap[$pid] = $sum;
      }
    }
    $stmt->close();
  }
}

$Remains  = $Amountset - $Amounts;
$Remains2 = $Amountset - $Amounts2;
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>ระบบบริหารจัดการการเงินและบัญชี</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="shortcut icon" type="image/x-icon" href="pic/fms.png" />

  <style>
    body {
      font-family: 'Sarabun', sans-serif;
      background:
        radial-gradient(1100px 600px at 12% 15%, rgba(91,155,213,.22), transparent 60%),
        radial-gradient(900px 520px at 92% 10%, rgba(0,176,80,.14), transparent 55%),
        linear-gradient(180deg, #f8fbff, #f6f8fc);
    }
    .container { max-width: 1200px; }

    /* ── Title bar ── */
    .page-titlebar {
      margin: 14px 0 16px;
      border-radius: 18px;
      padding: 14px 20px;
      background: rgba(255,255,255,.92);
      border: 1px solid #e9eef6;
      box-shadow: 0 12px 30px rgba(13,27,62,.08);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }
    .page-titlebar h3 {
      margin: 0;
      font-weight: 800;
      color: #1f2a44;
      font-size: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .page-titlebar .sub {
      color: #6b778c;
      margin-top: 4px;
      font-size: 13px;
    }

    /* ── Card panel ── */
    .card-panel {
      border-radius: 18px;
      border: 1px solid #e9eef6;
      background: rgba(255,255,255,.95);
      box-shadow: 0 8px 24px rgba(13,27,62,.07);
      overflow: hidden;
      margin-bottom: 16px;
    }
    .card-head {
      padding: 13px 18px;
      border-bottom: 1px solid #e9eef6;
      font-weight: 800;
      color: #1f2a44;
      background: linear-gradient(135deg, rgba(0,176,80,.13), rgba(0,176,80,.04));
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
    }
    .card-head-title {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 15px;
    }
    .card-body { padding: 16px 18px; }

    /* ── Year search form ── */
    .year-form-row {
      display: flex;
      align-items: flex-end;
      gap: 12px;
      flex-wrap: wrap;
    }
    .year-form-row .field-group {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }
    .year-form-row label {
      font-size: 13px;
      font-weight: 700;
      color: #374151;
    }
    .year-form-row .form-control {
      height: 42px;
      border-radius: 12px;
      border: 1.5px solid #dfe7f3;
      font-family: 'Sarabun', sans-serif;
      font-size: 15px;
      box-shadow: none;
      transition: border-color .2s;
      width: 140px;
    }
    .year-form-row .form-control:focus {
      border-color: #0B6E4F;
      outline: none;
      box-shadow: 0 0 0 3px rgba(11,110,79,.12);
    }
    .btn-search {
      height: 42px;
      padding: 0 22px;
      border-radius: 12px;
      background: linear-gradient(135deg, #0B6E4F, #08A045);
      color: #fff;
      border: none;
      font-family: 'Sarabun', sans-serif;
      font-weight: 700;
      font-size: 14px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      box-shadow: 0 6px 18px rgba(11,110,79,.22);
      transition: opacity .15s;
    }
    .btn-search:hover { opacity: .88; }
    .date-range-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #f0fdf4;
      border: 1px solid #bbf7d0;
      color: #15803d;
      border-radius: 10px;
      padding: 6px 14px;
      font-size: 13px;
      font-weight: 700;
      margin-bottom: 2px;
    }

    /* ── KPI cards ── */
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 12px;
    }
    @media (max-width: 992px) { .kpi-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 600px)  { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }

    .kpi-card {
      border-radius: 16px;
      padding: 16px 14px 14px;
      background: #fff;
      border: 1px solid #e9eef6;
      box-shadow: 0 4px 14px rgba(13,27,62,.06);
      border-left: 4px solid transparent;
      position: relative;
      overflow: hidden;
    }
    .kpi-card::before {
      content: '';
      position: absolute;
      top: 0; right: 0;
      width: 64px; height: 64px;
      border-radius: 0 0 0 64px;
      opacity: .07;
    }
    .kpi-card-blue   { border-left-color: #3B82F6; }
    .kpi-card-blue::before   { background: #3B82F6; }
    .kpi-card-purple { border-left-color: #8B5CF6; }
    .kpi-card-purple::before { background: #8B5CF6; }
    .kpi-card-teal   { border-left-color: #0D9488; }
    .kpi-card-teal::before   { background: #0D9488; }
    .kpi-card-amber  { border-left-color: #F59E0B; }
    .kpi-card-amber::before  { background: #F59E0B; }
    .kpi-card-green  { border-left-color: #16A34A; }
    .kpi-card-green::before  { background: #16A34A; }
    .kpi-card-red    { border-left-color: #DC2626; }
    .kpi-card-red::before    { background: #DC2626; }

    .kpi-icon {
      width: 38px; height: 38px;
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 10px;
      font-size: 20px;
    }
    .kpi-icon-blue   { background: #EFF6FF; color: #3B82F6; }
    .kpi-icon-purple { background: #F5F3FF; color: #8B5CF6; }
    .kpi-icon-teal   { background: #F0FDFA; color: #0D9488; }
    .kpi-icon-amber  { background: #FFFBEB; color: #F59E0B; }
    .kpi-icon-green  { background: #F0FDF4; color: #16A34A; }
    .kpi-icon-red    { background: #FEF2F2; color: #DC2626; }

    .kpi-label { font-size: 11px; font-weight: 700; color: #6b778c; text-transform: uppercase; letter-spacing: .5px; }
    .kpi-value { font-size: 18px; font-weight: 900; color: #1f2a44; margin-top: 4px; line-height: 1.2; }
    .kpi-hint  { font-size: 11px; color: #94a3b8; margin-top: 3px; }
    .kpi-value.val-neg { color: #b91c1c; }
    .kpi-value.val-pos { color: #0D9488; }

    /* ── Budget utilization bar ── */
    .budget-bar-wrap { margin-top: 6px; }
    .budget-bar-track {
      height: 5px; border-radius: 3px;
      background: #e9eef6;
      overflow: hidden;
    }
    .budget-bar-fill {
      height: 100%; border-radius: 3px;
      background: linear-gradient(90deg, #16A34A, #22c55e);
      transition: width .4s;
    }
    .budget-bar-fill.over { background: linear-gradient(90deg, #DC2626, #f87171); }
    .budget-pct { font-size: 10px; font-weight: 700; color: #64748b; margin-top: 2px; }

    /* ── Table ── */
    .table {
      margin-bottom: 0;
      font-size: 14px;
    }
    .table thead th {
      background: #e9f7ee;
      color: #1f2a44;
      font-weight: 800;
      border-bottom: 2px solid #d6f0df !important;
      vertical-align: middle !important;
      white-space: nowrap;
      padding: 11px 12px;
    }
    .table td { vertical-align: middle !important; padding: 9px 12px; }
    .row-type td {
      background: linear-gradient(135deg, #f8fafc, #f1f5f9) !important;
      font-weight: 900;
      color: #1f2a44;
      border-top: 2px solid #e2e8f0 !important;
    }
    .row-type .type-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: linear-gradient(135deg, #0B6E4F, #08A045);
      color: #fff;
      border-radius: 8px;
      padding: 3px 12px 3px 8px;
      font-size: 13px;
      font-weight: 800;
    }
    .row-subtotal td {
      background: #f8fafc !important;
      font-weight: 700;
      border-top: 1px solid #e2e8f0 !important;
      color: #374151;
      font-size: 13px;
    }
    .row-grand td {
      background: linear-gradient(135deg, #e9f7ee, #d1fae5) !important;
      font-weight: 900;
      border-top: 2px solid #a7f3d0 !important;
      color: #065f46;
    }
    .text-neg { color: #b91c1c; font-weight: 800; }
    .text-pos { color: #0f766e; font-weight: 800; }

    /* ── Edit pencil button ── */
    .btn-edit-plan {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 30px; height: 30px;
      border-radius: 8px;
      background: #FFF7ED;
      border: 1px solid #FDE68A;
      color: #D97706;
      cursor: pointer;
      text-decoration: none;
      transition: background .15s;
    }
    .btn-edit-plan:hover { background: #FDE68A; color: #92400E; }

    /* ── Plan amount input group ── */
    .plan-input-group {
      display: flex;
      align-items: center;
      gap: 6px;
      justify-content: flex-end;
    }
    .plan-input-group input {
      width: 160px;
      text-align: right;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
      padding: 4px 10px;
      font-size: 14px;
      font-family: 'Sarabun', sans-serif;
      background: #f8fafc;
      color: #1f2a44;
      font-weight: 700;
    }

    /* ── Modals ── */
    .modal-content { border-radius: 20px; overflow: hidden; border: none; box-shadow: 0 20px 60px rgba(0,0,0,.18); }
    .modal-header {
      background: linear-gradient(135deg, #0B6E4F, #08A045);
      border-bottom: none;
      padding: 16px 20px;
    }
    .modal-header .modal-title { color: #fff; font-weight: 800; font-size: 16px; display: flex; align-items: center; gap: 8px; }
    .modal-header .btn-close { filter: invert(1); opacity: .8; }
    .modal-body { padding: 20px; }
    .modal-footer { padding: 14px 20px; border-top: 1px solid #e9eef6; background: #f8fafc; }
    .modal-label { font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 6px; display: block; }
    .modal-input {
      width: 100%;
      height: 42px;
      border-radius: 12px;
      border: 1.5px solid #dfe7f3;
      padding: 0 14px;
      font-family: 'Sarabun', sans-serif;
      font-size: 15px;
      color: #1f2a44;
      transition: border-color .2s;
    }
    .modal-input:focus { border-color: #0B6E4F; outline: none; box-shadow: 0 0 0 3px rgba(11,110,79,.12); }
    .help-note { color: #6b778c; font-size: 12px; margin-top: 5px; }

    /* ── Buttons ── */
    .btn-export {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 7px 16px;
      border-radius: 10px;
      background: linear-gradient(135deg, #0B6E4F, #08A045);
      color: #fff;
      border: none;
      font-family: 'Sarabun', sans-serif;
      font-weight: 700;
      font-size: 13px;
      cursor: pointer;
      text-decoration: none;
      box-shadow: 0 4px 14px rgba(11,110,79,.22);
      transition: opacity .15s;
    }
    .btn-export:hover { opacity: .88; color: #fff; }
    .btn-modal-cancel {
      border-radius: 10px;
      font-family: 'Sarabun', sans-serif;
      font-weight: 700;
      border: 1.5px solid #e2e8f0;
      background: #fff;
      color: #374151;
      padding: 8px 18px;
    }
    .btn-modal-save {
      border-radius: 10px;
      font-family: 'Sarabun', sans-serif;
      font-weight: 700;
      background: linear-gradient(135deg, #0B6E4F, #08A045);
      color: #fff;
      border: none;
      padding: 8px 20px;
      box-shadow: 0 4px 14px rgba(11,110,79,.22);
    }

    .btn-go-back {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 7px 16px;
      border-radius: 10px;
      background: #f1f5f9;
      border: 1px solid #e2e8f0;
      color: #475569;
      font-weight: 700;
      font-size: 13px;
      text-decoration: none;
      transition: background .15s;
    }
    .btn-go-back:hover { background: #e2e8f0; color: #1e293b; }
  </style>

  <script>
    // datepicker: init เฉพาะถ้ามีปลั๊กอิน (กัน JS error)
    $(function(){
      if ($.fn.datepicker) {
        var d = new Date();
        var toDay = d.getDate() + '/' + (d.getMonth()+1) + '/' + (d.getFullYear()+543);

        $("#datepicker-th1").datepicker({
          dateFormat:'dd/mm/yy', isBuddhist:true, defaultDate: toDay,
          dayNames:['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'],
          dayNamesMin:['อา.','จ.','อ.','พ.','พฤ.','ศ.','ส.'],
          monthNames:['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'],
          monthNamesShort:['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.']
        });
        $("#datepicker-th2").datepicker({
          dateFormat:'dd/mm/yy', isBuddhist:true, defaultDate: toDay,
          dayNames:['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'],
          dayNamesMin:['อา.','จ.','อ.','พ.','พฤ.','ศ.','ส.'],
          monthNames:['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'],
          monthNamesShort:['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.']
        });
      }
    });

    // confirm ก่อนบันทึกประมาณการ
    $(document).on('submit', '.js-setplan-form', function(e){
      e.preventDefault();
      const form = this;
      Swal.fire({
        icon: 'question',
        title: 'ยืนยันการบันทึก?',
        text: 'ต้องการบันทึกประมาณการรายการนี้หรือไม่',
        showCancelButton: true,
        confirmButtonText: 'บันทึก',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#0B6E4F'
      }).then((r)=>{ if(r.isConfirmed) form.submit(); });
    });
  </script>
</head>

<body>
  <?php echo $__header_html; ?>

  <div class="container">

    <!-- Title bar -->
    <div class="page-titlebar">
      <div>
        <h3>
          <span class="msi msi-24" style="color:#0B6E4F;">list_alt</span>
          รายการค่าใช้จ่ายตามแผนเงินบำรุง
        </h3>
        <div class="sub">
          ปีงบประมาณ พ.ศ. <b><?= h($Qyear) ?></b>
          &nbsp;·&nbsp;<?= h($DatePaystart) ?> ถึง <?= h($DatePayend) ?>
        </div>
      </div>
      <a href="statistics.php" class="btn-go-back">
        <span class="msi">arrow_back</span> กลับ
      </a>
    </div>

    <!-- Nav tabs -->
    <div class="card-panel">
      <div class="card-body" style="padding-bottom: 0; padding-top: 12px;">
        <ul class="nav-tabs-modern">
          <li class="active">
            <a href="plan.php">
              <span class="msi msi-18">format_list_bulleted</span> รายการค่าใช้จ่ายตามแผนเงินบำรุง
            </a>
          </li>
          <li>
            <a href="planbudget.php">
              <span class="msi msi-18">account_balance</span> รายการค่าใช้จ่ายตามแผนเงินงบประมาณ
            </a>
          </li>
          <li>
            <a href="statistics.php">
              <span class="msi msi-18">bar_chart</span> สถิติและรายงาน
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Year selector -->
    <div class="card-panel">
      <div class="card-head">
        <div class="card-head-title">
          <span class="msi msi-18" style="color:#0B6E4F;">calendar_month</span>
          ระบุปีงบประมาณ
        </div>
        <div style="color:#64748b; font-size:12px; font-weight:700;">
          ระบบตั้งค่าเริ่มต้นเป็นปีงบประมาณปัจจุบัน
        </div>
      </div>
      <div class="card-body">
        <form method="get" action="<?= h($_SERVER['SCRIPT_NAME']) ?>">
          <div class="year-form-row">
            <div class="field-group">
              <label for="Qyear">ปีงบประมาณ พ.ศ.</label>
              <input class="form-control" name="Qyear" id="Qyear" type="text"
                     value="<?= h($Qyear) ?>" placeholder="เช่น 2568" required
                     style="width:140px;">
            </div>
            <button type="submit" class="btn-search">
              <span class="msi msi-18">search</span> แสดงผล
            </button>
            <div class="date-range-badge">
              <span class="msi msi-18">date_range</span>
              <?= h($DatePaystart) ?> &nbsp;—&nbsp; <?= h($DatePayend) ?>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- KPI Summary -->
    <div class="card-panel">
      <div class="card-head">
        <div class="card-head-title">
          <span class="msi msi-18" style="color:#0B6E4F;">dashboard</span>
          สรุปภาพรวมงบประมาณ
        </div>
        <div>
          <a data-bs-toggle="modal" data-bs-target="#myModalplan" href="#" class="btn-export">
            <span class="msi msi-18">download</span> Export Excel
          </a>
        </div>
      </div>
      <div class="card-body">
        <?php if (!$db_ok): ?>
          <div class="alert alert-danger" style="border-radius:14px;">ไม่สามารถเชื่อมต่อฐานข้อมูลได้ (ตรวจสอบ connect_db.php)</div>
        <?php else: ?>
          <div class="kpi-grid">

            <!-- รวมประมาณการ -->
            <div class="kpi-card kpi-card-blue">
              <div class="kpi-icon kpi-icon-blue">
                <span class="msi">savings</span>
              </div>
              <div class="kpi-label">รวมประมาณการทั้งสิ้น</div>
              <div class="kpi-value"><?= number_format($Amountset, 2) ?></div>
              <div class="kpi-hint">จาก planpayset ปี <?= h($Qyear) ?></div>
            </div>

            <!-- จ่ายตามแผน -->
            <div class="kpi-card kpi-card-purple">
              <div class="kpi-icon kpi-icon-purple">
                <span class="msi">event_available</span>
              </div>
              <div class="kpi-label">จ่ายตามแผน (DateIn)</div>
              <div class="kpi-value"><?= number_format($Amounts, 2) ?></div>
              <div class="kpi-hint">ภายในปีงบประมาณ</div>
              <?php if ($Amountset > 0): ?>
                <?php $pct1 = min(100, round($Amounts / $Amountset * 100)); ?>
                <div class="budget-bar-wrap">
                  <div class="budget-bar-track">
                    <div class="budget-bar-fill <?= $pct1 > 100 ? 'over' : '' ?>"
                         style="width:<?= $pct1 ?>%; background: linear-gradient(90deg,#8B5CF6,#a78bfa);"></div>
                  </div>
                  <div class="budget-pct"><?= $pct1 ?>% ของประมาณการ</div>
                </div>
              <?php endif; ?>
            </div>

            <!-- คงเหลือตามแผน -->
            <div class="kpi-card <?= $Remains < 0 ? 'kpi-card-red' : 'kpi-card-teal' ?>">
              <div class="kpi-icon <?= $Remains < 0 ? 'kpi-icon-red' : 'kpi-icon-teal' ?>">
                <span class="msi"><?= $Remains < 0 ? 'trending_down' : 'account_balance_wallet' ?></span>
              </div>
              <div class="kpi-label">คงเหลือตามแผน</div>
              <div class="kpi-value <?= $Remains < 0 ? 'val-neg' : 'val-pos' ?>"><?= number_format($Remains, 2) ?></div>
              <div class="kpi-hint">ประมาณการ − จ่ายตามแผน</div>
            </div>

            <!-- จ่ายจริง -->
            <div class="kpi-card kpi-card-amber">
              <div class="kpi-icon kpi-icon-amber">
                <span class="msi">payments</span>
              </div>
              <div class="kpi-label">จ่ายจริง (DatePaid)</div>
              <div class="kpi-value"><?= number_format($Amounts2, 2) ?></div>
              <div class="kpi-hint">ภายในปีงบประมาณ</div>
              <?php if ($Amountset > 0): ?>
                <?php $pct2 = min(100, round($Amounts2 / $Amountset * 100)); ?>
                <div class="budget-bar-wrap">
                  <div class="budget-bar-track">
                    <div class="budget-bar-fill <?= $pct2 > 100 ? 'over' : '' ?>"
                         style="width:<?= $pct2 ?>%; background: linear-gradient(90deg,#F59E0B,#fbbf24);"></div>
                  </div>
                  <div class="budget-pct"><?= $pct2 ?>% ของประมาณการ</div>
                </div>
              <?php endif; ?>
            </div>

            <!-- คงเหลือจ่ายจริง -->
            <div class="kpi-card <?= $Remains2 < 0 ? 'kpi-card-red' : 'kpi-card-green' ?>">
              <div class="kpi-icon <?= $Remains2 < 0 ? 'kpi-icon-red' : 'kpi-icon-green' ?>">
                <span class="msi"><?= $Remains2 < 0 ? 'trending_down' : 'check_circle' ?></span>
              </div>
              <div class="kpi-label">คงเหลือจ่ายจริง</div>
              <div class="kpi-value <?= $Remains2 < 0 ? 'val-neg' : 'val-pos' ?>"><?= number_format($Remains2, 2) ?></div>
              <div class="kpi-hint">ประมาณการ − จ่ายจริง</div>
            </div>

          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Table -->
    <div class="card-panel">
      <div class="card-head">
        <div class="card-head-title">
          <span class="msi msi-18" style="color:#0B6E4F;">table_chart</span>
          รายการค่าใช้จ่ายตามแผนเงินบำรุง
        </div>
        <div style="color:#64748b; font-weight:700; font-size:12px; display:flex; align-items:center; gap:4px;">
          <span class="msi msi-18" style="color:#D97706;">edit</span>
          คลิกไอคอนดินสอเพื่อแก้ "ประมาณการ"
        </div>
      </div>
      <div class="card-body" style="padding: 0;">
        <?php if (!$db_ok): ?>
          <div class="alert alert-danger" style="border-radius:0; margin:0;">ไม่สามารถเชื่อมต่อฐานข้อมูลได้ (ตรวจสอบ connect_db.php)</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover" style="margin-bottom:0;">
              <thead>
                <tr>
                  <th style="width:36px;"></th>
                  <th>ค่าใช้จ่าย</th>
                  <th style="width:200px; text-align:right;">ประมาณการ (บาท)</th>
                  <th style="width:150px; text-align:right;">จ่ายตามแผน</th>
                  <th style="width:150px; text-align:right;">คงเหลือตามแผน</th>
                  <th style="width:150px; text-align:right;">จ่ายจริง</th>
                  <th style="width:150px; text-align:right;">คงเหลือจ่ายจริง</th>
                  <th style="width:36px;"></th>
                </tr>
              </thead>
              <tbody>
              <?php
                $typeIds = array_unique(array_merge(array_keys($types), array_keys($itemsByType)));
                sort($typeIds);

                $grandPlan = 0; $grandIn = 0; $grandPaid = 0;

                foreach ($typeIds as $tid):
                  $typeName = $types[$tid] ?? ("หมวด ".$tid);
                  $items = $itemsByType[$tid] ?? [];
                  if (!$items) continue;
              ?>
                <tr class="row-type">
                  <td></td>
                  <td colspan="6">
                    <span class="type-badge">
                      <span class="msi msi-18">folder_open</span>
                      <?= h($typeName) ?>
                    </span>
                  </td>
                  <td></td>
                </tr>
              <?php
                  $sumPlan = 0; $sumIn = 0; $sumPaid = 0;

                  foreach ($items as $it):
                    $pid = (int)$it['PlanPayId'];
                    $pname = (string)$it['PlanPayName'];

                    $planAmt = (float)($planAmountMap[$pid] ?? 0);
                    $sumInAmt = (float)($sumInMap[$pid] ?? 0);
                    $sumPaidAmt = (float)($sumPaidMap[$pid] ?? 0);

                    $remain = $planAmt - $sumInAmt;
                    $remain2 = $planAmt - $sumPaidAmt;

                    $sumPlan += $planAmt;
                    $sumIn   += $sumInAmt;
                    $sumPaid += $sumPaidAmt;

                    $textRemain  = $remain  < 0 ? 'text-neg' : 'text-pos';
                    $textRemain2 = $remain2 < 0 ? 'text-neg' : 'text-pos';

                    $modalSetId = "myModalset".$pid;
              ?>
                <tr>
                  <td></td>
                  <td><?= h($pname) ?></td>
                  <td style="text-align:right;">
                    <div class="plan-input-group">
                      <input value="<?= number_format($planAmt, 2) ?>" disabled>
                      <a class="btn-edit-plan"
                         data-bs-toggle="modal"
                         data-bs-target="#<?= h($modalSetId) ?>"
                         title="แก้ไขประมาณการ"
                         href="#">
                        <span class="msi msi-18">edit</span>
                      </a>
                    </div>
                  </td>
                  <td style="text-align:right;"><?= number_format($sumInAmt, 2) ?></td>
                  <td style="text-align:right;" class="<?= $textRemain ?>"><?= number_format($remain, 2) ?></td>
                  <td style="text-align:right;"><?= number_format($sumPaidAmt, 2) ?></td>
                  <td style="text-align:right;" class="<?= $textRemain2 ?>"><?= number_format($remain2, 2) ?></td>
                  <td></td>
                </tr>

                <!-- Modal: edit plan amount -->
                <div class="modal fade" id="<?= h($modalSetId) ?>" tabindex="-1" aria-labelledby="<?= h($modalSetId) ?>Label" aria-hidden="true">
                  <div class="modal-dialog modal-sm modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <div class="modal-title" id="<?= h($modalSetId) ?>Label">
                          <span class="msi msi-18">edit</span>
                          ประมาณการปี พ.ศ. <?= h($Qyear) ?>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <form class="js-setplan-form" method="post" action="setplan.php">
                        <div class="modal-body">
                          <input type="hidden" name="PlanPayId" value="<?= (int)$pid ?>">
                          <input type="hidden" name="Qyears" value="<?= h($Qyear) ?>">
                          <div style="margin-bottom:12px;">
                            <span class="modal-label" style="font-size:14px; color:#1f2a44;"><?= h($pname) ?></span>
                          </div>
                          <label class="modal-label">จำนวนเงิน (บาท)</label>
                          <input class="modal-input" name="Amount" type="text" value="<?= h($planAmt) ?>">
                          <div class="help-note">แนะนำกรอกเป็นตัวเลข เช่น 15000 หรือ 15000.50</div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-modal-cancel" data-bs-dismiss="modal">ยกเลิก</button>
                          <button type="submit" class="btn btn-modal-save">
                            <span class="msi msi-18">save</span> บันทึก
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

              <?php
                  endforeach;

                  $sumRemain = $sumPlan - $sumIn;
                  $sumRemain2 = $sumPlan - $sumPaid;

                  $grandPlan += $sumPlan;
                  $grandIn   += $sumIn;
                  $grandPaid += $sumPaid;

                  $cls1 = $sumRemain  < 0 ? 'text-neg' : 'text-pos';
                  $cls2 = $sumRemain2 < 0 ? 'text-neg' : 'text-pos';
              ?>
                <tr class="row-subtotal">
                  <td></td>
                  <td style="text-align:right;">รวมหมวด</td>
                  <td style="text-align:right;"><?= number_format($sumPlan,  2) ?></td>
                  <td style="text-align:right;"><?= number_format($sumIn,    2) ?></td>
                  <td style="text-align:right;" class="<?= $cls1 ?>"><?= number_format($sumRemain,  2) ?></td>
                  <td style="text-align:right;"><?= number_format($sumPaid,  2) ?></td>
                  <td style="text-align:right;" class="<?= $cls2 ?>"><?= number_format($sumRemain2, 2) ?></td>
                  <td></td>
                </tr>
              <?php endforeach; ?>

              <tr class="row-grand">
                <td></td>
                <td style="text-align:right;">รวมจำนวนเงินทั้งสิ้น</td>
                <td style="text-align:right;"><?= number_format($Amountset, 2) ?></td>
                <td style="text-align:right;"><?= number_format($Amounts,   2) ?></td>
                <td style="text-align:right;" class="<?= $Remains  < 0 ? 'text-neg' : 'text-pos' ?>"><?= number_format($Remains,  2) ?></td>
                <td style="text-align:right;"><?= number_format($Amounts2,  2) ?></td>
                <td style="text-align:right;" class="<?= $Remains2 < 0 ? 'text-neg' : 'text-pos' ?>"><?= number_format($Remains2, 2) ?></td>
                <td></td>
              </tr>

              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Modal: export excel -->
    <div class="modal fade" id="myModalplan" tabindex="-1" aria-labelledby="myModalplanLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <div class="modal-title" id="myModalplanLabel">
              <span class="msi msi-18">download</span> ระบุช่วงเวลา Export
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="get" action="report.php" target="_blank">
            <div class="modal-body">
              <div style="margin-bottom:14px;">
                <label class="modal-label">ปีงบประมาณ พ.ศ.</label>
                <input class="modal-input" name="Qyear" type="text" value="<?= h($Qyear) ?>">
              </div>
              <div style="margin-bottom:14px;">
                <label class="modal-label">ระหว่างวันที่</label>
                <input class="modal-input" name="DatePaystart" id="datepicker-th1" value="<?= h($DatePaystart_th) ?>">
              </div>
              <div style="margin-bottom:6px;">
                <label class="modal-label">ถึงวันที่</label>
                <input class="modal-input" name="DatePayend" id="datepicker-th2" value="<?= h($DatePayend_th) ?>">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-modal-cancel" data-bs-dismiss="modal">ยกเลิก</button>
              <button type="submit" class="btn btn-modal-save">
                <span class="msi msi-18">download</span> ตกลง Export
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div style="height:16px;"></div>
  </div>

  <?php if (!empty($_GET['sw'])): ?>
  <script>
  (function(){
    const sw  = <?= json_encode($_GET['sw']) ?>;
    const msg = <?= json_encode($_GET['msg'] ?? '') ?>;
    if (typeof Swal === 'undefined') return;
    if (sw === 'success') {
      Swal.fire({ icon:'success', title:'สำเร็จ', text: msg || 'บันทึกข้อมูลเรียบร้อย', timer:1500, showConfirmButton:false });
    } else if (sw === 'error') {
      Swal.fire({ icon:'error', title:'ไม่สำเร็จ', text: msg || 'เกิดข้อผิดพลาด' });
    }
  })();
  </script>
  <?php endif; ?>

</body>
</html>
