<!DOCTYPE html>
<html>
        
        <haed> 
          
          <link rel="shortcut icon" type="image/x-icon" href="pic/schedule.png" />
          <title> ระบบบริหารจัดการการเงินและบัญชี </title>
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
              <li role="presentation" class="active"><a href="config1.php"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> หน่วยงาน</a></li>
              <li role="presentation"><a href="config2.php"><span class="glyphicon glyphicon-modal-window" aria-hidden="true"></span> หน่วยงานภายใน</a></li>
              <li role="presentation"><a href="config3.php"><span class="glyphicon glyphicon-bitcoin" aria-hidden="true"></span> บัญชีธนาคาร</a></li>
              <li role="presentation"><a href="config4.php"><span class="glyphicon glyphicon-th" aria-hidden="true"></span> หมวดค่าใช้จ่าย</a></li>
              <li role="presentation"><a href="config5.php"><span class="glyphicon glyphicon-th-large" aria-hidden="true"></span> ประเภทการจ่าย</a></li>
              <li role="presentation"><a href="config6.php"><span class="glyphicon glyphicon-briefcase" aria-hidden="true"></span> บริษัท/ผู้รับเช็ค</a></li>
              <?php echo $navbar;?> 
            </ul>

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
    
    $sql1 = "SELECT * FROM office";
    $result1 = $conn->query($sql1);
    $row1 = $result1->fetch_assoc();
    $OfficeId = $row1['OfficeId'];
    $OfficeName = $row1['OfficeName'];
    $Department = $row1['Department'];
    $Work = $row1['Work'];
    $No = $row1['No'];
    $Tombol = $row1['Tombol'];
    $District = $row1['District'];
    $Province = $row1['Province'];
    $Postcode = $row1['Postcode'];
    $BookNo = $row1['BookNo'];
    $BookNoDept = $row1['BookNoDept'];
    $Tel = $row1['Tel'];
    $Finance = $row1['Finance'];
    $Manager = $row1['Manager'];
    $Parcel = $row1['Parcel'];
    $HParcel = $row1['HParcel'];
    $Director = $row1['Director'];
    $PID = $row1['PID'];
    
    ?>
    <form class="form-horizontal" name="gis" method="get" action="editconfig1.php">
              <div class="panel panel-danger"><p class="bg-danger">&nbsp;<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> ข้อมูลหน่วยงาน</p>
                <div class="form-group" align="right">
                  <label class="col-md-3" contorl-label >ชื่อหน่วยงาน :</label>
                    <div class="col-md-5"  align="left">
                      <input class="form-control" name="OfficeName" type="text" value="<?php echo $OfficeName; ?>">
                    </div>
                </div>
                <div class="form-group" align="right">
                  <label class="col-md-3" contorl-label >ส่วนงาน :</label>
                  <div class="col-md-3"  align="left">
                      <input class="form-control" name="Department" type="text" value="<?php echo $Department; ?>">
                  </div>
                  <label class="col-md-1" contorl-label >งาน :</label>
                  <div class="col-md-3"  align="left">
                      <input class="form-control" name="Work" type="text" value="<?php echo $Work; ?>">
                  </div>
                </div>
                
                <div class="form-group" align="right">
                  <label class="col-md-3" contorl-label >ที่อยู่ เลขที่... หมู่... :</label>
                  <div class="col-md-2"  align="left">
                    <input class="form-control" name="No" type="text" value="<?php echo $No; ?>">
                  </div>
                  <label class="col-md-1" contorl-label >ตำบล :</label>
                  <div class="col-md-2"  align="left">
                    <input class="form-control" name="Tombol" type="text" value="<?php echo $Tombol; ?>">
                  </div>
                  <label class="col-md-1" contorl-label >อำเภอ  :</label>
                    <div class="col-md-2"  align="left">
                        <input class="form-control" name="District" type="text" value="<?php echo $District; ?>">
                    </div>
                </div>
                <div class="form-group" align="right">
                    <label class="col-md-3" contorl-label >จังหวัด :</label>
                    <div class="col-md-2"  align="left">
                        <input class="form-control" name="Province" type="text" value="<?php echo $Province; ?>">
                    </div>
                    <label class="col-md-2" contorl-label >รหัสไปรษณีย์ :</label>
                    <div class="col-md-2"  align="left">
                        <input class="form-control" name="Postcode" type="text" value="<?php echo $Postcode; ?>">
                    </div>
                </div>
                <div class="form-group" align="right">
                  <label class="col-md-3" contorl-label >เลขที่หนังสือหน่วยงาน :</label>
                  <div class="col-md-2"  align="left">
                         <input class="form-control" name="BookNo" type="text" value="<?php echo $BookNo; ?>">
                  </div>
                  <label class="col-md-2" contorl-label >เลขที่หนังสือส่วนงาน :</label>
                    <div class="col-md-2"  align="left">
                        <input class="form-control" name="BookNoDept" type="text" value="<?php echo $BookNoDept; ?>">
                    </div>
                </div>
                <div class="form-group" align="right">
                  <label class="col-md-3" contorl-label >เบอร์โทรศัพท์ :</label>
                    <div class="col-md-3"  align="left">
                        <input class="form-control" name="Tel" type="text" value="<?php echo $Tel; ?>">
                    </div>
                    <label class="col-md-2" contorl-label >หมายเลขผู้เสียภาษี :</label>
                    <div class="col-md-2"  align="left">
                        <input class="form-control" name="PID" type="text" value="<?php echo $PID; ?>">
                    </div>
                </div>
                <div class="form-group" align="right">
                    <label class="col-md-3" contorl-label>เจ้าหน้าที่การเงิน :</label>
                    <div class="col-md-3"  align="left">
                      <select class="form-control" name="Finance" id="Finance">
                        <option value="<?php echo $Finance; ?>">
                          <?php 
                          $sql = "SELECT * FROM employee where Username='$Finance'";
                          $result = $conn->query($sql);
                          $row = $result->fetch_assoc();
                          $Names = $row['Names'];
                          echo $Names;
                          ?>
                        </option>
                        <?php
                            $sqlem= "SELECT * FROM employee";
                            $resultem = $conn->query($sqlem);
                              if ($resultem->num_rows > 0) { 
                                      while($rowem = $resultem->fetch_assoc()) { 
                                      $Username = $rowem['Username'];
                                      $Names = $rowem['Names'];
                                    echo"<option value='$Username'>$Names</option>";       
                                  }
                            };
                            ?>
                    </select>
                    </div>
                </div>
                <div class="form-group" align="right">
                    <label class="col-md-3" contorl-label>เจ้าหน้าที่บริหารงานทั่วไป :</label>
                    <div class="col-md-3"  align="left">
                      <select class="form-control" name="Manager" id="Manager">
                        <option value="<?php echo $Manager; ?>">
                          <?php 
                          $sql = "SELECT * FROM employee where Username='$Manager'";
                          $result = $conn->query($sql);
                          $row = $result->fetch_assoc();
                          $Names = $row['Names'];
                          echo $Names;
                          ?>
                        </option>
                        <?php
                            $sqlem= "SELECT * FROM employee";
                            $resultem = $conn->query($sqlem);
                              if ($resultem->num_rows > 0) { 
                                      while($rowem = $resultem->fetch_assoc()) { 
                                      $Username = $rowem['Username'];
                                      $Names = $rowem['Names'];
                                    echo"<option value='$Username'>$Names</option>";       
                                  }
                            };
                            ?>
                    </select>
                    </div>
                </div>
                <div class="form-group" align="right">
                    <label class="col-md-3" contorl-label>เจ้าหน้าที่พัสดุ :</label>
                    <div class="col-md-3"  align="left">
                      <select class="form-control" name="Parcel" id="Parcel">
                        <option value="<?php echo $Parcel; ?>">
                          <?php 
                          $sql = "SELECT * FROM employee where Username='$Parcel'";
                          $result = $conn->query($sql);
                          $row = $result->fetch_assoc();
                          $Names = $row['Names'];
                          echo $Names;
                          ?>
                        </option>
                        <?php
                            $sqlem= "SELECT * FROM employee";
                            $resultem = $conn->query($sqlem);
                              if ($resultem->num_rows > 0) { 
                                      while($rowem = $resultem->fetch_assoc()) { 
                                      $Username = $rowem['Username'];
                                      $Names = $rowem['Names'];
                                    echo"<option value='$Username'>$Names</option>";       
                                  }
                            };
                            ?>
                    </select>
                    </div>
                </div>
                 <div class="form-group" align="right">
                    <label class="col-md-3" contorl-label>หัวหน้าเจ้าหน้าที่พัสดุ :</label>
                    <div class="col-md-3"  align="left">
                      <select class="form-control" name="HParcel" id="HParcel">
                        <option value="<?php echo $HParcel; ?>">
                          <?php 
                          $sql = "SELECT * FROM employee where Username='$HParcel'";
                          $result = $conn->query($sql);
                          $row = $result->fetch_assoc();
                          $Names = $row['Names'];
                          echo $Names;
                          ?>
                        </option>
                        <?php
                            $sqlem= "SELECT * FROM employee";
                            $resultem = $conn->query($sqlem);
                              if ($resultem->num_rows > 0) { 
                                      while($rowem = $resultem->fetch_assoc()) { 
                                      $Username = $rowem['Username'];
                                      $Names = $rowem['Names'];
                                    echo"<option value='$Username'>$Names</option>";       
                                  }
                            };
                            ?>
                    </select>
                    </div>
                </div>
                <div class="form-group" align="right">
                    <label class="col-md-3" contorl-label>ผู้อำนวยการ :</label>
                    <div class="col-md-3"  align="left">
                      <select class="form-control" name="Director" id="Director">
                        <option value="<?php echo $Director; ?>">
                          <?php 
                          $sql = "SELECT * FROM employee where Username='$Director'";
                          $result = $conn->query($sql);
                          $row = $result->fetch_assoc();
                          $Names = $row['Names'];
                          echo $Names;
                          ?>
                        </option>
                        <?php
                            $sqlem= "SELECT * FROM employee";
                            $resultem = $conn->query($sqlem);
                              if ($resultem->num_rows > 0) { 
                                      while($rowem = $resultem->fetch_assoc()) { 
                                      $Username = $rowem['Username'];
                                      $Names = $rowem['Names'];
                                    echo"<option value='$Username'>$Names</option>";       
                                  }
                            };
                            ?>
                    </select>
                    </div>
                </div>
                <div class="form-group">
                  <label class="col-md-3" contorl-label></label>
                <div class="col-md-1"  align="left">
                  <button type="botton" class="btn btn-danger">บันทึก</button>
                  <input type="hidden" name="OfficeId" value="<?php echo $OfficeId;?>">
                </div>
                <div class="col-md-1"  align="left">
                  <button type="reset" class="btn btn-default" value="1">ยกเลิก</button>
                </div>
              </div>
              </div>
            </form>
            <div class="modal fade bs-example-modal-sm" id="myModal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog modal-sm" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-credit-card" aria-hidden="true" ></span> บันทึกข้อมูลจัดทำเช็ค</h4>
                </div>
                <form name="addtax" method="get" action="addcheque.php" target="_self">
                <div class="modal-body">
                    <div class="form-group">
                      <label for="recipient-name" class="control-label">วันที่จ่ายเช็ค:</label>
                      <input class="form-control"  data-date-format="dd/mm/yyyy"  type="text" name="DatePay" value="<?php $strDate=date("Y-m-d"); $strYear = date("Y",strtotime($strDate))+543; echo $date = date("d/m/$strYear"); ?>">
                      <label for="recipient-name" class="control-label">เลขที่เช็ค:</label>
                      <input type="text" class="form-control" id="Cheque" name="Cheque">
                      <label for="recipient-name" class="control-label">หมายเหตุ:</label>
                      <input type="text" class="form-control" id="Comment" name="Comment">
                      <input type="hidden" name="PayId" id="PayId" value="<?php echo $PayId;?>">
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
   </div>
 <hr>
 </div>
<script type="text/javascript">
<!--
document.getElementById("Qyear").value = "<?=$_GET["Qyear"];?>";
</script>

</body>        
</html>
     