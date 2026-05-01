<?php
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Bangkok');
require_once __DIR__ . '/connect_db.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['Username'])) {
  header("Location: login.php");
  exit;
}

/* =========================
   Helpers
========================= */
function pick_id($text): int {
  $text = trim((string)$text);
  if ($text === '') return 0;
  if (preg_match('/^\s*(\d+)/', $text, $m)) return (int)$m[1];
  return 0;
}

function th_dmy_to_ymd($dmy_th): ?string {
  $dmy_th = trim((string)$dmy_th);
  if ($dmy_th === '') return null;

  $p = explode('/', $dmy_th);
  if (count($p) !== 3) return null;

  $d = (int)$p[0];
  $m = (int)$p[1];
  $y_th = (int)$p[2];

  $y = $y_th - 543;
  if (!checkdate($m, $d, $y)) return null;

  return sprintf('%04d-%02d-%02d', $y, $m, $d);
}

function to_money($v): float {
  $v = str_replace(',', '', (string)$v);
  return round((float)$v, 2);
}

function swal_page($ok, $title, $text, $redirectUrl, $ms = 1600){
  $icon = $ok ? "success" : "error";
  $titleJs = json_encode($title, JSON_UNESCAPED_UNICODE);
  $textJs  = json_encode($text, JSON_UNESCAPED_UNICODE);
  $redirJs = json_encode($redirectUrl, JSON_UNESCAPED_UNICODE);
  $ms = (int)$ms;

  echo <<<HTML
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>กำลังบันทึก...</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>body{font-family:"Kanit",sans-serif;background:#f6f8fc;}</style>
</head>
<body>
<script>
Swal.fire({
  icon: "{$icon}",
  title: {$titleJs},
  text: {$textJs},
  timer: {$ms},
  showConfirmButton: false,
  allowOutsideClick: false,
  allowEscapeKey: false
}).then(() => window.location.href = {$redirJs});
</script>
</body>
</html>
HTML;
  exit;
}

/* =========================
   Require POST
========================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: accounting.php");
  exit;
}

/* =========================
   Read inputs
========================= */
$PayId  = (int)($_POST['PayId'] ?? 0);
$DeptId = (int)($_POST['Dept'] ?? 0);
$DateIn = th_dmy_to_ymd($_POST['DateIn'] ?? '');

$TypesId   = pick_id($_POST['Types'] ?? '');
$PlanPayId = pick_id($_POST['PlanPay'] ?? '');
$CompanyId = pick_id($_POST['Company'] ?? '');

$NumList = (int)($_POST['NumList'] ?? 0);
$Detail  = trim((string)($_POST['Detail'] ?? ''));

$Price  = to_money($_POST['Price'] ?? 0);
$Vat    = to_money($_POST['Vat'] ?? 0);
$Amount = to_money($_POST['Amount'] ?? 0);

$Source = (int)($_POST['source'] ?? 1);
if ($Source !== 1 && $Source !== 2) $Source = 1;

/* =========================
   Validate
========================= */
if ($PayId <= 0) swal_page(false, "บันทึกไม่สำเร็จ", "ไม่พบ PayId", "accounting.php");
if (!$DateIn) swal_page(false, "บันทึกไม่สำเร็จ", "รูปแบบวันที่ไม่ถูกต้อง", "editform.php?PayId=".$PayId);
if ($DeptId <= 0) swal_page(false, "บันทึกไม่สำเร็จ", "กรุณาเลือกกลุ่มงาน/งาน", "editform.php?PayId=".$PayId);
if ($TypesId <= 0 || $PlanPayId <= 0 || $CompanyId <= 0) {
  swal_page(false, "บันทึกไม่สำเร็จ", "หมวด/แผน/บริษัท ต้องขึ้นต้นด้วยรหัส เช่น 3. ...", "editform.php?PayId=".$PayId);
}
if ($Detail === '') swal_page(false, "บันทึกไม่สำเร็จ", "กรุณากรอกเลขที่ใบส่งของ", "editform.php?PayId=".$PayId);

/* =========================
   Update (Prepared Statement)
========================= */
$sql = "UPDATE payment
        SET DateIn=?,
            TypesId=?,
            PlanPayId=?,
            DeptId=?,
            NumList=?,
            Detail=?,
            Price=?,
            Vat=?,
            Amount=?,
            CompanyId=?,
            Source=?
        WHERE PayId=?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  swal_page(false, "บันทึกไม่สำเร็จ", "Prepare failed: ".$conn->error, "editform.php?PayId=".$PayId);
}

/*
  ต้องมี 12 ตัวอักษร ตรงกับ 12 ตัวแปร:
  DateIn(s) TypesId(i) PlanPayId(i) DeptId(i) NumList(i) Detail(s)
  Price(d) Vat(d) Amount(d) CompanyId(i) Source(i) PayId(i)
*/
$stmt->bind_param(
  "siiiisdddiii",
  $DateIn,
  $TypesId,
  $PlanPayId,
  $DeptId,
  $NumList,
  $Detail,
  $Price,
  $Vat,
  $Amount,
  $CompanyId,
  $Source,
  $PayId
);

$ok  = $stmt->execute();
$err = $stmt->error;
$stmt->close();

if ($ok) {
  swal_page(true, "แก้ไขข้อมูลสำเร็จ", "ระบบจะพากลับไปหน้ารายการ", "accounting.php", 1300);
} else {
  swal_page(false, "บันทึกไม่สำเร็จ", "เกิดข้อผิดพลาด: ".$err, "editform.php?PayId=".$PayId, 2200);
}
