<?php
session_start();
include 'connect_db.php';
require_once __DIR__ . '/notify_helper.php';

$PayId=$_REQUEST['PayId'];
$BankId=$_REQUEST['Bank'];
$datenow=date("Y-m-d");
$Percent=$_REQUEST['Percent'];
$Price=$_REQUEST['Price'];
$Typeb=$_REQUEST['Typeb'];
$Amount=$_REQUEST['Amount'];
$Taxs=($Price*$Percent)/100;
$Nets=$Amount-$Taxs;
$Tax=number_format($Taxs, 2, '.', '');
$Net=number_format($Nets, 2, '.', '');

$sql = "UPDATE `payment` SET TypebId=$Typeb, DateApprove = '$datenow',BankId = '$BankId',Tax = $Tax, Net=$Net WHERE PayId=$PayId";
if ($conn->query($sql)) {
    // ── MOPH ALERT: สถานะ "อยู่ระหว่างจัดทำเช็ค" ──
    try {
        $info = moph_get_payment_info($conn, (int)$PayId);
        if ($info) {
            $actor = trim(($_SESSION['Names'] ?? '') . ' (' . ($_SESSION['Username'] ?? '') . ')');
            $actor = ($actor === ' ()') ? 'ระบบ' : $actor;
            $msgs  = moph_status_messages(
                $info['payId'],
                'อยู่ระหว่างจัดทำเช็ค',
                '🟣',
                $info['company'],
                $info['detail'],
                $info['amount'],
                $actor
            );
            moph_broadcast($msgs, $conn);
        }
    } catch (Throwable $e) {
        error_log("MOPH ALERT approved exception: " . $e->getMessage());
    }
}
header("location:finance.php");

?>