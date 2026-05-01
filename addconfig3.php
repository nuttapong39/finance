<?php
session_start();
include 'connect_db.php';
$BankOf=$_REQUEST['BankOf'];
$BankName=$_REQUEST['BankName'];
$BankNo=$_REQUEST['BankNo'];

$sql = "INSERT INTO `bank`(`BankId`, `BankOf`, `BankName`, `BankNo`) VALUES (NUll,'$BankOf','$BankName','$BankNo')";
$conn->query($sql);
header("location:config3.php");

?>