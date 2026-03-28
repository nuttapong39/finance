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
                'BI' => 'THSarabun BoldItalic.ttf',
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
				//$strMonthCut = Array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
				$strMonthThai=$strMonthCut[$strMonth];
				return "&nbsp;&nbsp;$strDay&nbsp;&nbsp;$strMonthThai&nbsp;&nbsp;$strYear";
}
function DateThaishort($strDate)
			{
				$strYear = date("Y",strtotime($strDate))+543;
				$strMonth= date("n",strtotime($strDate));
				$strDay= date("j",strtotime($strDate));
				$strHour= date("H",strtotime($strDate));
				$strMinute= date("i",strtotime($strDate));
				$strSeconds= date("s",strtotime($strDate));
				//$strMonthCut = Array("","มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
				$strMonthCut = Array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
				$strMonthThai=$strMonthCut[$strMonth];
				return "$strDay&nbsp;&nbsp;$strMonthThai&nbsp;&nbsp;$strYear";
}
include 'baht_text.php';
$PayId=$_GET["PayId"];
$ReceiveNo=$_GET["ReceiveNo"];

$Worker1=$_GET["Worker1"];
$w1 = "SELECT * FROM employee where Username='$Worker1'";
$resultw1  = $conn->query($w1);
$roww1 =$resultw1 ->fetch_assoc();
$Worker1n=$roww1['Names'];
$Position1=$roww1['Position'];

$Worker2=$_GET["Worker2"];
$w2 = "SELECT * FROM employee where Username='$Worker2'";
$resultw2  = $conn->query($w2);
$roww2 =$resultw2 ->fetch_assoc();
$Worker2n=$roww2['Names'];
$Position2=$roww2['Position'];

$Worker3=$_GET["Worker3"];
$w3 = "SELECT * FROM employee where Username='$Worker3'";
$resultw3  = $conn->query($w3);
$roww3 =$resultw3 ->fetch_assoc();
$Worker3n=$roww3['Names'];
$Position3=$roww3['Position'];

$Worker4=$_GET["Worker4"];
$w4 = "SELECT * FROM employee where Username='$Worker4'";
$resultw4  = $conn->query($w4);
$roww4 =$resultw4 ->fetch_assoc();
$Worker4n=$roww4['Names'];
$Position4=$roww4['Position'];


