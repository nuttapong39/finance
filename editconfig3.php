<?php
session_start();
include 'connect_db.php';
$BankId=$_REQUEST['BankId'];
$BankOf=$_REQUEST['BankOf'];
$BankName=$_REQUEST['BankName'];
$BankNo=$_REQUEST['BankNo'];
$sql = "UPDATE `bank` SET `BankOf`='$BankOf',`BankName`='$BankName',`BankNo`='$BankNo' WHERE BankId=$BankId";
$conn->query($sql);
header("location:config3.php");

?>