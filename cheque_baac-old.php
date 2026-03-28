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
    'format' => 'A4-L'
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
include 'baht_text.php';
$ChequeId=$_GET["ChequeId"];
$Sumnet=$_GET["Sumnet"];
$Sumnettext=baht_text($Sumnet);
$sql = "SELECT * FROM cheque where ChequeId='$ChequeId'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$PayTo = $row['PayTo'];
$sql2 = "SELECT Comment FROM payment where Cheque='$ChequeId'";
$result2 = $conn->query($sql2);
$row2 = $result2->fetch_assoc();
$Comment = $row2['Comment'];
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
	<table border="0" style="width:900px;">
		<tr>
			<td colspan="5">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="5">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="5">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="5">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="5">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="5">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="5">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="5">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="5">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="5"><span style="font-size:10pt;">&nbsp;</span></td>
		</tr>
		<tr>
			<td width="230">&nbsp;</td>
			<td width="130" rowspan="2" valign="top"><span style="font-size:10pt;">'.$PayTo.'<br>'.$Comment.'</span></td>
			<td width="100">&nbsp;</td>
			<td width="515" colspan="2">&nbsp;&nbsp;<span style="font-size:15pt;"><b>'.$PayTo.'</b></span></td>

		</tr>
		<tr>
			<td width="160">&nbsp;</td>
			<td width="100">&nbsp;</td>
			<td width="520" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-size:15pt;"><b>('.$Sumnettext.')</b></span></td>
		</tr>
		<tr>
			<td width="10">&nbsp;</td>
			<td width="130"><span style="font-size:10pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;***'.number_format($Sumnet, 2) . "\n".'***</span></td>
			<td width="100">&nbsp;</td>
			<td width="420" align="right"><span style="font-size:15pt;"><b>***'.number_format($Sumnet, 2) . "\n".'***</b></span></td>
			<td width="113.385"></td>
		</tr>
	</table>
	<br>

	</body>
	</html>'
;
// Write some HTML code:
$mpdf->SetMargins(12,100,12);
$mpdf->WriteHTML($html);

// Output a PDF file directly to the browser
$mpdf->Output();
?>