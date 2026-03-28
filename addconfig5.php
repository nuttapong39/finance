<?php
session_start();
include 'connect_db.php';
$TypebName=$_REQUEST['TypebName'];

$sql = "INSERT INTO `typeb`(`TypebId`, `TypebName`) VALUES (Null,'$TypebName')";
$conn->query($sql);
header("location:config5.php");

?>