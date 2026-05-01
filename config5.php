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
<?php
  if($_SESSION["TypeUser"]=="Admin"){
     $navbar="<li role='presentation'><a href='config7.php'><span class='glyphicon glyphicon-equalizer' aria-hidden='true'></span> แผนงาน</a></li>
     <li role='presentation'><a href='config8.php'><span class='glyphicon glyphicon-user' aria-hidden='true'></span> ผู้ใช้งาน</a></li>";
  };
?>
<div class="container">
  <div class="row">
      <ul class="list-group">
          <li class="list-group-item">
            <ul class="nav nav-tabs">
              <li role="presentation" ><a href="config1.php"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> หน่วยงาน</a></li>
              <li role="presentation"><a href="config2.php"><span class="glyphicon glyphicon-modal-window" aria-hidden="true"></span> หน่วยงานภายใน</a></li>
              <li role="presentation"><a href="config3.php"><span class="glyphicon glyphicon-bitcoin" aria-hidden="true"></span> บัญชีธนาคาร</a></li>
              <li role="presentation"><a href="config4.php"><span class="glyphicon glyphicon-th" aria-hidden="true"></span> หมวดค่าใช้จ่าย</a></li>
              <li role="presentation" class="active"><a href="config5.php"><span class="glyphicon glyphicon-th-large" aria-hidden="true"></span> ประเภทการจ่าย</a></li>
              <li role="presentation"><a href="config6.php"><span class="glyphicon glyphicon-briefcase" aria-hidden="true"></span> บริษัท/ผู้รับเช็ค</a></li>
              
              <?php echo $navbar;?>
            </ul>
            <form class="form-horizontal" name="plan" method="get" action="addconfig5.php">
              <div class="panel panel-danger"><p class="bg-danger">&nbsp;<span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span> เพิ่มข้อมูลรายการ</p>            
                <div class="form-group" align="right">
                  <label class="col-md-2" contorl-label >เพิ่มข้อมูล/รายการ  :</label>
                    <div class="col-md-3"  align="left">
                        <input class="form-control" name="TypebName" type="text" id="TypebName">
                    </div>
                    <div class="col-md-1"  align="left">
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

    $sql = "SELECT * FROM typeb order by TypebId";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
      echo "<div class='panel panel-danger'><p class='bg-danger'>&nbsp;<span class='glyphicon glyphicon-th-list' aria-hidden='true'></span> รายการประเภทการจ่าย</p>
    <div class='table-responsive'>
    <table class='table table-striped'>
      <tr class='danger'>
        <td width='100'></td>
        <td align='center'><strong>ลำดับ</strong></td>
        <td ><strong>ประเภทการจ่าย</strong></td>
        <td align='center'><strong>แก้ไข</strong></td>
        <td align='center'><strong>ลบ</strong></td>
        <td width='100'></td>
      </tr>";
      $i=1;
      while($row = $result->fetch_assoc()) {
        $TypebId = $row['TypebId'];
        $TypebName = $row['TypebName'];

    ?>
    
    <tr>
          <td align="center"></td>
          <td align="center"><?php echo $i;?></strong></td>
          <td><?php echo $TypebName;?></strong></td>
          <td align="center"><a data-toggle="modal" href="#myModal<?php echo $TypebId;?>" class="btn btn-default" role="button" target="_self"><span class="glyphicon glyphicon-pencil" aria-hidden="true" ></span></a></td>
          <td align="center"><a data-toggle="modal" href="#myModals<?php echo $TypebId;?>" class="btn btn-default" role="button" target="_self"><span class="glyphicon glyphicon-trash" aria-hidden="true" ></span></a></td>
          <td align="center"></td>
          <!-- Modal -->
          <div class="modal fade bs-example-modal-sm" id="myModal<?php echo $TypebId;?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog modal-sm" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-info-sign" aria-hidden="true" ></span> แก้ไขข้อมูลประเภทการจ่าย</h4>
                </div>
                <form name="editconfig" method="get" action="editconfig5.php" target="_self">
                <div class="modal-body">
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">ประเภทการจ่าย:</label>
                      <input type="text" class="form-control" id="TypebName" name="TypebName" value="<?php echo $TypebName;?>">
                      <input type="hidden" name="TypebId" value="<?php echo $TypebId;?>">
                    </div>
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
          <div class="modal fade bs-example-modal-sm" id="myModals<?php echo $TypebId;?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog modal-sm" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-question-sign" aria-hidden="true" ></span> ลบข้อมูลประเภทการจ่าย</h4>
                </div>
                <form name="deleteconfig" method="get" action="deleteconfig5.php" target="_self">
                <div class="modal-body">
                    <p align="center">ท่านต้องการลบรายการ "<?php echo $TypebName;?>" หรือไม่</p>
                    <input type="hidden" name="TypebId" value="<?php echo $TypebId;?>">
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
     