$data = "";
$i=1;
$sql = "SELECT *,SUM(Amount) as Amounts,sum(Tax) as Taxs, sum(Net) as Nets from payment where ReceiveNo='$ReceiveNo' group by ReceiveNo";
$result = $conn->query($sql);
$numlist=$result->num_rows;
if ($result){
	while($row = $result->fetch_assoc()) {
	$ReceiveNo=$row['ReceiveNo'];
	$TypesId=$row["TypesId"];
	$TypebId=$row["TypebId"];
	$PlanPayId=$row["PlanPayId"];
	$DeptId=$row["DeptId"];
	$BookNo=$row["BookNo"];
	$DateBook=$row["DateBook"];
	$Price=$row["Price"];
	$Vat=$row["Vat"];
	$Amounts=$row["Amounts"];
	$Taxs=$row["Taxs"];
	$Nets=$row["Nets"];
	$DateApprove=$row["DateApprove"];

	$sql2 = "SELECT * from payment where ReceiveNo='$ReceiveNo'";
	$result2 = $conn->query($sql2);
	while($row2 = $result2->fetch_assoc()) {
		$DateReceive=$row2['DateReceive'];
		$NumList=$row2["NumList"];
		$Detail=$row2["Detail"];
		$Amount=$row2["Amount"];
		$Tax=$row2["Tax"];
		$Net=$row2["Net"];
		$data.= "
	<tr>
			<td align='center'>".$i."</td>
			<td align='center'>".$Detail."</td>
			<td align='center'>".DateThaishort($DateReceive)."</td>
			<td align='right'>".number_format($Amount, 2) . "\n"."</td>
			<td align='right'>".number_format($Tax, 2) . "\n"."</td>
			<td align='right'>".number_format($Net, 2) . "\n"."</td>
	</tr>";
	$i++;
	}
	$sqltb = "SELECT * FROM typeb where TypebId=$TypebId";
	$resulttb = $conn->query($sqltb);
	$rowtb=$resulttb->fetch_assoc();
	$TypebName=$rowtb['TypebName'];

	$sqlts = "SELECT * FROM types where TypesId=$TypesId";
	$resultts = $conn->query($sqlts);
	$rowts=$resultts->fetch_assoc();
	$TypesName=$rowts['TypesName'];

	$CompanyId=$row["CompanyId"];
	$sqlcompany = "SELECT * FROM company where CompanyId=$CompanyId";
	$resultcompany = $conn->query($sqlcompany);
	$rowcompany=$resultcompany->fetch_assoc();
	$CompanyName=$rowcompany['CompanyName'];
	$BankId=$row["BankId"];

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

	$sql3 = "SELECT * FROM office";
	$result3 = $conn->query($sql3);
	$row3=$result3->fetch_assoc();
	$OfficeName=$row3['OfficeName'];//
	$Department=$row3['Department'];
	$Work=$row3['Work'];
	$No=$row3['No'];
	$Tombol=$row3['Tombol'];
	$District=$row3['District'];
	$Province=$row3['Province'];
	$BookNoDept=$row3['BookNoDept'];
	$Tel=$row3['Tel'];
	$Director=$row3['Director'];
	$Manager=$row3['Manager'];

	$sqld = "SELECT * FROM employee where Username='$Director'";
	$resultd  = $conn->query($sqld );
	$rowd =$resultd ->fetch_assoc();
	$Names=$rowd['Names'];
	$Position=$rowd['Position'];

	$sqlm = "SELECT * FROM employee where Username='$Manager'";
	$resultm  = $conn->query($sqlm);
	$rowm =$resultm ->fetch_assoc();
	$Namem=$rowm['Names'];
	$Positionm=$rowm['Position'];
	
	}

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
	<table border="0" width="680"  >
		
        <tr>
        	<td width="30"></td>
        	<td colspan="3"><img src="pic/pic1.png" width="80" height="80"></td>
        	<td colspan="7"><span style="font-size:29pt;"><b>บันทึกข้อความ</b></span></td>
		</tr>
		<tr>
			<td width="30"></td>
			<td colspan="10"><b>ส่วนราชการ</b>&nbsp;&nbsp;'.$Department.' '.$Work.' '.$Tel.'</td>
		</tr>
		<tr>
			<td width="30"></td>
			<td colspan="4"><b>ที่</b>&nbsp;&nbsp;'.$BookNoDept.'</td>
			<td colspan="4">&nbsp;&nbsp;&nbsp;&nbsp;<b>วันที่</b>&nbsp;&nbsp;'.DateThai($DateApprove).'</td>
		</tr>
		<tr>
			<td width="30"></td>
			<td colspan="10"><b>เรื่อง</b>&nbsp;&nbsp;ขออนุมัติเบิก-จ่ายเงิน'.$TypebName.' เพื่อจ่ายเป็น'.$TypesName.'</td>
		</tr>
		<tr>
			<td width="30"></td>
		    <td colspan="10"><b>เรียน</b>&nbsp;&nbsp;ผู้อำนวยการ'.$OfficeName.'</td>
		</tr>
	</table>
	<br>
	<table border="0" style="width:100%">
		<tr>
			<td width="30"></td>
			<td align="justify">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$Work.' '.$Department.' '.$OfficeName.' ได้ดำเนินการตรวจสอบแล้ว จึงมีความประสงค์ขออนุมัติเบิก-จ่ายเงิน'.$TypebName.' เพื่อจ่ายเป็น '.$TypesName.' ของ '.$CompanyName.' รวมเป็นจำนวนเงิน '.number_format($Amounts, 2) . "\n".' บาท ('. baht_text($Amounts).') รายละเอียดตามเอกสารที่แนบมาพร้อมนี้</td>
		</tr>
		<tr>
			<td></td><td></td>
		</tr>
		<tr>
			<td></td>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;จึงเรียนมาเพื่อโปรดพิจารณาอนุมัติ</td>
		</tr>
	</table>
	<br>


			<table border="1" style="width:100%">
			<tr>
				<td colspan="4" align="center" width="70%">จ่ายให้ '.$CompanyName.'</td>
				<td colspan="2" width="30%">ตามเช็คเลขที่<br>ลงวันที่<br>'.$BankName.'</td>
			</tr>
			<tr>
				<td align="center">ลำดับ</td>
				<td align="center">ใบรับสินค้าเลขที่</td>
				<td align="center">วันที่</td>
				<td align="center">จำนวนเงิน</td>
				<td align="center">ภาษีหัก ณ ที่จ่าย</td>
				<td align="center">ยอดสุทธิ</td>
			</tr>
			'.$data.'
			<tr>
				<td align="center" colspan="3"><b>'.baht_text($Amounts).'</b></td>
				<td align="right"><b>'.number_format($Amounts, 2) . "\n".'</b></td>
				<td align="right"><b>'.number_format($Taxs, 2) . "\n".'</b></td>
				<td align="right"><b>'.number_format($Nets, 2) . "\n".'</b></td>
			</tr>
				<tr>
				
					<td align="center" colspan="2">1.บันทึกเจ้าหนี้<br><br><br><span style="font-size:16pt;">('.$Worker1n.')<br>'.$Position1.'<br>......./......../.........</span></td>
					<td align="center" colspan="2">2.ผู้จัดทำ<br><br><br><span style="font-size:16pt;">('.$Worker2n.')<br>'.$Position2.'<br>......./......../.........</span></td>
			<!--    <td align="center" colspan="2" rowspan="2">5. [&nbsp;&nbsp;&nbsp;] อนุมัติ &nbsp;&nbsp;&nbsp;&nbsp;[&nbsp;&nbsp;&nbsp;] ไม่อนุมัติ<br><br><br><br>('.$Names.')<br>'.$Position.'<br>......./......../.........<br> -->
			<!--    <td align="center" colspan="2" rowspan="2">5. [&nbsp;&nbsp;&nbsp;] อนุมัติ &nbsp;&nbsp;&nbsp;&nbsp;[&nbsp;&nbsp;&nbsp;] ไม่อนุมัติ<br><br><br><br>............................................<br>..........................................<br>......./......../.........<br> 	-->
					<td align="center" colspan="2" rowspan="2">5. [&nbsp;&nbsp;&nbsp;] อนุมัติ &nbsp;&nbsp;&nbsp;&nbsp;[&nbsp;&nbsp;&nbsp;] 
				
					ไม่อนุมัติ<br><br><br>(นายกฤตพงษ์ โรจนวิภาต)
					<br>นายแพทย์เชี่ยวชาญ 

					<!--ไม่อนุมัติ<br><br><br>(นางอำไพ ไชยอามิตร)
					<br>พยาบาลวิชาชีพชำนาญการพิเศษ ปฏิบัติหน้าที่ -->

					<!-- ไม่อนุมัติ<br><br><br>(นายวิทยา มิ่งปรีชา)
					<br>นักจัดการงานทั่วไปชำนาญการ ปฏิบัติหน้าที่ -->
					<br>ผู้อำนวยการโรงพยาบาลทุ่งช้าง
					<br>......./......../.........
					<br>	
					<strong style="font-size: 25px;">จ่ายแล้ว</strong><br><br>
					..............................................<br>
									(นางอรุณี  จันต๊ะวงศ์)<br>
								จพ.การเงินและบัญชีชำนาญงาน<br>
								......../......../........
					</td>
				
				</tr>
				<tr>
					<td align="center" colspan="2">3.ผู้ตรวจสอบ<br><br><br><span style="font-size:16pt;">('.$Worker3n.')<br>'.$Position3.'</span><br>......./......../.........</td> 
					<td align="center" colspan="2">4.ผู้ตรวจสอบ<br><br><br><span style="font-size:16pt;">('.$Worker4n.')<br>'.$Position4.'</span><br>......./......../.........</td> 
				</tr>
				
			</table>
		
	</body>
	</html>'
;
//$mpdf->SetMargins(5,3,2);
// Write some HTML code:
$mpdf->WriteHTML($html);
;
// Output a PDF file directly to the browser
$mpdf->Output();
?>