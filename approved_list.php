<!DOCTYPE html>
<html>
        
        <head> 
          
          <link rel="shortcut icon" type="image/x-icon" href="pic/fms.png" />
          <title>ระบบบริหารจัดการการเงินและบัญชี</title>
          <meta charset="UTF-8">
          
          <meta http-equiv="X-UA-Compatible" content="IE=edge">
          <meta name="viewport" content="width=device-width, initial-scale=1">
          <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.13.2/jquery-ui.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/moph-font.css">
          <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
          <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
          <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
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

	       </script> 
        </head>
<body> 

<div class="container">
  <div class="row">
      <ul class="list-group">
          <li class="list-group-item">
            <ul class="nav-tabs-modern">
              <li role="presentation"><a href="receive.php"><i class="bi bi-inbox"></i> ลงรับเอกสาร</a></li>
              <li role="presentation" class="active"><a href="finance.php"><i class="bi bi-check2-circle"></i> ขออนุมัติ</a></li>
              <li role="presentation"><a href="cheque.php"><i class="bi bi-credit-card"></i> จัดทำเช็ค</a></li>
              <li role="presentation"><a href="printcheque.php"><i class="bi bi-printer"></i> พิมพ์เช็ค</a></li>
              <li role="presentation"><a href="control.php"><i class="bi bi-journal-text"></i> ทะเบียนคุม</a></li>
              <li role="presentation"><a href="paidment.php"><i class="bi bi-book"></i> ใบสำคัญ</a></li>
              <li role="presentation"><a href="paid.php"><i class="bi bi-check-square"></i> ตัดจ่ายเช็ค</a></li>
              <li role="presentation"><a href="daily.php"><i class="bi bi-calendar3"></i> รายงานประจำวัน</a></li>
              <li role="presentation"><a href="findpay.php"><i class="bi bi-search"></i> ค้นหารายการ</a></li>
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
      $strMonthCut = Array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
      $strMonthThai=$strMonthCut[$strMonth];
      return "$strDay&nbsp;&nbsp;$strMonthThai&nbsp;&nbsp;$strYear";
    }
    ?>
    <?php
    $Keyword=$_REQUEST['Keyword'];
    $TypeKey=$_REQUEST['TypeKey'];
    switch ($TypeKey) {
        case 1:
          $TypeKeyName="บริษัท/ร้านค้า";
          break;
        case 2:
          $TypeKeyName="เลขที่ใบส่งของ";
          break;
        case 3:
          $TypeKeyName="รหัสรายการ";
          break;
        default:
          $TypeKeyName="เลือกประเภทการค้น";
        }
    ?>
    <form class="form-horizontal" name="plan" method="get" action="<?php echo $_SERVER['SCRIPT_NAME'];?>">
      <div class="panel panel-success"><p class="bg-success">&nbsp;<i class="bi bi-search"></i> ค้นหารายการอนุมัติ</p>            
        <div class="form-group" align="right">
            <label class="col-md-2" contorl-label >ค้นด้วย :</label>
            <div class="col-md-2"  align="left">
                <select class="form-control" name="TypeKey" id="TypeKey">
                  <option value="<?php echo $TypeKey; ?>"><?php echo $TypeKeyName; ?></option>
                  <option value="1">บริษัท/ร้านค้า</option>
                  <option value="2">เลขที่ใบส่งของ</option>
                  <option value="3">รหัสรายการ</option>
                </select>
            </div>
            <label class="col-md-1" contorl-label >คำค้น :</label>
            <div class="col-md-4"  align="left">
                <input class="form-control" name="Keyword" type="text" id="Keyword" value="<?php echo $TypeKeyName; ?>" placeholder="ระบุคำค้น...">
            </div>
            <div class="col-md-1"  align="left">
                <button type="botton" class="btn btn-success">ค้นหา</button>
            </div>
        </div>   
      </div>
    </form>
    <?php

    $DateNows=date("Y-m-d");
    $Datepass = date('Y-m-d', strtotime("-30 days"));
    if($Keyword==""){
      $sql = "SELECT * FROM payment where DateApprove='' or DateApprove='$DateNows' order by DateApprove ASC";
    }else{
      switch ($TypeKey) {
      case 1:
        $sqlc = "SELECT * FROM company where CompanyName LIKE '%$Keyword%'";
        $resultc = $conn->query($sqlc);
        $rowc = $resultc->fetch_assoc();
        $CompanyId=$rowc['CompanyId'];
        $sql = "SELECT * FROM payment where CompanyId ='$CompanyId'  order by DateApprove ASC";
        break;
      case 2:
        $sql = "SELECT * FROM payment where Detail LIKE '%$Keyword%'  order by DateApprove ASC";
        break;
      default:
        $sql = "SELECT * FROM payment where PayId='$Keyword'";
      }  
    }
    
      $result = $conn->query($sql);
      $rows = $result->num_rows;

      $page_rows = 10;  //จำนวนข้อมูลที่ต้องการให้แสดงใน 1 หน้า  ตย. 5 record / หน้า 

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

      if($Keyword==""){
        $nquery=mysqli_query($conn,"SELECT * from  payment where DateApprove='' or DateApprove='$DateNows' order by DateApprove ASC $limit");
      }else{
      switch ($TypeKey) {
        case 1:
          $sqlc = "SELECT * FROM company where CompanyName LIKE '%$Keyword%'";
          $resultc = $conn->query($sqlc);
          $rowc = $resultc->fetch_assoc();
          $CompanyId=$rowc['CompanyId'];
          $nquery=mysqli_query($conn,"SELECT * from  payment where CompanyId ='$CompanyId'  order by DateApprove ASC $limit");
        break;
        case 2:
          $nquery=mysqli_query($conn,"SELECT * from  payment where Detail LIKE '%$Keyword%'  order by DateApprove ASC $limit");
          break;
        default:
          $nquery=mysqli_query($conn,"SELECT * FROM payment where PayId='$Keyword' $limit");
        }  
      }

      $paginationCtrls = '
        <nav aria-label="Page navigation">
        <ul class="pagination">


      ';

      if($last != 1){

        if ($pagenum > 1) {
        $previous = $pagenum - 1;
            $paginationCtrls .= '<li><a href="'.$_SERVER['PHP_SELF'].'?pn='.$previous.'&TypeKey='.$TypeKey.'&Keyword='.$Keyword.'"  aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';

            for($i = $pagenum-4; $i < $pagenum; $i++){
              if($i > 0){
                $paginationCtrls .= '<li><a href="'.$_SERVER['PHP_SELF'].'?pn='.$i.'&TypeKey='.$TypeKey.'&Keyword='.$Keyword.'" >'.$i.'</a></li> &nbsp; ';
              }
          }
        }

        $paginationCtrls .= '<li class="active"><a href="#">'.$pagenum.' <span class="sr-only">(current)</span></a></li> ';

        for($i = $pagenum+1; $i <= $last; $i++){
          $paginationCtrls .= '<li><a href="'.$_SERVER['PHP_SELF'].'?pn='.$i.'&TypeKey='.$TypeKey.'&Keyword='.$Keyword.'" >'.$i.'</a></li> &nbsp; ';
          if($i >= $pagenum+4){
            break;
          }
        }

        if ($pagenum != $last) {
          $next = $pagenum + 1;
          $paginationCtrls .= '<li><a href="'.$_SERVER['PHP_SELF'].'?pn='.$next.'&TypeKey='.$TypeKey.'&Keyword='.$Keyword.'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
        }
      }
      $paginationCtrls .= '
        </ul>
      </nav>';

    if ($result->num_rows > 0) {
      echo "<div class='panel panel-success'><p class='bg-success'>&nbsp;<span class='glyphicon glyphicon-th-list' aria-hidden='true'></span> รายการอนุมัติ</p>
    <div class='table-responsive'>
    <table class='table table-striped'>
      <tr class='success'>
        <td align='center'></td>
        <td align='center'><strong>รหัสรายการ</strong></td>
        <td align='center'><strong>วันที่อนุมัติ</strong></td>
        <td align='center'><strong>เลขที่ใบส่งของ</strong></td>
        <td><strong>รายการ/บริษัท</strong></td>
        <td align='right'><strong>จำนวเงิน</strong></td>
        <td align='center'><strong>ขออนุมัติ</strong></td>
        <td align='center'></td>
      </tr>";
      while($row = $nquery->fetch_assoc()) { 
        $PayId=$row['PayId'];
        $DateIn = $row['DateIn'];
        $BookNo = $row['BookNo'];
        $CompanyId = $row['CompanyId'];
        $Price = $row['Price'];
        $Amount = $row['Amount'];
        $DateApprove = $row['DateApprove']; 
        $DatePay = $row['DatePay'];
        $Detail = $row['Detail'];
        $ReceiveNo = $row['ReceiveNo'];

        $sql1 = "SELECT CompanyName FROM company where CompanyId = $CompanyId";
        $result1 = $conn->query($sql1);
        $row1 = $result1->fetch_assoc();
        $CompanyName=$row1["CompanyName"];
        

        if($DateApprove == '0000-00-00'){
            $statusp = "disabled";
            $DateApproved = "<p class='text-danger'>รออนุมัติ</p>";
        }else{
            $statusp = "";
            $DateApproved = DateThai($DateApprove);
        }
        
    ?>
    
    <tr>
          <td align="center"></td>
          <td align="center"><?php echo $PayId; ?></td>
          <td align="center"><?php echo $DateApproved; ?></td>
          <td align="center"><?php echo $Detail;?></strong></td>
          <td><?php echo $CompanyName; ?></td>
          <td align="right"><?php echo number_format($Amount, 2) . "\n"; ?></td>
          <td align="center"><a data-bs-toggle="modal" href="#myModal<?php echo $PayId;?>" class="btn btn-success" role="button" target="_self"><i class="bi bi-check2-circle"></i></a></td>
          <td align="center"></td>
          <!-- Modal -->
          <div class="modal fade bs-example-modal-sm" id="myModal<?php echo $PayId;?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog modal-sm" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-info-sign" aria-hidden="true" ></span> รายการขออนุมัติ</h4>
                </div>
                <form name="addtax" method="get" action="approved.php" target="_self">
                <div class="modal-body">
                    <div class="form-group">
                      <p align="center">ขออนุมัติ เลขที่รับเอกสาร <?php echo $PayId;?></p>
                      <label for="recipient-name" class="control-label">เลือกธนาคารสำหรับจ่ายเช็ค/เงินสด</label>
                      <select class="form-control" name="Bank" id="Bank">
                      <option>-- เลือกธนาคาร/เงินสด --</option>
                      <?php
                          $sqlb = "SELECT * FROM bank" ;
                          $resultb = $conn->query($sqlb);
                            if ($resultb->num_rows > 0) { 
                                    while($rowb = $resultb->fetch_assoc()) { 
                                    $BankId = $rowb['BankId'];
                                    $BankName = $rowb['BankName'];
                                  echo"<option value='$BankId'>$BankName</option>";       
                                }
                          };
                      ?>
                      </select>
                      <label  contorl-label >จ่ายจากเงิน :</label>
                      <select class="form-control" name="Typeb" id="Typeb" required>
                      <option>-- เลือกหมวดงบ --</option>
                      <?php
                        $sql = "SELECT * FROM typeb" ;
                        $result = $conn->query($sql);
                          if ($result->num_rows > 0) { 
                                  while($row = $result->fetch_assoc()) { 
                                  $TypebId = $row['TypebId'];
                                  $TypebName = $row['TypebName'];
                                echo"<option value='$TypebId'>$TypebId.$TypebName</option>";       
                              }
                        };
                        ?>
                      </select>
                      <label for="recipient-name" class="control-label">หักภาษี (%):</label>
                      <input type="text" class="form-control" id="Percent" name="Percent">
                      <input type="hidden" name="PayId" id="PayId" value="<?php echo $PayId;?>">
                      <input type="hidden" name="Price" id="Price" value="<?php echo $Price;?>">
                      <input type="hidden" name="Amount" id="Amount" value="<?php echo $Amount;?>">
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                  <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
              </form>
              </div>
            </div>
          </div>
        </tr>

    <?php
    $i++;
     }
    }else{
        echo "<div class='alert alert-danger' role='alert' align='center'> ไม่พบข้อมูล </div>";
     };
    ?>
    </table>
    <div id="pagination_controls" align="center"><?php echo $paginationCtrls; ?></div>
   </div>
 </div>
 </div>
 </div>
  <div class="container">
  <div class="row">
  <div class='panel panel-success'><p class='bg-success'>&nbsp;<span class='glyphicon glyphicon-th-list' aria-hidden='true'></span> รายการพิมพ์หนังสือขออนุมัติ (แสดงข้อมูลย้อนหลัง 30 วัน)</p>
        
          <?php
          $sql2 = "SELECT *,SUM(Amount) as Amounts FROM payment where DateApprove!='' and DateApprove between '$Datepass' and '$DateNows' group by ReceiveNo order by DateApprove DESC";
          
          $result2 = $conn->query($sql2);
            if ($result2->num_rows > 0) {
              echo "<div class='table-responsive'>
              <table class='table table-striped'>
              <tr class='success'>
                <td align='center'></td>
                <td align='center'><strong>เลขที่รับเอกสาร</strong></td>
                <td align='center'><strong>วันที่อนุมัติ</strong></td>
                <td><strong>รายการ/บริษัท</strong></td>
                <td align='right'><strong>จำนวเงิน</strong></td>
                <td align='center'><strong>พิมพ์</strong></td>
                <td align='center'></td>
              </tr>";
              while($row2 = $result2->fetch_assoc()) { 
              $PayId=$row2['PayId'];
              $DateIn = $row2['DateIn'];
              $BookNo = $row2['BookNo'];
              $CompanyId = $row2['CompanyId'];
              $Price = $row2['Price'];
              $Amounts = $row2['Amounts'];
              $DateApprove = $row2['DateApprove']; 
              $DatePay = $row2['DatePay'];
              $Detail = $row2['Detail'];
              $ReceiveNo = $row2['ReceiveNo'];

              $sql1 = "SELECT CompanyName FROM company where CompanyId = $CompanyId";
              $result1 = $conn->query($sql1);
              $row1 = $result1->fetch_assoc();
              $CompanyName=$row1["CompanyName"];
              
              
          ?>
          
          <tr>
                <td align="center"></td>
                <td align="center"><?php echo $ReceiveNo; ?></td>
                <td align="center"><?php echo DateThai($DateApprove); ?></td>
                <td><?php echo $CompanyName; ?></td>
                <td align="right"><?php echo number_format($Amounts, 2) . "\n"; ?></td>
                <td align="center"><a href="printapproved.php?ReceiveNo=<?php echo $ReceiveNo;?>" class="btn btn-primary" role="button" target="_blank"><span class="glyphicon glyphicon-print" aria-hidden="true" ></span></a></td>
                <td align="center"></td>
            </tr>
            <?php
          $i++;
           }
          }else{
              echo "<div class='alert alert-danger' role='alert' align='center'> ไม่พบข้อมูล </div>";
           };
          ?>
        </table> 
        </div>
        </div>
    </div>
 </div>
</div>
<script type="text/javascript">
      <!--
      document.getElementById("Keyword").value = "<?=$_GET["Keyword"];?>";
      document.getElementById("TypeKey").value = "<?=$_GET["TypeKey"];?>";
</script> 
</body>        
</html>
     