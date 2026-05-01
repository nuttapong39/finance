<?php
// cheque.php
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Bangkok');

ob_start();
require_once __DIR__ . '/header.php';
$__header_html = ob_get_clean();

require_once __DIR__ . '/connect_db.php';

/* ─── helpers ─── */
function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); }

function DateThaiShort($strDate) {
  if (!$strDate || $strDate === '0000-00-00') return '-';
  $ts = strtotime($strDate);
  if (!$ts) return '-';
  $y  = date('Y',$ts)+543;
  $m  = (int)date('n',$ts);
  $d  = (int)date('j',$ts);
  $mc = ["","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค."];
  return "$d&nbsp;&nbsp;{$mc[$m]}&nbsp;&nbsp;$y";
}

/* ─── inputs ─── */
$ID  = (string)($_REQUEST['ID'] ?? '');
$prv = (int)($_REQUEST['prv'] ?? 0);

/* ─── list query ─── */
$DateNows = date('Y-m-d');
if ($ID === '') {
  $sql = "SELECT * FROM payment WHERE DateApprove<>'' AND (DatePay='' OR DatePay='$DateNows') ORDER BY PayId DESC, DatePay ASC";
} else {
  $sql = "SELECT * FROM payment WHERE PayId='".mysqli_real_escape_string($conn,(string)$ID)."' AND DateApprove<>'' ORDER BY PayId DESC";
}
$result   = $conn->query($sql);
$rows     = $result ? $result->num_rows : 0;
$page_rows = 10;
$last     = max(1, (int)ceil($rows / $page_rows));
$pagenum  = max(1, min($last, (int)($_GET['pn'] ?? 1)));
$offset   = ($pagenum - 1) * $page_rows;

if ($ID === '') {
  $nquery = mysqli_query($conn,"SELECT * FROM payment WHERE DateApprove<>'' AND (DatePay='' OR DatePay='$DateNows') ORDER BY PayId DESC, DatePay ASC LIMIT $offset,$page_rows");
} else {
  $nquery = mysqli_query($conn,"SELECT * FROM payment WHERE PayId='".mysqli_real_escape_string($conn,(string)$ID)."' AND DateApprove<>'' ORDER BY PayId DESC LIMIT $offset,$page_rows");
}

/* ─── collect rows for rendering ─── */
$dataRows = [];
if ($nquery) {
  while ($row = $nquery->fetch_assoc()) {
    $cid = (int)($row['CompanyId'] ?? 0);
    $cname = '';
    if ($cid > 0) {
      $r1 = $conn->query("SELECT CompanyName FROM company WHERE CompanyId=$cid");
      if ($r1) { $tmp = $r1->fetch_assoc(); $cname = $tmp['CompanyName'] ?? ''; }
    }
    $row['_CompanyName'] = $cname;
    $dataRows[] = $row;
  }
}

