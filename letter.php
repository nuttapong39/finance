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
                'R' => 'THSarabunIT.ttf',
                'I' => 'THSarabunIT Italic.ttf',
                'B' =>  'THSarabunIT Bold.ttf',
                'BI' => 'THSarabunIT BoldItalic.ttf',
            ]
        ],

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
				return "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$strMonthThai&nbsp;&nbsp;$strYear";
			}
include 'baht_text.php';
$PayId=$_GET["PayId"];
$ChequeId=$_GET["ChequeId"];
$data = "";
$sqls = "SELECT sum(Amount) as Amounts,sum(Vat) as Vats,sum(Price) as Prices,sum(Tax) as Taxs, sum(Net) as Nets from payment where Cheque='$ChequeId'";
$results = $conn->query($sqls);
$rows = $results->fetch_assoc();
$Amounts=$rows["Amounts"];
$Vats=$rows["Vats"];
$Prices=$rows["Prices"];
$Taxs=$rows["Taxs"];
$Nets=$rows["Nets"];
$sql = "SELECT * from payment where Cheque='$ChequeId'";
$result = $conn->query($sql);
$numlist=$result->num_rows;
$i=1;
if ($result){
	while($row = $result->fetch_assoc()) {
	$DateIn=$row['DateIn'];
	$TypesId=$row["TypesId"];
	$TypebId=$row["TypebId"];
	$PlanPayId=$row["PlanPayId"];
	$DeptId=$row["DeptId"];
	$BookNo=$row["BookNo"];
	$DateBook=$row["DateBook"];
	$NumList=$row["NumList"];
	$Detail=$row["Detail"];
	$Price=$row["Price"];
	$Vat=$row["Vat"];
	$Amount=$row["Amount"];
	$Cheque=$row["Cheque"];
	$CompanyId=$row["CompanyId"];
	$BankId=$row["BankId"];
	$DateApprove=$row["DateApprove"];
	$DateNows=date("Y-m-d");
	$Tax=$row["Tax"];
	$Net=$row["Net"];

	$sqltb = "SELECT * FROM typeb where TypebId=$TypebId";
	$resulttb = $conn->query($sqltb);
	$rowtb=$resulttb->fetch_assoc();
	$TypebName=$rowtb['TypebName'];

	$sqlts = "SELECT * FROM types where TypesId=$TypesId";
	$resultts = $conn->query($sqlts);
	$rowts=$resultts->fetch_assoc();
	$TypesName=$rowts['TypesName'];

	$sqlcompany = "SELECT * FROM company where CompanyId=$CompanyId";
	$resultcompany = $conn->query($sqlcompany);
	$rowcompany=$resultcompany->fetch_assoc();
	$CompanyName=$rowcompany['CompanyName'];
	
	$sqlbank = "SELECT * FROM bank where BankId=$BankId";
	$resultbank = $conn->query($sqlbank);
	$rowbank=$resultbank->fetch_assoc();
	$BankNames=$rowbank['BankName'];
	$BankOf=$rowbank['BankOf'];
	if($BankId>1){
		$BankName="ธนาคาร".$BankNames;
	}else{
		$BankName=$BankNames;
	}

	$sql2 = "SELECT * FROM office";
	$result2 = $conn->query($sql2);
	$row2=$result2->fetch_assoc();
	$OfficeName=$row2['OfficeName'];//
	$Department=$row2['Department'];
	$Work=$row2['Work'];
	$No=$row2['No'];
	$Tombol=$row2['Tombol'];
	$District=$row2['District'];
	$Province=$row2['Province'];
	$Postcode=$row2['Postcode'];
	$BookNoDept=$row2['BookNo'];
	$Tel=$row2['Tel'];
	$Director=$row2['Director'];

	$sqld = "SELECT * FROM employee where Username='$Director'";
	$resultd  = $conn->query($sqld );
	$rowd =$resultd ->fetch_assoc();
	$Names=$rowd['Names'];
	$Position=$rowd['Position'];

	$data.= "
	<tr>
			<td width='20'>&nbsp;</td>
			<td width='20' align='center'>".$i."</td>
			<td width='90' align='center'>".$Detail."</td>
			<td width='110' align='right' >".number_format($Amount, 2) . "\n"."</td>
			<td width='100' align='right' >".number_format($Vat, 2) . "\n"."</td>
			<td width='110' align='right' >".number_format($Price, 2) . "\n"."</td>
			<td width='100' align='right' >".number_format($Tax, 2) . "\n"."</td>
			<td width='110' align='right' >".number_format($Net, 2) . "\n"."</td>
			<td width='40'>บาท</td>
	</tr>";
	$i++;
}
}else{
	echo "<div class='alert alert-danger' role='alert' align='center'> ไม่พบข้อมูล </div>";
}

