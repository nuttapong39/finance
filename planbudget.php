<?php
// planbudget.php
require_once __DIR__ . '/config.php';
checkAuth();

// ── Helper: วันที่ แบบไทย ──────────────────────────────────────
function DateThai($strDate) {
    if (empty($strDate)) return '';
    $ts        = strtotime($strDate);
    $strYear   = date("Y", $ts) + 543;
    $strMonth  = (int)date("n", $ts);
    $strDay    = (int)date("j", $ts);
    $months    = ["","มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน",
                  "กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
    return "$strDay&nbsp;&nbsp;{$months[$strMonth]}&nbsp;&nbsp;$strYear";
}

// ── วันที่ Thai default สำหรับ datepicker ──────────────────────
function thaiDateDefault() {
    $y = date("Y") + 543;
    return date("d/m/") . $y;
}

// ── รับ Qyear จาก GET ──────────────────────────────────────────
$Qyear = isset($_GET['Qyear']) ? (int)$_GET['Qyear'] : 0;

// ── คำนวณช่วงงบประมาณ ─────────────────────────────────────────
$DatePaystart = '';
$DatePayend   = '';
if ($Qyear > 0) {
    $yearbe       = $Qyear - 544;          // ตุลาคม ปีก่อน
    $yearthis     = $Qyear - 543;          // กันยายน ปีนี้
    $DatePaystart = $yearbe  . "-10-01";
    $DatePayend   = $yearthis . "-09-30";
}

// ── ยอดรวม payment (ตามแผน) ───────────────────────────────────
$Amounts  = 0.0;
$Amounts2 = 0.0;
if ($Qyear > 0) {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(Amount),0) AS s FROM payment WHERE DateIn BETWEEN ? AND ? AND Source='2'");
    $stmt->bind_param("ss", $DatePaystart, $DatePayend);
    $stmt->execute();
    $Amounts = (float)$stmt->get_result()->fetch_assoc()['s'];
    $stmt->close();

    $stmt = $conn->prepare("SELECT COALESCE(SUM(Amount),0) AS s FROM payment WHERE DatePaid BETWEEN ? AND ? AND Source='2'");
    $stmt->bind_param("ss", $DatePaystart, $DatePayend);
    $stmt->execute();
    $Amounts2 = (float)$stmt->get_result()->fetch_assoc()['s'];
    $stmt->close();
}

// ── ยอดรวม planpayset2 ────────────────────────────────────────
$Amountset = 0.0;
if ($Qyear > 0) {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(Amount),0) AS s FROM planpayset2 WHERE Qyear=?");
    $stmt->bind_param("i", $Qyear);
    $stmt->execute();
    $Amountset = (float)$stmt->get_result()->fetch_assoc()['s'];
    $stmt->close();
}

