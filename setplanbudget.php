<?php
session_start();
include 'connect_db.php';

$i=0;
$PlanPayId=$_REQUEST['PlanPayId'];
$Qyear=$_REQUEST['Qyears'];
$Amount=$_REQUEST['Amount'];

$sql2 = "SELECT * FROM planpayset2 where PlanPayId='$PlanPayId' and Qyear='$Qyear'";
$result2 = $conn->query($sql2);
$row2 = $result2->fetch_assoc();
$Id=$row2["Id"];
if ($result2->num_rows > 0){
	$sql3="UPDATE `planpayset2` SET `Amount`='$Amount' WHERE Id='$Id'";
	$conn->query($sql3);
}else{
	$sql4 = "INSERT INTO `planpayset2`(`Id`, `PlanPayId`, `Amount`, `Qyear`) VALUES (null,'$PlanPayId','$Amount','$Qyear')";
	$conn->query($sql4);
}

header("location:planbudget.php?Qyear=$Qyear");

?>