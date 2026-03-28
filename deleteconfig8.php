<?php
session_start();
include 'connect_db.php';
$Username=$_REQUEST['Username'];
$sql = "DELETE FROM `employee` WHERE Username='$Username'";
$conn->query($sql);
header("location:config8.php");
?>