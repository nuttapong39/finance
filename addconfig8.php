<?php
session_start();
include 'connect_db.php';
$Username=$_REQUEST['Username'];
$Password=$_REQUEST['Password'];
$Names=$_REQUEST['Names'];
$Position=$_REQUEST['Position'];
$TypeUser=$_REQUEST['TypeUser'];

$sql = "INSERT INTO `employee`(`Username`, `Password`, `Names`, `Position`, `TypeUser`, `Status`) VALUES ('$Username','$Password','$Names','$Position','$TypeUser','1')";

$conn->query($sql);
header("location:config8.php");

?>