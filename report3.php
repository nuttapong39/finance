<?php
// report3.php – รายงานเจ้าหนี้การค้า (PDF via mPDF)
date_default_timezone_set('Asia/Bangkok');
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
// เปิด error reporting ชั่วคราว เพื่อ debug – ปิดได้หลังใช้งานจริง
error_reporting(E_ALL); ini_set('display_errors', 1);
require_once __DIR__ . '/connect_db.php';

// ── mPDF setup ────────────────────────────────────────────────
$tmpPath = __DIR__ . '/tmp';
if (!is_dir($tmpPath)) { mkdir($tmpPath, 0775, true); }

require_once __DIR__ . '/mpdf/vendor/autoload.php';
$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData          = $defaultFontConfig['fontdata'];
$mpdf = new \Mpdf\Mpdf([
    'tempDir' => $tmpPath,
    'fontdata' => $fontData + [
        'sarabun' => [
            'R'  => 'THSarabun.ttf',
            'I'  => 'THSarabun Italic.ttf',
            'B'  => 'THSarabun Bold.ttf',
            'BI' => 'THSarabun BoldItalic.ttf',
        ]
    ],
    'format' => 'A4',
]);

// ── Helper: DateThai ─────────────────────────────────────────
function DateThai(string $strDate): string {
    if (empty($strDate)) return '–';
    $ts = strtotime($strDate);
    if ($ts === false) return '–';
    $day    = (int)date("j", $ts);
    $month  = (int)date("n", $ts);
    $year   = (int)date("Y", $ts) + 543;
    $months = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
               'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
    return "$day&nbsp;&nbsp;{$months[$month]}&nbsp;&nbsp;$year";
}

// ── รับ input ─────────────────────────────────────────────────
$DateStartb = isset($_GET['DateStart']) ? $_GET['DateStart'] : '';
$DateEndb   = isset($_GET['DateEnd'])   ? $_GET['DateEnd']   : '';
$PlanPayRaw = isset($_GET['PlanPay'])   ? trim($_GET['PlanPay']) : '';
$Worker     = isset($_GET['Worker'])    ? trim($_GET['Worker']) : '';
$Audit      = isset($_GET['Audit'])     ? trim($_GET['Audit']) : '';
$Source     = isset($_GET['Source'])    ? $_GET['Source'] : '1';

// ── แตก PlanPayId จาก autocomplete string "202. ค่าสมทบ..." ──
// เดิม: ส่ง PlanPay="202. ค่าสมทบกองทุน..." เข้า SQL PlanPayId='...' → ไม่ match
$PlanPayId = 0;
if ($PlanPayRaw !== '') {
    // ตัด ส่วน number ก่อน "." แค่พอ
    $dotPos = strpos($PlanPayRaw, '.');
    if ($dotPos !== false) {
        $PlanPayId = (int)trim(substr($PlanPayRaw, 0, $dotPos));
    } else {
        $PlanPayId = (int)$PlanPayRaw;
    }
}

// ── แปลง Thai date → MySQL date ──────────────────────────────
function thaiToMySQL(string $thai): string {
    $p = explode('/', $thai);
    if (count($p) !== 3) return '';
    return ($p[2] - 543) . '-' . $p[1] . '-' . $p[0];
}
$DateStart = thaiToMySQL($DateStartb);
$DateEnd   = thaiToMySQL($DateEndb);

$Sourcetext = ($Source === '1') ? '(เงินบำรุง)' : '(เงินงบประมาณ)';

// ── Office info ──────────────────────────────────────────────
$OfficeName = ''; $Director = '';
$stmt = $conn->prepare("SELECT OfficeName, Director FROM office LIMIT 1");
$stmt->execute();
$rowO = $stmt->get_result()->fetch_assoc();
$stmt->close();
if ($rowO) {
    $OfficeName = (string)($rowO['OfficeName'] ?? '');
    $Director   = (string)($rowO['Director'] ?? '');
}

// ── Employee lookup helper ───────────────────────────────────
function getEmployee(object $conn, string $username): array {
    $result = ['Names' => '', 'Position' => ''];
    if ($username === '') return $result;
    $stmt = $conn->prepare("SELECT Names, Position FROM employee WHERE Username=? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) {
        $result['Names']    = (string)($row['Names'] ?? '');
        $result['Position'] = (string)($row['Position'] ?? '');
    }
    return $result;
}

