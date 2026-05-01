<?php
session_start();
include 'connect_db.php';
$TypebId=$_REQUEST['TypebId'];
$sql = "DELETE FROM `typeb` WHERE TypebId=$TypebId";
$conn->query($sql);
header("location:config5.php");
?>