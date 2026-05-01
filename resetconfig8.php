<?php
session_start();
include 'connect_db.php';
$Username=$_REQUEST['Username'];

$sql = "UPDATE `employee` SET `Password`='pass1234' WHERE Username='$Username'";
$conn->query($sql);
header("location:config8.php");
?>