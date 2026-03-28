<?php
session_start();
include 'connect_db.php';
$BankId=$_REQUEST['BankId'];
$sql = "DELETE FROM `bank` WHERE BankId=$BankId";
$conn->query($sql);
header("location:config3.php");
?>