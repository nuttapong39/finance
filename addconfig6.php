<?php
session_start();
include 'connect_db.php';
$CompanyName=$_REQUEST['CompanyName'];
$Address=$_REQUEST['Address'];
$Tel=$_REQUEST['Tel'];
$PID=$_REQUEST['PID'];
$IncomeTax=$_REQUEST['IncomeTax'];

$sql = "INSERT INTO `company`(`CompanyId`, `CompanyName`, `Address`, `Tel`, `PID`, `IncomeTax`) VALUES (Null,'$CompanyName','$Address','$Tel','$PID','$IncomeTax')";

$conn->query($sql);
header("location:config6.php");

?>