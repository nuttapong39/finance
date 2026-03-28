<?php
session_start();
include 'connect_db.php';
$Username=$_REQUEST['Username'];
$Names=$_REQUEST['Names'];
$Position=$_REQUEST['Position'];
$TypeUser=$_REQUEST['TypeUser'];
$Status=$_REQUEST['Status'];

$sql = "UPDATE `employee` SET `Names`='$Names',`Position`='$Position',`TypeUser`='$TypeUser',`Status`='$Status' WHERE Username='$Username'";
$conn->query($sql);
header("location:config8.php");
?>