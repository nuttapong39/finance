<?php
include 'connect_db.php';
$PayId=$_REQUEST['PayId'];
$Percent=$_REQUEST['Percent'];
$Price=$_REQUEST['Price'];
$Amount=$_REQUEST['Amount'];
$Tax=$Price*$Percent/100;
$Net=$Amount-$Tax;
echo $Tax.",".$Net;
$sql = "UPDATE `payment` SET Tax = '$Tax', Net='$Net' WHERE PayId=$PayId";
$conn->query($sql);
header("location:finance.php");

?>