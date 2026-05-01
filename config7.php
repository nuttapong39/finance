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
              <li role="presentation" ><a href="config1.php"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> หน่วยงาน</a></li>
              <li role="presentation"><a href="config2.php"><span class="glyphicon glyphicon-modal-window" aria-hidden="true"></span> หน่วยงานภายใน</a></li>
              <li role="presentation"><a href="config3.php"><span class="glyphicon glyphicon-bitcoin" aria-hidden="true"></span> บัญชีธนาคาร</a></li>
              <li role="presentation"><a href="config4.php"><span class="glyphicon glyphicon-th" aria-hidden="true"></span> หมวดค่าใช้จ่าย</a></li>
              <li role="presentation"><a href="config5.php"><span class="glyphicon glyphicon-th-large" aria-hidden="true"></span> ประเภทการจ่าย</a></li>
              <li role="presentation"><a href="config6.php"><span class="glyphicon glyphicon-briefcase" aria-hidden="true"></span> บริษัท/ผู้รับเช็ค</a></li>
              <li role="presentation" class="active"><a href="config7.php"><span class="glyphicon glyphicon-equalizer" aria-hidden="true"></span> แผนงาน</a></li>
              <li role="presentation"><a href="config8.php"><span class='glyphicon glyphicon-user' aria-hidden='true'></span> ผู้ใช้งาน</a></li>
            </ul>
            <form class="form-horizontal" name="plan" method="get" action="addconfig7.php">
              <div class="panel panel-danger"><p class="bg-danger">&nbsp;<span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span> เพิ่มข้อมูลรายการ</p>            
                <div class="form-group" align="right">
                  <label class="col-md-2" contorl-label >เพิ่มประเภทแผน  :</label>
                    <div class="col-md-3"  align="left">
                        <input class="form-control" name="PlanName" type="text" id="PlanName">
                    </div>
                    <div class="col-md-1"  align="left">
                    <button type="botton" class="btn btn-danger">บันทึก</button>
                    </div>
                  <label class="col-md-3" contorl-label ></label>
                    <div class="col-md-3"  align="left">
                        <a data-toggle='modal' href='#myModalplan' class='btn btn-success' role='button' target='_self'><span class='glyphicon glyphicon-plus' aria-hidden='true'> เพิ่มรายการค่าใช้จ่าย</span></a>
                    </div>
                </div>   
              </div>
            </form>
          <!-- Modal -->
          <div class="modal fade bs-example-modal-sm" id="myModalplan" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-credit-card" aria-hidden="true" ></span> บันทึกรายการค่าใช้จ่ายตามแผน</h4>
                </div>
                <form name="addplanpay" method="get" action="addplanpay.php" target="_self">
                <div class="modal-body">
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">ค่าใช้จ่าย:</label>
                      <input type="text" class="form-control" id="PlanPayName" name="PlanPayName">
                      <label for="recipient-name" class="control-label">ประเภทค่าใช้จ่าย:</label>
                      <select class="form-control" name="PlanPayTypeId" id="PlanPayTypeId">
                      <option value="">-- เลือกประเภทค่าใช้จ่าย --</option>
                      <?php
                          $sqlb = "SELECT * FROM planpaytype order by PlanPayTypeId" ;
                          $resultb = $conn->query($sqlb);
                            if ($resultb->num_rows > 0) { 
                                    while($rowb = $resultb->fetch_assoc()) { 
                                    $PlanPayTypeId = $rowb['PlanPayTypeId'];
                                    $PlanName = $rowb['PlanName'];
                                  echo"<option value='$PlanPayTypeId'>$PlanName</option>";       
                                }
                          };
                      ?>
                      </select>
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

    $sql = "SELECT * FROM planpay group by PlanPayTypeId";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
      echo "<div class='panel panel-danger'><p class='bg-danger'>&nbsp;<span class='glyphicon glyphicon-th-list' aria-hidden='true'></span> รายการแผนงาน</p>
    <div class='table-responsive'>
    <table class='table table-striped'>
      <tr class='danger'>
        <td width='100'></td>
        <td align='center'><strong>รหัส</strong></td>
        <td ><strong>ประเภทแผน-ค่าใช้จ่าย</strong></td>
        <td align='center'><strong>แก้ไข</strong></td>
        <td align='center'><strong>ลบ</strong></td>
        <td width='100'></td>
      </tr>";
      $i=1;
      while($row = $result->fetch_assoc()) {
        $PlanPayTypeId = $row['PlanPayTypeId'];
        $sql3 = "SELECT * FROM planpay where PlanPayTypeId='$PlanPayTypeId' ";
        $result3 = $conn->query($sql3);

        $sql4 = "SELECT * FROM planpaytype where PlanPayTypeId='$PlanPayTypeId'";
        $result4 = $conn->query($sql4);
        $row4  = $result4 ->fetch_assoc();
        $PlanName=$row4 ["PlanName"];

    ?>
    <tr>
          <td align="center"></td>
          <td align="center"><strong><?php echo $PlanPayTypeId;?></strong></td>
          <td><strong><?php echo $PlanName;?></strong></td>
          <td align="center"><a data-toggle="modal" href="#myModal<?php echo $PlanPayTypeId;?>" class="btn btn-default" role="button" target="_self"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a></td>
          <td align="center"><a data-toggle="modal" href="#myModals<?php echo $PlanPayTypeId;?>" class="btn btn-default" role="button" target="_self"><span class="glyphicon glyphicon-trash" aria-hidden="true" ></span></a></td>
          <td align="center"></td>
          <!-- Modal -->
          <div class="modal fade bs-example-modal-sm" id="myModal<?php echo $PlanPayTypeId;?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-info-sign" aria-hidden="true" ></span> แก้ไขข้อมูลประเภทแผน</h4>
                </div>
                <form name="editconfig" method="get" action="editconfig7.php" target="_self">
                <div class="modal-body">
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">ประเภทแผน:</label>
                      <input type="text" class="form-control" id="PlanName" name="PlanName" value="<?php echo $PlanName;?>">
                      <input type="hidden" name="PlanPayTypeId" value="<?php echo $PlanPayTypeId;?>">
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
          <div class="modal fade bs-example-modal-sm" id="myModals<?php echo $PlanPayTypeId;?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-question-sign" aria-hidden="true" ></span> ลบข้อมูลประเภทแผน</h4>
                </div>
                <form name="deleteconfig" method="get" action="deleteconfig7.php" target="_self">
                <div class="modal-body">
                    <p align="center">ท่านต้องการลบรายการ "<?php echo $PlanName;?>" หรือไม่</p>
                    <input type="hidden" name="PlanPayTypeId" value="<?php echo $PlanPayTypeId;?>">
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

        <?php
        $Sumtype=0;
        $Sumplanset=0;
         while($row3 = $result3->fetch_assoc()) {
            $PlanPayId = $row3['PlanPayId'];
            $PlanPayName=$row3['PlanPayName'];
            $PlanPayTypeId=$row3['PlanPayTypeId'];
        ?>
    <tr>
            <td width='100'></td>
            <td align='center'></td>
            <td ><?php echo '<span class="glyphicon glyphicon-triangle-right" aria-hidden="true" ></span> '.$PlanPayName;?></td>
            <td align='center'><a data-toggle="modal" href="#myModalp<?php echo $PlanPayId;?>"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a></td>
            <td align='center'><a data-toggle="modal" href="#myModalps<?php echo $PlanPayId;?>"><span class="glyphicon glyphicon-trash" aria-hidden="true" ></span></a></td>
            <td width='100'></td>
            <!-- Modal -->
          <div class="modal fade bs-example-modal-sm" id="myModalp<?php echo $PlanPayId;?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-info-sign" aria-hidden="true" ></span> แก้ไขข้อมูลค่าใช้จ่ายตามแผน</h4>
                </div>
                <form name="editconfig" method="get" action="editconfigpay7.php" target="_self">
                <div class="modal-body">
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">ค่าใช้จ่ายตามแผน:</label>
                      <input type="text" class="form-control" id="PlanPayName" name="PlanPayName" value="<?php echo $PlanPayName;?>">
                      <label for="recipient-name" class="control-label">ประเภทค่าใช้จ่าย:</label>
                      <select class="form-control" name="PlanPayTypeId" id="PlanPayTypeId">
                      <option value="<?php echo $PlanPayTypeId;?>"><?php echo $PlanName;?></option>
                      <?php
                          $sqlb = "SELECT * FROM planpaytype order by PlanPayTypeId" ;
                          $resultb = $conn->query($sqlb);
                            if ($resultb->num_rows > 0) { 
                                    while($rowb = $resultb->fetch_assoc()) { 
                                    $PlanPayTypeId = $rowb['PlanPayTypeId'];
                                    $PlanName = $rowb['PlanName'];
                                  echo"<option value='$PlanPayTypeId'>$PlanName</option>";       
                                }
                          };
                      ?>
                      </select>
                      <input type="hidden" name="PlanPayId" value="<?php echo $PlanPayId;?>">
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
          <div class="modal fade bs-example-modal-sm" id="myModalps<?php echo $PlanPayId;?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-question-sign" aria-hidden="true" ></span> ลบข้อมูลค่าใช้จ่ายตามแผน</h4>
                </div>
                <form name="deleteconfig" method="get" action="deleteconfigpay7.php" target="_self">
                <div class="modal-body">
                    <p align="center">ท่านต้องการลบรายการ "<?php echo $PlanPayName;?>" หรือไม่<br>
                    **คำเตือน หากลบรายการนี้จะมีผลต่อการประมาณการค่าใช้จ่ายตามแผนที่ตั้งไว้</p>
                    <input type="hidden" name="PlanPayId" value="<?php echo $PlanPayId;?>">
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
        
    <?php

     }

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
     