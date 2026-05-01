<!DOCTYPE html>
<html>
        
        <haed> 
          
          <link rel="shortcut icon" type="image/x-icon" href="pic/schedule.png" />
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
            var values =  parseFloat(valuess * 7 / 100).toFixed(2);
            var addvalues = parseFloat(valuess) + parseFloat(values);
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
    $PayId=$_REQUEST['PayId'];
    $sql = "SELECT * from payment where PayId=$PayId";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $DateInb=$row['DateIn'];
    $exds = explode('-',$DateInb);
    $YearS=$exds[0]+543;
    $DateIn=$exds[2]."/".$exds[1]."/".$YearS;
    $TypesId=$row["TypesId"];
    $TypebId=$row["TypebId"];
    $PlanPayId=$row["PlanPayId"];
    $DeptId=$row["DeptId"];
    $BookNo=$row["BookNo"];
    $DateBookb=$row["DateBook"];
    $exd = explode('-',$DateBookb);
    $YearD=$exds[0]+543;
    $DateBook=$exd[2]."/".$exd[1]."/".$YearD;
    $NumList=$row["NumList"];
    $Detail=$row["Detail"];
    $Price=$row["Price"];
    $Vat=$row["Vat"];
    $Amount=$row["Amount"];
    $CompanyId=$row["CompanyId"];

