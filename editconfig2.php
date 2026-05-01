<?php
session_start();
include 'connect_db.php';
$DeptName=$_REQUEST['DeptName'];
$DeptId=$_REQUEST['DeptId'];
$sql = "UPDATE `department` SET `DeptName`='$DeptName' WHERE DeptId=$DeptId";
$conn->query($sql);
header("location:config2.php");

?>