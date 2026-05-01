<?php
session_start();
include 'connect_db.php';
$PlanPayName=$_REQUEST['PlanPayName'];
$PlanPayTypeId=$_REQUEST['PlanPayTypeId'];
$sql2 = "SELECT max(PlanPayId) as MaxId FROM planpay where PlanPayTypeId = $PlanPayTypeId";
$result2 = $conn->query($sql2);
$row2 = $result2->fetch_assoc();
$MaxId=$row2["MaxId"];
if($MaxId==0){
	$PlanPayId=strval($PlanPayTypeId)."01";
}else{
	$PlanPayId=$MaxId+1;
}

//echo $PlanPayId;
$sql = "INSERT INTO `planpay`(`PlanPayId`, `PlanPayName`, `PlanPayTypeId`) VALUES ($PlanPayId,'$PlanPayName',$PlanPayTypeId)";
$conn->query($sql);
header("location:config7.php");

?>