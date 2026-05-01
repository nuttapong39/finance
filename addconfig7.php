<?php
session_start();
include 'connect_db.php';
$PlanName=$_REQUEST['PlanName'];

$sql = "INSERT INTO `planpaytype`(`PlanPayTypeId`, `PlanName`) VALUES (Null,'$PlanName')";
$conn->query($sql);
header("location:config7.php");

?>