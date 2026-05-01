<?php
session_start();
include 'connect_db.php';
$PlanName=$_REQUEST['PlanName'];
$PlanPayTypeId=$_REQUEST['PlanPayTypeId'];
$sql = "UPDATE `planpaytype` SET `PlanName`='$PlanName' WHERE PlanPayTypeId=$PlanPayTypeId";
$conn->query($sql);
header("location:config7.php");
?>