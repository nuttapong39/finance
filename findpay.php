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
              <li role="presentation" ><a href="receive.php"><i class="bi bi-inbox"></i> ลงรับเอกสาร</a></li>
              <li role="presentation"><a href="finance.php"><i class="bi bi-check2-circle"></i> ขออนุมัติ</a></li>
              <li role="presentation"><a href="cheque.php"><i class="bi bi-credit-card"></i> จัดทำเช็ค</a></li>
              <li role="presentation"><a href="printcheque.php"><i class="bi bi-printer"></i> พิมพ์เช็ค</a></li>
              <li role="presentation"><a href="control.php"><i class="bi bi-journal-text"></i> ทะเบียนคุม</a></li>
              <li role="presentation"><a href="paidment.php"><i class="bi bi-book"></i> ใบสำคัญ</a></li>
              <li role="presentation"><a href="paid.php"><i class="bi bi-check-square"></i> ตัดจ่ายเช็ค</a></li>
              <li role="presentation"><a href="daily.php"><i class="bi bi-calendar3"></i> รายงานประจำวัน</a></li>
              <li role="presentation" class="active"><a href="findpay.php"><i class="bi bi-search"></i> ค้นหารายการ</a></li>
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
    function DateThaiShort($strDate) //function in php
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
          $TypeKeyName="ชื่อบริษัท/ผู้รับเช็ค";
          break;
        case 2:
          $TypeKeyName="จำนวนเงิน";
          break;
        case 3:
          $TypeKeyName="เลขที่เช็ค";
          break;
        case 4:
          $TypeKeyName="รหัสรายการ";
          break;
        default:
          $TypeKeyName="เลือกประเภทการค้น";
        }
    ?>
    <form class="form-horizontal" name="plan" method="get" action="<?php echo $_SERVER['SCRIPT_NAME'];?>">
      <div class="panel panel-success"><p class="bg-success">&nbsp;<i class="bi bi-search"></i> ค้นหารายการจ่าย</p>            
        <div class="form-group" align="right">
          <label class="col-md-2" contorl-label >ค้นด้วย :</label>
          <div class="col-md-2"  align="left">
                <select class="form-control" name="TypeKey" id="TypeKey">
                  <option value="<?php echo $TypeKey; ?>"><?php echo $TypeKeyName; ?></option>
                  <option value="1">ชื่อบริษัท/ผู้รับเช็ค</option>
                  <option value="2">จำนวนเงิน</option>
                  <option value="3">เลขที่เช็ค</option>
                  <option value="4">รหัสรายการ</option>
                </select>
            </div>
          <label class="col-md-1" contorl-label >คำค้น :</label>
            <div class="col-md-4"  align="left">
                <input class="form-control" name="Keyword" type="text" id="Keyword" alue="<?php echo $TypeKeyName; ?>" placeholder="ระบุคำค้น...">
            </div>
            <div class="col-md-1"  align="left">
                <button type="botton" class="btn btn-success">ค้นหา</button>
            </div>
        </div>   
      </div>
    </form>
    <?php
    $Keyword=$_REQUEST['Keyword'];
    $TypeKey=$_REQUEST['TypeKey'];
    if($Keyword==""){
      $sql = "SELECT * FROM payment ORDER BY DateIn DESC";
    }else{
      switch ($TypeKey) {
      case 1:
        $sqlc = "SELECT * FROM company where CompanyName LIKE '%$Keyword%'";
        $resultc = $conn->query($sqlc);
        $rowc = $resultc->fetch_assoc();
        $CompanyId=$rowc['CompanyId'];
        $sql = "SELECT * FROM payment where CompanyId ='$CompanyId' ORDER BY DateIn DESC";
        break;
      case 2:
        $sql = "SELECT * FROM payment where Amount ='$Keyword' ORDER BY DateIn DESC";
        break;
      case 3:
        $sql = "SELECT * FROM payment where Cheque ='$Keyword' ORDER BY DateIn DESC";
        break;
      default:
        $sql = "SELECT * FROM payment where PayId='$Keyword' ORDER BY DateIn DESC";
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
        $nquery=mysqli_query($conn,"SELECT * from payment ORDER BY DateIn DESC $limit");
      }else{
        switch ($TypeKey) {
        case 1:
          $sqlc = "SELECT * FROM company where CompanyName LIKE '%$Keyword%'";
          $resultc = $conn->query($sqlc);
          $rowc = $resultc->fetch_assoc();
          $CompanyId=$rowc['CompanyId'];
          $nquery=mysqli_query($conn,"SELECT * from payment where CompanyId ='$CompanyId' ORDER BY DateIn DESC $limit");
          break;
        case 2:
          $nquery=mysqli_query($conn,"SELECT * from payment where Amount ='$Keyword' ORDER BY DateIn DESC $limit");
          break;
        case 3:
          $nquery=mysqli_query($conn,"SELECT * from payment where Cheque ='$Keyword' ORDER BY DateIn DESC $limit");
          break;
        default:
          $nquery=mysqli_query($conn,"SELECT * from payment where PayId='$Keyword' ORDER BY DateIn DESC $limit");
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
      echo "<div class='panel panel-success'><p class='bg-success'>&nbsp;<span class='glyphicon glyphicon-th-list' aria-hidden='true'></span> รายการจ่าย</p>
    <div class='table-responsive'>
    <table class='table table-striped'>
      <tr class='success'>
        <td align='center'></td>
        <td align='center'><strong>รหัสรายการ</strong></td>
        <td align='center'><strong>วันที่รับเอกสาร</strong></td>
        <td align='center'><strong>ใบรับของ</strong></td>
        <td><strong>บริษัท/ผู้รับเช็ค</strong></td>
        <td align='right'><strong>จำนวนเงิน</strong></td>
        <td align='center'><strong>เลขที่เช็ค</strong></td>
        <td align='center'><strong>วันที่จัดทำเช็ค</strong></td>
        <td align='center'><strong>บันทึกใบเสร็จรับเงิน</strong></td>
        <td align='center'><strong>รายละเอียด</strong></td>
        <td align='center'></td>
      </tr>";
      while($row = $nquery->fetch_assoc()) { 
        $PayId=$row['PayId'];
        $DateIn=$row['DateIn'];
        $CompanyId=$row['CompanyId'];
        $Detail=$row['Detail'];
        $Amount=$row['Amount'];
        $DatePays=$row['DatePay'];
        $Cheque=$row['Cheque'];
        $DateApproved=$row['DateApprove'];
        $BookNo=$row['BookNo'];
        $DateBooks=$row['DateBook'];
        $DatePaids=$row['DatePaid'];
        $BillNo=$row['BillNo'];
        $BillNos=$row['BillNo'];
        $BillDates=$row['BillDate'];

        if($DatePaids == '0000-00-00'){
            $textdisale="Disabled";
            $BillDate='-';
            $btncolor='btn btn-warning';
        }else{
            $btncolor='btn btn-warning';
        }

        if($BillNo == '' and $DatePaids != '0000-00-00'){
            $BillNo = "ยังไม่ได้รับ";
            $textdisale='';
            $BillDate="-";
        }else{
            if($BillNo==''){
              $BillDate='-';
            }else{
              $BillDate=DateThaiShort($BillDates);
              $btncolor='btn btn-success';
            }
            
        }
        

        $sql1 = "SELECT * FROM company where CompanyId='$CompanyId'";
        $result1 = $conn->query($sql1);
        $row1 = $result1->fetch_assoc();
        $CompanyName=$row1['CompanyName'];

        if($DatePays == '0000-00-00'){
            $DatePay = "ยังไม่จัดทำเช็ค";
        }else{
            $DatePay = DateThaiShort($DatePays);
            if($Cheque==""){
            $Cheque="ไม่จัดทำเช็ค";
          }
        }
        if($DateApproved == '0000-00-00'){
            $DateApprove = "ยังไม่อนุมัติ";
        }else{
            $DateApprove = DateThaiShort($DateApproved);
        }
        if($DateBooks == '0000-00-00'){
            $DateBook = "ยังไม่บันทึกใบสำคัญ";
        }else{
            $DateBook = DateThaiShort($DateBooks);
        }
        if($DatePaids == '0000-00-00'){
            $DatePaid = "ยังไม่ตัดจ่าย";
        }else{
            $DatePaid = DateThaiShort($DatePaids);
        }
        
    ?>
    
    <tr>
          <td align="center"></td>
          <td align="center"><?php echo $PayId;?></td>
          <td align="center"><?php echo DateThaiShort($DateIn); ?></td>
          <td align="center"><?php echo $Detail;?></td>
          <td><?php echo $CompanyName;?></td>
          <td align="right"><?php echo number_format($Amount, 2) . "\n"; ?> </td>
          <td align="center"><?php echo $Cheque;?></td>
          <td align="center"><?php echo $DatePay; ?></td>
          <td align="center"><a data-bs-toggle="modal" href="#myModalp2<?php echo $PayId;?>" class="<?php echo $btncolor;?>" role="button" target="_self" <?php echo $textdisale;?>><span class="glyphicon glyphicon-save-file" aria-hidden="true"></span></a></td>
          <td align="center"><a data-bs-toggle="modal" href="#myModalp<?php echo $PayId;?>" class="btn btn-info" role="button" target="_self"><span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span></a></td>
          <td align="center"></td>
          <!-- Modal -->
          <div class="modal fade bs-example-modal-sm" id="myModalp<?php echo $PayId;?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog modal-sm" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-list-alt" aria-hidden="true" ></span> รายละเอียดรายการจ่าย</h4>
                </div>
                <form name="addtax" method="get" action="#" target="_self">
                <div class="modal-body">
                  
                      <p><b>&nbsp;&nbsp;วันที่อนุมัติ : </b><?php echo $DateApprove;?></p>
                      <p><b>&nbsp;&nbsp;&nbsp;&nbsp;ใบสำคัญ : </b><?php echo $BookNo;?></p>
                      <p><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ลงวันที่ : </b><?php echo $DateBook;?></p>
                      <p><b>วันที่ตัดจ่าย : </b><?php echo $DatePaid;?></p>
                      <p><b>เลขที่ใบเสร็จรับเงิน : </b><?php echo $BillNo;?></p>
                      <p><b>ใบเสร็จลงวันที่ : </b><?php echo $BillDate;?></p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
                </form>
              </div>
            </div>
          </div>
          <!-- Modal -->
          <div class="modal fade bs-example-modal-sm" id="myModalp2<?php echo $PayId;?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog modal-sm" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-floppy-save" aria-hidden="true" ></span> บันทึกใบเสร็จรับเงิน</h4>
                </div>
                <form name="addtax" method="get" action="addbill.php" target="_self">
                <div class="modal-body">
                    <div class="form-group">
                       <label for="recipient-name" class="control-label">เลขที่ใบเสร็จรับเงิน:</label>
                      <input type="text" class="form-control" id="BillNo" name="BillNo" value="<?php echo $BillNos;?>" required>
                      <label for="recipient-name" class="control-label">ลงวันที่:</label>
                      <input class="form-control"  data-date-format="dd/mm/yyyy" id="datepicker-th2" type="text" name="BillDate" value="<?php $strDate=date("Y-m-d"); $strYear = date("Y",strtotime($strDate))+543; echo $date = date("d/m/$strYear"); ?>" required>
                      <input type="hidden" name="PayId" id="PayId" value="<?php echo $PayId;?>">
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
 <hr>
 </div>
<script type="text/javascript">
      <!--
      document.getElementById("Keyword").value = "<?=$_GET["Keyword"];?>";
      document.getElementById("TypeKey").value = "<?=$_GET["TypeKey"];?>";
</script>
</body>        
</html>
     