<?php
session_start();
include 'connect_db.php';
$DeptName=$_REQUEST['DeptName'];

$sql = "INSERT INTO `department`(`DeptId`, `DeptName`) VALUES (Null,'$DeptName')";
$conn->query($sql);
header("location:config2.php");

?>