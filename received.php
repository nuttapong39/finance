<?php
session_start();
include 'connect_db.php';
$DateReceive=$_REQUEST['DateReceive'];
$ReceiveNo=$_REQUEST['ReceiveNo'];
$PayId=$_REQUEST['PayId'];
$exds = explode('/',$DateReceive);
$YearS=$exds[2]-543;
$DateReceived=$YearS."-".$exds[1]."-".$exds[0];

$sql = "UPDATE `payment` SET DateReceive = '$DateReceived',ReceiveNo = '$ReceiveNo' WHERE PayId=$PayId";
$conn->query($sql);
header("location:receive.php");

?>