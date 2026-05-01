<?php
session_start();
include 'connect_db.php';
$PlanPayId=$_REQUEST['PlanPayId'];
$sql = "DELETE FROM `planpay` WHERE PlanPayId=$PlanPayId";
$conn->query($sql);
header("location:config7.php");
?>