<?php
// paid.php
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Bangkok');

ob_start();
require_once __DIR__ . '/header.php';
$__header_html = ob_get_clean();

require_once __DIR__ . '/connect_db.php';

function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); }
function DateThaiShort($strDate) {
  if (!$strDate || $strDate === '0000-00-00') return '-';
  $ts = strtotime($strDate); if (!$ts) return '-';
  $y=(int)date('Y',$ts)+543; $m=(int)date('n',$ts); $d=(int)date('j',$ts);
  $mc=["","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค."];
  return "$d&nbsp;&nbsp;{$mc[$m]}&nbsp;&nbsp;$y";
}

/* ─── inputs ─── */
$Keyword = (string)($_REQUEST['Keyword'] ?? '');
$TypeKey = (int)($_REQUEST['TypeKey'] ?? 0);

/* ─── query ─── */
$DateNows = date('Y-m-d');
if ($Keyword === '') {
  $sql = "SELECT * FROM cheque WHERE Net!=0 AND (DatePaid='' OR DatePaid='$DateNows') ORDER BY DatePrint DESC";
} else {
  switch ($TypeKey) {
    case 1: $sql = "SELECT * FROM cheque WHERE Net!=0 AND PayTo LIKE '%".mysqli_real_escape_string($conn,$Keyword)."%' ORDER BY DatePrint DESC"; break;
    case 2: $sql = "SELECT * FROM cheque WHERE Net!=0 AND Net='".mysqli_real_escape_string($conn,$Keyword)."' ORDER BY DatePrint DESC"; break;
    default: $sql = "SELECT * FROM cheque WHERE Net!=0 AND ChequeId='".mysqli_real_escape_string($conn,$Keyword)."' ORDER BY DatePrint DESC";
  }
}
$result    = $conn->query($sql);
$rows      = $result ? $result->num_rows : 0;
$page_rows = 25;
$last      = max(1,(int)ceil($rows/$page_rows));
$pagenum   = max(1,min($last,(int)($_GET['pn'] ?? 1)));
$offset    = ($pagenum-1)*$page_rows;

$qs = '&TypeKey='.(int)$TypeKey.'&Keyword='.urlencode($Keyword);
if ($Keyword === '') {
  $nquery = mysqli_query($conn,"SELECT * FROM cheque WHERE Net!=0 AND (DatePaid='' OR DatePaid='$DateNows') ORDER BY DatePrint DESC LIMIT $offset,$page_rows");
} else {
  switch ($TypeKey) {
    case 1: $nquery = mysqli_query($conn,"SELECT * FROM cheque WHERE Net!=0 AND PayTo LIKE '%".mysqli_real_escape_string($conn,$Keyword)."%' ORDER BY DatePrint DESC LIMIT $offset,$page_rows"); break;
    case 2: $nquery = mysqli_query($conn,"SELECT * FROM cheque WHERE Net!=0 AND Net='".mysqli_real_escape_string($conn,$Keyword)."' ORDER BY DatePrint DESC LIMIT $offset,$page_rows"); break;
    default: $nquery = mysqli_query($conn,"SELECT * FROM cheque WHERE Net!=0 AND ChequeId='".mysqli_real_escape_string($conn,$Keyword)."' ORDER BY DatePrint DESC LIMIT $offset,$page_rows");
  }
}

/* ─── collect ─── */
$dataRows = [];
if ($nquery) {
  while ($row = $nquery->fetch_assoc()) {
    $cid = (string)($row['ChequeId'] ?? '');
    $r1  = $conn->query("SELECT * FROM payment WHERE Cheque='".mysqli_real_escape_string($conn,$cid)."'");
    $row1 = $r1 ? $r1->fetch_assoc() : [];
    $row['_PayId']    = (int)($row1['PayId'] ?? 0);
    $row['_hasPaymt'] = ($r1 && $r1->num_rows > 0) ? true : false;
    // re-query num_rows after fetch
    $r1b = $conn->query("SELECT COUNT(*) as cnt FROM payment WHERE Cheque='".mysqli_real_escape_string($conn,$cid)."'");
    $cnt = $r1b ? $r1b->fetch_assoc() : ['cnt'=>0];
    $row['_hasPaymt'] = ((int)$cnt['cnt'] > 0);
    $dataRows[] = $row;
  }
}