$html = '
	<!DOCTYPE html>
	<html>
	<head>
	<style>
		body {
	    font-family: sarabun;
	    font-size:16pt;
		}
		.dotshed { border-bottom: 1.5px dotted;  }
		hr {
		   border-top:1px dotted;
		}
		table {
		  border-collapse: collapse;
		}
	</style>
	</head>
	<body>
	<table border="0" style="width:680px"  >
		<tr>
			<td width="30"><br><br></td>
			<td width="200"></td>
			<td width="120"></td>
			<td width="120"></td>
			<td width="200"></td>
		</tr>
        <tr>
        	<td width="30"></td>
        	<td width="200">ที่</b>&nbsp;&nbsp;นน ๐๐๓๓.๓๐๑.๐๒/</td>
        	<td width="240" align="center" colspan="2"><img src="pic/pic1.png" width="115" height="115"><br><br>&nbsp;</td>
        	<td width="200"><br><br>'.$OfficeName.'<br>'.$No.'&nbsp;ต.'.$Tombol.'<br>อ.'.$District.'&nbsp;จ.'.$Province.'&nbsp;'.$Postcode.'</td>
		</tr>
		<tr>
        	<td width="30"><span style="font-size:5pt;">&nbsp;</span></td>
        	<td width="200"></td>
        	<td width="120"></td>
        	<td width="120"></td>
        	<td width="200"></td>
		</tr>
		<tr>
        	<td width="30"></td>
        	<td width="200"></td>
        	<td width="120"></td>
        	<td width="320" colspan="2"></td>
		</tr>
		<tr>
        	<td width="30"><span style="font-size:5pt;">&nbsp;</span></td>
        	<td width="200"></td>
        	<td width="120"></td>
        	<td width="120"></td>
        	<td width="200"></td>
		</tr>
		<tr>
			<td width="30"></td>
			<td width="640" colspan="4"><b>เรื่อง</b>&nbsp;&nbsp;ขอนำส่งเช็คชำระ '.$TypesName.'</td>
		</tr>
		<tr>
			<td width="30"></td>
			<td width="640" colspan="4"><span style="font-size:5pt;">&nbsp;</span></td>
		</tr>
		<tr>
			<td width="30"></td>
			<td width="640" colspan="4"><b>เรียน</b>&nbsp;&nbsp;'.$CompanyName.'</td>
		</tr>
		<tr>
			<td width="30"></td>
			<td width="640" colspan="4"><span style="font-size:5pt;">&nbsp;</span></td>
		</tr>
		<tr>
			<td width="30"></td>
			<td width="440" colspan="3"><b>สิ่งที่ส่งมาด้วย</b>&nbsp;&nbsp;1. เช็คธนาคาร'.$BankOf.' เลขที่ '.$Cheque.' ลงวันที่</td>
			<td width="200">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;จำนวน&nbsp; 1 &nbsp;ฉบับ</td>
		</tr>
		<tr>
			<td width="30"></td>
			<td width="440" colspan="3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2. ใบหักภาษี ณ ที่จ่าย</td>
			<td width="200">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;จำนวน &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ฉบับ</td>
		</tr>
		<tr>
			<td width="30"></td>
			<td width="640" colspan="4"><span style="font-size:5pt;">&nbsp;</span></td>
		</tr>
	</table>
	<table border="0" style="width:680px">
		<tr>
			<td width="30"></td>
			<td width="90"></td>
			<td width="570" align="justify">ตามที่ '.$OfficeName.' สั่งซื้อ/สั่งจ้าง '.$TypesName.'</td>
		</tr>
		<tr>
			<td width="30"></td>
			<td width="660" align="justify" colspan="2"> จาก '.$CompanyName.' ตามรายละเอียดที่เอกสารดังนี้</td>
		</tr>
		
	</table>
	<table border="0" style="width:680px">
		<tr>
			<td width="30"><span style="font-size:3pt;">&nbsp;</span></td>
			<td width="30"></td>
			<td width="70"></td>
			<td width="110" align="right" ></td>
			<td width="100" align="right" ></td>
			<td width="110" align="right" ></td>
			<td width="100" align="right" ></td>
			<td width="110" align="right" ></td>
			<td width="40"></td>
		</tr>
		<tr>
			<td width="30"></td>
			<td width="20" align="center">ลำดับ</td>
			<td width="70" align="center">เลขที่บิล</td>
			<td width="110" align="right" >ราคาสุทธิ</td>
			<td width="100" align="right" >ภาษี 7%</td>
			<td width="110" align="right" >ราคาสินค้า</td>
			<td width="100" align="right" >หักภาษี 1%</td>
			<td width="110" align="right" >ชำระจริง</td>
			<td width="40"></td>
		</tr>
		'.$data.'
		<tr>
			<td width="30">&nbsp;</td>
			<td width="90" colspan="2"><b>จำนวน '.$numlist.' ชุด</b></td>
			<td width="110" align="right"><b><u>'.number_format($Amounts, 2) . "\n".'</u></b></td>
			<td width="100" align="right"><b><u>'.number_format($Vats, 2) . "\n".'</u></b></td>
			<td width="110" align="right"><b><u>'.number_format($Prices, 2) . "\n".'</u></b></td>
			<td width="100" align="right"><b><u>'.number_format($Taxs, 2) . "\n".'</u></b></td>
			<td width="110" align="right"><b><u>'.number_format($Nets, 2) . "\n".'</u></b></td>
			<td width="40"><b>บาท</b></td>
		</tr>
		<tr>
			<td width="30">&nbsp;</td>
			<td width="660" colspan="8">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b><u>รวมจ่ายเป็นเงินตามเช็ค</u> ('.baht_text($Nets).')</b></td>
		</tr>
	</table>
	<table border="0" style="width:680px">
		<tr>
			<td width="30"></td>
			<td width="660" colspan="2" align="justify">เมื่อท่านได้รับเงินไว้เรียบร้อยแล้ว โปรดตอบรับยืนยัน พร้อมส่งใบเสร็จรับเงินให้แก่'.$Work.''.$Department.' '.$OfficeName.'โดยด่วน</td>
		</tr>
		<tr>
			<td width="30"><span style="font-size:5pt;">&nbsp;</span></td>
			<td width="90"></td>
			<td width="570"></td>
		</tr>
		<tr>
			<td width="30"></td>
			<td width="90"></td>
			<td width="570">จึงเรียนมาเพื่อโปรดดำเนินการต่อไป</td>
		</tr>
	</table>
	<table border="0" style="width:680px">
		<tr>
			<td width="30"></td>
			<td width="200" colspan="2"></td>
			<td width="220" colspan="2" align="center"><font color="#fff">ขอแสดงความนับถือ</font></td>
		</tr>
		<tr>
			<td width="30"></td>
			<td width="100"></td>
			<td width="100"></td>
			<td width="180" align="center">ขอแสดงความนับถือ</td>
			<td width="80"></td>
		</tr>
		<tr>
			<td width="30"></td>
			<td width="100"></td>
		<!--
			<td width="370" colspan="3" align="center"><br><br>(นายวิทยา มิ่งปรีชา)<br>นักจัดการงานทั่วไปชำนาญการ ปฏิบัติหน้าที่<br>ผู้อำนวยการโรงพยาบาลทุ่งช้าง</td>
		-->
		<!--	
			<td width="370" colspan="3" align="center"><br><br>(นางอำไพ ไชยอามิตร)<br>พยาบาลวิชาชีพชำนาญการพิเศษ ปฏิบัติหน้าที่<br>ผู้อำนวยการโรงพยาบาลทุ่งช้าง</td>
			-->
			<td width="370" colspan="3" align="center"><br><br>(นายกฤตพงษ์ โรจนวิภาต)<br>นายแพทย์เชี่ยวชาญ<br>ผู้อำนวยการโรงพยาบาลทุ่งช้าง</td>
		
		</tr>
	</table>
	<br>
	<table border="0" style="width:680px">
	<tr>
		<td width="30"></td>
		<td width="660" colspan="4">'.$Department.'<br>'.$Work.'<br>'.$Tel.'</td>
	</tr>
	</table>
	</body>
	</html>'
;
$mpdf->SetMargins(5,3,5);
// Write some HTML code:
$mpdf->WriteHTML($html);
;
// Output a PDF file directly to the browser
$mpdf->Output();
?>