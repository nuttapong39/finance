<!DOCTYPE html>
<?php 
error_reporting(~E_NOTICE);
@session_start();
include "connect_db.php"; 
$PID=$_SESSION["PID"];
$Names=$_SESSION["Names"];
if($Names == ""){
	header('location:index.php');
}
?>
<html>
<head>
	<link rel="shortcut icon" type="image/x-icon" href="pic/map-icon.png" />
	<title> ระบบจัดเก็บพิกัดที่อยู่ </title>
	<meta charset="UTF-8">
	<meta http-equiv=Content-Type content="text/html; charset=tis-620">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="./css/bootstrap.min.css">
	<script type="text/javascript" src="./js/jquery.js"></script>
	<script type="text/javascript" src="./js/bootstrap.min.js"></script>
    <script type="text/javascript" src="./js/bootbox.min.js"></script>

<script type="text/JavaScript">
function copyit(field) {
   var temp=document.getElementById(field);
   temp.focus()
   temp.select()
   therange=temp.createTextRange()
   therange.execCommand("Copy")
   }
</script>

</head>

<body>

<div class="row">
<br>
</div>
<div class="row">
  <div class="col-xs-6 col-md-1"></div>
  <div class="col-xs-6 col-md-10">
        <div class="media">
          <div class="media-left">
                <a href="#">
                  <img src="pic/map-icon.png" width="90" height="90">
                </a>
          </div>
          <div class="media-body">
              <br>
              <h3 class="media-heading"><p class="text-primary">ระบบจัดเก็บพิกัดที่อยู่</p></h3>
              <h4><p class="text-info">จังหวัดพะเยา</p></h4>
          </div>
        </div>
  </div>
  <div class="col-xs-6 col-md-1"></div>
</div>
<div class="row">
<br>
</div>
<?php
$sql_log1 = "SELECT * FROM log where PID = '$PID' and activity='Search'";
$result_log1 = $conn->query($sql_log1);
$searchnum=$result_log1->num_rows;

$sql_log2 = "SELECT * FROM log where PID = '$PID' and activity='Add'";
$result_log2 = $conn->query($sql_log2);
$addnum=$result_log2->num_rows;

$sql_log3 = "SELECT * FROM log where PID = '$PID' and activity='Edit'";
$result_log3 = $conn->query($sql_log3);
$editnum=$result_log3->num_rows;

$sql_log4 = "SELECT * FROM log where PID = '$PID' and activity='Delete'";
$result_log4 = $conn->query($sql_log4);
$deletenum=$result_log4->num_rows;


?>
<div class="row">
  <div class="col-xs-6 col-md-3"></div>
  <div class="col-xs-6 col-md-6">
  		<h4 align="center">สถิติการเข้าใช้งานในระบบของ <?php echo $Names; ?> (จำนวนครั้ง)</h4>
        <ul class="list-group">
		  <li class="list-group-item">
		    <span class="badge"><?php echo $searchnum; ?></span>
		    การค้นหาข้อมูล
	  	  </li>
	  	  <li class="list-group-item">
		    <span class="badge"><?php echo $addnum; ?></span>
		    การบันทึกข้อมูล
	  	  </li>
	  	  <li class="list-group-item">
		    <span class="badge"><?php echo $editnum; ?></span>
		    การแก้ไขข้อมูล
	  	  </li>
	  	  <li class="list-group-item">
		    <span class="badge"><?php echo $deletenum; ?></span>
		    การลบข้อมูล
	  	  </li>
		</ul>
  </div>
  <div class="col-xs-6 col-md-3"></div>
</div>