// ── ดึง planpay group by type ─────────────────────────────────
$planTypes = [];
$stmt = $conn->prepare("SELECT DISTINCT PlanPayTypeId FROM planpay");
$stmt->execute();
$resTypes = $stmt->get_result();
while ($r = $resTypes->fetch_assoc()) {
    $planTypes[] = (int)$r['PlanPayTypeId'];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="pic/fms.png">
    <title>รายการค่าใช้จ่ายตามแผนเงินงบประมาณ – MOPH</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- jQuery UI (datepicker) -->
    <link href="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.13.2/jquery-ui.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        :root {
            --moph-primary:   #0B6E4F;
            --moph-secondary:#08A045;
            --moph-dark:      #1a4d2e;
            --moph-light:     #e8f5e9;
            --white:          #ffffff;
            --gray-50:        #f8f9fa;
            --gray-100:       #f1f3f5;
            --gray-200:       #e9ecef;
            --gray-600:       #6c757d;
            --gray-700:       #495057;
            --gray-800:       #343a40;
            --shadow-md:      0 4px 12px rgba(11,110,79,.12);
            --shadow-lg:      0 8px 24px rgba(11,110,79,.15);
        }

        body {
            font-family: 'Sarabun', -apple-system, sans-serif;
            background: linear-gradient(135deg, var(--moph-light) 0%, #fff 50%, var(--gray-50) 100%);
            min-height: 100vh;
            color: var(--gray-800);
        }

        /* ── Page Layout ── */
        .main-container { max-width: 1400px; margin: 0 auto; padding: 24px; }

        /* ── Page Header ── */
        .page-header {
            background: linear-gradient(135deg, var(--moph-primary), var(--moph-secondary));
            border-radius: 20px;
            padding: 28px 32px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-lg);
            position: relative; overflow: hidden;
        }
        .page-header::before {
            content:''; position:absolute; top:-40%; right:-8%;
            width:350px; height:350px;
            background:radial-gradient(circle, rgba(255,255,255,.12) 0%, transparent 70%);
            border-radius:50%;
        }
        .page-header h1 { color:#fff; font-size:26px; font-weight:700; margin:0; position:relative; z-index:1; }
        .page-header p  { color:rgba(255,255,255,.9); font-size:15px; margin:6px 0 0; position:relative; z-index:1; }
        .page-header .badge-moph {
            display:inline-flex; align-items:center; gap:6px;
            background:rgba(255,255,255,.2); color:#fff;
            padding:6px 14px; border-radius:20px; font-size:13px; font-weight:500;
            margin-top:10px; position:relative; z-index:1;
        }

        /* ── Tabs ── */
        .nav-tabs-moph {
            display:flex; gap:8px; flex-wrap:wrap;
            border-bottom:2px solid var(--gray-200);
            padding-bottom:0; margin-bottom:24px;
        }
        .nav-tabs-moph a {
            text-decoration:none; color:var(--gray-600); font-weight:600; font-size:15px;
            padding:12px 20px; border-radius:12px 12px 0 0;
            border:2px solid transparent; border-bottom:none;
            background:var(--gray-100); transition:all .25s ease;
            display:flex; align-items:center; gap:8px;
        }
        .nav-tabs-moph a:hover { background:var(--moph-light); color:var(--moph-dark); }
        .nav-tabs-moph a.active {
            background:#fff; color:var(--moph-primary); font-weight:700;
            border-color:var(--moph-primary); border-bottom-color:#fff;
            margin-bottom:-2px;
        }
        .nav-tabs-moph a i { font-size:18px; }

        /* ── Card ── */
        .card-moph {
            background:#fff; border-radius:16px; border:1px solid var(--gray-200);
            box-shadow:var(--shadow-md); margin-bottom:24px; overflow:hidden;
        }
        .card-header-moph {
            background:linear-gradient(135deg, rgba(11,110,79,.08), rgba(8,160,69,.05));
            border-bottom:1px solid var(--gray-200);
            padding:16px 24px; display:flex; align-items:center; gap:10px;
        }
        .card-header-moph i { color:var(--moph-primary); font-size:20px; }
        .card-header-moph span { font-weight:700; font-size:16px; color:var(--moph-dark); }
        .card-body-moph { padding:24px; }

        /* ── Search Form ── */
        .search-row { display:flex; align-items:flex-end; gap:12px; flex-wrap:wrap; }
        .search-row .form-label { font-weight:600; color:var(--gray-700); font-size:15px; margin-bottom:6px; }
        .search-row input {
            height:44px; border:2px solid var(--gray-200); border-radius:10px;
            padding:0 16px; font-size:16px; transition:all .25s;
            width:140px;
        }
        .search-row input:focus { border-color:var(--moph-primary); box-shadow:0 0 0 3px rgba(11,110,79,.12); outline:none; }
        .btn-moph-primary {
            height:44px; padding:0 24px; background:var(--moph-primary); color:#fff;
            border:none; border-radius:10px; font-size:16px; font-weight:700;
            cursor:pointer; transition:all .25s; display:flex; align-items:center; gap:6px;
        }
        .btn-moph-primary:hover { background:var(--moph-dark); transform:translateY(-1px); box-shadow:0 4px 10px rgba(11,110,79,.3); }

        /* ── Table ── */
        .table-moph {
            width:100%; border-collapse:collapse; font-size:15px;
            margin-top:8px;
        }
        .table-moph thead th {
            background:linear-gradient(135deg, var(--moph-primary), var(--moph-secondary));
            color:#fff; font-weight:600; padding:12px 14px;
            text-align:center; white-space:nowrap; position:sticky; top:0; z-index:2;
        }
        .table-moph thead th:first-child { border-radius:10px 0 0 0; }
        .table-moph thead th:last-child  { border-radius:0 10px 0 0; }

        .table-moph tbody tr { border-bottom:1px solid var(--gray-200); transition:background .2s; }
        .table-moph tbody tr:hover { background:rgba(11,110,79,.04); }

        /* Category row */
        .table-moph .row-category td {
            background:var(--moph-light); font-weight:700; color:var(--moph-dark);
            padding:10px 14px;
        }
        /* Sub-row */
        .table-moph .row-item td { padding:10px 14px; }
        .table-moph .row-item td:not(:first-child):not(:last-child) { text-align:right; }

        /* Summary rows */
        .table-moph .row-subtotal td,
        .table-moph .row-total td {
            padding:12px 14px; font-weight:700; text-align:right;
        }
        .table-moph .row-subtotal { background:rgba(11,110,79,.06); }
        .table-moph .row-total     { background:var(--moph-primary); color:#fff; }
        .table-moph .row-total td  { color:#fff !important; }

        /* Colors */
        .text-danger-moph { color:#dc3545 !important; font-weight:600; }
        .text-ok          { color:var(--moph-primary); font-weight:600; }

        /* Budget input inline */
        .budget-input-wrap { display:flex; align-items:center; gap:6px; justify-content:center; }
        .budget-input-wrap input {
            width:130px; text-align:right; padding:6px 10px;
            border:1px solid var(--gray-200); border-radius:8px;
            font-size:15px; background:var(--gray-50); color:var(--gray-700);
        }
        .btn-edit-inline {
            width:32px; height:32px; border:1px solid var(--moph-primary);
            background:#fff; color:var(--moph-primary); border-radius:8px;
            cursor:pointer; display:flex; align-items:center; justify-content:center;
            transition:all .2s; font-size:14px;
        }
        .btn-edit-inline:hover { background:var(--moph-primary); color:#fff; }

        /* Excel Button */
        .btn-excel {
            padding:8px 18px; background:#217A3C; color:#fff; border:none;
            border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;
            display:inline-flex; align-items:center; gap:6px; transition:all .2s;
        }
        .btn-excel:hover { background:#1a6530; transform:translateY(-1px); box-shadow:0 3px 8px rgba(33,122,60,.3); }

        /* Excel button row */
        .excel-row { display:flex; justify-content:flex-end; padding:12px 0; }

        /* ── Modal ── */
        .modal-content { border-radius:16px; overflow:hidden; border:none; box-shadow:0 20px 60px rgba(11,110,79,.2); }
        .modal-header-moph {
            background:linear-gradient(135deg, var(--moph-primary), var(--moph-secondary));
            color:#fff; padding:18px 24px; display:flex; align-items:center; justify-content:space-between;
        }
        .modal-header-moph h5 { margin:0; font-weight:700; font-size:18px; display:flex; align-items:center; gap:8px; }
        .modal-header-moph .btn-close { filter:invert(1); opacity:.8; }
        .modal-header-moph .btn-close:hover { opacity:1; }
        .modal-body-moph { padding:24px; }
        .modal-body-moph .form-label { font-weight:600; color:var(--gray-700); font-size:14px; }
        .modal-body-moph .form-control {
            border:2px solid var(--gray-200); border-radius:10px;
            height:42px; font-size:15px; transition:all .25s;
        }
        .modal-body-moph .form-control:focus { border-color:var(--moph-primary); box-shadow:0 0 0 3px rgba(11,110,79,.12); outline:none; }
        .modal-footer-moph { padding:16px 24px; border-top:1px solid var(--gray-200); display:flex; justify-content:flex-end; gap:10px; }
        .btn-modal-primary {
            padding:8px 22px; background:var(--moph-primary); color:#fff; border:none;
            border-radius:10px; font-size:15px; font-weight:600; cursor:pointer; transition:all .2s;
        }
        .btn-modal-primary:hover { background:var(--moph-dark); }
        .btn-modal-cancel {
            padding:8px 22px; background:#fff; color:var(--gray-700); border:2px solid var(--gray-200);
            border-radius:10px; font-size:15px; font-weight:600; cursor:pointer; transition:all .2s;
        }
        .btn-modal-cancel:hover { background:var(--gray-50); }

        /* ── Responsive ── */
        @media (max-width:768px) {
            .main-container { padding:12px; }
            .page-header { padding:20px; border-radius:14px; }
            .page-header h1 { font-size:20px; }
            .table-moph { font-size:13px; }
            .table-moph thead th, .table-moph tbody td { padding:8px 6px; }
            .nav-tabs-moph a { padding:10px 12px; font-size:13px; }
            .search-row input { width:110px; }
        }

        /* ── SweetAlert2 ── */
        .swal2-popup { border-radius:16px; font-family:'Sarabun',sans-serif; }
        .swal2-title { color:var(--gray-800); font-weight:700; }
        .swal2-confirm { background:var(--moph-primary)!important; border-radius:10px; font-weight:600; }
        .swal2-cancel  { border-radius:10px; font-weight:600; }
    </style>
</head>
<body>

<!-- Header -->
<?php include 'header.php'; ?>

<div class="main-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="bi bi-clipboard-data"></i> แผนงานและรายงาน</h1>
        <p>รายการค่าใช้จ่ายตามแผนเงินงบประมาณ</p>
        <span class="badge-moph"><i class="bi bi-shield-check"></i> กระทรวงสาธารณสุข</span>
    </div>

    <!-- Tabs -->
    <div class="nav-tabs-moph">
        <a href="plan.php"><i class="bi bi-list"></i> รายการค่าใช้จ่ายตามแผนเงินบำรุง</a>
        <a href="planbudget.php" class="active"><i class="bi bi-tasks"></i> รายการค่าใช้จ่ายตามแผนเงินงบประมาณ</a>
        <a href="statistics.php"><i class="bi bi-bar-chart"></i> สถิติและรายงาน</a>
    </div>

    <!-- Search Budget Year -->
    <div class="card-moph">
        <div class="card-header-moph">
            <i class="bi bi-search"></i>
            <span>ระบุปีงบประมาณ</span>
        </div>
        <div class="card-body-moph">
            <form method="get" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" id="searchForm">
                <div class="search-row">
                    <div>
                        <label class="form-label">ปีงบประมาณ พ.ศ.</label>
                        <input type="text" name="Qyear" id="Qyear" required
                               value="<?php echo $Qyear > 0 ? $Qyear : ''; ?>"
                               placeholder="เช่น 2568">
                    </div>
                    <button type="submit" class="btn-moph-primary">
                        <i class="bi bi-check-circle"></i> ตกลง
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table (only when Qyear is set) -->
    <?php if ($Qyear > 0): ?>
    <div class="card-moph">
        <div class="card-header-moph">
            <i class="bi bi-th-list"></i>
            <span>รายการค่าใช้จ่ายตามแผนเงินงบประมาณ – ปี พ.ศ. <?php echo $Qyear; ?></span>
        </div>

        <!-- Excel Export -->
        <div class="card-body-moph pb-0">
            <div class="excel-row">
                <button type="button" class="btn-excel" onclick="openExcelModal()">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </button>
            </div>
        </div>

        <!-- Table Wrapper (horizontal scroll on mobile) -->
        <div class="card-body-moph pt-0" style="overflow-x:auto;">
            <table class="table-moph">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th style="width:auto; text-align:left">ค่าใช้จ่าย</th>
                        <th style="width:160px">ประมาณการ</th>
                        <th style="width:150px">จ่ายตามแผน</th>
                        <th style="width:150px">คงเหลือตามแผน</th>
                        <th style="width:150px">จ่ายจริง</th>
                        <th style="width:150px">คงเหลือจ่ายจริง</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $grandSumplan  = 0;
                $grandSumtype  = 0;
                $grandSumtype5 = 0;

                foreach ($planTypes as $PlanPayTypeId) {
                    // ── ชื่อ Category ──
                    $stmt = $conn->prepare("SELECT PlanName FROM planpaytype WHERE PlanPayTypeId=? LIMIT 1");
                    $stmt->bind_param("i", $PlanPayTypeId);
                    $stmt->execute();
                    $rowType  = $stmt->get_result()->fetch_assoc();
                    $PlanName = $rowType ? (string)($rowType['PlanName'] ?? '') : '';
                    $stmt->close();

                    // ── รายการทั้งหมดในCategory ──
                    $stmt = $conn->prepare("SELECT PlanPayId, PlanPayName FROM planpay WHERE PlanPayTypeId=?");
                    $stmt->bind_param("i", $PlanPayTypeId);
                    $stmt->execute();
                    $resItems = $stmt->get_result();
                    $items    = [];
                    while ($r = $resItems->fetch_assoc()) { $items[] = $r; }
                    $stmt->close();

                    // Category Header Row
                ?>
                    <tr class="row-category">
                        <td></td>
                        <td colspan="6"><?php echo htmlspecialchars($PlanName); ?></td>
                    </tr>
                <?php
                    $Sumplanset = 0;
                    $Sumtype    = 0;
                    $Sumtype5   = 0;
                    $rowNum     = 1;

                    foreach ($items as $item) {
                        $PlanPayId   = (int)$item['PlanPayId'];
                        $PlanPayName = htmlspecialchars($item['PlanPayName']);

                        // ── ประมาณการ (planpayset2) ──
                        $Amount = 0.0;
                        $stmt = $conn->prepare("SELECT COALESCE(SUM(Amount),0) AS s FROM planpayset2 WHERE PlanPayId=? AND Qyear=?");
                        $stmt->bind_param("ii", $PlanPayId, $Qyear);
                        $stmt->execute();
                        $Amount = (float)$stmt->get_result()->fetch_assoc()['s'];
                        $stmt->close();
                        $Sumplanset += $Amount;

                        // ── จ่ายตามแผน (DateIn) ──
                        $Sumamout = 0.0;
                        $stmt = $conn->prepare("SELECT COALESCE(SUM(Amount),0) AS s FROM payment WHERE PlanPayId=? AND DateIn BETWEEN ? AND ? AND Source='2'");
                        $stmt->bind_param("iss", $PlanPayId, $DatePaystart, $DatePayend);
                        $stmt->execute();
                        $Sumamout = (float)$stmt->get_result()->fetch_assoc()['s'];
                        $stmt->close();
                        $Sumtype += $Sumamout;

                        // ── จ่ายจริง (DatePaid) ──
                        $Sumamout5 = 0.0;
                        $stmt = $conn->prepare("SELECT COALESCE(SUM(Amount),0) AS s FROM payment WHERE PlanPayId=? AND DatePaid BETWEEN ? AND ? AND Source='2'");
                        $stmt->bind_param("iss", $PlanPayId, $DatePaystart, $DatePayend);
                        $stmt->execute();
                        $Sumamout5 = (float)$stmt->get_result()->fetch_assoc()['s'];
                        $stmt->close();
                        $Sumtype5 += $Sumamout5;

                        // ── คงเหลือ ──
                        $remain  = $Amount - $Sumamout;
                        $remain5 = $Amount - $Sumamout5;
                        $colorR  = $remain  < 0 ? 'text-danger-moph' : 'text-ok';
                        $colorR5 = $remain5 < 0 ? 'text-danger-moph' : 'text-ok';
                    ?>
                        <tr class="row-item">
                            <td style="text-align:center; color:var(--gray-600);"><?php echo $rowNum++; ?></td>
                            <td style="text-align:left;"><?php echo $PlanPayName; ?></td>
                            <td>
                                <div class="budget-input-wrap">
                                    <input type="text" value="<?php echo number_format($Amount, 2); ?>" disabled>
                                    <button type="button" class="btn-edit-inline"
                                            onclick="openBudgetModal(<?php echo $PlanPayId; ?>, '<?php echo addslashes($item['PlanPayName']); ?>', <?php echo $Amount; ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </div>
                            </td>
                            <td><?php echo number_format($Sumamout, 2); ?></td>
                            <td class="<?php echo $colorR; ?>"><?php echo number_format($remain, 2); ?></td>
                            <td><?php echo number_format($Sumamout5, 2); ?></td>
                            <td class="<?php echo $colorR5; ?>"><?php echo number_format($remain5, 2); ?></td>
                        </tr>
                    <?php
                    }

                    // ── Subtotal Row ──
                    $subRemain  = $Sumplanset - $Sumtype;
                    $subRemain5 = $Sumplanset - $Sumtype5;
                    $subColorR  = $subRemain  < 0 ? 'text-danger-moph' : 'text-ok';
                    $subColorR5 = $subRemain5 < 0 ? 'text-danger-moph' : 'text-ok';

                    $grandSumplan  += $Sumplanset;
                    $grandSumtype  += $Sumtype;
                    $grandSumtype5 += $Sumtype5;
                ?>
                    <tr class="row-subtotal">
                        <td></td>
                        <td style="text-align:right;">รวม <?php echo htmlspecialchars($PlanName); ?></td>
                        <td><?php echo number_format($Sumplanset, 2); ?></td>
                        <td><?php echo number_format($Sumtype, 2); ?></td>
                        <td class="<?php echo $subColorR; ?>"><?php echo number_format($subRemain, 2); ?></td>
                        <td><?php echo number_format($Sumtype5, 2); ?></td>
                        <td class="<?php echo $subColorR5; ?>"><?php echo number_format($subRemain5, 2); ?></td>
                    </tr>
                <?php
                } // end foreach planTypes

                // ── Grand Total Row ──
                $grandRemain  = $Amountset - $Amounts;
                $grandRemain5 = $Amountset - $Amounts2;
                $grandColorR  = $grandRemain  < 0 ? 'text-danger-moph' : 'text-ok';
                $grandColorR5 = $grandRemain5 < 0 ? 'text-danger-moph' : 'text-ok';
                ?>
                    <tr class="row-total">
                        <td></td>
                        <td style="text-align:right;">รวมจำนวนเงินทั้งสิ้น</td>
                        <td><?php echo number_format($Amountset, 2); ?></td>
                        <td><?php echo number_format($Amounts, 2); ?></td>
                        <td class="<?php echo $grandColorR; ?>"><?php echo number_format($grandRemain, 2); ?></td>
                        <td><?php echo number_format($Amounts2, 2); ?></td>
                        <td class="<?php echo $grandColorR5; ?>"><?php echo number_format($grandRemain5, 2); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ────────────────────────────────────────────
     MODALS
     ──────────────────────────────────────────── -->

<!-- Modal: แก้ไข ประมาณการ -->
<div class="modal fade" id="modalBudget" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header-moph">
                <h5><i class="bi bi-pencil-square"></i> แก้ไข ประมาณการ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="get" action="setplanbudget.php" id="budgetForm">
                <div class="modal-body-moph">
                    <input type="hidden" name="PlanPayId" id="modalPlanPayId">
                    <input type="hidden" name="Qyears" value="<?php echo $Qyear; ?>">

                    <label class="form-label" id="modalPlanPayName">–</label>
                    <label class="form-label">ปีงบประมาณ พ.ศ. <?php echo $Qyear; ?></label>

                    <label class="form-label">จำนวนเงิน (บาท)</label>
                    <input type="text" class="form-control" name="Amount" id="modalAmount" placeholder="0.00">
                </div>
                <div class="modal-footer-moph">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> ยกเลิก</button>
                    <button type="submit" class="btn-modal-primary" onclick="return confirmBudgetSave(event)"><i class="bi bi-floppy-disk"></i> บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Excel Export -->
<div class="modal fade" id="modalExcel" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header-moph">
                <h5><i class="bi bi-file-earmark-excel"></i> ระบุช่วงเวลา (Excel)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="get" action="reportbudget.php" target="_blank" id="excelForm">
                <div class="modal-body-moph">
                    <input type="hidden" name="Qyear" value="<?php echo $Qyear; ?>">

                    <label class="form-label">ปีงบประมาณ พ.ศ.</label>
                    <input type="text" class="form-control" value="<?php echo $Qyear; ?>" disabled>

                    <label class="form-label">ระหว่างวันที่</label>
                    <input type="text" class="form-control" name="DatePaystart" id="dpStart" placeholder="dd/mm/yyyy">

                    <label class="form-label">ถึงวันที่</label>
                    <input type="text" class="form-control" name="DatePayend" id="dpEnd" placeholder="dd/mm/yyyy">
                </div>
                <div class="modal-footer-moph">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> ยกเลิก</button>
                    <button type="submit" class="btn-modal-primary"><i class="bi bi-box-arrow-up-right"></i> ส่งออก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Scripts ── -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>

<script>
// ── Thai Datepicker Config ──────────────────────────────────
const thaiDP = {
    dateFormat: 'dd/mm/yy',
    isBuddhist: true,
    dayNames:      ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'],
    dayNamesMin:   ['อา.','จ.','อ.','พ.','พฤ.','ศ.','ส.'],
    monthNames:    ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
                    'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'],
    monthNamesShort:['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.',
                    'ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'],
    changeMonth: true,
    changeYear:  true
};

$(document).ready(function() {
    $('#dpStart').datepicker($.extend({}, thaiDP, { defaultDate: '<?php echo thaiDateDefault(); ?>' }));
    $('#dpEnd').datepicker($.extend({}, thaiDP, { defaultDate: '<?php echo thaiDateDefault(); ?>' }));

    // Set default values
    $('#dpStart').val('<?php echo thaiDateDefault(); ?>');
    $('#dpEnd').val('<?php echo thaiDateDefault(); ?>');
});

// ── Open Budget Edit Modal ──────────────────────────────────
function openBudgetModal(planPayId, planPayName, currentAmount) {
    document.getElementById('modalPlanPayId').value = planPayId;
    document.getElementById('modalPlanPayName').textContent = planPayName;
    document.getElementById('modalAmount').value = currentAmount;
    var modal = new bootstrap.Modal(document.getElementById('modalBudget'));
    modal.show();
}

// ── Confirm Budget Save ─────────────────────────────────────
function confirmBudgetSave(e) {
    e.preventDefault();
    var amount = document.getElementById('modalAmount').value;
    if (!amount || isNaN(amount)) {
        Swal.fire({
            icon: 'warning',
            title: 'กรุณาตรวจสอบ',
            text: 'กรุณากรอกจำนวนเงินให้ถูกต้อง',
            confirmButtonColor: '#0B6E4F',
            confirmButtonText: 'ตรวจสอบ'
        });
        return false;
    }

    Swal.fire({
        title: 'ยืนยันการบันทึก',
        text: 'คุณต้องการบันทึกประมาณการใช่หรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0B6E4F',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-check-circle"></i> ยืนยัน',
        cancelButtonText: '<i class="bi bi-x-circle"></i> ยกเลิก',
        reverseButtons: true
    }).then(function(result) {
        if (result.isConfirmed) {
            document.getElementById('budgetForm').submit();
        }
    });
    return false;
}

// ── Open Excel Modal ────────────────────────────────────────
function openExcelModal() {
    var modal = new bootstrap.Modal(document.getElementById('modalExcel'));
    modal.show();
}

// ── Form: ปีงบ validation ──────────────────────────────────
document.getElementById('searchForm').addEventListener('submit', function(e) {
    var val = document.getElementById('Qyear').value.trim();
    if (!val || isNaN(val) || val.length !== 4) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'กรุณาตรวจสอบ',
            text: 'กรุณากรอกปีงบประมาณ พ.ศ. เป็นตัวเลข 4 หลัก เช่น 2568',
            confirmButtonColor: '#0B6E4F',
            confirmButtonText: 'ตรวจสอบ'
        });
    }
});
</script>
</body>
</html>