$dirEmp   = getEmployee($conn, $Director);
$workerEmp = getEmployee($conn, $Worker);
$auditEmp  = getEmployee($conn, $Audit);
$Named     = $dirEmp['Names'];    $Positiond  = $dirEmp['Position'];
$Namew     = $workerEmp['Names']; $Positionw = $workerEmp['Position'];
$Namea     = $auditEmp['Names'];  $Positiona = $auditEmp['Position'];

// ── Main data: เจ้าหนี้คงเหลือ (AmountAll) ──────────────────
$AmountAll = 0.0;
if ($PlanPayId > 0) {
    $stmt = $conn->prepare(
        "SELECT COALESCE(SUM(Amount),0) AS s FROM payment "
      . "WHERE DateIn BETWEEN ? AND ? AND PlanPayId=? AND Source=? "
      . "AND DatePaid NOT BETWEEN ? AND ?"
    );
    $stmt->bind_param("ssisis", $DateStart, $DateEnd, $PlanPayId, $Source, $DateStart, $DateEnd);
} else {
    $stmt = $conn->prepare(
        "SELECT COALESCE(SUM(Amount),0) AS s FROM payment "
      . "WHERE DateIn BETWEEN ? AND ? AND Source=? "
      . "AND DatePaid NOT BETWEEN ? AND ?"
    );
    $stmt->bind_param("ssiss", $DateStart, $DateEnd, $Source, $DateStart, $DateEnd);
}
$stmt->execute();
$AmountAll = (float)$stmt->get_result()->fetch_assoc()['s'];
$stmt->close();

// ── Group by PlanPayId ───────────────────────────────────────
if ($PlanPayId > 0) {
    $stmt = $conn->prepare(
        "SELECT PlanPayId, SUM(Amount) AS Amounts FROM payment "
      . "WHERE DateIn BETWEEN ? AND ? AND PlanPayId=? AND Source=? "
      . "AND DatePaid NOT BETWEEN ? AND ? "
      . "GROUP BY PlanPayId"
    );
    $stmt->bind_param("ssisis", $DateStart, $DateEnd, $PlanPayId, $Source, $DateStart, $DateEnd);
} else {
    $stmt = $conn->prepare(
        "SELECT PlanPayId, SUM(Amount) AS Amounts FROM payment "
      . "WHERE DateIn BETWEEN ? AND ? AND Source=? "
      . "AND DatePaid NOT BETWEEN ? AND ? "
      . "GROUP BY PlanPayId"
    );
    $stmt->bind_param("ssiss", $DateStart, $DateEnd, $Source, $DateStart, $DateEnd);
}
$stmt->execute();
$resultGroups = $stmt->get_result();
$groupRows    = [];
while ($r = $resultGroups->fetch_assoc()) { $groupRows[] = $r; }
$stmt->close();

// ── Build HTML data ──────────────────────────────────────────
$data = '';
$i    = 1;

