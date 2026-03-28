<?php
session_start();
include 'connect_db.php';
$PayId=$_REQUEST['PayId'];

$sql = "DELETE FROM `payment` WHERE PayId='$PayId'";
$conn->query($sql);
header("location:accounting.php");

?>