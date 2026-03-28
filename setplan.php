<?php
session_start();
require_once __DIR__ . '/connect_db.php';

@mysqli_set_charset($conn, 'utf8mb4');

$PlanPayId = isset($_REQUEST['PlanPayId']) ? (int)$_REQUEST['PlanPayId'] : 0;
$Qyear     = trim($_REQUEST['Qyears'] ?? ''); // varchar(4)
$AmountRaw = trim($_REQUEST['Amount'] ?? '');
$AmountRaw = str_replace([',', ' '], '', $AmountRaw);

if ($PlanPayId <= 0) {
  header("Location: plan.php?sw=error&msg=" . urlencode("รหัสรายการไม่ถูกต้อง"));
  exit;
}
if (!preg_match('/^\d{4}$/', $Qyear)) {
  header("Location: plan.php?sw=error&msg=" . urlencode("ปีงบประมาณไม่ถูกต้อง"));
  exit;
}
if (!is_numeric($AmountRaw)) {
  header("Location: plan.php?Qyear={$Qyear}&sw=error&msg=" . urlencode("จำนวนเงินต้องเป็นตัวเลข"));
  exit;
}

$Amount = round((float)$AmountRaw, 2);
if ($Amount < 0) $Amount = 0;

try {
  // ต้องมี UNIQUE KEY (PlanPayId,Qyear) ก่อน ถึงจะใช้ UPSERT ได้
  $sql = "INSERT INTO planpayset (PlanPayId, Amount, Qyear)
          VALUES (?, ?, ?)
          ON DUPLICATE KEY UPDATE Amount = VALUES(Amount)";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ids", $PlanPayId, $Amount, $Qyear);
  $stmt->execute();
  $stmt->close();

  header("Location: plan.php?Qyear={$Qyear}&sw=success&msg=" . urlencode("บันทึกข้อมูลเรียบร้อย"));
  exit;

} catch (Throwable $e) {
  header("Location: plan.php?Qyear={$Qyear}&sw=error&msg=" . urlencode("บันทึกไม่สำเร็จ: " . $e->getMessage()));
  exit;
}
