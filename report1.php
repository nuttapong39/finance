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
     'format' => 'A4-L',
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
$Datepay=$_GET["Datereport"];
$StartDay = explode('/',$Datepay);
$YearS=$StartDay[2]-543;
$Datereport=$YearS."-".$StartDay[1]."-".$StartDay[0];
$sqloffice = "SELECT * FROM office";
$resultoffice = $conn->query($sqloffice);
$rowoffice=$resultoffice->fetch_assoc();
$OfficeName=$rowoffice['OfficeName'];//
$sql = "SELECT *,sum(Amount) as Amounts,sum(Tax) as Taxs, sum(Net) as Nets FROM payment where DatePay='$Datereport' group by BankId";
$result = $conn->query($sql);
$Amounts=0;
$Vats=0;
$Nets=0;
$i=1;
$data = "";
if ($result->num_rows > 0) {
	$nums=$result->num_rows;
	while($row = $result->fetch_assoc()) {
		$BankId=$row['BankId'];
		$Amounts=$row["Amounts"];
		$Taxs=$row["Taxs"];
		$Nets=$row["Nets"];
		$sqlbank = "SELECT * FROM bank where BankId=$BankId";
		$resultbank = $conn->query($sqlbank);
		$rowbank=$resultbank->fetch_assoc();
		$BankNames=$rowbank['BankName'];

		$BankOf=$rowbank['BankOf'];
		if($BankId>1){
			$BankName="ธนาคาร".$BankOf." ".$BankNames;
		}else{
			$BankName=$BankOf." ".$BankNames;
		}

		$PayId=$row['PayId'];
		$data2=0;
		$sql2 = "SELECT *,sum(Amount) as Amountc,sum(Tax) as Taxc, sum(Net) as Netc FROM payment where DatePay='$Datereport' and BankId='$BankId' and Cheque!='' group by Cheque";
		$result2 = $conn->query($sql2);
		if ($result2){
			while($row2 = $result2->fetch_assoc()) {
			$Cheque=$row2['Cheque'];
			$PayId=$row2['PayId'];
			$Amountc=$row2["Amountc"];
			$Taxc=$row2["Taxc"];
			$Netc=$row2["Netc"];
			$data3=0;
			$sqlCheque = "SELECT * FROM cheque where ChequeId='$Cheque'";
			$resultCheque = $conn->query($sqlCheque);
			$rowCheque=$resultCheque->fetch_assoc();
			$PayTo=$rowCheque['PayTo'];
			$sql3 = "SELECT * FROM payment where DatePay='$Datereport' and Cheque='$Cheque'";
			$result3 = $conn->query($sql3);
			if ($result3){
				while($row3 = $result3->fetch_assoc()) {
					$PayId=$row3['PayId'];
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
					$sqlts = "SELECT * FROM types where TypesId=$TypesId";
					$resultts = $conn->query($sqlts);
					$rowts=$resultts->fetch_assoc();
					$TypesName=$rowts['TypesName'];

					$data3.= "
				    <tr>
				    	<td width='60'></td>
				    	<td width='250'><span style='font-size:12pt;'>".$TypesName." (".$Detail.")</span></td>
						<td width='80' align='right'>".number_format($Amount, 2) . "\n"."</td>
						<td width='80' align='right'>".number_format($Tax, 2) . "\n"."</td>
						<td width='80' align='right'>".number_format($Net, 2) . "\n"."</td>
						<td width='100' style='border-bottom: 0px; border-top: 0px'></td>
						<td width='100' style='border-bottom: 0px; border-top: 0px'></td>
						<td width='100' style='border-bottom: 0px; border-top: 0px'></td>
						<td width='100' style='border-bottom: 0px; border-top: 0px'></td>
						<td width='120' style='border-bottom: 0px; border-top: 0px'></td>
						<td width='120' style='border-bottom: 0px; border-top: 0px'></td>
				    </tr>
				    ";
	
				}
			}
			$data2.= "
				<tr>
			    	<td width='80' align='center'>&nbsp;&nbsp;".$Cheque."</td>
			    	<td colspan='4'>ผู้รับเช็ค : ".$PayTo."</td>
			    	<td width='100' style='border-bottom: 0px;'></td>
					<td width='100' style='border-bottom: 0px;'></td>
					<td width='100' style='border-bottom: 0px;'></td>
					<td width='100' style='border-bottom: 0px;'></td>
					<td width='120' style='border-bottom: 0px;'></td>
					<td width='120' style='border-bottom: 0px;'></td>
			    </tr>".$data3."
			    <tr>
			    	<td colspan='2' align='right'><b>รวมจำนวนเงิน</b></td>
			    	<td align='right'><b>".number_format($Amountc, 2) . "\n"."</b></td>
					<td align='right'><b>".number_format($Taxc, 2) . "\n"."</b></td>
					<td align='right'><b>".number_format($Netc, 2) . "\n"."</b></td>
					<td width='100' style='border-top: 0px;'></td>
					<td width='100' style='border-top: 0px;'></td>
					<td width='100' style='border-top: 0px;'></td>
					<td width='100' style='border-top: 0px;'></td>
					<td width='120' style='border-top: 0px;'></td>
					<td width='120' style='border-top: 0px;'></td>
			    </tr>
			    ";
			}
		}
		$break="";
		if($i<$nums){
	 		$break.= "<pagebreak/>";
		}
		$data.= "
		<table border='1' style='width:1100' >
	    <tr>
	    	<td colspan='11'><span style='font-size:16pt;'><b>".$BankName."</b></span></td>
	    </tr>

	    ".$data2."
		<tr>
	    	<td colspan='2' align='right'><b>รวมจำนวนเงินทั้งสิ้น</b></td>
	    	<td align='right'><b>".number_format($Amounts, 2) . "\n"."</b></td>
			<td align='right'><b>".number_format($Taxs, 2) . "\n"."</b></td>
			<td align='right'><b>".number_format($Nets, 2) . "\n"."</b></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
	    </tr>
	    </table>".$break."";
	$i++;    
	}	
}else{

    $data.="<p align='center'><strong><span style='font-size:16pt;'> - ไม่พบข้อมูล - </span></strong></p>";
};

