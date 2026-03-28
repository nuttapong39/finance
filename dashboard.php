<?php
require_once __DIR__ . '/header.php';
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Bangkok');

/* ── Helpers ─────────────────────────────────────────── */
function dbDate(string $d): string {
    if (!$d || $d === '0000-00-00') return '-';
    $ts = strtotime($d); if (!$ts) return '-';
    $m = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    return date('j', $ts) . ' ' . $m[(int)date('n', $ts)] . ' ' . (date('Y', $ts) + 543);
}
$thMonths = ['01'=>'ม.ค.','02'=>'ก.พ.','03'=>'มี.ค.','04'=>'เม.ย.','05'=>'พ.ค.','06'=>'มิ.ย.',
             '07'=>'ก.ค.','08'=>'ส.ค.','09'=>'ก.ย.','10'=>'ต.ค.','11'=>'พ.ย.','12'=>'ธ.ค.'];

/* ── Fiscal Year (Thai: Oct–Sep) ─────────────────────── */
$cy = (int)date('Y'); $cm = (int)date('n');
if ($cm >= 10) { $fyBE = $cy + 544; $fyStart = "$cy-10-01"; $fyEnd = ($cy+1).'-09-30'; }
else           { $fyBE = $cy + 543; $fyStart = ($cy-1).'-10-01'; $fyEnd = "$cy-09-30"; }

