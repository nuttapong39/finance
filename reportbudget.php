<?php
// reportbudget.php – Excel Export (งบประมาณ Source=2)
if (!ob_get_level()) ob_start();
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
date_default_timezone_set('Asia/Bangkok');
require_once __DIR__ . '/connect_db.php';

// ── รับ input ─────────────────────────────────────────────────
$Qyear      = isset($_REQUEST['Qyear'])        ? (int)$_REQUEST['Qyear']        : 0;
$DateStartb = isset($_REQUEST['DatePaystart']) ? $_REQUEST['DatePaystart'] : '';
$DateEndb   = isset($_REQUEST['DatePayend'])   ? $_REQUEST['DatePayend']   : '';

// ── แปลง dd/mm/yyyy (Thai BE) → yyyy-mm-dd ──────────────────
function thaiToMySQL(string $thai): string {
    $p = explode('/', $thai);
    if (count($p) !== 3) return '';
    return ($p[2] - 543) . '-' . $p[1] . '-' . $p[0];
}
$DatePaystart = thaiToMySQL($DateStartb);
$DatePayend   = thaiToMySQL($DateEndb);

// ── DateThai helper ──────────────────────────────────────────
function DateThai(string $strDate): string {
    if (empty($strDate)) return '–';
    $ts = strtotime($strDate);
    if ($ts === false) return '–';
    $day    = (int)date("j", $ts);
    $month  = (int)date("n", $ts);
    $year   = (int)date("Y", $ts) + 543;
    $months = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    return "$day {$months[$month]} $year";
}

// ── ยอดรวม payment ───────────────────────────────────────────
$Amounts  = 0.0;
$Amounts2 = 0.0;
if ($Qyear > 0 && $DatePaystart !== '' && $DatePayend !== '') {
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

// ── ดึง plan types ────────────────────────────────────────────
$planTypes = [];
$stmt = $conn->prepare("SELECT DISTINCT PlanPayTypeId FROM planpay");
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) { $planTypes[] = (int)$r['PlanPayTypeId']; }
$stmt->close();

