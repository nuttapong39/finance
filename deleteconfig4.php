<?php
session_start();
include 'connect_db.php';
$TypesId=$_REQUEST['TypesId'];
$sql = "DELETE FROM `types` WHERE TypesId=$TypesId";
$conn->query($sql);
header("location:config4.php");
?>