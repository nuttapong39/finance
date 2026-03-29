<?php ob_start(); ?>
<!DOCTYPE html>
<html>
        
<haed>    
<?php include 'header.php';?>
<link rel="shortcut icon" type="image/x-icon" href="pic/fms.png" />
  <title>ระบบบริหารจัดการการเงินและบัญชี</title>
  <meta charset="UTF-8">
  <meta http-equiv=Content-Type content="text/html; charset=tis-620">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" type="text/css" href="./css/bootstrap.min.css">
  <script type="text/javascript" src="./js/jquery.js"></script>
  <script type="text/javascript" src="./js/bootstrap.min.js"></script>
  <script type="text/javascript" src="./js/bootbox.min.js"></script>  

<body> 
<div class="container">
  <div class="row">
    <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-3"></div>
        <div class="col-md-6">
          <div class="alert alert-success" role="alert" align="center"><h4><span class="glyphicon glyphicon-floppy-saved" aria-hidden="true"></span> บันทึกข้อมูลเรียบร้อย</h4></div>
        </div>
        <div class="col-md-3"></div>
      </div>
    </div>
    </div>
    <hr>
  </div>
</div> 
<?php
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  include "connect_db.php";
  require_once __DIR__ . '/notify_helper.php';

  // ── MOPH ALERT helpers ──────────────────────────────────────────
  function save_build_payment_messages(
    string $companyName,
    string $detail,
    string $price,
    string $vat,
    string $amount,
    string $dateInThai,
    string $recorderName
  ): array {
    $dt   = new DateTimeImmutable('now', new DateTimeZone('Asia/Bangkok'));
    $date = $dt->format('Y-m-d');
    $time = $dt->format('H:i:s');

    $summaryText = sprintf(
      "📋 บันทึกเจ้าหนี้การค้า/รายการสั่งจ่าย\n ------------------------\n🟡 สถานะ: รอรับเอกสารการเงิน\n ------------------------\n🏢 บริษัท/ร้านค้า: %s\n📄 เลขที่ใบส่งของ: %s\n💰 จำนวนเงิน: %s บาท\n🧾 VAT: %s บาท\n💵 รวมทั้งสิ้น: %s บาท\n ------------------------\n📅 วันที่รับเอกสาร: %s\n👤 ผู้บันทึก: %s\n ------------------------\n📅 วันที่: %s  ⏰ เวลา: %s\n ------------------------",
      $companyName, $detail, $price, $vat, $amount, $dateInThai, $recorderName, $date, $time
    );

    $bubble = [
      "type" => "bubble",
      "size" => "giga",
      "header" => [
        "type" => "box", "layout" => "vertical", "paddingAll" => "0px",
        "contents" => [[
          "type" => "image",
          "url"  => CI_LOGO_URL,
          "size" => "full", "aspectMode" => "cover", "aspectRatio" => "3120:885"
        ]]
      ],
      "body" => [
        "type" => "box", "layout" => "vertical", "spacing" => "md",
        "contents" => [
          // Title
          [
            "type" => "box", "layout" => "vertical", "margin" => "sm",
            "contents" => [
              ["type" => "text", "text" => "📋 บันทึกเจ้าหนี้การค้า", "size" => "lg", "weight" => "bold", "color" => CI_COLOR_PRIMARY, "align" => "center"],
              ["type" => "text", "text" => "รายการสั่งจ่าย", "size" => "md", "color" => "#666666", "align" => "center", "margin" => "xs"],
              ["type" => "text", "text" => "🟡 สถานะ: รอรับเอกสารการเงิน", "size" => "sm", "weight" => "bold", "color" => "#F59E0B", "align" => "center", "margin" => "sm"]
            ]
          ],
          [
            "type" => "box", "layout" => "baseline",
            "contents" => [
              ["type" => "text", "text" => "✅ บันทึกข้อมูลสำเร็จ", "weight" => "bold", "size" => "lg", "flex" => 0],
              ["type" => "text", "text" => "สำเร็จ", "size" => "sm", "color" => CI_COLOR_PRIMARY, "align" => "end", "weight" => "bold"]
            ]
          ],
          ["type" => "separator", "margin" => "sm"],
          // Data rows
          [
            "type" => "box", "layout" => "vertical", "spacing" => "sm",
            "contents" => [
              [
                "type" => "box", "layout" => "baseline",
                "contents" => [
                  ["type" => "text", "text" => "🏢", "size" => "sm", "flex" => 0],
                  ["type" => "text", "text" => "บริษัท/ร้านค้า", "size" => "sm", "color" => "#8a8a8a", "flex" => 3],
                  ["type" => "text", "text" => $companyName, "size" => "sm", "align" => "end", "weight" => "bold", "flex" => 4, "wrap" => true]
                ]
              ],
              [
                "type" => "box", "layout" => "baseline",
                "contents" => [
                  ["type" => "text", "text" => "📄", "size" => "sm", "flex" => 0],
                  ["type" => "text", "text" => "เลขที่ใบส่งของ", "size" => "sm", "color" => "#8a8a8a", "flex" => 3],
                  ["type" => "text", "text" => $detail, "size" => "sm", "align" => "end", "weight" => "bold", "flex" => 4]
                ]
              ],
              [
                "type" => "box", "layout" => "baseline",
                "contents" => [
                  ["type" => "text", "text" => "💰", "size" => "sm", "flex" => 0],
                  ["type" => "text", "text" => "จำนวนเงิน", "size" => "sm", "color" => "#8a8a8a", "flex" => 3],
                  ["type" => "text", "text" => $price . " บาท", "size" => "sm", "align" => "end", "weight" => "bold", "flex" => 4, "color" => CI_COLOR_PRIMARY]
                ]
              ],
              [
                "type" => "box", "layout" => "baseline",
                "contents" => [
                  ["type" => "text", "text" => "🧾", "size" => "sm", "flex" => 0],
                  ["type" => "text", "text" => "VAT", "size" => "sm", "color" => "#8a8a8a", "flex" => 3],
                  ["type" => "text", "text" => $vat . " บาท", "size" => "sm", "align" => "end", "weight" => "bold", "flex" => 4]
                ]
              ],
              [
                "type" => "box", "layout" => "baseline",
                "contents" => [
                  ["type" => "text", "text" => "💵", "size" => "sm", "flex" => 0],
                  ["type" => "text", "text" => "รวมทั้งสิ้น", "size" => "sm", "color" => "#8a8a8a", "flex" => 3],
                  ["type" => "text", "text" => $amount . " บาท", "size" => "sm", "align" => "end", "weight" => "bold", "flex" => 4, "color" => CI_COLOR_PRIMARY]
                ]
              ]
            ]
          ],
          ["type" => "separator", "margin" => "sm"],
          [
            "type" => "box", "layout" => "baseline",
            "contents" => [
              ["type" => "text", "text" => "📅 วันที่รับเอกสาร: " . $dateInThai, "size" => "sm", "color" => "#8a8a8a", "flex" => 2],
              ["type" => "text", "text" => "👤 " . $recorderName, "size" => "sm", "color" => "#8a8a8a", "align" => "end", "flex" => 3]
            ]
          ],
          [
            "type" => "box", "layout" => "baseline",
            "contents" => [
              ["type" => "text", "text" => "📅 " . $date, "size" => "sm", "color" => "#8a8a8a", "flex" => 2],
              ["type" => "text", "text" => "⏰ " . $time, "size" => "sm", "color" => "#8a8a8a", "align" => "end", "flex" => 3]
            ]
          ],
          ["type" => "separator", "margin" => "sm"]
        ]
      ]
    ];

    return [
      ["type" => "text",  "text" => $summaryText],
      ["type" => "flex",  "altText" => "บันทึกเจ้าหนี้การค้า/รายการสั่งจ่าย", "contents" => $bubble]
    ];
  }

  // ────────────────────────────────────────────────────────────────

    $DateInb=$_REQUEST['DateIn'];
    $exds = explode('/',$DateInb);
    $YearS=$exds[2]-543;
    $DateIn=$YearS."-".$exds[1]."-".$exds[0];
    $TypesId=$_REQUEST['Types'];
    $PlanPayId=$_REQUEST['PlanPay'];
    $DeptId=$_REQUEST['Dept'];
    $DateBook = ''; // DateBook ไม่มีในฟอร์มนี้
    $NumList=$_REQUEST['NumList'];
    $Detail=$_REQUEST['Detail'];
    $Price=number_format($_REQUEST['Price'], 2, '.', '');
    $Vat=number_format($_REQUEST['Vat'], 2, '.', '');
    $Amount=number_format($_REQUEST['Amount'], 2, '.', '');
    $CompanyId=$_REQUEST['Company']; //Comment DateApprove
    $source=$_REQUEST['source'];

    //$result_save = mysql_query($conn,$sql_save);
    $sql_save= "INSERT INTO `payment`(`PayId`, `DateIn`, `TypesId`, `TypebId`, `PlanPayId`, `DeptId`, `BookNo`, `DateBook`, `NumList`, `Detail`, `Price`, `Vat`, `Amount`, `DatePay`, `BankId`, `Tax`, `Net`, `Cheque`, `CompanyId`, `Comment`, `DateApprove`, `DatePaid`, `ReceiveNo`, `DateReceive`, `Source`, `BillNo`, `BillDate`) VALUES (NULL,'$DateIn','$TypesId','','$PlanPayId','$DeptId','$BookNo','$DateBook','$NumList','$Detail','$Price','$Vat','$Amount','$DatePay','$BankId','$Tax','$Net','$Cheque','$CompanyId','$Comment','$DateApprove','','','',$source,'','')";
    if($conn->query($sql_save)){
      // ── ส่ง MOPH ALERT หลัง INSERT สำเร็จ ──
      try {
        // ดึงชื่อบริษัทจาก CompanyId
        $companyName = '';
        $stmtCN = $conn->prepare("SELECT CompanyName FROM company WHERE CompanyId=? LIMIT 1");
        if ($stmtCN) {
          $stmtCN->bind_param("i", $CompanyId);
          $stmtCN->execute();
          $rCN = $stmtCN->get_result()->fetch_assoc();
          $stmtCN->close();
          $companyName = (string)($rCN['CompanyName'] ?? $CompanyId);
        }
        $recorderName = trim(($_SESSION['Names'] ?? '') . ' (' . ($_SESSION['Username'] ?? '') . ')');
        if ($recorderName === ' ()') $recorderName = 'ไม่ระบุ';

        $msgs = save_build_payment_messages(
          $companyName ?: (string)$CompanyId,
          (string)$Detail,
          (string)$Price,
          (string)$Vat,
          (string)$Amount,
          (string)$DateInb,    // วันที่ในรูปแบบ dd/mm/YYYY พุทธศักราช
          $recorderName
        );
        moph_broadcast($msgs, $conn);
      } catch (Throwable $e) {
        error_log("MOPH ALERT payment exception: " . $e->getMessage());
      }
      // ────────────────────────────────────────────────
      header("Refresh: 2; accounting.php");
      //header("location:accounting.php");
    }
  ?>
</body>        
</html>
     