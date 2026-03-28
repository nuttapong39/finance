<?php
session_start();
include 'connect_db.php';
$ChequeId=$_REQUEST['ChequeId'];
$sql1 = "UPDATE `payment` SET DatePay = '',Cheque = '',Comment = '' WHERE Cheque='$ChequeId'";
$conn->query($sql1);

$sql2 = "DELETE FROM `cheque` WHERE ChequeId=$ChequeId";
$conn->query($sql2);

header("location:printcheque.php");

?>