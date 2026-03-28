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
$Datenow=date("Y-m-d");
$StartDay = explode('-',$Datenow);
$YearS=$StartDay[0]+543;
$Datereport=$StartDay[2]."-".$StartDay[1]."-".$YearS;

$DateStartb=$_GET["DateStart"];
$PlanPay=$_GET["PlanPay"];
$Worker=$_GET["Worker"];
$Audit=$_GET["Audit"];
$Source=$_GET["Source"];
if($Source=='1'){
  $Sourcetext="(เงินบำรุง)";
}else{
  $Sourcetext="(เงินงบประมาณ)";
}
$DateStarttf = explode('/',$DateStartb);
$YearS1=$DateStarttf[2]-543;
$DateStart=$YearS1."-".$DateStarttf[1]."-".$DateStarttf[0];

$DateEndb=$_GET["DateEnd"];
$DateEndtf = explode('/',$DateEndb);
$YearS2=$DateEndtf[2]-543;
$DateEnd=$YearS2."-".$DateEndtf[1]."-".$DateEndtf[0];

$sqloffice = "SELECT * FROM office";
$resultoffice = $conn->query($sqloffice);
$rowoffice=$resultoffice->fetch_assoc();
$OfficeName=$rowoffice['OfficeName'];//
$Director=$rowoffice['Director'];

$sqla = "SELECT * FROM employee where Username='$Audit'";
$resulta  = $conn->query($sqla);
$rowa =$resulta ->fetch_assoc();
$Namea=$rowa['Names'];
$Positiona=$rowa['Position'];
if($PlanPay!=""){
  $sql1 = "SELECT *,sum(Amount) as AmountAll FROM payment where DatePaid between '$DateStart' and '$DateEnd' and PlanPayId='$PlanPay' and Source='$Source'";
  $sql = "SELECT *,sum(Amount) as Amounts FROM payment where DatePaid between '$DateStart' and '$DateEnd'  and PlanPayId='$PlanPay' and Source='$Source' group by PlanPayId";

}else{
  $sql1 = "SELECT *,sum(Amount) as AmountAll FROM payment where DatePaid between '$DateStart' and '$DateEnd' and Source='$Source'";
  $sql = "SELECT *,sum(Amount) as Amounts FROM payment where DatePaid between '$DateStart' and '$DateEnd' and Source='$Source' group by PlanPayId";
}

$result1 = $conn->query($sql1);
$row1=$result1->fetch_assoc();
$AmountAll=$row1['AmountAll'];

$result = $conn->query($sql);
$Amounts=0;
$Vats=0;
$Nets=0;
$i=1;
$data = "";

$strExcelFileName="export_paid_".$Datereport.".xls";
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
<strong>รายงานค่าใช้จ่ายตามแผน <?php echo $Sourcetext.' '.$OfficeName;?><br>(ระหว่างวันที่ <?php echo DateThai($DateStart);?> ถึงวันที่ <?php echo DateThai($DateEnd);?>)</strong>
<div id="SiXhEaD_Excel" align=center x:publishsource="Excel">
<table class='table table-striped' border="1">
      <tr>
        <td width="100" align="center"><b>ลำดับ</b></td>
        <td width="400"><b>ค่าใช้จ่าย (เลขที่เอกสาร, บรัษัท/ผู้รับเงิน)</b></td>
        <td width="200" align="right"><b>จำนวนเงิน</b></td>
        <td width="200" align="center"><b>หมายเหตุ</b></td>

<?php
$data="";
if ($result->num_rows > 0) {
  $nums=$result->num_rows;
    while($row = $result->fetch_assoc()) {
    $PayId=$row['PayId'];
    $TypesId=$row['TypesId'];
    $TypebId=$row['TypebId'];
    $Cheque=$row['Cheque'];
    $PayId=$row['PayId'];
    $Amounts=$row["Amounts"];
    $PlanPayId=$row["PlanPayId"];
    $sqlts = "SELECT * FROM planpay where PlanPayId='$PlanPayId'";
    $resultts = $conn->query($sqlts);
    $rowts=$resultts->fetch_assoc();
    $PlanPayName=$rowts['PlanPayName'];
    
    $data3="";
    $sql3 = "SELECT * FROM payment where DatePaid between '$DateStart' and '$DateEnd' and Source='$Source' and PlanPayId='$PlanPayId'";
    $result3 = $conn->query($sql3);
    if ($result3){
      while($row3 = $result3->fetch_assoc()) {
        $CompanyId=$row3["CompanyId"];
        $BookNo=$row3["BookNo"];
        if($BookNo=="-"){
          $BookNo="";
        }
        $Amount=$row3["Amount"];
        $sqltc = "SELECT * FROM company where CompanyId='$CompanyId'";
        $resulttc = $conn->query($sqltc);
        $rowtc=$resulttc->fetch_assoc();
        $CompanyName=$rowtc['CompanyName'];

          $data3.= "
            <tr>
              <td width='50' align='center'></td>
              <td width='400'>"."&nbsp;- ".$BookNo." ".$CompanyName."</td>
              <td width='130' align='right'>".number_format($Amount, 2) . "\n"."</td>
              <td width='120' align='center'></td>
            </tr>
            ";
  
        }
      }      
          //data
          $data.= "
          <table border='1' style='width:700px'>
            <tr>
              <td width='50' align='center'><b>".$i."</b></td>
              <td width='400'><b>".$PlanPayName."</b></td>
              <td width='130' align='right'><b>".number_format($Amounts, 2) . "\n"."</b></td>
              <td width='120' align='center'></td>
            </tr>".$data3."";
    $i++;
    }
}else{

    $data.="<tr><td colspan='4'><p align='center'><strong><span style='font-size:12pt;'> - ไม่พบข้อมูล - </span></strong></p></td></tr>";
};
echo $data;
?>
    <tr>
      <td width="500" colspan="2" align="right"><b>รวมจำนวนเงินทั้งสิ้น</b></td>
      <td width="200" align="right"><b><?php echo number_format($AmountAll, 2) . "\n";?></b></td>
      <td width="200" align="right"></td>
    </tr>
  </table>
</div>
<script>
window.onbeforeunload = function(){return false;};
setTimeout(function(){window.close();}, 10000);
</script>
</body>
</html>