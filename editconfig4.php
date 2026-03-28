<?php
session_start();
include 'connect_db.php';
$TypesName=$_REQUEST['TypesName'];
$TypesId=$_REQUEST['TypesId'];
$sql = "UPDATE `types` SET `TypesName`='$TypesName' WHERE TypesId=$TypesId";
$conn->query($sql);
header("location:config4.php");
?>