<?php
session_start();
include 'connect_db.php';
$DeptId=$_REQUEST['DeptId'];
$sql = "DELETE FROM `department` WHERE DeptId=$DeptId";
$conn->query($sql);
header("location:config2.php");

?>