?>  
<div class="container">
  <div class="row">
      <ul class="list-group">
          <li class="list-group-item">
            <form class="form-horizontal" name="form1" method="get" action="edit.php">
              <div class="panel panel-info"><p class="bg-info">&nbsp;<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> แก้ไขรายการเจ้าหนี้การค้า/รายการสั่งจ่าย</p>
                <div class="form-group" align="right">
                  <label class="col-md-3" contorl-label >กลุ่มงานที่ส่งหลักฐาน :</label>
                  <div class="col-md-4"  align="left">
                    <select class="form-control" name="Detp" id="Detp">
                    <option value="<?php echo $DeptId; ?>">
                    <?php 
                    $sqldept = "SELECT * FROM department where DeptId='$DeptId'";
                    $resultdept = $conn->query($sqldept);
                    $rowdept = $resultdept->fetch_assoc();
                    $DeptName = $rowdept['DeptName'];
                    echo $DeptName;
                    ?>
                    </option>
                    <?php
                        $sql = "SELECT * FROM department" ;
                        $result = $conn->query($sql);
                          if ($result->num_rows > 0) { 
                                  while($row = $result->fetch_assoc()) { 
                                  $DeptId = $row['DeptId'];
                                  $DeptName = $row['DeptName'];
                                echo"<option value='$DeptId'>$DeptName</option>";       
                              }
                        };
                        ?>
                    </select>
                  </div>
                  <label class="col-md-2" contorl-label >วันที่รับเอกสาร :</label>
                    <div class="col-md-2"  align="left">
                      <div class="input-group" >
                        <input class="form-control"  data-date-format="dd/mm/yyyy" id="datepicker-th1" type="text" name="DateIn" value="<?php echo $DateIn; ?>" required>
                        <span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                      </div>
                </div>
                </div>
                <div class="form-group" align="right">
                  <label class="col-md-3" contorl-label >หมวด :</label>
                  <div class="col-md-4"  align="left">
                    <?php 
                    $sqltypes = "SELECT * FROM types where TypesId='$TypesId'";
                    $resulttypes = $conn->query($sqltypes);
                    $rowtypes = $resulttypes->fetch_assoc();
                    $TypesName = $rowtypes['TypesName'];
                    $Typesshow= $TypesId.".".$TypesName; 
                        $sql = "SELECT * FROM types" ;
                        $query = $conn->query($sql);
                      ?>
                      <script type="text/javascript">
                        var Types = [
                        <?php
                        $TypesName = "";
                        while ($result = $query->fetch_assoc()) {
                            $TypesName .= "'" . $result['TypesId'].". ".$result['TypesName'] . "',";
                        }
                        echo rtrim($TypesName, ",");
                        ?>
                        ];
                        $(function () {
                          $j("#input1").autocomplete({
                            source: [Types]
                            });
                        });         
                      </script>  
                    <div class="input-group" >
                      <input type="text" class="form-control" placeholder="พิมพ์ค้นหาหมวด..." aria-describedby="basic-addon1" id="input1" name="Types" type="text" value="<?php echo $Typesshow; ?>" required>
                      <span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></span>
                    </div>
                  </div>
                  
                </div>
                
                <div class="form-group" align="right">
                  <label class="col-md-3" contorl-label >ตัดจ่ายจากแผน :</label>
                  <div class="col-md-4"  align="left">
                    <?php 
                    $sqlplan = "SELECT * FROM planpay where PlanPayId='$PlanPayId'";
                    $resultplan = $conn->query($sqlplan);
                    $rowplan = $resultplan->fetch_assoc();
                    $PlanPayName = $rowplan['PlanPayName'];
                    $Typebshow= $PlanPayId.".".$PlanPayName;
                      $sql2 = "SELECT * FROM planpay" ;
                      $query2 = $conn->query($sql2);
                      ?>
                      <script type="text/javascript">
                        var PlanPay = [
                        <?php
                        $PlanPayName = "";
                        while ($result2 = $query2->fetch_assoc()) {
                            $PlanPayName .= "'" . $result2['PlanPayId'].". ".$result2['PlanPayName'] . "',";
                        }
                        echo rtrim($PlanPayName, ",");
                        ?>
                        ];
                        $(function () {
                          $j("#input2").autocomplete({
                            source: [PlanPay]
                            });
                        });         
                      </script>
                      <div class="input-group" >
                      <input type="text" class="form-control" placeholder="พิมพ์ค้นหาแผน..." aria-describedby="basic-addon1" id="input2" name="PlanPay" type="text" value="<?php echo $Typebshow; ?>" required>
                      <span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></span>
                    </div>
                  </div>
                </div>
                
                 <div class="form-group" align="right">
                  <label class="col-md-3" contorl-label >เลขที่ใบส่งของ :</label>
                    <div class="col-md-4"  align="left">
                      <input class="form-control" name="Detail"  id="Detail" type="text" value="<?php echo $Detail; ?>">
                    </div>
                </div>
                <div class="form-group" align="right">
                  <label class="col-md-3" contorl-label >รายการ/บริษัท :</label>
                  <div class="col-md-4"  align="left">
                    <?php 
                    $sqlcompany = "SELECT * FROM company where CompanyId='$CompanyId'";
                    $resultcompany = $conn->query($sqlcompany);
                    $rowcompany = $resultcompany->fetch_assoc();
                    $CompanyName = $rowcompany['CompanyName'];
                    $Companyshow= $CompanyId.".".$CompanyName;
                    ?>
                   <?php
                        $sql3 = "SELECT * FROM company" ;
                        $query3 = $conn->query($sql3);
                      ?>
                      <script type="text/javascript">
                        var Company = [
                        <?php
                        $CompanyName = "";
                        while ($result3 = $query3->fetch_assoc()) {
                            $CompanyName .= "'" . $result3['CompanyId'].". ".$result3['CompanyName'] . "',";
                        }
                        echo rtrim($CompanyName, ",");
                        ?>
                        ];
                        $(function () {
                          $j("#input3").autocomplete({
                            source: [Company]
                            });
                        });         
                      </script> 
                      <div class="input-group" >
                      <input type="text" class="form-control" placeholder="พิมพ์ค้นหาบริษัท..." aria-describedby="basic-addon1" id="input3" name="Company" type="text" value="<?php echo $Companyshow; ?>" required>
                      <span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></span>
                    </div> 
                  </div>
                </div>
                <div class="form-group" align="right">
                  <label class="col-md-3" contorl-label >จำนวน :</label>
                    <div class="col-md-2"  align="left">
                        <input class="form-control" name="NumList" type="text" value="<?php echo $NumList; ?>" required>
                    </div>
                    <div class="col-md-1"  align="left">รายการ</div>
                </div>
                <div class="form-group" align="right">
                    <label class="col-md-3" contorl-label >จำนวนเงิน :</label>
                    <div class="col-md-2"  align="left">
                        <input class="form-control" name="Price" id="Price" type="text" value="<?php echo $Price; ?>" required>
                    </div>
                    <div class="col-md-1"  align="left">บาท</div>
                </div>
                <div class="form-group" align="right">
                  <label class="col-md-3" contorl-label >VAT :</label>
                    <div class="col-md-2"  align="left">
                    <label>
                      <?php 
                        if($Vat==0){
                          $text1 = "";
                          $text2 = "checked";
                        }else{ 
                          $text1 = "checked";
                          $text2 = "";
                        } 
                        ?>
                      <input type="radio" name="optionsRadios" id="optionsRadios1" value="option1" onclick="processvat()" <?php echo $text1; ?>>
                      คำนวณ &nbsp;&nbsp;
                      <input type="radio" name="optionsRadios" id="optionsRadios2" value="option2" onclick="noprocessvat()" <?php echo $text2; ?>>
                      ไม่คำนวณ
                    </label>
                  </div>
                    <div class="col-md-2"  align="left">
                        <input class="form-control" name="Vat" id="Vat" type="text" value="<?php echo $Vat; ?>">
                    </div>
                    <div class="col-md-1"  align="left">บาท</div>
                </div>
                <div class="form-group" align="right">
                  <label class="col-md-3" contorl-label >จำนวนเงินรวม VAT :</label>
                    <div class="col-md-2"  align="left">
                        <input class="form-control" name="Amount"  id="Amount" type="text" value="<?php echo $Amount; ?>">
                    </div>
                    <div class="col-md-1"  align="left">บาท</div>
                </div> 
                               
                <div class="form-group">
                  <label class="col-md-4" contorl-label></label>
                <div class="col-md-1"  align="left">
                  <button type="botton" class="btn btn-info">บันทึก</button>
                  <input type="hidden" name="PayId" value="<?php echo $PayId;?>">
                </div>
                <div class="col-md-1"  align="left">
                  <button type="reset" class="btn btn-default" value="1">ยกเลิก</button>
                </div>
              </div>
              </div>
            </form>
 </div>
 <hr>
 </div>


</body>        
</html>
     