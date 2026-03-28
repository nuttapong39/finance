<?php
session_start();
include 'connect_db.php';
$CompanyId=$_REQUEST['CompanyId'];
$sql = "DELETE FROM `company` WHERE CompanyId=$CompanyId";
$conn->query($sql);
header("location:config6.php");
?>