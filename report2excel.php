<?php
include "connect_db.php";
function DateThai($strDate)
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

$Datepay=$_GET["Datereport"];
$StartDay = explode('/',$Datepay);
$YearS=$StartDay[2]-543;
$Datereport=$YearS."-".$StartDay[1]."-".$StartDay[0];
$sqloffice = "SELECT * FROM office";
$resultoffice = $conn->query($sqloffice);
$rowoffice=$resultoffice->fetch_assoc();
$OfficeName=$rowoffice['OfficeName'];//

$strExcelFileName="export_daily_".$Datereport.".xls";
header("Content-Type: application/x-msexcel; name=\"$strExcelFileName\"");
header("Content-Disposition: inline; filename=\"$strExcelFileName\"");
header("Pragma:no-cache");
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"xmlns:x="urn:schemas-microsoft-com:office:excel"xmlns="http://www.w3.org/TR/REC-html40">
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<strong>รายงานจ่ายประจำวัน วันที่ <?php echo DateThai($Datereport);?> </strong>
<div id="SiXhEaD_Excel" align=center x:publishsource="Excel">
<table class='table table-striped' border="1">
      <tr>
        <td width="40" align="center"><b>ลำดับ</b></td>
        <td width="80" align="center"><b>ใบสำคัญ</b></td>
        <td width="100" align="center"><b>เลขที่เช็ค</b></td>
        <td width="280"><b>บริษัท/ร้าน</b></td>
        <td width="100" align="center"><b>เลขที่ใบส่งของ</b></td>
        <td width="100" align="center"><b>จำนวนเงิน (บาท)</b></td>
      </tr>	
<?php 
$sql1 = "SELECT *,sum(Amount) as AmountAll FROM payment where DatePaid='$Datereport'";
$result1 = $conn->query($sql1);
$row1=$result1->fetch_assoc();
$AmountAll=$row1['AmountAll'];

$sql = "SELECT *,sum(Amount) as Amounts FROM payment where DatePaid='$Datereport' group by TypesId";
$result = $conn->query($sql);
$Amounts=0;
$Vats=0;
$Nets=0;
$data = "";
if ($result->num_rows > 0) {
  $nums=$result->num_rows;
    while($row = $result->fetch_assoc()) {
    $PayId=$row['PayId'];
    $TypesId=$row['TypesId'];
    $TypebId=$row['TypebId'];
    $Cheque=$row['Cheque'];
    $PayId=$row['PayId'];
    $Amounts=$row["Amounts"];
    $sqlts = "SELECT * FROM types where TypesId='$TypesId'";
    $resultts = $conn->query($sqlts);
    $rowts=$resultts->fetch_assoc();
    $TypesName=$rowts['TypesName'];
    $data2="";
          //data2
          $sql3 = "SELECT *,sum(Amount) as Amountc FROM payment where DatePaid='$Datereport' and TypesId='$TypesId' group by TypebId";
          $result3 = $conn->query($sql3);
          if ($result3){
            while($row3 = $result3->fetch_assoc()) {
            $TypebId=$row3['TypebId'];
            $sqltb = "SELECT * FROM typeb where TypebId='$TypebId'";
            $resulttb = $conn->query($sqltb);
            $rowtb=$resulttb->fetch_assoc();
            $TypebName=$rowtb['TypebName'];
            $Amountc=$row3["Amountc"];
            //data3
            $data3="";
            $i=1;
            $sql1 = "SELECT * FROM payment where DatePaid='$Datereport' and TypesId='$TypesId' and TypebId='$TypebId'";
            $result1 = $conn->query($sql1);
            if ($result1){
              while($row1 = $result1->fetch_assoc()) {
                $BookNo=$row1['BookNo'];
                $Cheque=$row1["Cheque"];
                $CompanyId=$row1["CompanyId"];
                $Detail=$row1["Detail"];
                $Amount=$row1["Amount"];
                $sqlcompany = "SELECT * FROM company where CompanyId=$CompanyId";
                $resultcompany = $conn->query($sqlcompany);
                $rowcompany=$resultcompany->fetch_assoc();
                $CompanyName=$rowcompany['CompanyName'];
                $data3.= "
                  <tr>
                    <td width='40' align='center'>".$i."</td>
                    <td width='80' align='center'>".$BookNo."</td>
                    <td width='100' align='center'>".$Cheque."</td>
                    <td width='280'>".$CompanyName."</td>
                    <td width='100' align='center'>".$Detail."</td>
                  <td width='100' align='right'>".number_format($Amount, 2) . "\n"."</td>
                  </tr>
                  ";
                $i++;   
              }
              
            }
            $data2.= "
            <tr>
                <td colspan='6'>เงิน : ".$TypebName."</td>
            </tr>
            ".$data3."
            <tr>
                <td colspan='5' align='right'><b>รวม</b></td>
                <td align='right'><b>".number_format($Amountc, 2) . "\n"."</b></td>
              </tr>
             ";
            }
          }
          //data
          $data.= "
            <tr>
              <td colspan='6'><b>".$TypesName."</b></td>
            </tr>
            ".$data2."
            <tr>
              <td colspan='5' align='right'><b>รวมทั้งสิ้น</b></td>
              <td align='right'><b>".number_format($Amounts, 2) . "\n"."</b></td>
           </tr>
          ";
        }
}else{

    $data.="<tr><td colspan='6'><p align='center'><strong><span style='font-size:12pt;'> - ไม่พบข้อมูล - </span></strong></p></td></tr>";
};
echo $data;
?>
    <tr>
      <td colspan="5" align="right" ><b>รวมยอดชำระแล้วทั้งสิ้น</b></td>
      <td align="right" width="100" ><b><?php echo number_format($AmountAll, 2) . "\n";?></b></td>
    </tr>
  </table>
</div>
<script>
window.onbeforeunload = function(){return false;};
setTimeout(function(){window.close();}, 10000);
</script>
</body>
</html>