<div class="row">
	<div class="col-xs-12 col-md-1"></div>
  	<div class="col-xs-12 col-md-10">
  		<ul class="list-group">
            <li class="list-group-item">
                    
	<div class="panel panel-primary"><p class="bg-primary">&nbsp;<span class="glyphicon glyphicon-stats" aria-hidden="true"></span> สถิติการบันทึกข้อมูลทั้งระบบ</p>
	 	<div class="table-responsive">
	 	<table class="table table-striped">
		 	<tr class="info">
		 	  <td align="center"></td>
			  <td><strong>อำเภอ</strong></td>
			  <td><strong>ตำบล</strong></td>
			  <td><strong>จำนวน</strong></td>
			  <td align="center"></td>
			</tr>
		
    <?php
    include "connect_db.php";
    //<!-- อ.เมืองพะเยา -->
	$sum1=0;
	$sql1 = "SELECT * FROM tambon_py where ampurcode = 5601 ORDER BY tamboncodefull";
	$result1 = $conn->query($sql1);
	if ($result1->num_rows > 0){
		while($row1 = $result1->fetch_assoc()) {
			$tamboncodefull = $row1['tamboncodefull'];
			$tambonname=$row1['tambonname'];
			$sql = "SELECT * FROM point_py where id_vill_no LIKE '".$tamboncodefull."%' ";
			$result = $conn->query($sql);
			$sum1=$sum1+$result->num_rows;
 	?>
 	
 	<tr>
 			  <td align="center"></td>
			  <td>อำเภอเมืองพะเยา</td>
			  <td><?php echo $tambonname;?></td>
			  <td><?php echo $result->num_rows; ?></td>
			  <td align="center"></td>
	</tr>
 	<?php
	$i++;
	 }
	}
	?>
	<tr>
 			  <td align="center"></td>
			  <td align="center"></td>
			  <td><strong>รวมอำเภอเมืองพะเยา</strong></td>
			  <td><strong><?php echo $sum1; ?></strong></td>
			  <td align="center"></td>
	</tr>
	<?php
	//<!-- อ.เชียงม่วน -->
	$sum2=0;
	$sql2 = "SELECT * FROM tambon_py where ampurcode = 5604 ORDER BY tamboncodefull";
	$result2 = $conn->query($sql2);
	if ($result2->num_rows > 0){
		while($row2 = $result2->fetch_assoc()) {
			$tamboncodefull = $row2['tamboncodefull'];
			$tambonname=$row2['tambonname'];
			$sql = "SELECT * FROM point_py where id_vill_no LIKE '".$tamboncodefull."%' ";
			$result = $conn->query($sql);
			$sum2=$sum2+$result->num_rows;
 	?>
 	
 	<tr>
 			  <td align="center"></td>
			  <td>อำเภอเชียงม่วน</td>
			  <td><?php echo $tambonname;?></td>
			  <td><?php echo $result->num_rows; ?></td>
			  <td align="center"></td>
	</tr>
 	<?php
	$i++;
	 }
	}
	?>
	<tr>
 			  <td align="center"></td>
			  <td align="center"></td>
			  <td><strong>รวมอำเภอเชียงม่วน</strong></td>
			  <td><strong><?php echo $sum2; ?></strong></td>
			  <td align="center"></td>
	</tr>
	<?php
	//<!-- อ.ดอกคำใต้ -->
	$sum3=0;
	$sql3 = "SELECT * FROM tambon_py where ampurcode = 5605 ORDER BY tamboncodefull";
	$result3 = $conn->query($sql3);
	if ($result3->num_rows > 0){
		while($row3 = $result3->fetch_assoc()) {
			$tamboncodefull = $row3['tamboncodefull'];
			$tambonname=$row3['tambonname'];
			$sql = "SELECT * FROM point_py where id_vill_no LIKE '".$tamboncodefull."%' ";
			$result = $conn->query($sql);
			$sum3=$sum3+$result->num_rows;
 	?>
 	
 	<tr>
 			  <td align="center"></td>
			  <td>อำเภอดอกคำใต้</td>
			  <td><?php echo $tambonname;?></td>
			  <td><?php echo $result->num_rows; ?></td>
			  <td align="center"></td>
	</tr>
 	<?php
	$i++;
	 }
	}
	?>
	<tr>
 			  <td align="center"></td>
			  <td align="center"></td>
			  <td><strong>รวมอำเภอดอกคำใต้</strong></td>
			  <td><strong><?php echo $sum3; ?></strong></td>
			  <td align="center"></td>
	</tr>
	<?php
	//<!-- อ.ปง -->
	$sum4=0;
	$sql4 = "SELECT * FROM tambon_py where ampurcode = 5606 ORDER BY tamboncodefull";
	$result4 = $conn->query($sql4);
	if ($result4->num_rows > 0){
		while($row4 = $result4->fetch_assoc()) {
			$tamboncodefull = $row4['tamboncodefull'];
			$tambonname=$row4['tambonname'];
			$sql = "SELECT * FROM point_py where id_vill_no LIKE '".$tamboncodefull."%' ";
			$result = $conn->query($sql);
			$sum4=$sum4+$result->num_rows;
 	?>
 	
 	<tr>
 			  <td align="center"></td>
			  <td>อำเภอปง</td>
			  <td><?php echo $tambonname;?></td>
			  <td><?php echo $result->num_rows; ?></td>
			  <td align="center"></td>
	</tr>
 	<?php
	$i++;
	 }
	}
	?>
	<tr>
 			  <td align="center"></td>
			  <td align="center"></td>
			  <td><strong>รวมอำเภอปง</strong></td>
			  <td><strong><?php echo $sum4; ?></strong></td>
			  <td align="center"></td>
	</tr>
	<?php
	//<!-- อ.แม่ใจ -->
	$sum5=0;
	$sql5 = "SELECT * FROM tambon_py where ampurcode = 5607 ORDER BY tamboncodefull";
	$result5 = $conn->query($sql5);
	if ($result5->num_rows > 0){
		while($row5 = $result5->fetch_assoc()) {
			$tamboncodefull = $row5['tamboncodefull'];
			$tambonname=$row5['tambonname'];
			$sql = "SELECT * FROM point_py where id_vill_no LIKE '".$tamboncodefull."%' ";
			$result = $conn->query($sql);
			$sum5=$sum5+$result->num_rows;
 	?>
 	
 	<tr>
 			  <td align="center"></td>
			  <td>อำเภอแม่ใจ</td>
			  <td><?php echo $tambonname;?></td>
			  <td><?php echo $result->num_rows; ?></td>
			  <td align="center"></td>
	</tr>
 	<?php
	$i++;
	 }
	}
	?>
	<tr>
 			  <td align="center"></td>
			  <td align="center"></td>
			  <td><strong>รวมอำเภอแม่ใจ</strong></td>
			  <td><strong><?php echo $sum5; ?></strong></td>
			  <td align="center"></td>
	</tr>
	<?php
	//<!-- อ.ภูกามยาว -->
	$sum6=0;
	$sql6 = "SELECT * FROM tambon_py where ampurcode = 5609 ORDER BY tamboncodefull";
	$result6 = $conn->query($sql6);
	if ($result6->num_rows > 0){
		while($row6 = $result6->fetch_assoc()) {
			$tamboncodefull = $row6['tamboncodefull'];
			$tambonname=$row6['tambonname'];
			$sql = "SELECT * FROM point_py where id_vill_no LIKE '".$tamboncodefull."%' ";
			$result = $conn->query($sql);
			$sum6=$sum6+$result->num_rows;
 	?>
 	
 	<tr>
 			  <td align="center"></td>
			  <td>อำเภอภูกามยาว</td>
			  <td><?php echo $tambonname;?></td>
			  <td><?php echo $result->num_rows; ?></td>
			  <td align="center"></td>
	</tr>
 	<?php
	$i++;
	 }
	}
	?>
	<tr>
 			  <td align="center"></td>
			  <td align="center"></td>
			  <td><strong>รวมอำเภอภูกามยาว</strong></td>
			  <td><strong><?php echo $sum6; ?></strong></td>
			  <td align="center"></td>
	</tr>
	<tr>
 			  <td align="center"></td>
			  <td align="center"></td>
			  <td><strong>รวมทั้งสิ้น</strong></td>
			  <td><strong><?php echo $sum1+$sum2+$sum3+$sum4+$sum5+$sum6; ?></strong></td>
			  <td align="center"></td>
	</tr>
	</table>
	</div>
	
</div>

</body>
</html>