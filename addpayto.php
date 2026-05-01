<?php
session_start();
include 'connect_db.php';
$ChequeId=$_REQUEST['ChequeId'];
$CompanyName=$_REQUEST['CompanyName'];
$receiver=$_REQUEST['receiver'];
$received=$_REQUEST['received'];
$Sumnets=number_format($_REQUEST['Sumnet'], 2, '.', '');

if($received!=""){
	$PayTo=$received;
}elseif($CompanyName==""){
	$PayTo = $receiver;
	}else{
		$PayTo = $CompanyName;
	}
//echo $Sumnet;
$datenow=date("Y-m-d");
$sql = "UPDATE `cheque` SET PayTo = '$PayTo', Net = '$Sumnets' WHERE ChequeId='$ChequeId'";
$conn->query($sql);
header("location:printcheque.php");

?>