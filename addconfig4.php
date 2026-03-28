<?php
session_start();
include 'connect_db.php';
$TypesName=$_REQUEST['TypesName'];

$sql = "INSERT INTO `types`(`TypesId`, `TypesName`) VALUES (Null,'$TypesName')";
$conn->query($sql);
header("location:config4.php");

?>