<?php
@session_start();
include 'connect_db.php';
$Username=$_SESSION["Username"];
$Password=$_GET["Password"];
$NewPassword=$_GET["NewPassword"];
$ConPassword=$_GET["ConPassword"];
date_default_timezone_set('Asia/Bangkok');


$sql = "SELECT Password FROM employee where Password='$Password'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if (mysqli_num_rows($result) > 0 ){

	if($NewPassword==$ConPassword){
		if($NewPassword==$Password){
			header("location:changepass.php?prv=2");//รหัสผ่านใหม่ตรงกัน
		}else{
			$sql_edit= "UPDATE `employee` SET `Password`='$NewPassword' WHERE Username='$Username'";
			if($conn->query($sql_edit)){
				session_destroy();
				$_SESSION["PID"]="";
				$_SESSION["Names"]="";
				$_SESSION["TypeUser"]="";
			}
			header("location:changepass.php?prv=4");//รหัสผ่านใหม่ตรงกัน
		}
		
	}else if($NewPassword==$Password){
		header("location:changepass.php?prv=2");//รหัสผ่านเดิมตงกับใหม่ตรงกัน
	}else{
		header("location:changepass.php?prv=3");//รหัสผ่านใหม่ไม่่ตรงกัน
	}
} else {
   header("location:changepass.php?prv=1"); //รหัสผ่านเดิมไม่ถูก

}
 ?>