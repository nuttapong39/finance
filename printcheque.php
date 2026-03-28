<?php
// printcheque.php
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
$ID = (string)($_REQUEST['ID'] ?? '');

/* ─── query ─── */
$DateNows = date('Y-m-d');
if ($ID === '') {
  $sql = "SELECT * FROM cheque WHERE DatePrint='$DateNows' ORDER BY DatePrint DESC, ChequeId DESC";
} else {
  $sql = "SELECT * FROM cheque WHERE ChequeId='".mysqli_real_escape_string($conn,$ID)."' ORDER BY DatePrint DESC";
}
$result    = $conn->query($sql);
$rows      = $result ? $result->num_rows : 0;
$page_rows = 10;
$last      = max(1,(int)ceil($rows/$page_rows));
$pagenum   = max(1, min($last, (int)($_GET['pn'] ?? 1)));
$offset    = ($pagenum-1)*$page_rows;

if ($ID === '') {
  $nquery = mysqli_query($conn,"SELECT * FROM cheque WHERE DatePrint='$DateNows' ORDER BY DatePrint DESC, ChequeId DESC LIMIT $offset,$page_rows");
} else {
  $nquery = mysqli_query($conn,"SELECT * FROM cheque WHERE ChequeId='".mysqli_real_escape_string($conn,$ID)."' ORDER BY DatePrint DESC LIMIT $offset,$page_rows");
}

/* ─── collect rows ─── */
$dataRows = [];
if ($nquery) {
  while ($row = $nquery->fetch_assoc()) {
    $cid = (string)($row['ChequeId'] ?? '');
    // sum net
    $r1 = $conn->query("SELECT *,SUM(Net) as Sumnet FROM payment WHERE Cheque='".mysqli_real_escape_string($conn,$cid)."'");
    $row1 = $r1 ? $r1->fetch_assoc() : [];
    $row['_Sumnet']  = (float)($row1['Sumnet'] ?? 0);
    $row['_PayId']   = (int)($row1['PayId'] ?? 0);

    // companies linked to this cheque (for radio)
    $companies = [];
    $rc = $conn->query("SELECT DISTINCT p.CompanyId, c.CompanyName FROM payment p JOIN company c ON p.CompanyId=c.CompanyId WHERE p.Cheque='".mysqli_real_escape_string($conn,$cid)."'");
    if ($rc) { while ($rr = $rc->fetch_assoc()) $companies[] = $rr; }
    $row['_Companies'] = $companies;

    $dataRows[] = $row;
  }
}

/* ─── all companies (for select) ─── */
$allCompanies = [];
$rc = $conn->query("SELECT CompanyId, CompanyName FROM company ORDER BY CompanyName");
if ($rc) { while ($rr = $rc->fetch_assoc()) $allCompanies[] = $rr; }

/* ─── pagination ─── */
$pagination = '';
if ($last > 1) {
  $pagination = '<nav><ul class="pagination" style="margin:8px 0;">';
  if ($pagenum > 1) $pagination .= '<li><a href="?pn='.($pagenum-1).'&ID='.h($ID).'">&laquo;</a></li>';
  for ($i=max(1,$pagenum-2); $i<=min($last,$pagenum+2); $i++) {
    $act=($i===$pagenum)?' class="active"':'';
    $pagination .= '<li'.$act.'><a href="?pn='.$i.'&ID='.h($ID).'">'.$i.'</a></li>';
  }
  if ($pagenum < $last) $pagination .= '<li><a href="?pn='.($pagenum+1).'&ID='.h($ID).'">&raquo;</a></li>';
  $pagination .= '</ul></nav>';
}

