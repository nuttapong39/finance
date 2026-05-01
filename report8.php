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
function DateThaiShort($strDate)
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
  $sql1 = "SELECT *,sum(Amount) as AmountAll FROM payment where DateIn between '$DateStart' and '$DateEnd' and PlanPayId='$PlanPay' and Source='$Source'";
  $sql = "SELECT *,sum(Amount) as Amounts FROM payment where DateIn between '$DateStart' and '$DateEnd'  and PlanPayId='$PlanPay' and Source='$Source' group by PlanPayId";

}else{
  $sql1 = "SELECT *,sum(Amount) as AmountAll FROM payment where DateIn between '$DateStart' and '$DateEnd' and Source='$Source'";
  $sql = "SELECT *,sum(Amount) as Amounts FROM payment where DateIn between '$DateStart' and '$DateEnd' and Source='$Source' group by PlanPayId";
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

$strExcelFileName="export_paydetail_".$Datereport.".xls";
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
<strong><center>รายละเอียดรายการค่าใช้จ่ายตามแผน <?php echo $Sourcetext.' '.$OfficeName;?><br>(ระหว่างวันที่ <?php echo DateThai($DateStart);?> ถึงวันที่ <?php echo DateThai($DateEnd);?>)</center></strong>
<div id="SiXhEaD_Excel" align=center x:publishsource="Excel">
<table class='table table-striped' border="1">
      <tr>
        <td width="80" align="center"><b>ค่าใช้จ่าย</b></td>
        <td width="100" align="center"><b>วันที่รับเอกสาร</b></td>
        <td width="80" align="center"><b>รหัสรายการ</b></td>
        <td width="120" align="center"><b>เลขที่ใบส่งของ</b></td>
        <td width="220" align="center"><b>รายการ/บริษัท</b></td>
        <td width="100" align="right"><b>จำนวนเงิน</b></td>
      </tr>
<?php 
if ($result->num_rows > 0) {
  $nums=$result->num_rows;
    while($row = $result->fetch_assoc()) {
    $PayId=$row['PayId'];
    $TypesId=$row['TypesId'];
    $TypebId=$row['TypebId'];
    $Cheque=$row['Cheque'];
    $Amounts=$row["Amounts"];
    $PlanPayId=$row["PlanPayId"];
    $sqlts = "SELECT * FROM planpay where PlanPayId='$PlanPayId'";
    $resultts = $conn->query($sqlts);
    $rowts=$resultts->fetch_assoc();
    $PlanPayName=$rowts['PlanPayName'];
    $data3="";
    $j=1;
    $sql3 = "SELECT * FROM payment where PlanPayId='$PlanPayId'";
      $result3 = $conn->query($sql3);
      if ($result3){
        while($row3 = $result3->fetch_assoc()) {
          $PayId=$row3['PayId'];
          $DateIn=$row3['DateIn'];
          $CompanyId=$row3["CompanyId"];
          $BookNo=$row3["BookNo"];
          $Detail=$row3["Detail"];
          $Tax=$row3["Tax"];
          $Amount=$row3["Amount"];
          $Net=$row3["Net"];
          $sqlcompany = "SELECT * FROM company where CompanyId=$CompanyId";
          $resultcompany = $conn->query($sqlcompany);
          $rowcompany=$resultcompany->fetch_assoc();
          $CompanyName=$rowcompany['CompanyName'];
          $TypesId=$row3["TypesId"];
          $sqlts = "SELECT * FROM types where TypesId=$TypesId ";
          $resultts = $conn->query($sqlts);
          $rowts=$resultts->fetch_assoc();
          $TypesName=$rowts['TypesName'];
          
          $data3.= "
            <tr>
              <td width='80'></td>
            <td width='100'>".DateThaiShort($DateIn)."</td>
            <td width='80' align='center'>".$PayId."</td>
            <td width='120' align='center'>".$Detail."</td>
            <td width='220'>".$CompanyName."</td>
            <td width='100' align='right'>".number_format($Amount,2)."\n"."</td>
            </tr>
            ";
            $j++;
        }
      }

          //data
          $data.= "
          
            <tr>
              <td width='700' colspan='6'>".$i.". ".$PlanPayName."</td>
            </tr>".$data3."
            <tr>
              <td width='600' colspan='5' align='right'>รวมเงิน</td>
              <td width='100' align='right'><b>".number_format($Amounts, 2) . "\n"."</b></td>
            </tr>
            ";
    $i++;
    }
}else{

    $data.="<tr><td colspan='4'><p align='center'><strong> - ไม่พบข้อมูล - </span></p></td></tr>";
};
echo $data;
?>
    <tr>
      <td width="600" align="right" colspan="5"><b>รวมทั้งสิ้น</b></td>
      <td width="100" align="right"><b><?php echo number_format($AmountAll, 2) . "\n"?></b></td>
    </tr>
  </table>
</div>
<script>
window.onbeforeunload = function(){return false;};
setTimeout(function(){window.close();}, 10000);
</script>
</body>
</html>