/* ─── pagination ─── */
$pagination = '';
if ($last > 1) {
  $pagination = '<nav><ul class="pagination" style="margin:8px 0;">';
  if ($pagenum > 1) {
    $pagination .= '<li><a href="?pn='.($pagenum-1).'&ID='.h($ID).'">&laquo;</a></li>';
  }
  for ($i = max(1,$pagenum-2); $i <= min($last,$pagenum+2); $i++) {
    $act = ($i===$pagenum) ? ' class="active"' : '';
    $pagination .= '<li'.$act.'><a href="?pn='.$i.'&ID='.h($ID).'">'.$i.'</a></li>';
  }
  if ($pagenum < $last) {
    $pagination .= '<li><a href="?pn='.($pagenum+1).'&ID='.h($ID).'">&raquo;</a></li>';
  }
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
    body {
      font-family:'Sarabun',sans-serif;
      background:
        radial-gradient(1100px 600px at 12% 15%,rgba(91,155,213,.22),transparent 60%),
        radial-gradient(900px 520px at 92% 10%,rgba(0,176,80,.14),transparent 55%),
        linear-gradient(180deg,#f8fbff,#f6f8fc);
    }
    .container { max-width:1200px; }
    .page-titlebar {
      margin:14px 0 16px; border-radius:18px; padding:14px 16px;
      background:rgba(255,255,255,.88); border:1px solid #e9eef6;
      box-shadow:0 12px 30px rgba(13,27,62,.08); text-align:center;
    }
    .page-titlebar h3 { margin:2px 0 0; font-weight:800; color:#1f2a44; font-size:20px; }
    .page-titlebar .sub { color:#6b778c; margin-top:6px; font-size:13px; }
    .card-panel {
      border-radius:18px; border:1px solid #e9eef6;
      background:rgba(255,255,255,.92); box-shadow:0 12px 30px rgba(13,27,62,.08);
      overflow:hidden; margin-bottom:14px;
    }
    .card-head {
      padding:12px 14px; border-bottom:1px solid #e9eef6;
      font-weight:800; color:#1f2a44;
      background:linear-gradient(135deg,rgba(0,176,80,.16),rgba(0,176,80,.06));
    }
    .card-body { padding:14px 16px; }
    .form-control {
      height:42px; border-radius:12px; border:1px solid #dfe7f3;
      box-shadow:none; font-family:'Sarabun',sans-serif; font-size:14px;
      transition:border-color .2s,box-shadow .2s;
    }
    .form-control:focus { border-color:rgba(0,176,80,.55); box-shadow:0 0 0 3px rgba(0,176,80,.18); }
    select.form-control {
      appearance:none; -webkit-appearance:none;
      background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b778c' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
      background-repeat:no-repeat; background-position:right 12px center; padding-right:36px;
    }
    .btn { border-radius:12px; font-weight:700; font-family:'Sarabun',sans-serif; padding:9px 14px; transition:transform .15s,box-shadow .15s; }
    .btn:active { transform:scale(.96); }
    .btn-success { box-shadow:0 10px 22px rgba(0,176,80,.18); }
    .btn-action { width:36px; height:36px; padding:0; display:inline-flex; align-items:center; justify-content:center; }
    .btn-action:hover { transform:translateY(-1px); }
    .table { background:#fff; border-radius:14px; overflow:hidden; margin-bottom:8px; }
    .table thead th { background:#e9f7ee; color:#1f2a44; font-weight:800; border-bottom:1px solid #d6f0df !important; vertical-align:middle !important; }
    .table td { vertical-align:middle !important; }
    /* ── Row Hover ── */
    .table tbody tr { transition:background .18s ease,transform .18s ease,box-shadow .18s ease; }
    .table tbody tr:hover {
      background:linear-gradient(90deg,#eef7ff,#f4f9ff) !important;
      transform:scale(1.005); box-shadow:0 4px 14px rgba(0,176,80,.22);
      position:relative; z-index:2;
    }
    .table tbody tr:hover td:first-child { border-left:3px solid #00b050; border-radius:8px 0 0 8px; }
    .table tbody tr:hover td:last-child { border-radius:0 8px 8px 0; }
    .badge-pill { display:inline-block; padding:6px 10px; border-radius:999px; font-weight:800; font-size:12px; border:1px solid #e2e8f0; background:#f8fafc; color:#334155; transition:transform .15s; }
    .bd-wait { background:#fff7ed; border-color:#fed7aa; color:#9a3412; }
    .bd-ok   { background:#f0fdf4; border-color:#bbf7d0; color:#166534; }
    .pagination>li>a,.pagination>li>span { border-radius:10px !important; margin:0 4px; border:1px solid #e2e8f0; color:#1f2a44; }
    .pagination>.active>a { background:#00b050; border-color:#00b050; }
    .pagination>li>a:hover { background:#d6f0df; border-color:#00b050; }
    /* SweetAlert */
    .swal2-popup { border-radius:20px; font-family:'Sarabun',sans-serif; }
    .swal2-title { color:#1f2a44; font-weight:800; }
    .swal2-confirm,.swal2-cancel { border-radius:12px !important; font-weight:700; }
    .swal-form .form-group { margin-bottom:14px; text-align:left; }
    .swal-form label { font-weight:700; color:#1f2a44; font-size:13px; display:block; margin-bottom:5px; }
    .swal-form input[type="text"],.swal-form select {
      width:100%; height:40px; border-radius:10px; border:1px solid #dfe7f3;
      font-family:'Sarabun',sans-serif; font-size:14px; padding:0 12px; box-sizing:border-box;
    }
    .swal-form input:focus,.swal-form select:focus { outline:none; border-color:#00b050; box-shadow:0 0 0 3px rgba(0,176,80,.18); }
    .swal-form .radio-row { display:flex; align-items:center; gap:10px; margin-top:6px; }
    .swal-form .radio-row input[type="radio"] { accent-color:#00b050; width:16px; height:16px; cursor:pointer; }
    .swal-form .radio-row label { margin:0; font-weight:600; cursor:pointer; }
    .swal-btns { display:flex; gap:10px; justify-content:center; margin-top:20px; }
    /* datepicker inside swal */
    .ui-datepicker { z-index:10000 !important; font-family:'Sarabun',sans-serif; font-size:13px; }
  </style>
</head>
<body>
  <?php echo $__header_html; ?>
  <div class="container">
    <div class="page-titlebar" style="display:flex; align-items:center; justify-content:space-between; gap:12px; text-align:left;">
      <div>
        <h3 style="margin:0; display:flex; align-items:center; gap:8px;">
          <span class="msi msi-24" style="color:#0B6E4F;">credit_card</span> จัดทำเช็ค
        </h3>
        <div class="sub">ค้นหาและจัดทำเช็คจากรายการที่ได้รับอนุมัติ</div>
      </div>
      <a href="main.php" class="btn-go-back"><span class="msi">arrow_back</span> กลับหน้าหลัก</a>
    </div>

    <!-- Tabs -->
    <div class="card-panel">
      <div class="card-body" style="padding-bottom:0;">
        <ul class="nav-tabs-modern">
          <li><a href="receive.php"><span class="msi">inbox</span> ลงรับเอกสาร</a></li>
          <li><a href="finance.php"><span class="msi">check_circle</span> ขออนุมัติ</a></li>
          <li class="active"><a href="cheque.php"><span class="msi">credit_card</span> จัดทำเช็ค</a></li>
          <li><a href="printcheque.php"><span class="msi">print</span> พิมพ์เช็ค</a></li>
          <li><a href="control.php"><span class="msi">menu_book</span> ทะเบียนคุม</a></li>
          <li><a href="paidment.php"><span class="msi">book</span> ใบสำคัญ</a></li>
          <li><a href="paid.php"><span class="msi">task_alt</span> ตัดจ่ายเช็ค</a></li>
          <li><a href="daily.php"><span class="msi">calendar_today</span> รายงานประจำวัน</a></li>
          <li><a href="findpay.php"><span class="msi">search</span> ค้นหารายการ</a></li>
        </ul>
      </div>
    </div>

    <!-- Search -->
    <div class="card-panel">
      <div class="card-head"><span class="msi">search</span> ค้นหারายการจัดทำเช็ค</div>
      <div class="card-body">
        <form method="get" action="<?= h($_SERVER['PHP_SELF']) ?>">
          <div class="row" style="margin:0;">
            <div class="col-md-8" style="padding-left:0;">
              <label style="font-weight:800;color:#1f2a44;">รหัสรายการ</label>
              <input class="form-control" name="ID" type="text" value="<?= h($ID) ?>" placeholder="ระบุรหัสรายการ...">
            </div>
            <div class="col-md-2" style="padding-right:0; padding-top:26px;">
              <button type="submit" class="btn btn-success" style="width:100%;"><span class="msi">search</span> ค้นหา</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Table -->
    <div class="card-panel">
      <div class="card-head">
        <span class="msi">credit_card</span> รายการจัดทำเช็ค
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
                  <th style="text-align:center;">รหัสรายการ</th>
                  <th style="text-align:center;">วันที่อนุมัติ</th>
                  <th style="text-align:center;">เลขที่ใบส่งของ</th>
                  <th>รายการ/บริษัท</th>
                  <th style="text-align:right;">ยอดสุทธิ</th>
                  <th style="text-align:center;">วันที่จัดทำ</th>
                  <th style="text-align:center;">เลขที่เช็ค</th>
                  <th style="text-align:center;">จัดทำเช็ค</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($dataRows as $row): ?>
                <?php
                  $PayId        = (int)($row['PayId'] ?? 0);
                  $DateApprove  = (string)($row['DateApprove'] ?? '');
                  $Detail       = (string)($row['Detail'] ?? '');
                  $CompanyName  = (string)($row['_CompanyName'] ?? '');
                  $Net          = (float)($row['Net'] ?? 0);
                  $DatePays     = (string)($row['DatePay'] ?? '');
                  $Cheque       = (string)($row['Cheque'] ?? '');

                  if ($DatePays === '' || $DatePays === '0000-00-00') {
                    $DatePayDisp = '<span class="badge-pill bd-wait">รอจัดทำเช็ค</span>';
                    $ChequeDisp  = '-';
                  } else {
                    $DatePayDisp = DateThaiShort($DatePays);
                    $ChequeDisp  = ($Cheque === '') ? 'ไม่จัดทำเช็ค' : h($Cheque);
                  }
                ?>
                <tr>
                  <td style="text-align:center;"><?= $PayId ?></td>
                  <td style="text-align:center;"><?= DateThaiShort($DateApprove) ?></td>
                  <td style="text-align:center;"><?= h($Detail) ?></td>
                  <td><?= h($CompanyName) ?></td>
                  <td style="text-align:right;"><?= number_format($Net, 2) ?></td>
                  <td style="text-align:center;"><?= $DatePayDisp ?></td>
                  <td style="text-align:center;"><?= $ChequeDisp ?></td>
                  <td style="text-align:center;">
                    <button type="button" class="btn btn-success btn-action"
                      onclick="openCheque(<?= $PayId ?>)"
                      title="จัดทำเช็ค">
                      <span class="msi">edit</span>
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
  var prvVal = <?= (int)$prv ?>;
  var todayTh = (function(){
    var d=new Date();
    var y=d.getFullYear()+543;
    var m=String(d.getMonth()+1).padStart(2,'0');
    var dd=String(d.getDate()).padStart(2,'0');
    return dd+'/'+m+'/'+y;
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

  $(function(){
    // prv alerts as SweetAlert
    var msgs = {
      1: { icon:'error',  title:'บันทึกไม่สำเร็จ',  text:'เลขที่เช็คซ้ำ กรุณาระบุเลขที่เช็คอีกครั้ง' },
      2: { icon:'warning',title:'บันทึกสำเร็จ',     text:'เช็คนี้ถูกใช้ร่วมกับรายการอื่นๆ หากไม่ต้องการรวม กรุณาตรวจสอบ' },
      3: { icon:'success',title:'บันทึกสำเร็จ',     text:'บันทึกรายการจัดทำเช็คเรียบร้อย' }
    };
    if (msgs[prvVal]) {
      Swal.fire({ icon:msgs[prvVal].icon, title:msgs[prvVal].title, text:msgs[prvVal].text, confirmButtonText:'ตกลง', confirmButtonColor:'#00b050' });
    }
  });

  /* ─── จัดทำเช็ค ─── */
  function openCheque(payId) {
    Swal.fire({
      title:'บันทึกข้อมูลจัดทำเช็ค',
      showConfirmButton:false, showCloseButton:true, width:'460px',
      html:
        '<div class="swal-form" style="padding:0 10px;">' +
          '<div class="form-group">' +
            '<label>วันที่จัดทำรายการ</label>' +
            '<input type="text" id="swal-datepay" class="form-control" value="'+todayTh+'" required>' +
          '</div>' +
          '<div class="form-group">' +
            '<label>เลขที่เช็ค</label>' +
            '<div class="radio-row" style="margin-bottom:8px;">' +
              '<input type="radio" name="swal-active" id="swal-act1" value="1" checked>' +
              '<label for="swal-act1">จัดทำเช็ค</label>' +
            '</div>' +
            '<input type="text" id="swal-cheque" class="form-control" placeholder="จัดทำเช็คเลขที่">' +
            '<div class="radio-row" style="margin-top:8px;">' +
              '<input type="radio" name="swal-active" id="swal-act0" value="0">' +
              '<label for="swal-act0">ไม่จัดทำเช็ค</label>' +
            '</div>' +
          '</div>' +
          '<div class="form-group">' +
            '<label>หมายเหตุ</label>' +
            '<input type="text" id="swal-comment" class="form-control">' +
          '</div>' +
          '<div class="swal-btns">' +
            '<button class="btn btn-success" onclick="submitCheque('+payId+')">' +
              '<span class="msi">check_circle</span> บันทึก</button>' +
            '<button class="btn btn-secondary" onclick="Swal.close()">' +
              '<span class="msi">cancel</span> ยกเลิก</button>' +
          '</div>' +
        '</div>',
      didOpen: function() {
        fpTH(document.getElementById('swal-datepay'));
        // toggle cheque input based on radio
        $('input[name="swal-active"]').on('change', function(){
          $('#swal-cheque').prop('disabled', $(this).val()==='0');
        });
      }
    });
  }

  function submitCheque(payId) {
    var datePay = document.getElementById('swal-datepay').value;
    var active  = document.querySelector('input[name="swal-active"]:checked').value;
    var cheque  = document.getElementById('swal-cheque').value.trim();
    var comment = document.getElementById('swal-comment').value.trim();

    if (!datePay) {
      Swal.fire({ icon:'warning', title:'กรุณาระบุวันที่จัดทำรายการ', confirmButtonText:'เข้าใจ', confirmButtonColor:'#5b9bd5' });
      return;
    }
    if (active === '1' && cheque === '') {
      Swal.fire({ icon:'warning', title:'กรุณาระบุเลขที่เช็ค', confirmButtonText:'เข้าใจ', confirmButtonColor:'#5b9bd5' });
      return;
    }

    Swal.fire({
      icon:'question', title:'ยืนยันการบันทึก?',
      html:'รายการ <strong>#'+payId+'</strong> เช็ค <strong>'+(active==='1'?cheque:'ไม่จัดทำเช็ค')+'</strong>',
      showCancelButton:true,
      confirmButtonText:'บันทึก',
      cancelButtonText:'ยกเลิก',
      confirmButtonColor:'#00b050', cancelButtonColor:'#6b778c'
    }).then(function(r){
      if (r.isConfirmed) {
        Swal.fire({ title:'กำลังบันทึก...', allowOutsideClick:false, showConfirmButton:false, didOpen:function(){ Swal.showLoading(); }});
        var url = 'addcheque.php?PayId='+payId+'&DatePay='+encodeURIComponent(datePay)+'&Cheque='+encodeURIComponent(cheque)+'&Comment='+encodeURIComponent(comment);
        if (active === '0') { url += '&active=0'; }
        window.location.href = url;
      }
    });
  }
  </script>
</body>
</html>