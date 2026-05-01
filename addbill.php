<?php
session_start();
include 'connect_db.php';
$PayId=$_REQUEST['PayId'];
$BillDates=$_REQUEST['BillDate'];
$BillNo=$_REQUEST['BillNo'];
$exds = explode('/',$BillDates);
$YearS=$exds[2]-543;
$BillDate=$YearS."-".$exds[1]."-".$exds[0];

//echo $Sumnet;
$datenow=date("Y-m-d");
$sql = "UPDATE `payment` SET BillNo='$BillNo', BillDate = '$BillDate' WHERE PayId='$PayId'";
$conn->query($sql);
header("location:findpay.php");

?>