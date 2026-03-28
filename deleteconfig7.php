<?php
session_start();
include 'connect_db.php';
$PlanPayTypeId=$_REQUEST['PlanPayTypeId'];
$sql = "DELETE FROM `planpaytype` WHERE PlanPayTypeId=$PlanPayTypeId";
$conn->query($sql);
header("location:config7.php");
?>