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
$Datepay=$_GET["Datereport"];
$Worker=$_GET["Worker"];
$Audit=$_GET["Audit"];

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

$StartDay = explode('/',$Datepay);
$YearS=$StartDay[2]-543;
$Datereport=$YearS."-".$StartDay[1]."-".$StartDay[0];
$sqloffice = "SELECT * FROM office";
$resultoffice = $conn->query($sqloffice);
$rowoffice=$resultoffice->fetch_assoc();
$OfficeName=$rowoffice['OfficeName'];//
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
								if($Cheque==""){
						          $Cheque="-";
						        }
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
						</tr>".$data3."
						<tr>
					    	<td colspan='5' align='right'><b>รวม</b></td>
					    	<td align='right'><b>".number_format($Amountc, 2) . "\n"."</b></td>
					    </tr>
						 ";
						}
					}
					//data
					$data.= "
					<table border='0' style='width:700px'>
				    <tr>
				    	<td colspan='6' style='border-top-style:solid; border-width:1px'><span style='font-size:16pt;'><b>".$TypesName."</b></span></td>
				    </tr>
				    ".$data2."
				    <tr>
					    <td colspan='5' align='right'><b>รวมทั้งสิ้น</b></td>
					    <td align='right'><b>".number_format($Amounts, 2) . "\n"."</b></td>
					 </tr>
				    </table><br>";	
				}			
			}else{
				$data.="<p align='center'><strong><span style='font-size:16pt;'> - ไม่พบข้อมูล - </span></strong></p>";
			};	

$mpdf->SetHTMLHeader('<p align="center"><span style="font-size:18pt;"><b>'.$OfficeName.' (รายงานจ่ายประจำวัน วันที่ '.DateThai($Datereport).')</span></b></p><table border="0" style="width:700px">
		<tr style="border-top-style:solid; border-width:1px">
			<td width="40" align="center"><b>ลำดับ</b></td>
			<td width="80" align="center"><b>ใบสำคัญ</b></td>
			<td width="100" align="center"><b>เลขที่เช็ค</b></td>
			<td width="280"><b>บริษัท/ร้าน</b></td>
			<td width="100" align="center"><b>เลขที่ใบส่งของ</b></td>
			<td width="100" align="center"><b>จำนวนเงิน (บาท)</b></td>
		</tr>
	</table>
	', '0');
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
		}
	</style>
	</head>
	<body>
	'.$data.' 
	<table border="0" style="width:700px">
		<tr>
			<td colspan="5" align="right" style="border-top-style:solid; border-width:1px"><b>รวมยอดชำระแล้วทั้งสิ้น</b></td>
			<td align="right" width="100" style="border-bottom-style:double; border-top-style:solid; border-width:1px 1px 3px 1px"><b>'.number_format($AmountAll, 2) . "\n".'</b></td>
		</tr>
	</table>
	<table border="0" style="width:700px">
		<tr>
			<td align="center"><br><br><br>......................................................................ผู้จัดทำ</td>
			<td align="center"><br><br><br>......................................................................ผู้รับรอง</td>
		</tr>
		<tr>
			<td align="center">('.$Namew.')<br>'.$Positionw.'</td>
			<td align="center">('.$Namea.')<br>'.$Positiona.'</td>
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