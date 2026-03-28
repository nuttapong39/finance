<?php
session_start();
include 'connect_db.php';
$PayId=$_REQUEST['PayId'];
$BookNo=$_REQUEST['BookNo'];
$DateBooks=$_REQUEST['DateBook'];
$exd = explode('/',$DateBooks);
$YearD = $exd[2]-543;
$DateBook = $YearD."-".$exd[1]."-".$exd[0];

//echo $DateBooks."   ".$DateBook;
$sql = "UPDATE `payment` SET BookNo = '$BookNo',DateBook = '$DateBook',DatePaid='$DateBook' WHERE PayId=$PayId";
$conn->query($sql);
header("location:paidment.php");

?>