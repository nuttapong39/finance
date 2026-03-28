<?php
// control.php
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Bangkok');

ob_start();
require_once __DIR__ . '/header.php';
$__header_html = ob_get_clean();
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
    .btn { border-radius:12px; font-weight:700; font-family:'Sarabun',sans-serif; padding:9px 14px; transition:transform .15s,box-shadow .15s; }
    .btn:active { transform:scale(.96); }
    .btn-success { box-shadow:0 10px 22px rgba(0,176,80,.18); }
    .btn-report {
      display:block; width:100%; max-width:400px; margin:10px auto;
      padding:16px 20px; border-radius:16px; border:1px solid #d6f0df;
      background:linear-gradient(135deg,#f0fdf4,#e8f5e9); color:#166534;
      font-weight:800; font-size:15px; text-align:center; cursor:pointer;
      transition:transform .18s,box-shadow .18s,background .18s;
    }
    .btn-report:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,176,80,.22); background:linear-gradient(135deg,#e8f5e9,#c8e6c9); }
    .btn-report:active { transform:scale(.97); }
    .btn-report .glyphicon { margin-right:8px; }
    /* SweetAlert */
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
          <span class="msi msi-24" style="color:#0B6E4F;">tune</span> ทะเบียนคุมเช็ค
        </h3>
        <div class="sub">พิมพ์รายงานทะเบียนคุมเช็ค</div>
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
          <li class="active"><a href="control.php"><span class="msi">menu_book</span> ทะเบียนคุม</a></li>
          <li><a href="paidment.php"><span class="msi">book</span> ใบสำคัญ</a></li>
          <li><a href="paid.php"><span class="msi">task_alt</span> ตัดจ่ายเช็ค</a></li>
          <li><a href="daily.php"><span class="msi">calendar_today</span> รายงานประจำวัน</a></li>
          <li><a href="findpay.php"><span class="msi">search</span> ค้นหารายการ</a></li>
        </ul>
      </div>
    </div>

    <!-- Action Card -->
    <div class="card-panel">
      <div class="card-head"><span class="msi">menu_book</span> รายงานทะเบียนคุมเช็ค</div>
      <div class="card-body" style="padding:30px 16px;">
        <div class="btn-report" onclick="openReport()">
          <span class="msi">print</span> พิมพ์รายงานทะเบียนคุมเช็ค
        </div>
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

  function openReport() {
    Swal.fire({
      title:'พิมพ์รายงานทะเบียนคุมเช็ค',
      showConfirmButton:false, showCloseButton:true, width:'420px',
      html:
        '<div class="swal-form" style="padding:0 10px;">' +
          '<div class="form-group">' +
            '<label>วันที่</label>' +
            '<input type="text" id="swal-datereport" class="form-control" value="'+todayTh+'">' +
          '</div>' +
          '<div class="swal-btns">' +
            '<button class="btn btn-success" onclick="submitReport()">' +
              '<span class="msi">print</span> พิมพ์</button>' +
            '<button class="btn btn-secondary" onclick="Swal.close()">' +
              '<span class="msi">cancel</span> ยกเลิก</button>' +
          '</div>' +
        '</div>',
      didOpen: function() { fpTH(document.getElementById('swal-datereport')); }
    });
  }

  function submitReport() {
    var dateReport = document.getElementById('swal-datereport').value.trim();
    if (!dateReport) {
      Swal.fire({ icon:'warning', title:'กรุณาระบุวันที่', confirmButtonText:'เข้าใจ', confirmButtonColor:'#5b9bd5' });
      return;
    }
    window.open('report1.php?Datereport='+encodeURIComponent(dateReport), '_blank');
    Swal.close();
  }
  </script>
</body>
</html>
