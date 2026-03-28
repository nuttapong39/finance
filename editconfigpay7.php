<?php
session_start();
include 'connect_db.php';
$PlanPayId=$_REQUEST['PlanPayId'];
$PlanPayName=$_REQUEST['PlanPayName'];
$PlanPayTypeId=$_REQUEST['PlanPayTypeId'];
$sql = "UPDATE `planpay` SET `PlanPayName`='$PlanPayName',`PlanPayTypeId`=$PlanPayTypeId WHERE PlanPayId=$PlanPayId";
$conn->query($sql);
header("location:config7.php");
?>