<?php
session_start();
include 'connect_db.php';
$CompanyId=$_REQUEST['CompanyId'];
$CompanyName=$_REQUEST['CompanyName'];
$Address=$_REQUEST['Address'];
$Tel=$_REQUEST['Tel'];
$PID=$_REQUEST['PID'];
$IncomeTax=$_REQUEST['IncomeTax'];

$sql = "UPDATE `company` SET `CompanyName`='$CompanyName',`Address`='$Address',`Tel`='$Tel',`PID`='$PID',`IncomeTax`='$IncomeTax' WHERE CompanyId=$CompanyId";
$conn->query($sql);
header("location:config6.php");
?>