$mpdf->SetHTMLHeader('<p align="center"><span style="font-size:18pt;"><b>'.$OfficeName.'<br>รายงานทะเบียนคุมเช็ค ประจำวันที่ '.DateThai($Datereport).'</span></b></p><table border="1" style="width:1100">
	<tr>
			<td width="80" align="center"><b>เลขที่เช็ค</b></td>
			<td width="250" align="center"><b>จ่ายให้ใคร-เพื่ออะไร</b></td>
			<td width="80" align="center"><b>จำนวนเงิน</b></td>
			<td width="80" align="center"><b>หักภาษี</b></td>
			<td width="80" align="center"><b>จำนวนจ่าย</b></td>
			<td width="100" align="center"><b>ผู้อนุมัติ 1</b></td>
			<td width="100" align="center"><b>ผู้อนุมัติ 2</b></td>
			<td width="100" align="center"><b>ผู้เสนอลงนาม</b></td>
			<td width="100" align="center"><b>ผู้รับเช็ค</b></td>
			<td width="120" align="center"><b>วันที่รับเช็ค</b></td>
			<td width="120" align="center"><b>วันที่จ่ายเช็ค</b></td>
	</tr></table>', 'O');
$html = '
	<!DOCTYPE html>
	<html>
	<head>
	<style>
		body {
	    font-family: sarabun;
	    font-size:14pt;
		}
		.dotshed { border-bottom: 1px dotted;  }
		hr {
		   border-top:1px dotted;
		}
		table {
		  border-collapse: collapse;
		  table-layout: fixed;
		  width: 1100px;
		}
		td{
			overflow:hidden;
		}

	</style>
	</head>
	<body>
		'.$data.'
	</body>
	</html>'
;
$mpdf->SetMargins(36.5,100,36.5);
// Write some HTML code:
//$mpdf->setFooter('{PAGENO}/{nbpg}');

$mpdf->WriteHTML($html);

// Output a PDF file directly to the browser
$mpdf->Output();
?>