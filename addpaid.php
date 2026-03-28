<?php
session_start();
include 'connect_db.php';
$ChequeId=$_REQUEST['ChequeId'];
$DatePaids=$_REQUEST['DatePaid'];
$PayId=$_REQUEST['PayId'];
$exds = explode('/',$DatePaids);
$YearS=$exds[2]-543;
$DatePaid=$YearS."-".$exds[1]."-".$exds[0];

//echo $Sumnet;
$datenow=date("Y-m-d");
$sql = "UPDATE `cheque` SET DatePaid = '$DatePaid' WHERE ChequeId='$ChequeId'";
$conn->query($sql);
$sql2 = "UPDATE `payment` SET DatePaid = '$DatePaid' WHERE Cheque='$ChequeId'";
$conn->query($sql2);
header("location:paid.php");

?>