if (count($groupRows) > 0) {
    foreach ($groupRows as $row) {
        $grpPlanPayId = (int)$row['PlanPayId'];
        $Amounts      = (float)$row['Amounts'];

        // ── PlanPayName ──
        $PlanPayName = '';
        $stmt = $conn->prepare("SELECT PlanPayName FROM planpay WHERE PlanPayId=? LIMIT 1");
        $stmt->bind_param("i", $grpPlanPayId);
        $stmt->execute();
        $r2 = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($r2) $PlanPayName = (string)($r2['PlanPayName'] ?? '');

        // ── Detail rows ──
        $data3 = '';
        $stmt = $conn->prepare(
            "SELECT PayId, CompanyId, Amount FROM payment "
          . "WHERE DateIn BETWEEN ? AND ? AND Source=? AND PlanPayId=? "
          . "AND DatePaid NOT BETWEEN ? AND ?"
        );
        $stmt->bind_param("ssisis", $DateStart, $DateEnd, $Source, $grpPlanPayId, $DateStart, $DateEnd);
        $stmt->execute();
        $detailRes = $stmt->get_result();
        while ($row3 = $detailRes->fetch_assoc()) {
            $CompanyId = (int)$row3['CompanyId'];
            $PayId3    = $row3['PayId'];
            $Amount3   = (float)$row3['Amount'];

            // Company name
            $CompanyName = '';
            $stmt2 = $conn->prepare("SELECT CompanyName FROM company WHERE CompanyId=? LIMIT 1");
            $stmt2->bind_param("i", $CompanyId);
            $stmt2->execute();
            $rc = $stmt2->get_result()->fetch_assoc();
            $stmt2->close();
            if ($rc) $CompanyName = (string)($rc['CompanyName'] ?? '');

            $data3 .= "<tr>"
                    . "<td width='50' align='center'></td>"
                    . "<td width='450'>&nbsp; - {$PayId3}/{$CompanyName}</td>"
                    . "<td width='120' align='right'>" . number_format($Amount3, 2) . "</td>"
                    . "<td width='80' align='center'></td>"
                    . "</tr>";
        }
        $stmt->close();

        // Group header + details
        $data .= "<table border='1' style='width:700px'>"
               . "<tr>"
               . "<td width='50' align='center'><b>{$i}</b></td>"
               . "<td width='450'><span style='font-size:16pt;'><b>" . htmlspecialchars($PlanPayName) . "</b></span></td>"
               . "<td width='120' align='right'><b>" . number_format($Amounts, 2) . "</b></td>"
               . "<td width='80'></td>"
               . "</tr>"
               . $data3
               . "</table>";
        $i++;
    }
} else {
    $data = "<table border='1' style='width:700px'>"
          . "<tr><td width='700' align='center' colspan='4'>- ไม่พบข้อมูล -</td></tr>"
          . "</table>";
}

// ── PDF Header ───────────────────────────────────────────────
$mpdf->SetHTMLHeader(
    '<p align="center"><span style="font-size:18pt;"><b>'
  . 'รายงานเจ้าหนี้การค้า ' . $Sourcetext . ' ' . htmlspecialchars($OfficeName) . '<br>'
  . '(ระหว่างวันที่ ' . DateThai($DateStart) . ' ถึงวันที่ ' . DateThai($DateEnd) . ')'
  . '</span></b></p>', '0'
);

// ── PDF Body ─────────────────────────────────────────────────
$html = '
<!DOCTYPE html>
<html>
<head>
<style>
    body            { font-family: sarabun; font-size: 16pt; }
    .dotshed        { border-bottom: 1px dotted; }
    hr              { border-top: 1px dotted; }
    table           { border-collapse: collapse; }
</style>
</head>
<body>

<table border="1" style="width:700px">
    <tr>
        <td width="50"  align="center"><b>ลำดับ</b></td>
        <td width="450"><b>ค่าใช้จ่าย (รหัสรายการ/ชื่อเจ้าหนี้การค้า)</b></td>
        <td width="120" align="right"><b>จำนวนเงิน</b></td>
        <td width="80"  align="center"><b>หมายเหตุ</b></td>
    </tr>
</table>

' . $data . '

<table border="1" style="width:700px">
    <tr>
        <td width="450" colspan="2" align="right"><b>เจ้าหนี้คงเหลือ</b></td>
        <td width="120" align="right"><b>' . number_format($AmountAll, 2) . '</b></td>
        <td width="80"  align="right"></td>
    </tr>
</table>

<br>
<table border="0" style="width:700px">
    <tr>
        <td align="right" colspan="2"><br><br>......................................................................ผู้จัดทำ</td>
        <td align="right" colspan="2"><br><br>......................................................................ผู้ตรวจสอบ</td>
    </tr>
    <tr>
        <td align="center" colspan="2">(' . htmlspecialchars($Namew) . ')<br>' . htmlspecialchars($Positionw) . '</td>
        <td align="center" colspan="2">(' . htmlspecialchars($Namea) . ')<br>' . htmlspecialchars($Positiona) . '</td>
    </tr>
    <tr>
        <td width="175"></td>
        <td width="350" colspan="2" align="center"><br><br><br>.....................................................<br>
            (' . htmlspecialchars($Named) . ')<br>' . htmlspecialchars($Positiond) . '
        </td>
        <td width="175"></td>
    </tr>
</table>

</body>
</html>';

$mpdf->SetMargins(30, 100, 30);
$mpdf->WriteHTML($html);
$mpdf->Output();
?>