<?php
session_start();
include 'connect_db.php';
$TypebName=$_REQUEST['TypebName'];
$TypebId=$_REQUEST['TypebId'];
$sql = "UPDATE `typeb` SET `TypebName`='$TypebName' WHERE TypebId=$TypebId";
$conn->query($sql);
header("location:config5.php");
?>