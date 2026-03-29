<?php
session_start();
include 'connect_db.php';
require_once __DIR__ . '/notify_helper.php';

$PayId=$_REQUEST['PayId'];
$DatePays=$_REQUEST['DatePay'];
$exds = explode('/',$DatePays);
$YearS=$exds[2]-543;
$DatePay=$YearS."-".$exds[1]."-".$exds[0];
$Cheque=$_REQUEST['Cheque'];
$active=$_REQUEST['active'];
$Comment=$_REQUEST['Comment'];
$datenow=date("Y-m-d");

// ── ฟังก์ชันส่ง alert หลังบันทึกสำเร็จ ──
function notify_addcheque(mysqli $conn, int $payId, string $chequeNo): void {
    try {
        $info = moph_get_payment_info($conn, $payId);
        if ($info) {
            $actor = trim(($_SESSION['Names'] ?? '') . ' (' . ($_SESSION['Username'] ?? '') . ')');
            $actor = ($actor === ' ()') ? 'ระบบ' : $actor;
            $extra = $chequeNo ? "เลขที่เช็ค: {$chequeNo}" : '';
            $msgs  = moph_status_messages(
                $info['payId'],
                'ดำเนินการเบิกจ่าย',
                '🔵',
                $info['company'],
                $info['detail'],
                $info['amount'],
                $actor,
                $extra
            );
            moph_broadcast($msgs, $conn);
        }
    } catch (Throwable $e) {
        error_log("MOPH ALERT addcheque exception: " . $e->getMessage());
    }
}

//echo $DatePay.'/'.$Cheque.'/'.$Comment;
if($active==1){
	$sql2 = "SELECT * FROM cheque where ChequeId='$Cheque'";
	$result2 = $conn->query($sql2);
	$row2 = $result2->fetch_assoc();
	$DatePrint=$row2["DatePrint"];
		if ($result2->num_rows > 0){
			if($DatePrint==$datenow){
				$sql = "UPDATE `payment` SET DatePay = '$DatePay',Cheque = '$Cheque',Comment = '$Comment' WHERE PayId='$PayId'";
				if ($conn->query($sql)) notify_addcheque($conn, (int)$PayId, (string)$Cheque);
				$sql3="UPDATE `cheque` SET `DatePrint`='$DatePay' WHERE ChequeId='$Cheque'";
				$conn->query($sql3);
				header("location:cheque.php?prv=2");
			}else{
				header("location:cheque.php?prv=1");
			}
		}else{
			$sql = "UPDATE `payment` SET DatePay = '$DatePay',Cheque = '$Cheque',Comment = '$Comment' WHERE PayId='$PayId'";
			if ($conn->query($sql)) notify_addcheque($conn, (int)$PayId, (string)$Cheque);
			$sql4 = "INSERT INTO `cheque`(`ChequeId`, `PayTo`, `DatePrint`, `DatePaid`, `Net`) VALUES ('$Cheque','','$DatePay','','')";
			$conn->query($sql4);
			header("location:cheque.php?prv=3");
		}
}else{
	$sql = "UPDATE `payment` SET DatePay = '$DatePay',Cheque = '',Comment = '$Comment' WHERE PayId='$PayId'";
	if ($conn->query($sql)) notify_addcheque($conn, (int)$PayId, '');
	header("location:cheque.php");
}
?>