// ── Excel headers ─────────────────────────────────────────────
$strExcelFileName = "report_budget_{$Qyear}.xls";
header("Content-Type: application/x-msexcel; name=\"{$strExcelFileName}\"");
header("Content-Disposition: inline; filename=\"{$strExcelFileName}\"");
header("Pragma: no-cache");
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style>
    body        { font-family:'Tahoma','Segoe UI',sans-serif; font-size:11px; color:#343a40; }
    table       { border-collapse:collapse; width:100%; max-width:900px; margin:0 auto; }
    .title-row  { background:#0B6E4F; color:#fff; text-align:center; padding:14px; font-size:14px; font-weight:bold; }
    .sub-title  { background:#08A045; color:#fff; text-align:center; padding:8px; font-size:12px; }
    th          { background:#0B6E4F; color:#fff; border:1px solid #0a5c42; padding:10px 12px; text-align:center; font-weight:600; white-space:nowrap; }
    td          { border:1px solid #dee2e6; padding:8px 12px; }
    .cat-row td { background:#e8f5e9; font-weight:700; color:#1a4d2e; }
    .sub-row td { background:#f8f9fa; font-weight:700; }
    .total-row td { background:#0B6E4F; color:#fff; font-weight:700; }
    .num        { text-align:right; }
    .danger     { color:#dc3545; font-weight:600; }
    .ok         { color:#0B6E4F; font-weight:600; }
</style>
</head>
<body>
<table>
    <!-- Title -->
    <tr>
        <td colspan="6" class="title-row">
            รายการค่าใช้จ่ายตามแผนเงินงบประมาณ ปีงบประมาณ พ.ศ. <?php echo $Qyear; ?>
        </td>
    </tr>
    <tr>
        <td colspan="6" class="sub-title">
            ระหว่างวันที่ <?php echo DateThai($DatePaystart); ?> ถึงวันที่ <?php echo DateThai($DatePayend); ?>
        </td>
    </tr>
    <!-- Header -->
    <tr>
        <th style="width:35%">ค่าใช้จ่าย</th>
        <th style="width:13%">ประมาณการ</th>
        <th style="width:13%">จ่ายตามแผน</th>
        <th style="width:13%">คงเหลือตามแผน</th>
        <th style="width:13%">จ่ายจริง</th>
        <th style="width:13%">คงเหลือจ่ายจริง</th>
    </tr>

<?php
foreach ($planTypes as $PlanPayTypeId) {

    // ── ชื่อ Category ──
    $stmt = $conn->prepare("SELECT PlanName FROM planpaytype WHERE PlanPayTypeId=? LIMIT 1");
    $stmt->bind_param("i", $PlanPayTypeId);
    $stmt->execute();
    $rowT     = $stmt->get_result()->fetch_assoc();
    $PlanName = $rowT ? htmlspecialchars((string)($rowT['PlanName'] ?? '')) : '';
    $stmt->close();

    // ── รายการ ──
    $stmt = $conn->prepare("SELECT PlanPayId, PlanPayName FROM planpay WHERE PlanPayTypeId=?");
    $stmt->bind_param("i", $PlanPayTypeId);
    $stmt->execute();
    $resItems = $stmt->get_result();
    $items    = [];
    while ($r = $resItems->fetch_assoc()) { $items[] = $r; }
    $stmt->close();

    // Category header row
    echo "<tr class=\"cat-row\"><td colspan=\"6\">{$PlanName}</td></tr>";

    $Sumplanset = 0.0;
    $Sumtype    = 0.0;
    $Sumtype5   = 0.0;

    foreach ($items as $item) {
        $PlanPayId   = (int)$item['PlanPayId'];
        $PlanPayName = htmlspecialchars($item['PlanPayName']);

        // ── ประมาณการ (planpayset2) ── ← นี่คือจุดที่เกิด error line 105
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
        $cls1    = $remain  < 0 ? 'danger' : 'ok';
        $cls2    = $remain5 < 0 ? 'danger' : 'ok';

        echo "<tr>"
           . "<td>{$PlanPayName}</td>"
           . "<td class=\"num\">" . number_format($Amount,2)    . "</td>"
           . "<td class=\"num\">" . number_format($Sumamout,2)  . "</td>"
           . "<td class=\"num {$cls1}\">" . number_format($remain,2)  . "</td>"
           . "<td class=\"num\">" . number_format($Sumamout5,2) . "</td>"
           . "<td class=\"num {$cls2}\">" . number_format($remain5,2) . "</td>"
           . "</tr>";
    }

    // ── Subtotal row ──
    $subR  = $Sumplanset - $Sumtype;
    $subR5 = $Sumplanset - $Sumtype5;
    $sc1   = $subR  < 0 ? 'danger' : 'ok';
    $sc2   = $subR5 < 0 ? 'danger' : 'ok';

    echo "<tr class=\"sub-row\">"
       . "<td style=\"text-align:right;\">รวม {$PlanName}</td>"
       . "<td class=\"num\">" . number_format($Sumplanset,2) . "</td>"
       . "<td class=\"num\">" . number_format($Sumtype,2)    . "</td>"
       . "<td class=\"num {$sc1}\">" . number_format($subR,2)  . "</td>"
       . "<td class=\"num\">" . number_format($Sumtype5,2)  . "</td>"
       . "<td class=\"num {$sc2}\">" . number_format($subR5,2) . "</td>"
       . "</tr>";
}

// ── Grand Total ───────────────────────────────────────────────
$gR  = $Amountset - $Amounts;
$gR5 = $Amountset - $Amounts2;
$gc1 = $gR  < 0 ? 'danger' : 'ok';
$gc2 = $gR5 < 0 ? 'danger' : 'ok';
?>
    <tr class="total-row">
        <td style="text-align:right;">รวมจำนวนเงินทั้งสิ้น</td>
        <td class="num"><?php echo number_format($Amountset,2); ?></td>
        <td class="num"><?php echo number_format($Amounts,2); ?></td>
        <td class="num <?php echo $gc1; ?>"><?php echo number_format($gR,2); ?></td>
        <td class="num"><?php echo number_format($Amounts2,2); ?></td>
        <td class="num <?php echo $gc2; ?>"><?php echo number_format($gR5,2); ?></td>
    </tr>
</table>

<script>
window.onbeforeunload = function(){ return false; };
setTimeout(function(){ window.close(); }, 10000);
</script>
</body>
</html>