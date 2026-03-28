<!DOCTYPE html>
<html>
        
        <haed> 
          
          <link rel="shortcut icon" type="image/x-icon" href="pic/fms.png" />
          <title>ระบบบริหารจัดการการเงินและบัญชี</title>
          <meta charset="UTF-8">
          <meta http-equiv=Content-Type content="text/html; charset=tis-620">
          <meta http-equiv="X-UA-Compatible" content="IE=edge">
          <meta name="viewport" content="width=device-width, initial-scale=1">
          <link rel="stylesheet" type="text/css" href="./css/bootstrap.min.css">
          <script type="text/javascript" src="./js/jquery.js"></script>
          <script type="text/javascript" src="./js/bootstrap.min.js"></script>
          <script type="text/javascript" src="./js/bootbox.min.js"></script>
          <div>
            <?php include 'header.php';?>
          </div>
          <script type="text/javascript">
          $(function () {
              var d = new Date();
              var toDay = d.getDate() + '/'
              + (d.getMonth() + 1) + '/'
              + (d.getFullYear() + 543);

              // Datepicker
              $("#datepicker-th1").datepicker({ dateFormat: 'dd/mm/yy', isBuddhist: true, defaultDate: toDay, dayNames: ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'],
                    dayNamesMin: ['อา.','จ.','อ.','พ.','พฤ.','ศ.','ส.'],
                    monthNames: ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'],
                    monthNamesShort: ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.']});
              $("#datepicker-th2").datepicker({ dateFormat: 'dd/mm/yy', isBuddhist: true, defaultDate: toDay, dayNames: ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'],
                    dayNamesMin: ['อา.','จ.','อ.','พ.','พฤ.','ศ.','ส.'],
                    monthNames: ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'],
                    monthNamesShort: ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.']});
              $("#datepicker-th-2").datepicker({ changeMonth: true, changeYear: true,dateFormat: 'dd/mm/yy', isBuddhist: true, defaultDate: toDay,dayNames: ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'],
                    dayNamesMin: ['อา.','จ.','อ.','พ.','พฤ.','ศ.','ส.'],
                    monthNames: ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'],
                    monthNamesShort: ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.']});
              $("#datepicker-en").datepicker({ dateFormat: 'dd/mm/yy'});
              $("#inline").datepicker({ dateFormat: 'dd/mm/yy', inline: true });
            });
          function processvat() {
            var valuess = parseFloat(document.getElementById("Price").value);
            var values = valuess * 7 / 100;
            var addvalues =  valuess + values;
            parseFloat(document.getElementById("Vat").value = values).toFixed(2);
            parseFloat(document.getElementById("Amount").value = addvalues).toFixed(2);
          }
          function noprocessvat() {
            var valuess = parseFloat(document.getElementById("Price").value);
            var values = 0;
            var addvalues =  valuess + values;
            parseFloat(document.getElementById("Vat").value = values).toFixed(2);
            parseFloat(document.getElementById("Amount").value = addvalues).toFixed(2);
          }

	       </script>   
        </haed>
<body>

<div class="container">
  <div class="row">
      <ul class="list-group">
          <li class="list-group-item">
            <ul class="nav nav-tabs">
              <li role="presentation" class="active"><a href="config1.php"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> หน่วยงาน</a></li>
              <li role="presentation"><a href="config2.php"><span class="glyphicon glyphicon-modal-window" aria-hidden="true"></span> หน่วยงานภายใน</a></li>
              <li role="presentation"><a href="config3.php"><span class="glyphicon glyphicon-bitcoin" aria-hidden="true"></span> บัญชีธนาคาร</a></li>
              <li role="presentation"><a href="config4.php"><span class="glyphicon glyphicon-th" aria-hidden="true"></span> หมวดค่าใช้จ่าย</a></li>
              <li role="presentation"><a href="config5.php"><span class="glyphicon glyphicon-th-large" aria-hidden="true"></span> ประเภทการจ่าย</a></li>
              <li role="presentation"><a href="config6.php"><span class="glyphicon glyphicon-briefcase" aria-hidden="true"></span> บริษัท/ผู้รับเช็ค</a></li>
              <li role="presentation"><a href="config7.php"><span class="glyphicon glyphicon-equalizer" aria-hidden="true"></span> แผนงาน</a></li>
              <li role="presentation" class="active"><a href="config8.php"><span class='glyphicon glyphicon-user' aria-hidden='true'></span> ผู้ใช้งาน</a></li>
            </ul>
            <form class="form-horizontal" name="config6" method="get" action="addconfig8.php">
              <div class="panel panel-danger"><p class="bg-danger">&nbsp;<span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span> เพิ่มข้อมูลรายการ</p>            
                <div class="form-group" align="right">
                    <label class="col-md-2" contorl-label >ชื่อ-สกุล</label>
                    <div class="col-md-4"  align="left">
                        <input class="form-control" name="Names" type="text" id="Names" required>
                    </div>
                    <label class="col-md-1" contorl-label >ตำแหน่ง</label>
                    <div class="col-md-4"  align="left">
                        <input class="form-control" name="Position" type="text" id="Position" required>
                    </div>
                </div>
                <div class="form-group" align="right">
                    <label class="col-md-2" contorl-label >Username</label>
                    <div class="col-md-2"  align="left">
                        <input class="form-control" name="Username" type="text" id="Username" required>
                    </div>
                    <label class="col-md-1" contorl-label >Password</label>
                    <div class="col-md-2"  align="left">
                        <input class="form-control" name="Password" type="text" id="Password" required>
                    </div>
                    <label class="col-md-1" contorl-label >สิทธิ์การใช้งาน</label>
                    <div class="col-md-2"  align="left">
                    <label>
                      <input type="radio" name="TypeUser" id="TypeUser1" value="User" checked>
                      User &nbsp;&nbsp;
                      <input type="radio" name="TypeUser" id="TypeUser2" value="Admin" >
                      Admin
                    </label>
                  </div>    
                    <div class="col-md-1"  align="center">
                        <button type="botton" class="btn btn-danger">บันทึก</button>
                    </div>
                </div>
              </div>

            </form>
            

    <?php
    include "connect_db.php";
    function DateThai($strDate) //function in php
    {
      $strYear = date("Y",strtotime($strDate))+543;
      $strMonth= date("n",strtotime($strDate));
      $strDay= date("j",strtotime($strDate));
      $strHour= date("H",strtotime($strDate));
      $strMinute= date("i",strtotime($strDate));
      $strSeconds= date("s",strtotime($strDate));
      $strMonthCut = Array("","มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
      $strMonthThai=$strMonthCut[$strMonth];
      return "$strDay&nbsp;&nbsp;$strMonthThai&nbsp;&nbsp;$strYear";
    }

    $sql = "SELECT * FROM employee order by Names";
    $result = $conn->query($sql);
    $rows = $result->num_rows;

      $page_rows = 50;  //จำนวนข้อมูลที่ต้องการให้แสดงใน 1 หน้า  ตย. 5 record / หน้า 

      $last = ceil($rows/$page_rows);

      if($last < 1){
        $last = 1;
      }

      $pagenum = 1;

      if(isset($_GET['pn'])){
        $pagenum = preg_replace('#[^0-9]#', '', $_GET['pn']);
      }

      if ($pagenum < 1) {
        $pagenum = 1;
      }
      else if ($pagenum > $last) {
        $pagenum = $last;
      }

      $limit = 'LIMIT ' .($pagenum - 1) * $page_rows .',' .$page_rows;

      $nquery=mysqli_query($conn,"SELECT * from  employee order by Names $limit");

      $paginationCtrls = '
        <nav aria-label="Page navigation">
        <ul class="pagination">


      ';

      if($last != 1){

        if ($pagenum > 1) {
        $previous = $pagenum - 1;
            $paginationCtrls .= '<li><a href="'.$_SERVER['PHP_SELF'].'?pn='.$previous.'"  aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';

            for($i = $pagenum-4; $i < $pagenum; $i++){
              if($i > 0){
                $paginationCtrls .= '<li><a href="'.$_SERVER['PHP_SELF'].'?pn='.$i.'" >'.$i.'</a></li> &nbsp; ';
              }
          }
        }

        $paginationCtrls .= '<li class="active"><a href="#">'.$pagenum.' <span class="sr-only">(current)</span></a></li> ';

        for($i = $pagenum+1; $i <= $last; $i++){
          $paginationCtrls .= '<li><a href="'.$_SERVER['PHP_SELF'].'?pn='.$i.'" >'.$i.'</a></li> &nbsp; ';
          if($i >= $pagenum+4){
            break;
          }
        }

        if ($pagenum != $last) {
          $next = $pagenum + 1;
          $paginationCtrls .= '<li><a href="'.$_SERVER['PHP_SELF'].'?pn='.$next.'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
        }
      }
      $paginationCtrls .= '
        </ul>
      </nav>';
    if ($result->num_rows > 0) {
      echo "<div class='panel panel-danger'><p class='bg-danger'>&nbsp;<span class='glyphicon glyphicon-th-list' aria-hidden='true'></span> ผู้ใช้งานในระบบ</p>
    <div class='table-responsive'>
    <table class='table table-striped'>
      <tr class='danger'>
        <td></td>
        <td align='center'><strong>Username</strong></td>
        <td ><strong>ชื่อ - สกุล</strong></td>
        <td ><strong>ตำแหน่ง</strong></td>
        <td ><strong>สิทธิ์</strong></td>
        <td ><strong>สถานะ</strong></td>
        <td align='center'><strong>แก้ไข</strong></td>
        <td align='center'><strong>รีเซ็ต</strong></td>
        <td align='center'><strong>ลบ</strong></td>
        <td></td>
      </tr>";
      $i=1;
      while($row = $nquery->fetch_assoc()) {
        $Username = $row['Username'];
        $Names = $row['Names'];
        $Position = $row['Position'];
        $TypeUser = $row['TypeUser'];
        $Status = $row['Status'];
        if($Status==1){
          $Statusname="ใช้งาน";
        }else{
          $Statusname="ระงับใช้งาน";
        }
    ?>
    
    <tr>
          <td align="center"></td>
          <td align="center"><?php echo $Username;?></strong></td>
          <td><?php echo $Names;?></strong></td>
          <td><?php echo $Position;?></strong></td>
          <td><?php echo $TypeUser;?></strong></td>
          <td><?php echo $Statusname;?></strong></td>
          <td align="center"><a data-toggle="modal" href="#myModal<?php echo $Username;?>" class="btn btn-default" role="button" target="_self"><span class="glyphicon glyphicon-pencil" aria-hidden="true" ></span></a></td>
          <td align="center"><a data-toggle="modal" href="#myModalr<?php echo $Username;?>" class="btn btn-default" role="button" target="_self"><span class="glyphicon glyphicon-refresh" aria-hidden="true" ></span></a></td>
          <td align="center"><a data-toggle="modal" href="#myModals<?php echo $Username;?>" class="btn btn-default" role="button" target="_self"><span class="glyphicon glyphicon-trash" aria-hidden="true" ></span></a></td>
          <td align="center"></td>
          <!-- Modal -->
          <div class="modal fade bs-example-modal-sm" id="myModal<?php echo $Username;?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-info-sign" aria-hidden="true" ></span> แก้ไขข้อมูลผู้ใช้งาน <?php echo $Username;?></h4>
                </div>
                <form name="editconfig" method="get" action="editconfig8.php" target="_self">
                <div class="modal-body">
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">ชื่อ - สกุล :</label>
                      <input type="text" class="form-control" id="Names" name="Names" value="<?php echo $Names;?>">
                      <input type="hidden" name="CompanyId" value="<?php echo $CompanyId;?>">
                    </div>
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">ตำแหน่ง :</label>
                      <input type="text" class="form-control" id="Position" name="Position" value="<?php echo $Position;?>">
                    </div>
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">สิทธิ์การใช้งาน :</label>&nbsp;&nbsp;
                      <input type="radio" name="TypeUser" id="TypeUser1" value="User" <?php if($TypeUser=="User"){echo "checked";}?>>
                      User &nbsp;&nbsp;
                      <input type="radio" name="TypeUser" id="TypeUser2" value="Admin" <?php if($TypeUser=="Admin"){echo "checked";}?>>
                      Admin
                    </div>
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">สถานะ :</label>&nbsp;&nbsp;
                      <input type="radio" name="Status" id="Status1" value="1" <?php if($Status=="1"){echo "checked";}?>>
                      ใช้งาน &nbsp;&nbsp;
                      <input type="radio" name="Status" id="Status0" value="0" <?php if($Status=="0"){echo "checked";}?>>
                      ระงับการใช้งาน
                    </div>
                    <input type="hidden" name="Username" value="<?php echo $Username;?>">
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">ยกเลิก</button>
                  <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
                </form>
              </div>
            </div>
          </div>
          <!-- Modals -->
          <div class="modal fade bs-example-modal-sm" id="myModals<?php echo $Username;?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-question-sign" aria-hidden="true" ></span> ลบข้อมูลผู้ใช้งาน</h4>
                </div>
                <form name="deleteconfig" method="get" action="deleteconfig8.php" target="_self">
                <div class="modal-body">
                    <p align="center">ท่านต้องการลบรายการ "<?php echo $Username;?>" หรือไม่</p>
                    <input type="hidden" name="Username" value="<?php echo $Username;?>">
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">ยกเลิก</button>
                  <button type="submit" class="btn btn-primary">ตกลง</button>
                </div>
                </form>
              </div>
            </div>
          </div>
          <!-- Modals -->
          <div class="modal fade bs-example-modal-sm" id="myModalr<?php echo $Username;?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-question-sign" aria-hidden="true" ></span> รีเซตรหัสผ่านผู้ใช้งาน</h4>
                </div>
                <form name="deleteconfig" method="get" action="resetconfig8.php" target="_self">
                <div class="modal-body">
                    <p align="center">ท่านต้องการรีเซตรหัสผ่านของ "<?php echo $Username;?>" หรือไม่<br>*หมายเหตุ รหัสผ่านที่ใช้หลังจากรีเซต คือ "pass1234"</p>
                    <input type="hidden" name="Username" value="<?php echo $Username;?>">
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">ยกเลิก</button>
                  <button type="submit" class="btn btn-primary">ตกลง</button>
                </div>
                </form>
              </div>
            </div>
          </div>  
        </tr>
    <?php //echo "<h3 align='center'> บ้านเลขที่ ".$house_no." = ".round($lat,6).",".round($longs,6)."</h3>"; ?>
    

    <?php
    $i++;
      }
    };
    ?>
    </table>
    <div id="pagination_controls" align="center"><?php echo $paginationCtrls; ?></div>
   </div>
 </div>
 <hr>
 </div>
<script type="text/javascript">
<!--
document.getElementById("Qyear").value = "<?=$_GET["Qyear"];?>";
</script>

</body>        
</html>
     