/* ─── pagination ─── */
$pagination = '';
if ($last > 1) {
  $pagination = '<nav><ul class="pagination" style="margin:8px 0;">';
  if ($pagenum > 1) $pagination .= '<li><a href="?pn='.($pagenum-1).$qs.'">&laquo;</a></li>';
  for ($i=max(1,$pagenum-2);$i<=min($last,$pagenum+2);$i++) {
    $act=($i===$pagenum)?' class="active"':'';
    $pagination .= '<li'.$act.'><a href="?pn='.$i.$qs.'">'.$i.'</a></li>';
  }
  if ($pagenum < $last) $pagination .= '<li><a href="?pn='.($pagenum+1).$qs.'">&raquo;</a></li>';
  $pagination .= '</ul></nav>';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" type="image/x-icon" href="pic/fms.png"/>
  <title>ระบบบริหารจัดการการเงินและบัญชี</title>
  <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css" rel="stylesheet">
  <style>
    body { font-family:'Sarabun',sans-serif; background:radial-gradient(1100px 600px at 12% 15%,rgba(91,155,213,.22),transparent 60%),radial-gradient(900px 520px at 92% 10%,rgba(0,176,80,.14),transparent 55%),linear-gradient(180deg,#f8fbff,#f6f8fc); }
    .container { max-width:1200px; }
    .page-titlebar { margin:14px 0 16px; border-radius:18px; padding:14px 20px; background:rgba(255,255,255,.88); border:1px solid #e9eef6; box-shadow:0 12px 30px rgba(13,27,62,.08); }
    .page-titlebar h3 { margin:2px 0 0; font-weight:800; color:#1f2a44; font-size:20px; }
    .page-titlebar .sub { color:#6b778c; margin-top:6px; font-size:13px; }
    .card-panel { border-radius:18px; border:1px solid #e9eef6; background:rgba(255,255,255,.92); box-shadow:0 12px 30px rgba(13,27,62,.08); overflow:hidden; margin-bottom:14px; }
    .card-head { padding:12px 14px; border-bottom:1px solid #e9eef6; font-weight:800; color:#1f2a44; background:linear-gradient(135deg,rgba(0,176,80,.16),rgba(0,176,80,.06)); }
    .card-body { padding:14px 16px; }
    .form-control { height:42px; border-radius:12px; border:1px solid #dfe7f3; box-shadow:none; font-family:'Sarabun',sans-serif; font-size:14px; transition:border-color .2s,box-shadow .2s; }
    .form-control:focus { border-color:rgba(0,176,80,.55); box-shadow:0 0 0 3px rgba(0,176,80,.18); }
    select.form-control { appearance:none; -webkit-appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b778c' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 12px center; padding-right:36px; }
    .btn { border-radius:12px; font-weight:700; font-family:'Sarabun',sans-serif; padding:9px 14px; transition:transform .15s; }
    .btn:active { transform:scale(.96); }
    .btn-success { box-shadow:0 10px 22px rgba(0,176,80,.18); }
    .btn-action { width:36px; height:36px; padding:0; display:inline-flex; align-items:center; justify-content:center; }
    .btn-action:hover { transform:translateY(-1px); }
    .table { background:#fff; border-radius:14px; overflow:hidden; margin-bottom:8px; }
    .table thead th { background:#e9f7ee; color:#1f2a44; font-weight:800; border-bottom:1px solid #d6f0df !important; vertical-align:middle !important; }
    .table td { vertical-align:middle !important; }
    .table tbody tr { transition:background .18s ease,transform .18s ease,box-shadow .18s ease; }
    .table tbody tr:hover { background:linear-gradient(90deg,#eef7ff,#f4f9ff) !important; transform:scale(1.005); box-shadow:0 4px 14px rgba(0,176,80,.22); position:relative; z-index:2; }
    .table tbody tr:hover td:first-child { border-left:3px solid #00b050; border-radius:8px 0 0 8px; }
    .table tbody tr:hover td:last-child { border-radius:0 8px 8px 0; }
    .badge-pill { display:inline-block; padding:6px 10px; border-radius:999px; font-weight:800; font-size:12px; border:1px solid #e2e8f0; background:#f8fafc; color:#334155; }
    .bd-wait { background:#fff7ed; border-color:#fed7aa; color:#9a3412; }
    .pagination>li>a,.pagination>li>span { border-radius:10px !important; margin:0 4px; border:1px solid #e2e8f0; color:#1f2a44; }
    .pagination>.active>a { background:#00b050; border-color:#00b050; }
    .pagination>li>a:hover { background:#d6f0df; border-color:#00b050; }
    .swal2-popup { border-radius:20px; font-family:'Sarabun',sans-serif; }
    .swal2-title { color:#1f2a44; font-weight:800; }
    .swal2-confirm,.swal2-cancel { border-radius:12px !important; font-weight:700; }
    .swal-form .form-group { margin-bottom:14px; text-align:left; }
    .swal-form label { font-weight:700; color:#1f2a44; font-size:13px; display:block; margin-bottom:5px; }
    .swal-form input[type="text"] { width:100%; height:40px; border-radius:10px; border:1px solid #dfe7f3; font-family:'Sarabun',sans-serif; font-size:14px; padding:0 12px; box-sizing:border-box; }
    .swal-form input:focus { outline:none; border-color:#00b050; box-shadow:0 0 0 3px rgba(0,176,80,.18); }
    .swal-btns { display:flex; gap:10px; justify-content:center; margin-top:20px; }
    .ui-datepicker { z-index:10000 !important; font-family:'Sarabun',sans-serif; font-size:13px; }
  </style>
</head>
<body>
  <?php echo $__header_html; ?>
  <div class="container">
    <div class="page-titlebar" style="display:flex; align-items:center; justify-content:space-between; gap:12px; text-align:left;">
      <div>
        <h3 style="margin:0; display:flex; align-items:center; gap:8px;">
          <span class="msi msi-24" style="color:#0B6E4F;">task_alt</span> ตัดจ่ายเช็ค
        </h3>
        <div class="sub">ค้นหาและบันทึกวันที่ตัดจ่ายเช็ค</div>
      </div>
      <a href="main.php" class="btn-go-back"><span class="msi">arrow_back</span> กลับหน้าหลัก</a>
    </div>

    <!-- Tabs -->
    <div class="card-panel">
      <div class="card-body" style="padding-bottom:0;">
        <ul class="nav-tabs-modern">
          <li><a href="receive.php"><span class="msi">inbox</span> ลงรับเอกสาร</a></li>
          <li><a href="finance.php"><span class="msi">check_circle</span> ขออนุมัติ</a></li>
          <li><a href="cheque.php"><span class="msi">credit_card</span> จัดทำเช็ค</a></li>
          <li><a href="printcheque.php"><span class="msi">print</span> พิมพ์เช็ค</a></li>
          <li><a href="control.php"><span class="msi">menu_book</span> ทะเบียนคุม</a></li>
          <li><a href="paidment.php"><span class="msi">book</span> ใบสำคัญ</a></li>
          <li class="active"><a href="paid.php"><span class="msi">task_alt</span> ตัดจ่ายเช็ค</a></li>
          <li><a href="daily.php"><span class="msi">calendar_today</span> รายงานประจำวัน</a></li>
          <li><a href="findpay.php"><span class="msi">search</span> ค้นหารายการ</a></li>
        </ul>
      </div>
    </div>

    <!-- Search -->
    <div class="card-panel">
      <div class="card-head"><span class="msi">search</span> ค้นหารายการจ่ายเช็ค</div>
      <div class="card-body">
        <form method="get" action="<?= h($_SERVER['PHP_SELF']) ?>">
          <div class="row" style="margin:0;">
            <div class="col-md-3" style="padding-left:0;">
              <label style="font-weight:800;color:#1f2a44;">ค้นด้วย</label>
              <select class="form-control" name="TypeKey">
                <option value="0">เลือกประเภทการค้น</option>
                <option value="1" <?= $TypeKey===1?'selected':''; ?>>ผู้รับเช็ค</option>
                <option value="2" <?= $TypeKey===2?'selected':''; ?>>ยอดสุทธิ</option>
                <option value="3" <?= $TypeKey===3?'selected':''; ?>>เลขที่เช็ค</option>
              </select>
            </div>
            <div class="col-md-5">
              <label style="font-weight:800;color:#1f2a44;">คำค้น</label>
              <input class="form-control" name="Keyword" type="text" value="<?= h($Keyword) ?>" placeholder="ระบุคำค้น...">
            </div>
            <div class="col-md-2" style="padding-right:0;padding-top:26px;">
              <button type="submit" class="btn btn-success" style="width:100%;"><span class="msi">search</span> ค้นหา</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Table -->
    <div class="card-panel">
      <div class="card-head">
        <span class="msi">task_alt</span> รายการรอตัดจ่ายเช็ค
        <span style="font-weight:600;color:#64748b;">(ทั้งหมด <?= (int)$rows ?> รายการ)</span>
      </div>
      <div class="card-body">
        <?php if (count($dataRows) === 0): ?>
          <div class="alert alert-warning" style="border-radius:14px;text-align:center;"><span class="msi">info</span> ไม่พบข้อมูล</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th style="text-align:center;">เลขที่เช็ค</th>
                  <th style="text-align:center;">วันที่จัดทำเช็ค</th>
                  <th>ผู้รับเช็ค</th>
                  <th style="text-align:right;">ยอดสุทธิ</th>
                  <th style="text-align:center;">วันที่จ่าย</th>
                  <th style="text-align:center;">บันทึกตัดจ่าย</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($dataRows as $row): ?>
                <?php
                  $ChequeId  = (string)($row['ChequeId'] ?? '');
                  $PayTo     = (string)($row['PayTo'] ?? '');
                  $DatePrint = (string)($row['DatePrint'] ?? '');
                  $DatePaids = (string)($row['DatePaid'] ?? '');
                  $Net       = (float)($row['Net'] ?? 0);
                  $PayId     = (int)($row['_PayId'] ?? 0);
                  $hasPaymt  = (bool)($row['_hasPaymt'] ?? false);

                  if ($hasPaymt) {
                    $SumnetDisp = number_format($Net, 2);
                    $DatePaidDisp = ($DatePaids === '' || $DatePaids === '0000-00-00')
                      ? '<span class="badge-pill bd-wait">รอตัดจ่ายเช็ค</span>'
                      : DateThaiShort($DatePaids);
                  } else {
                    $SumnetDisp = '<span style="color:#ef4444;font-weight:700;">ยกเลิกเช็ค</span>';
                    $DatePaidDisp = '-';
                  }
                ?>
                <tr>
                  <td style="text-align:center;"><?= h($ChequeId) ?></td>
                  <td style="text-align:center;"><?= DateThaiShort($DatePrint) ?></td>
                  <td><?= h($PayTo) ?></td>
                  <td style="text-align:right;"><?= $SumnetDisp ?></td>
                  <td style="text-align:center;"><?= $DatePaidDisp ?></td>
                  <td style="text-align:center;">
                    <button type="button" class="btn btn-success btn-action"
                      onclick="openPaid('<?= addslashes(h($ChequeId)) ?>',<?= $PayId ?>)"
                      title="บันทึกตัดจ่าย">
                      <span class="msi">calendar_today</span>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div style="text-align:center;margin-top:6px;"><?= $pagination ?></div>
        <?php endif; ?>
      </div>
    </div>
    <div style="height:16px;"></div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
  <script>
  var todayTh = (function(){
    var d=new Date(); var y=d.getFullYear()+543;
    return String(d.getDate()).padStart(2,'0')+'/'+String(d.getMonth()+1).padStart(2,'0')+'/'+y;
  })();

  function fpTH(el) {
    var iv = el.value.trim(), initDate = null;
    if (iv) { var p=iv.split('/'); if(p.length===3){var y=parseInt(p[2]);if(y>2500)y-=543;initDate=new Date(y,parseInt(p[1])-1,parseInt(p[0]));} }
    return flatpickr(el, {
      locale:'th', dateFormat:'d/m/Y', allowInput:true,
      defaultDate: initDate || new Date(),
      onReady: function(sd,ds,inst){ if(initDate){var d=initDate;inst.element.value=('0'+d.getDate()).slice(-2)+'/'+('0'+(d.getMonth()+1)).slice(-2)+'/'+(d.getFullYear()+543);} },
      onChange: function(sd,ds,inst){ if(!sd.length)return;var d=sd[0];inst.element.value=('0'+d.getDate()).slice(-2)+'/'+('0'+(d.getMonth()+1)).slice(-2)+'/'+(d.getFullYear()+543); },
      parseDate: function(s){ if(!s)return null;var p=s.split('/');if(p.length!==3)return null;var y=parseInt(p[2]);if(y>2500)y-=543;return new Date(y,parseInt(p[1])-1,parseInt(p[0])); }
    });
  }

  function openPaid(chequeId, payId) {
    Swal.fire({
      title:'บันทึกวันที่ตัดจ่าย',
      showConfirmButton:false, showCloseButton:true, width:'420px',
      html:
        '<div class="swal-form" style="padding:0 10px;">' +
          '<div class="form-group">' +
            '<label>วันที่ตัดจ่าย</label>' +
            '<input type="text" id="swal-datepaid" class="form-control" value="'+todayTh+'" required>' +
          '</div>' +
          '<div class="swal-btns">' +
            '<button class="btn btn-success" onclick="submitPaid(\''+chequeId+'\','+payId+')">' +
              '<span class="msi">check_circle</span> บันทึก</button>' +
            '<button class="btn btn-secondary" onclick="Swal.close()">' +
              '<span class="msi">cancel</span> ยกเลิก</button>' +
          '</div>' +
        '</div>',
      didOpen: function() { fpTH(document.getElementById('swal-datepaid')); }
    });
  }

  function submitPaid(chequeId, payId) {
    var datePaid = document.getElementById('swal-datepaid').value;
    if (!datePaid) {
      Swal.fire({ icon:'warning', title:'กรุณาระบุวันที่ตัดจ่าย', confirmButtonText:'เข้าใจ', confirmButtonColor:'#5b9bd5' });
      return;
    }
    Swal.fire({
      icon:'question', title:'ยืนยันการบันทึก?',
      html:'เช็ค <strong>'+chequeId+'</strong> วันที่จ่าย <strong>'+datePaid+'</strong>',
      showCancelButton:true,
      confirmButtonText:'บันทึก',
      cancelButtonText:'ยกเลิก',
      confirmButtonColor:'#00b050', cancelButtonColor:'#6b778c'
    }).then(function(r){
      if (r.isConfirmed) {
        Swal.fire({ title:'กำลังบันทึก...', allowOutsideClick:false, showConfirmButton:false, didOpen:function(){ Swal.showLoading(); }});
        window.location.href = 'addpaid.php?ChequeId='+encodeURIComponent(chequeId)+'&PayId='+payId+'&DatePaid='+encodeURIComponent(datePaid);
      }
    });
  }
  </script>
</body>
</html>