/* ── 1. KPI ──────────────────────────────────────────── */
$kpi = $conn->query("
    SELECT COUNT(*) AS total,
      SUM(CASE WHEN DateReceive IN ('0000-00-00','') OR DateReceive IS NULL THEN 1 ELSE 0 END) AS st_wait,
      SUM(CASE WHEN (DateReceive NOT IN ('0000-00-00','') AND DateReceive IS NOT NULL)
               AND  (DateApprove IN ('0000-00-00','') OR DateApprove IS NULL) THEN 1 ELSE 0 END) AS st_approve,
      SUM(CASE WHEN (DateApprove NOT IN ('0000-00-00','') AND DateApprove IS NOT NULL)
               AND  (DatePay IN ('0000-00-00','') OR DatePay IS NULL) THEN 1 ELSE 0 END) AS st_cheque,
      SUM(CASE WHEN DatePay NOT IN ('0000-00-00','') AND DatePay IS NOT NULL THEN 1 ELSE 0 END) AS st_paid,
      COALESCE(SUM(Amount),0) AS total_amount,
      COALESCE(SUM(CASE WHEN DatePay NOT IN ('0000-00-00','') AND DatePay IS NOT NULL THEN Net ELSE 0 END),0) AS paid_net,
      COALESCE(SUM(CASE WHEN DateIn BETWEEN '$fyStart' AND '$fyEnd' THEN Amount ELSE 0 END),0) AS fy_amount
    FROM payment")->fetch_assoc();

/* ── 2. Monthly trend (12 months) ────────────────────── */
$mRows = []; $mRes = $conn->query("
    SELECT DATE_FORMAT(DateIn,'%Y-%m') AS ym, SUM(Amount) AS total, COUNT(*) AS cnt
    FROM payment WHERE DateIn >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND DateIn NOT IN ('0000-00-00','')
    GROUP BY ym ORDER BY ym ASC");
while ($r = $mRes->fetch_assoc()) $mRows[] = $r;

/* ── 3. Source (เงินบำรุง / งบประมาณ) ──────────────── */
$srcRows = []; $srcRes = $conn->query("SELECT Source, COUNT(*) AS cnt, COALESCE(SUM(Amount),0) AS total FROM payment GROUP BY Source");
while ($r = $srcRes->fetch_assoc()) $srcRows[(int)$r['Source']] = $r;

/* ── 4. Top 5 Companies ───────────────────────────────── */
$topCo = []; $res = $conn->query("
    SELECT COALESCE(c.CompanyName,'ไม่ระบุ') AS name, COUNT(p.PayId) AS cnt, COALESCE(SUM(p.Amount),0) AS total
    FROM payment p LEFT JOIN company c ON c.CompanyId=p.CompanyId
    GROUP BY p.CompanyId ORDER BY total DESC LIMIT 5");
while ($r = $res->fetch_assoc()) $topCo[] = $r;

/* ── 5. Top 5 Departments ────────────────────────────── */
$topDept = []; $res = $conn->query("
    SELECT COALESCE(d.DeptName,'ไม่ระบุ') AS name, COUNT(p.PayId) AS cnt, COALESCE(SUM(p.Amount),0) AS total
    FROM payment p LEFT JOIN department d ON d.DeptId=p.DeptId
    GROUP BY p.DeptId ORDER BY total DESC LIMIT 5");
while ($r = $res->fetch_assoc()) $topDept[] = $r;

/* ── 6. Expense Categories ───────────────────────────── */
$typRows = []; $res = $conn->query("
    SELECT COALESCE(t.TypesName,'ไม่ระบุ') AS name, COUNT(p.PayId) AS cnt, COALESCE(SUM(p.Amount),0) AS total
    FROM payment p LEFT JOIN types t ON t.TypesId=p.TypesId
    GROUP BY p.TypesId ORDER BY total DESC LIMIT 8");
while ($r = $res->fetch_assoc()) $typRows[] = $r;

/* ── 7. Budget vs Actual (เงินบำรุง, current FY) ────── */
$budRows = []; $res = $conn->query("
    SELECT pp.PlanPayName AS planName,
           COALESCE(ps.Amount,0) AS budget, COALESCE(SUM(p.Amount),0) AS actual
    FROM planpay pp
    LEFT JOIN planpayset ps ON ps.PlanPayId=pp.PlanPayId AND ps.Qyear=$fyBE
    LEFT JOIN payment p ON p.PlanPayId=pp.PlanPayId AND p.Source=1 AND p.DateIn BETWEEN '$fyStart' AND '$fyEnd'
    GROUP BY pp.PlanPayId HAVING budget>0 OR actual>0 ORDER BY pp.PlanPayId LIMIT 10");
while ($r = $res->fetch_assoc()) $budRows[] = $r;

/* ── 8. Recent 10 transactions ───────────────────────── */
$recent = []; $res = $conn->query("
    SELECT p.PayId, p.DateIn, p.Detail, p.Amount, p.Source, p.DateReceive, p.DateApprove, p.DatePay,
           COALESCE(c.CompanyName,'—') AS CompanyName
    FROM payment p LEFT JOIN company c ON c.CompanyId=p.CompanyId ORDER BY p.PayId DESC LIMIT 10");
while ($r = $res->fetch_assoc()) $recent[] = $r;

/* ── Chart.js data (JSON) ─────────────────────────────── */
$mLabels   = []; $mAmounts  = [];
foreach ($mRows as $row) {
    [$y,$m] = explode('-', $row['ym']);
    $mLabels[]  = ($thMonths[$m]??$m).' '.((int)$y+543);
    $mAmounts[] = round((float)$row['total'],2);
}
$coLabels  = array_column($topCo,   'name');
$coAmounts = array_map(fn($r)=>round((float)$r['total'],2), $topCo);
$dLabels   = array_column($topDept, 'name');
$dAmounts  = array_map(fn($r)=>round((float)$r['total'],2), $topDept);
$tLabels   = array_column($typRows, 'name');
$tAmounts  = array_map(fn($r)=>round((float)$r['total'],2), $typRows);
$bLabels   = array_column($budRows, 'planName');
$bBudget   = array_map(fn($r)=>round((float)$r['budget'],2), $budRows);
$bActual   = array_map(fn($r)=>round((float)$r['actual'],2), $budRows);
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" href="pic/fms.png">
  <title>Dashboard – ระบบบริหารจัดการการเงินและบัญชี</title>
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <style>
    /* ── Page wrapper ── */
    .dash-wrap { padding: 22px 24px 40px; }

    /* ── Page title bar ── */
    .page-titlebar { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:22px; }
    .page-titlebar h3 { margin:0; font-size:20px; font-weight:800; color:#1f2a44; display:flex; align-items:center; gap:8px; }
    .page-titlebar .sub { font-size:13px; color:#64748b; margin-top:3px; }

    /* ── KPI Cards ── */
    .kpi-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:14px; margin-bottom:22px; }
    .kpi-card { background:#fff; border-radius:16px; padding:18px 18px 16px;
                box-shadow:0 2px 12px rgba(0,0,0,.07); border-top:4px solid transparent;
                display:flex; align-items:center; gap:14px; transition:transform .18s; }
    .kpi-card:hover { transform:translateY(-3px); }
    .kpi-card.kc-green  { border-color:#0B6E4F; }
    .kpi-card.kc-amber  { border-color:#F59E0B; }
    .kpi-card.kc-orange { border-color:#F97316; }
    .kpi-card.kc-purple { border-color:#8B5CF6; }
    .kpi-card.kc-blue   { border-color:#3B82F6; }
    .kpi-card.kc-teal   { border-color:#14B8A6; }
    .kpi-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .kc-green  .kpi-icon { background:#e8f5e9; color:#0B6E4F; }
    .kc-amber  .kpi-icon { background:#fef3c7; color:#D97706; }
    .kc-orange .kpi-icon { background:#fff7ed; color:#EA580C; }
    .kc-purple .kpi-icon { background:#f5f3ff; color:#7C3AED; }
    .kc-blue   .kpi-icon { background:#eff6ff; color:#2563EB; }
    .kc-teal   .kpi-icon { background:#f0fdfa; color:#0D9488; }
    .kpi-label { font-size:12px; color:#64748b; font-weight:600; margin-bottom:3px; }
    .kpi-value { font-size:22px; font-weight:800; color:#1f2a44; line-height:1; }
    .kpi-sub   { font-size:11px; color:#94a3b8; margin-top:3px; }

    /* ── Card ── */
    .dash-card { background:#fff; border-radius:16px; box-shadow:0 2px 12px rgba(0,0,0,.07); margin-bottom:18px; overflow:hidden; }
    .dash-card-head { padding:14px 18px 12px; border-bottom:1px solid #f1f5f9;
                      display:flex; align-items:center; justify-content:space-between; gap:8px; }
    .dash-card-head .title { font-size:14px; font-weight:800; color:#1f2a44; display:flex; align-items:center; gap:7px; }
    .dash-card-head .badge-fy { font-size:11px; font-weight:700; background:#e8f5e9; color:#0B6E4F;
                                 padding:3px 10px; border-radius:99px; }
    .dash-card-body { padding:16px 18px; }

    /* ── Workflow Pipeline ── */
    .pipeline { display:flex; gap:0; margin:4px 0; }
    .pipe-step { flex:1; text-align:center; padding:12px 8px; position:relative; }
    .pipe-step::after { content:''; position:absolute; right:-1px; top:50%; transform:translateY(-50%);
                        width:0; height:0; border:10px solid transparent; z-index:1; }
    .pipe-step.ps-amber  { background:#fef3c7; }
    .pipe-step.ps-orange { background:#fff7ed; }
    .pipe-step.ps-purple { background:#f5f3ff; }
    .pipe-step.ps-blue   { background:#eff6ff; }
    .pipe-step.ps-amber::after  { border-left-color:#fef3c7; }
    .pipe-step.ps-orange::after { border-left-color:#fff7ed; }
    .pipe-step.ps-purple::after { border-left-color:#f5f3ff; }
    .pipe-step:last-child::after { display:none; }
    .pipe-num   { font-size:24px; font-weight:800; line-height:1; }
    .pipe-label { font-size:11px; font-weight:600; color:#64748b; margin-top:4px; }
    .pipe-step.ps-amber  .pipe-num { color:#D97706; }
    .pipe-step.ps-orange .pipe-num { color:#EA580C; }
    .pipe-step.ps-purple .pipe-num { color:#7C3AED; }
    .pipe-step.ps-blue   .pipe-num { color:#2563EB; }

    /* ── Budget table ── */
    .budget-table { width:100%; border-collapse:collapse; font-size:13px; }
    .budget-table th { font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;
                       letter-spacing:.4px; padding:8px 10px; border-bottom:2px solid #f1f5f9; }
    .budget-table td { padding:9px 10px; border-bottom:1px solid #f8fafc; vertical-align:middle; }
    .budget-table tr:last-child td { border:none; }
    .budget-table tr:hover td { background:#f8fafc; }
    .prog-bar-wrap { height:8px; background:#f1f5f9; border-radius:99px; overflow:hidden; min-width:80px; }
    .prog-bar-fill { height:100%; border-radius:99px; background:linear-gradient(90deg,#0B6E4F,#08A045); }
    .prog-bar-fill.over { background:linear-gradient(90deg,#ef4444,#f97316); }
    .text-over { color:#ef4444; font-weight:700; }

    /* ── Recent table ── */
    .rt { width:100%; border-collapse:collapse; font-size:13px; }
    .rt th { font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.4px;
             padding:9px 10px; border-bottom:2px solid #f1f5f9; white-space:nowrap; }
    .rt td { padding:9px 10px; border-bottom:1px solid #f8fafc; vertical-align:middle; }
    .rt tr:last-child td { border:none; }
    .rt tr:hover td { background:#f8fafc; }
    .st-badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:99px;
                font-size:11px; font-weight:700; white-space:nowrap; }
    .st-wait    { background:#fef3c7; color:#D97706; }
    .st-approve { background:#fff7ed; color:#EA580C; }
    .st-cheque  { background:#f5f3ff; color:#7C3AED; }
    .st-paid    { background:#ecfdf5; color:#059669; }
    .amount-cell { font-weight:700; color:#0B6E4F; text-align:right; white-space:nowrap; }

    /* ── Chart canvas ── */
    .chart-wrap { position:relative; }

    /* ── Responsive ── */
    @media(max-width:992px) { .kpi-grid { grid-template-columns:repeat(3,1fr); } }
    @media(max-width:576px) { .kpi-grid { grid-template-columns:repeat(2,1fr); } .dash-wrap { padding:14px 12px 32px; } }
  </style>
</head>
<body>
<div class="dash-wrap">

  <!-- Page title -->
  <div class="page-titlebar">
    <div>
      <h3><span class="msi msi-24" style="color:#0B6E4F;">dashboard</span> แดชบอร์ดภาพรวม</h3>
      <div class="sub">วิเคราะห์ข้อมูลการเงิน · ปีงบประมาณ <?= $fyBE ?> (<?= $fyStart ?> ถึง <?= $fyEnd ?>)</div>
    </div>
    <a href="main.php" class="btn-go-back"><span class="msi">arrow_back</span> กลับหน้าหลัก</a>
  </div>

  <!-- ── KPI Cards ── -->
  <div class="kpi-grid">
    <div class="kpi-card kc-green">
      <div class="kpi-icon"><span class="msi msi-24">receipt_long</span></div>
      <div>
        <div class="kpi-label">รายการทั้งหมด</div>
        <div class="kpi-value"><?= number_format((int)$kpi['total']) ?></div>
        <div class="kpi-sub">รายการในระบบ</div>
      </div>
    </div>
    <div class="kpi-card kc-amber">
      <div class="kpi-icon"><span class="msi msi-24">pending</span></div>
      <div>
        <div class="kpi-label">รอรับเอกสาร</div>
        <div class="kpi-value"><?= number_format((int)$kpi['st_wait']) ?></div>
        <div class="kpi-sub">ยังไม่ลงรับ</div>
      </div>
    </div>
    <div class="kpi-card kc-orange">
      <div class="kpi-icon"><span class="msi msi-24">hourglass_empty</span></div>
      <div>
        <div class="kpi-label">รอ / อยู่ระหว่างอนุมัติ</div>
        <div class="kpi-value"><?= number_format((int)$kpi['st_approve']) ?></div>
        <div class="kpi-sub">ยังไม่อนุมัติ</div>
      </div>
    </div>
    <div class="kpi-card kc-purple">
      <div class="kpi-icon"><span class="msi msi-24">edit_document</span></div>
      <div>
        <div class="kpi-label">จัดทำเช็ค</div>
        <div class="kpi-value"><?= number_format((int)$kpi['st_cheque']) ?></div>
        <div class="kpi-sub">รออกเช็ค</div>
      </div>
    </div>
    <div class="kpi-card kc-blue">
      <div class="kpi-icon"><span class="msi msi-24">payments</span></div>
      <div>
        <div class="kpi-label">เบิกจ่ายแล้ว</div>
        <div class="kpi-value"><?= number_format((int)$kpi['st_paid']) ?></div>
        <div class="kpi-sub">ดำเนินการครบ</div>
      </div>
    </div>
  </div>

  <!-- ── Row: ยอดรวม + ปีงบประมาณ ── -->
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="kpi-card kc-green" style="border-radius:16px;">
        <div class="kpi-icon"><span class="msi msi-24">account_balance_wallet</span></div>
        <div>
          <div class="kpi-label">ยอดรวมทั้งหมด (บาท)</div>
          <div class="kpi-value" style="font-size:18px;"><?= number_format((float)$kpi['total_amount'],2) ?></div>
          <div class="kpi-sub">ทุกรายการในระบบ</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="kpi-card kc-teal" style="border-radius:16px;">
        <div class="kpi-icon"><span class="msi msi-24">check_circle</span></div>
        <div>
          <div class="kpi-label">ยอดสุทธิ (Net) ที่จ่ายแล้ว</div>
          <div class="kpi-value" style="font-size:18px;"><?= number_format((float)$kpi['paid_net'],2) ?></div>
          <div class="kpi-sub">หลังหักภาษี ณ ที่จ่าย</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="kpi-card kc-blue" style="border-radius:16px;">
        <div class="kpi-icon"><span class="msi msi-24">calendar_month</span></div>
        <div>
          <div class="kpi-label">ยอดปีงบประมาณ <?= $fyBE ?></div>
          <div class="kpi-value" style="font-size:18px;"><?= number_format((float)$kpi['fy_amount'],2) ?></div>
          <div class="kpi-sub">ต.ค. <?= $fyBE-1 ?> – ก.ย. <?= $fyBE ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Row: Monthly trend + Source pie ── -->
  <div class="row g-3 mb-0">
    <div class="col-lg-8">
      <div class="dash-card">
        <div class="dash-card-head">
          <div class="title"><span class="msi">bar_chart</span> ยอดเบิกจ่ายรายเดือน (12 เดือนล่าสุด)</div>
        </div>
        <div class="dash-card-body">
          <div class="chart-wrap" style="height:260px;">
            <canvas id="chartMonthly"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="dash-card">
        <div class="dash-card-head">
          <div class="title"><span class="msi">donut_large</span> แหล่งเงิน</div>
        </div>
        <div class="dash-card-body">
          <div class="chart-wrap" style="height:200px;">
            <canvas id="chartSource"></canvas>
          </div>
          <div style="display:flex;justify-content:center;gap:20px;margin-top:10px;font-size:12px;font-weight:700;">
            <span style="display:flex;align-items:center;gap:5px;">
              <span style="width:12px;height:12px;border-radius:3px;background:#0B6E4F;display:inline-block;"></span>
              เงินบำรุง (<?= number_format((int)($srcRows[1]['cnt']??0)) ?>)
            </span>
            <span style="display:flex;align-items:center;gap:5px;">
              <span style="width:12px;height:12px;border-radius:3px;background:#3B82F6;display:inline-block;"></span>
              งบประมาณ (<?= number_format((int)($srcRows[2]['cnt']??0)) ?>)
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Row: Top Companies + Top Depts ── -->
  <div class="row g-3 mb-0 mt-0">
    <div class="col-lg-6">
      <div class="dash-card">
        <div class="dash-card-head">
          <div class="title"><span class="msi">store</span> Top 5 บริษัท/ร้านค้า (ยอดสูงสุด)</div>
        </div>
        <div class="dash-card-body">
          <div class="chart-wrap" style="height:220px;">
            <canvas id="chartCompany"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="dash-card">
        <div class="dash-card-head">
          <div class="title"><span class="msi">corporate_fare</span> Top 5 กลุ่มงาน (ยอดสูงสุด)</div>
        </div>
        <div class="dash-card-body">
          <div class="chart-wrap" style="height:220px;">
            <canvas id="chartDept"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Row: Workflow Pipeline + Category donut ── -->
  <div class="row g-3 mb-0 mt-0">
    <div class="col-lg-7">
      <div class="dash-card">
        <div class="dash-card-head">
          <div class="title"><span class="msi">route</span> Workflow Pipeline (สถานะรายการ)</div>
        </div>
        <div class="dash-card-body" style="padding:12px 18px 16px;">
          <div class="pipeline">
            <div class="pipe-step ps-amber">
              <div class="pipe-num"><?= number_format((int)$kpi['st_wait']) ?></div>
              <div class="pipe-label">🟡 รอรับเอกสาร</div>
            </div>
            <div class="pipe-step ps-orange">
              <div class="pipe-num"><?= number_format((int)$kpi['st_approve']) ?></div>
              <div class="pipe-label">🟠 รออนุมัติ</div>
            </div>
            <div class="pipe-step ps-purple">
              <div class="pipe-num"><?= number_format((int)$kpi['st_cheque']) ?></div>
              <div class="pipe-label">🟣 จัดทำเช็ค</div>
            </div>
            <div class="pipe-step ps-blue">
              <div class="pipe-num"><?= number_format((int)$kpi['st_paid']) ?></div>
              <div class="pipe-label">🔵 เบิกจ่ายแล้ว</div>
            </div>
          </div>
          <div class="chart-wrap mt-3" style="height:160px;">
            <canvas id="chartPipeline"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="dash-card">
        <div class="dash-card-head">
          <div class="title"><span class="msi">category</span> หมวดค่าใช้จ่าย</div>
        </div>
        <div class="dash-card-body">
          <div class="chart-wrap" style="height:240px;">
            <canvas id="chartType"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Budget vs Actual ── -->
  <?php if (!empty($budRows)): ?>
  <div class="dash-card mt-3">
    <div class="dash-card-head">
      <div class="title"><span class="msi">compare_arrows</span> แผนงบประมาณ vs ยอดจริง (เงินบำรุง ปี <?= $fyBE ?>)</div>
      <span class="badge-fy">ปีงบ <?= $fyBE ?></span>
    </div>
    <div class="dash-card-body" style="padding:0;">
      <table class="budget-table">
        <thead>
          <tr>
            <th>รายการแผนงาน</th>
            <th class="text-end">งบประมาณ (บาท)</th>
            <th class="text-end">เบิกจ่ายจริง (บาท)</th>
            <th class="text-end">คงเหลือ (บาท)</th>
            <th style="min-width:120px;">% ใช้ไป</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($budRows as $b):
            $budget = (float)$b['budget'];
            $actual = (float)$b['actual'];
            $remain = $budget - $actual;
            $pct    = $budget > 0 ? min(100, round($actual/$budget*100, 1)) : 0;
            $isOver = $actual > $budget;
          ?>
          <tr>
            <td style="font-weight:600; color:#374151;"><?= htmlspecialchars($b['planName']) ?></td>
            <td class="text-end" style="color:#374151;"><?= number_format($budget,2) ?></td>
            <td class="text-end <?= $isOver?'text-over':'' ?>"><?= number_format($actual,2) ?></td>
            <td class="text-end" style="color:<?= $remain>=0?'#059669':'#ef4444' ?>; font-weight:700;"><?= number_format($remain,2) ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:8px;">
                <div class="prog-bar-wrap" style="flex:1;">
                  <div class="prog-bar-fill <?= $isOver?'over':'' ?>" style="width:<?= min(100,$pct) ?>%;"></div>
                </div>
                <span style="font-size:11px;font-weight:700;color:<?= $isOver?'#ef4444':'#374151' ?>;min-width:36px;"><?= $pct ?>%</span>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── Recent Transactions ── -->
  <div class="dash-card mt-3">
    <div class="dash-card-head">
      <div class="title"><span class="msi">history</span> รายการล่าสุด 10 รายการ</div>
      <a href="accounting.php" style="font-size:12px;font-weight:700;color:#0B6E4F;text-decoration:none;">ดูทั้งหมด →</a>
    </div>
    <div class="dash-card-body" style="padding:0; overflow-x:auto;">
      <table class="rt">
        <thead>
          <tr>
            <th>#</th>
            <th>วันที่รับ</th>
            <th>บริษัท/ร้านค้า</th>
            <th>เลขที่ใบส่งของ</th>
            <th>แหล่งเงิน</th>
            <th class="text-end">ยอด (บาท)</th>
            <th>สถานะ</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent as $row):
            $dPay = ($row['DatePay']     && $row['DatePay']     !== '0000-00-00');
            $dApp = ($row['DateApprove'] && $row['DateApprove'] !== '0000-00-00');
            $dRec = ($row['DateReceive'] && $row['DateReceive'] !== '0000-00-00');
            if      ($dPay) { $sCls='st-paid';    $sTxt='🔵 เบิกจ่ายแล้ว'; }
            elseif  ($dApp) { $sCls='st-cheque';  $sTxt='🟣 จัดทำเช็ค'; }
            elseif  ($dRec) { $sCls='st-approve'; $sTxt='🟠 รออนุมัติ'; }
            else            { $sCls='st-wait';    $sTxt='🟡 รอรับเอกสาร'; }
          ?>
          <tr>
            <td style="color:#64748b;font-size:12px;">#<?= $row['PayId'] ?></td>
            <td style="white-space:nowrap;color:#374151;"><?= dbDate($row['DateIn']) ?></td>
            <td style="font-weight:600;color:#1f2a44;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($row['CompanyName']) ?></td>
            <td style="color:#64748b;"><?= htmlspecialchars($row['Detail'] ?: '—') ?></td>
            <td><span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:99px;background:<?= $row['Source']==2?'#eff6ff':'#e8f5e9' ?>;color:<?= $row['Source']==2?'#2563EB':'#059669' ?>;"><?= $row['Source']==2?'งบประมาณ':'เงินบำรุง' ?></span></td>
            <td class="amount-cell"><?= number_format((float)$row['Amount'],2) ?></td>
            <td><span class="st-badge <?= $sCls ?>"><?= $sTxt ?></span></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($recent)): ?>
          <tr><td colspan="7" style="text-align:center;padding:32px;color:#94a3b8;">ไม่พบข้อมูล</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── Quick Actions ── -->
  <div class="dash-card mt-3">
    <div class="dash-card-head">
      <div class="title"><span class="msi">bolt</span> Quick Actions</div>
    </div>
    <div class="dash-card-body" style="display:flex; flex-wrap:wrap; gap:10px;">
      <a href="accounting.php" style="display:inline-flex; align-items:center; gap:7px; padding:10px 18px; background:#e8f5e9; color:#0B6E4F; border-radius:10px; font-weight:700; font-size:13px; text-decoration:none;">
        <span class="msi msi-18">add_circle</span> บันทึกรายการใหม่
      </a>
      <a href="receive.php" style="display:inline-flex; align-items:center; gap:7px; padding:10px 18px; background:#fef3c7; color:#D97706; border-radius:10px; font-weight:700; font-size:13px; text-decoration:none;">
        <span class="msi msi-18">inbox</span> ลงรับเอกสาร
      </a>
      <a href="finance.php" style="display:inline-flex; align-items:center; gap:7px; padding:10px 18px; background:#fff7ed; color:#EA580C; border-radius:10px; font-weight:700; font-size:13px; text-decoration:none;">
        <span class="msi msi-18">check_circle</span> อนุมัติจ่าย
      </a>
      <a href="cheque.php" style="display:inline-flex; align-items:center; gap:7px; padding:10px 18px; background:#f5f3ff; color:#7C3AED; border-radius:10px; font-weight:700; font-size:13px; text-decoration:none;">
        <span class="msi msi-18">credit_card</span> จัดทำเช็ค
      </a>
      <a href="statistics.php" style="display:inline-flex; align-items:center; gap:7px; padding:10px 18px; background:#eff6ff; color:#2563EB; border-radius:10px; font-weight:700; font-size:13px; text-decoration:none;">
        <span class="msi msi-18">bar_chart</span> รายงานสถิติ
      </a>
      <a href="backup.php" style="display:inline-flex; align-items:center; gap:7px; padding:10px 18px; background:#f0fdf4; color:#059669; border-radius:10px; font-weight:700; font-size:13px; text-decoration:none; border:1.5px solid #bbf7d0;">
        <span class="msi msi-18">backup</span> สำรองข้อมูล
      </a>
    </div>
  </div>

</div><!-- .dash-wrap -->

<!-- ── Chart.js scripts ── -->
<script>
Chart.defaults.font.family = "'Sarabun', sans-serif";
Chart.defaults.font.size   = 12;
const GREEN  = '#0B6E4F', GREEN2 = '#08A045', BLUE = '#3B82F6',
      AMBER  = '#F59E0B', ORANGE = '#F97316', PURPLE = '#8B5CF6',
      TEAL   = '#14B8A6', RED    = '#EF4444';
const PALETTE = [GREEN,BLUE,AMBER,ORANGE,PURPLE,TEAL,'#EC4899','#84CC16'];

// ── Monthly bar chart ──
new Chart(document.getElementById('chartMonthly'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($mLabels, JSON_UNESCAPED_UNICODE) ?>,
    datasets: [{
      label: 'ยอด (บาท)',
      data: <?= json_encode($mAmounts) ?>,
      backgroundColor: 'rgba(11,110,79,.15)',
      borderColor: GREEN,
      borderWidth: 2,
      borderRadius: 8,
      borderSkipped: false
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      y: { grid: { color: 'rgba(0,0,0,.04)' }, ticks: { callback: v => Number(v).toLocaleString('th-TH') } },
      x: { grid: { display: false } }
    }
  }
});

// ── Source donut ──
new Chart(document.getElementById('chartSource'), {
  type: 'doughnut',
  data: {
    labels: ['เงินบำรุง','งบประมาณ'],
    datasets: [{
      data: [<?= round((float)($srcRows[1]['total']??0),2) ?>, <?= round((float)($srcRows[2]['total']??0),2) ?>],
      backgroundColor: [GREEN, BLUE],
      borderWidth: 3, borderColor: '#fff', hoverOffset: 6
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    cutout: '68%',
    plugins: { legend: { display: false },
      tooltip: { callbacks: { label: ctx => ctx.label + ': ' + Number(ctx.raw).toLocaleString('th-TH') + ' บาท' } }
    }
  }
});

// ── Top Companies horizontal bar ──
new Chart(document.getElementById('chartCompany'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($coLabels, JSON_UNESCAPED_UNICODE) ?>,
    datasets: [{ label:'ยอด (บาท)', data: <?= json_encode($coAmounts) ?>,
      backgroundColor: [GREEN+'cc',GREEN2+'cc',TEAL+'cc',BLUE+'cc',AMBER+'cc'],
      borderRadius: 6 }]
  },
  options: {
    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: 'rgba(0,0,0,.04)' }, ticks: { callback: v => Number(v).toLocaleString('th-TH') } },
      y: { grid: { display: false } }
    }
  }
});

// ── Top Departments horizontal bar ──
new Chart(document.getElementById('chartDept'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($dLabels, JSON_UNESCAPED_UNICODE) ?>,
    datasets: [{ label:'ยอด (บาท)', data: <?= json_encode($dAmounts) ?>,
      backgroundColor: [BLUE+'cc',PURPLE+'cc',ORANGE+'cc',AMBER+'cc',TEAL+'cc'],
      borderRadius: 6 }]
  },
  options: {
    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: 'rgba(0,0,0,.04)' }, ticks: { callback: v => Number(v).toLocaleString('th-TH') } },
      y: { grid: { display: false } }
    }
  }
});

// ── Pipeline bar ──
new Chart(document.getElementById('chartPipeline'), {
  type: 'bar',
  data: {
    labels: ['🟡 รอรับเอกสาร','🟠 รออนุมัติ','🟣 จัดทำเช็ค','🔵 เบิกจ่ายแล้ว'],
    datasets: [{ label:'จำนวน',
      data: [<?= (int)$kpi['st_wait'] ?>,<?= (int)$kpi['st_approve'] ?>,<?= (int)$kpi['st_cheque'] ?>,<?= (int)$kpi['st_paid'] ?>],
      backgroundColor: [AMBER+'cc', ORANGE+'cc', PURPLE+'cc', BLUE+'cc'],
      borderRadius: 8
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      y: { grid: { color: 'rgba(0,0,0,.04)' }, ticks: { stepSize: 1 } },
      x: { grid: { display: false } }
    }
  }
});

// ── Category donut ──
new Chart(document.getElementById('chartType'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode($tLabels, JSON_UNESCAPED_UNICODE) ?>,
    datasets: [{ data: <?= json_encode($tAmounts) ?>,
      backgroundColor: PALETTE, borderWidth: 3, borderColor: '#fff', hoverOffset: 6 }]
  },
  options: {
    responsive: true, maintainAspectRatio: false, cutout: '58%',
    plugins: {
      legend: { position: 'right', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } },
      tooltip: { callbacks: { label: ctx => ctx.label + ': ' + Number(ctx.raw).toLocaleString('th-TH') + ' บาท' } }
    }
  }
});
</script>

</body>
</html>