/* ─── build company options HTML ─── */
$companyOptions = '';
foreach ($allCompanies as $c) {
  $companyOptions .= '<option value="'.h($c['CompanyName']).'">'.h($c['CompanyName']).'</option>';
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
    .pagination>li>a,.pagination>li>span { border-radius:10px !important; margin:0 4px; border:1px solid #e2e8f0; color:#1f2a44; }
    .pagination>.active>a { background:#00b050; border-color:#00b050; }
    .pagination>li>a:hover { background:#d6f0df; border-color:#00b050; }
    .swal2-popup { border-radius:20px; font-family:'Sarabun',sans-serif; }
    .swal2-title { color:#1f2a44; font-weight:800; }
    .swal2-confirm,.swal2-cancel { border-radius:12px !important; font-weight:700; }
    .swal-form .form-group { margin-bottom:14px; text-align:left; }
    .swal-form label { font-weight:700; color:#1f2a44; font-size:13px; display:block; margin-bottom:5px; }
    .swal-form input[type="text"],.swal-form select { width:100%; height:40px; border-radius:10px; border:1px solid #dfe7f3; font-family:'Sarabun',sans-serif; font-size:14px; padding:0 12px; box-sizing:border-box; }
    .swal-form input:focus,.swal-form select:focus { outline:none; border-color:#00b050; box-shadow:0 0 0 3px rgba(0,176,80,.18); }
    .swal-form .radio-row { display:flex; align-items:center; gap:8px; margin:6px 0; }
    .swal-form .radio-row input[type="radio"] { accent-color:#00b050; width:16px; height:16px; cursor:pointer; }
    .swal-form .radio-row label { margin:0; font-weight:600; cursor:pointer; }
    .swal-btns { display:flex; gap:10px; justify-content:center; margin-top:20px; }
    .cheque-bank-btn { width:44px; height:44px; padding:4px; border-radius:10px; border:1px solid #e2e8f0; background:#fff; transition:transform .15s,box-shadow .15s; cursor:pointer; }
    .cheque-bank-btn:hover { transform:translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,.12); }
    .cheque-bank-btn img { width:100%; height:100%; object-fit:contain; }
  </style>
</head>
<body>
  <?php echo $__header_html; ?>
  <div class="container">
    <div class="page-titlebar" style="display:flex; align-items:center; justify-content:space-between; gap:12px; text-align:left;">
      <div>
        <h3 style="margin:0; display:flex; align-items:center; gap:8px;">
          <span class="msi msi-24" style="color:#0B6E4F;">print</span> พิมพ์เช็ค
        </h3>
        <div class="sub">ค้นหาและพิมพ์เช็คจากรายการที่จัดทำวันนี้</div>
      </div>
      <a href="cheque.php" class="btn-go-back"><span class="msi">arrow_back</span> กลับ</a>
    </div>

    <!-- Tabs -->
    <div class="card-panel">
      <div class="card-body" style="padding-bottom:0;">
        <ul class="nav-tabs-modern">
          <li><a href="receive.php"><span class="msi">inbox</span> ลงรับเอกสาร</a></li>
          <li><a href="finance.php"><span class="msi">check_circle</span> ขออนุมัติ</a></li>
          <li><a href="cheque.php"><span class="msi">credit_card</span> จัดทำเช็ค</a></li>
          <li class="active"><a href="printcheque.php"><span class="msi">print</span> พิมพ์เช็ค</a></li>
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
      <div class="card-head"><span class="msi">search</span> ค้นหาเช็ค</div>
      <div class="card-body">
        <form method="get" action="<?= h($_SERVER['PHP_SELF']) ?>">
          <div class="row" style="margin:0;">
            <div class="col-md-8" style="padding-left:0;">
              <label style="font-weight:800;color:#1f2a44;">เลขที่เช็ค</label>
              <input class="form-control" name="ID" type="text" value="<?= h($ID) ?>" placeholder="ระบุเลขที่เช็ค...">
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
        <span class="msi">print</span> รายการรอพิมพ์เช็ค
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
                  <th style="text-align:center;" colspan="3">พิมพ์เช็ค</th>
                  <th style="text-align:center;">หนังสือแจ้งฯ</th>
                  <th style="text-align:center;">ผู้รับเช็ค</th>
                  <th style="text-align:center;">ลบเช็ค</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($dataRows as $row): ?>
                <?php
                  $ChequeId  = (string)($row['ChequeId'] ?? '');
                  $PayTo     = (string)($row['PayTo'] ?? '');
                  $DatePrint = (string)($row['DatePrint'] ?? '');
                  $Sumnet    = (float)($row['_Sumnet'] ?? 0);
                  $companies = $row['_Companies'];
                ?>
                <tr>
                  <td style="text-align:center;"><?= h($ChequeId) ?></td>
                  <td style="text-align:center;"><?= DateThaiShort($DatePrint) ?></td>
                  <td><?= h($PayTo) ?></td>
                  <td style="text-align:right;"><?= number_format($Sumnet, 2) ?></td>
                  <td style="text-align:center;">
                    <a href="cheque_gsb.php?ChequeId=<?= h($ChequeId) ?>&Sumnet=<?= $Sumnet ?>" target="_blank" class="cheque-bank-btn"><img src="pic/gsb.jpg" alt="GSB"></a>
                  </td>
                  <td style="text-align:center;">
                    <a href="cheque_ktb.php?ChequeId=<?= h($ChequeId) ?>&Sumnet=<?= $Sumnet ?>" target="_blank" class="cheque-bank-btn"><img src="pic/ktb.png" alt="KTB"></a>
                  </td>
                  <td style="text-align:center;">
                    <a href="cheque_baac.php?ChequeId=<?= h($ChequeId) ?>&Sumnet=<?= $Sumnet ?>" target="_blank" class="cheque-bank-btn"><img src="pic/baac.jpg" alt="BAAC"></a>
                  </td>
                  <td style="text-align:center;">
                    <a href="letter.php?ChequeId=<?= h($ChequeId) ?>" target="_blank" class="btn btn-success btn-action" title="พิมพ์หนังสือ"><span class="msi">print</span></a>
                  </td>
                  <td style="text-align:center;">
                    <button type="button" class="btn btn-warning btn-action"
                      onclick="openPayTo('<?= addslashes(h($ChequeId)) ?>','<?= $Sumnet ?>', <?= json_encode($companies) ?>)"
                      title="บันทึกผู้รับเช็ค">
                      <span class="msi">person</span>
                    </button>
                  </td>
                  <td style="text-align:center;">
                    <button type="button" class="btn btn-danger btn-action"
                      onclick="confirmDelete('<?= addslashes(h($ChequeId)) ?>')"
                      title="ลบเช็ค">
                      <span class="msi">delete</span>
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

  <script>
  var companyOptions = '<?= addslashes($companyOptions) ?>';

  /* ─── บันทึกผู้รับเช็ค ─── */
  function openPayTo(chequeId, sumnet, companies) {
    var radioHtml = '';
    companies.forEach(function(c){
      radioHtml += '<div class="radio-row"><input type="radio" name="swal-received" value="'+c.CompanyName+'"><label>'+c.CompanyName+'</label></div>';
    });

    Swal.fire({
      title:'บันทึกผู้รับเช็ค',
      showConfirmButton:false, showCloseButton:true, width:'500px',
      html:
        '<div class="swal-form" style="padding:0 10px;">' +
          '<div class="form-group">' +
            '<label>ใช้ข้อมูลผู้รับเช็คตามเอกสาร/ใบรับของ</label>' +
            radioHtml +
          '</div>' +
          '<div class="form-group">' +
            '<label>หรือเลือกผู้รับเช็ค</label>' +
            '<select id="swal-company" class="form-control"><option value="">-- เลือกผู้รับเช็ค --</option>' + companyOptions + '</select>' +
          '</div>' +
          '<div class="form-group">' +
            '<label>กรณีระบุผู้รับเช็คอื่นๆ</label>' +
            '<input type="text" id="swal-receiver" class="form-control">' +
          '</div>' +
          '<div class="swal-btns">' +
            '<button class="btn btn-success" onclick="submitPayTo(\''+chequeId+'\','+sumnet+')">' +
              '<span class="msi">check_circle</span> บันทึก</button>' +
            '<button class="btn btn-secondary" onclick="Swal.close()">' +
              '<span class="msi">cancel</span> ยกเลิก</button>' +
          '</div>' +
        '</div>'
    });
  }

  function submitPayTo(chequeId, sumnet) {
    // priority: radio > select > text
    var radio    = document.querySelector('input[name="swal-received"]:checked');
    var select   = document.getElementById('swal-company').value;
    var receiver = document.getElementById('swal-receiver').value.trim();

    var payTo = '';
    if (radio) payTo = radio.value;
    else if (select) payTo = select;
    else if (receiver) payTo = receiver;

    if (!payTo) {
      Swal.fire({ icon:'warning', title:'กรุณาเลือกผู้รับเช็ค', confirmButtonText:'เข้าใจ', confirmButtonColor:'#5b9bd5' });
      return;
    }

    Swal.fire({ title:'กำลังบันทึก...', allowOutsideClick:false, showConfirmButton:false, didOpen:function(){ Swal.showLoading(); }});
    var url = 'addpayto.php?ChequeId='+encodeURIComponent(chequeId)+'&Sumnet='+sumnet;
    if (radio) { url += '&received='+encodeURIComponent(payTo); }
    else if (select) { url += '&CompanyName='+encodeURIComponent(payTo); }
    else { url += '&receiver='+encodeURIComponent(payTo); }
    window.location.href = url;
  }

  /* ─── ลบเช็ค ─── */
  function confirmDelete(chequeId) {
    Swal.fire({
      icon:'warning',
      title:'ลบรายการเช็ค',
      html:'ท่านต้องการลบเช็คเลขที่ <strong>'+chequeId+'</strong> หรือไม่?<br><span style="color:#ef4444;font-size:13px;">**หากลบแล้วต้องจัดทำเช็คใหม่อีกครั้ง</span>',
      showCancelButton:true,
      confirmButtonText:'ลบ',
      cancelButtonText:'ยกเลิก',
      confirmButtonColor:'#ef4444', cancelButtonColor:'#6b778c'
    }).then(function(r){
      if (r.isConfirmed) {
        Swal.fire({ title:'กำลังลบ...', allowOutsideClick:false, showConfirmButton:false, didOpen:function(){ Swal.showLoading(); }});
        window.location.href = 'deletecheque.php?ChequeId='+encodeURIComponent(chequeId);
      }
    });
  }
  </script>
</body>
</html>