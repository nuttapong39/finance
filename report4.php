<?php
error_reporting(0);
date_default_timezone_set('Asia/Bangkok');
include 'connect_db.php';
session_start();
// Require composer autoload
require_once __DIR__ . '/mpdf/vendor/autoload.php';
// Create an instance of the class:
// เพิ่ม Font ให้กับ mPDF
$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];
$mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . '/tmp',
    'fontdata' => $fontData + [
            'sarabun' => [ // ส่วนที่ต้องเป็น lower case ครับ
                'R' => 'THSarabun.ttf',
                'I' => 'THSarabun Italic.ttf',
                'B' =>  'THSarabun Bold.ttf',
                'BI' => "THSarabun BoldItalic.ttf",
            ]
        ],
     'format' => 'A4',
]);

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

$sqld = "SELECT * FROM employee where Username='$Director'";
$resultd  = $conn->query($sqld);
$rowd =$resultd ->fetch_assoc();
$Named=$rowd['Names'];
$Positiond=$rowd['Position'];

$sqlw = "SELECT * FROM employee where Username='$Worker'";
$resultw  = $conn->query($sqlw);
$roww =$resultw ->fetch_assoc();
$Namew=$roww['Names'];
$Positionw=$roww['Position'];

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

		$data3=0;
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
				    	<td width='450'>"."&nbsp; - ".$BookNo." ".$CompanyName."</td>
				    	<td width='120' align='right'>".number_format($Amount, 2) . "\n"."</td>
				    	<td width='80' align='center'></td>
				    </tr>
				    ";
	
				}
			}

					//data
					$data.= "
					<table border='1' style='width:700px'>
				    <tr>
				    	<td width='50' align='center'><b>".$i."</b></td>
				    	<td width='450'><span style='font-size:16pt;'><b>".$PlanPayName."</b></span></td>
				    	<td width='120' align='right'><b>".number_format($Amounts, 2) . "\n"."</b></td>
				    	<td width='80' align='center'></td>
				    </tr>".$data3."
				    </table>";
		$i++;
		}
}else{

    $data.="
    <table border='1' style='width:700px'>
    	<tr>
			<td width='700' align='center' colspan='4'>- ไม่พบข้อมูล -</td>
		</tr>
	</table>";
};	

$mpdf->SetHTMLHeader('<p align="center"><span style="font-size:18pt;"><b>รายงานค่าใช้จ่ายตามแผน '.$Sourcetext.' '.$OfficeName.'<br>(ระหว่างวันที่ '.DateThai($DateStart).' ถึงวันที่ '.DateThai($DateEnd).')</span></b></p>
	', '0');
$html = '
	<!DOCTYPE html>
	<html>
	<head>
	<style>
		body {
	    font-family: sarabun;
	    font-size:16pt;
		}
		.dotshed { border-bottom: 1px dotted;  }
		hr {
		   border-top:1px dotted;
		}
		table {
		  border-collapse: collapse;
		}
	</style>
	</head>
	<body>
	<table border="1" style="width:700px">
		<tr>
			<td width="50" align="center"><b>ลำดับ</b></td>
			<td width="450"><b>ค่าใช้จ่าย (เลขที่เอกสาร, บรัษัท/ผู้รับเงิน)</b></td>
			<td width="120" align="right"><b>จำนวนเงิน</b></td>
			<td width="80" align="center"><b>หมายเหตุ</b></td>
		</tr>
	</table>
	'.$data.'
	<table border="1" style="width:700px">
		<tr>
			<td width="450" colspan="2" align="right"><b>รวมจำนวนเงินทั้งสิ้น</b></td>
			<td width="120" align="right"><b>'.number_format($AmountAll, 2) . "\n".'</b></td>
			<td width="80" align="center"></td>
		</tr>
	</table>
	<br>
	<table border="0" style="width:700px">
		<tr>
			<td align="right" colspan="2"><br><br>......................................................................ผู้จัดทำ</td>
			<td align="right" colspan="2"><br><br>......................................................................ผู้ตรวจสอบ</td>
		</tr>
		<tr>
			<td align="center" colspan="2">('.$Namew.')<br>'.$Positionw.'</td>
			<td align="center" colspan="2">('.$Namea.')<br>'.$Positiona.'</td>
		</tr>
		<tr>
			<td width="175"></td>
			<td width="350" colspan="2" align="center"><br><br><br>.....................................................<br>('.$Named.')<br>'.$Positiond.'</td>
			<td width="175"></td>
		</tr>
	</table>  
	</body>
	</html>'
;
$mpdf->SetMargins(30,100,30);
// Write some HTML code:
$mpdf->WriteHTML($html);

// Output a PDF file directly to the browser
$mpdf->Output();
?>