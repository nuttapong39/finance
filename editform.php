<?php
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Bangkok');

require_once __DIR__ . '/connect_db.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['Username'])) {
  header("Location: login.php");
  exit;
}

$PayId = (int)($_GET['PayId'] ?? 0);
if ($PayId <= 0) {
  header("Location: accounting.php");
  exit;
}

/* ========= Helpers ========= */
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function ymd_to_th_dmy($ymd){
  if (!$ymd || $ymd === '0000-00-00') return '';
  $p = explode('-', $ymd);
  if (count($p) !== 3) return '';
  return sprintf('%02d/%02d/%04d', (int)$p[2], (int)$p[1], (int)$p[0] + 543);
}

/* ========= Load payment ========= */
$stmt = $conn->prepare("SELECT * FROM payment WHERE PayId=? LIMIT 1");
$stmt->bind_param("i", $PayId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
  header("Location: accounting.php");
  exit;
}

$DateInTH  = ymd_to_th_dmy($row['DateIn'] ?? '');
$DeptId    = (int)($row['DeptId']    ?? 0);
$TypesId   = (int)($row['TypesId']   ?? 0);
$PlanPayId = (int)($row['PlanPayId'] ?? 0);
$CompanyId = (int)($row['CompanyId'] ?? 0);
$Source    = (int)($row['Source']    ?? 1);
$NumList   = (int)($row['NumList']   ?? 0);
$Detail    = (string)($row['Detail'] ?? '');
$Price     = (float)($row['Price']   ?? 0);
$Vat       = (float)($row['Vat']     ?? 0);
$Amount    = (float)($row['Amount']  ?? 0);

/* ========= Pre-load Dropdowns ========= */
$deptRows = [];
$s = $conn->prepare("SELECT DeptId, DeptName FROM department ORDER BY DeptId");
$s->execute();
$res = $s->get_result();
while ($r = $res->fetch_assoc()) { $deptRows[] = $r; }
$s->close();

$typesList = [];
$s = $conn->prepare("SELECT TypesId, TypesName FROM types ORDER BY TypesId");
$s->execute();
$res = $s->get_result();
while ($r = $res->fetch_assoc()) { $typesList[] = $r; }
$s->close();

$planpayList = [];
$s = $conn->prepare("SELECT PlanPayId, PlanPayName FROM planpay ORDER BY PlanPayId");
$s->execute();
$res = $s->get_result();
while ($r = $res->fetch_assoc()) { $planpayList[] = $r; }
$s->close();

$companyList = [];
$s = $conn->prepare("SELECT CompanyId, CompanyName FROM company ORDER BY CompanyName");
$s->execute();
$res = $s->get_result();
while ($r = $res->fetch_assoc()) { $companyList[] = $r; }
$s->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" type="image/x-icon" href="pic/fms.png"/>
  <title>แก้ไขรายการเจ้าหนี้การค้า</title>

  <!-- Bootstrap Icons & Flatpickr (Bootstrap5, SweetAlert, theme loaded by header.php) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">

  <style>
    body {
      font-family: 'Sarabun', sans-serif;
      background:
        radial-gradient(1100px 600px at 12% 15%, rgba(91,155,213,.22), transparent 60%),
        radial-gradient(900px 520px at 92% 10%, rgba(0,176,80,.14), transparent 55%),
        linear-gradient(180deg, #f8fbff, #f6f8fc);
    }
    .container { max-width: 900px; }

    .page-titlebar {
      margin: 14px 0 16px;
      border-radius: 18px;
      padding: 14px 20px;
      background: rgba(255,255,255,.88);
      border: 1px solid #e9eef6;
      box-shadow: 0 12px 30px rgba(13,27,62,.08);
    }
    .page-titlebar h3 {
      margin: 2px 0 0;
      font-weight: 800;
      color: #1f2a44;
      font-size: 20px;
    }
    .page-titlebar .sub {
      color: #6b778c;
      margin-top: 6px;
      font-size: 13px;
    }
    .badge-payid {
      display: inline-block;
      background: #eef5ff;
      border: 1px solid #bfdbfe;
      color: #1d4ed8;
      border-radius: 999px;
      padding: 2px 12px;
      font-weight: 700;
      font-size: 13px;
    }

    .card-panel {
      border-radius: 18px;
      border: 1px solid #e9eef6;
      background: rgba(255,255,255,.92);
      box-shadow: 0 12px 30px rgba(13,27,62,.08);
      overflow: hidden;
      margin-bottom: 14px;
    }
    .card-head {
      padding: 12px 14px;
      border-bottom: 1px solid #e9eef6;
      font-weight: 800;
      color: #1f2a44;
      background: linear-gradient(135deg, rgba(91,155,213,.16), rgba(91,155,213,.06));
    }
    .card-body { padding: 20px 20px 14px; }

    .form-control {
      height: 42px;
      border-radius: 12px;
      border: 1px solid #dfe7f3;
      box-shadow: none;
      font-family: 'Sarabun', sans-serif;
      font-size: 14px;
      transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus {
      border-color: rgba(91,155,213,.65);
      box-shadow: 0 0 0 3px rgba(91,155,213,.18);
    }
    select.form-control {
      appearance: none;
      -webkit-appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b778c' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
      padding-right: 36px;
    }
    .form-control[readonly] {
      background: #f8fafc;
      color: #64748b;
    }

    .field-label {
      font-weight: 700;
      color: #1f2a44;
      font-size: 13px;
      margin-bottom: 6px;
      display: block;
    }

    .btn {
      border-radius: 12px;
      font-weight: 700;
      font-family: 'Sarabun', sans-serif;
      padding: 10px 18px;
      transition: transform .15s, box-shadow .15s;
    }
    .btn:active { transform: scale(.96); }
    .btn-primary { box-shadow: 0 10px 22px rgba(13,110,253,.18); }
    .btn-default { border: 1px solid #dfe7f3; }

    .form-row { margin-bottom: 18px; }

    .radio-group {
      padding-top: 10px;
      display: flex;
      gap: 24px;
    }
    .radio-group label {
      font-weight: 600;
      color: #334155;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .radio-group input[type="radio"] {
      accent-color: #5b9bd5;
      width: 16px;
      height: 16px;
      cursor: pointer;
    }

    .section-divider {
      border: none;
      border-top: 1px solid #e9eef6;
      margin: 22px 0 18px;
    }

    .btn-actions {
      text-align: center;
      margin-top: 24px;
      display: flex;
      justify-content: center;
      gap: 10px;
      flex-wrap: wrap;
    }

    .swal2-popup         { border-radius: 20px; font-family: 'Sarabun', sans-serif; }
    .swal2-title         { color: #1f2a44; font-weight: 800; }
    .swal2-confirm       { border-radius: 12px !important; font-weight: 700; }
    .swal2-cancel        { border-radius: 12px !important; font-weight: 700; }
  </style>
</head>

<body>
  <?php include __DIR__ . '/header.php'; ?>

  <div class="container">
    <div class="page-titlebar" style="display:flex; align-items:center; justify-content:space-between; gap:12px; text-align:left;">
      <div>
        <h3 style="margin:0; display:flex; align-items:center; gap:8px;">
          <span class="msi msi-24" style="color:#0B6E4F;">edit_document</span> แก้ไขรายการเจ้าหนี้การค้า / รายการสั่งจ่าย
        </h3>
        <div class="sub">รหัสรายการ <span class="badge-payid">#<?php echo $PayId; ?></span></div>
      </div>
      <a href="accounting.php" class="btn-go-back"><span class="msi">arrow_back</span> กลับ</a>
    </div>

    <div class="card-panel">
      <div class="card-head">
        <i class="bi bi-pencil"></i> ฟอร์มแก้ไขรายการ
      </div>

      <div class="card-body">
        <form id="editForm" method="post" action="edit.php">
          <input type="hidden" name="PayId" value="<?php echo $PayId; ?>">

          <!-- กลุ่มงาน + วันที่ -->
          <div class="row form-row">
            <div class="col-md-6">
              <label class="field-label">กลุ่มงานที่ส่งหลักฐาน</label>
              <select class="form-control" name="Dept" id="Dept">
                <option value="">-- เลือกกลุ่มงาน/งาน --</option>
                <?php foreach ($deptRows as $d): ?>
                  <option value="<?php echo h($d['DeptId']); ?>" <?php echo ((int)$d['DeptId'] === $DeptId ? 'selected' : ''); ?>>
                    <?php echo h($d['DeptName']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="field-label">วันที่รับเอกสาร</label>
              <input class="form-control" type="text" name="DateIn" id="DateIn"
                value="<?php echo h($DateInTH); ?>" placeholder="เช่น 29/12/2568">
            </div>
          </div>

          <!-- หมวด + แผน -->
          <div class="row form-row">
            <div class="col-md-6">
              <label class="field-label">หมวด</label>
              <select class="form-control" name="Types" id="Types">
                <option value="">-- เลือกหมวด --</option>
                <?php foreach ($typesList as $t): ?>
                  <option value="<?php echo h($t['TypesId']); ?>" <?php echo ((int)$t['TypesId'] === $TypesId ? 'selected' : ''); ?>>
                    <?php echo h($t['TypesId'] . '. ' . $t['TypesName']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="field-label">ตัดจ่ายจากแผน</label>
              <select class="form-control" name="PlanPay" id="PlanPay">
                <option value="">-- เลือกแผน --</option>
                <?php foreach ($planpayList as $p): ?>
                  <option value="<?php echo h($p['PlanPayId']); ?>" <?php echo ((int)$p['PlanPayId'] === $PlanPayId ? 'selected' : ''); ?>>
                    <?php echo h($p['PlanPayId'] . '. ' . $p['PlanPayName']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- บริษัท + ใบส่งของ -->
          <div class="row form-row">
            <div class="col-md-6">
              <label class="field-label">รายการ/บริษัท</label>
              <select class="form-control" name="Company" id="Company">
                <option value="">-- เลือกบริษัท/ร้านค้า --</option>
                <?php foreach ($companyList as $c): ?>
                  <option value="<?php echo h($c['CompanyId']); ?>" <?php echo ((int)$c['CompanyId'] === $CompanyId ? 'selected' : ''); ?>>
                    <?php echo h($c['CompanyName']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="field-label">เลขที่ใบส่งของ</label>
              <input class="form-control" type="text" name="Detail" id="Detail"
                value="<?php echo h($Detail); ?>" placeholder="กรอกเลขที่ใบส่งของ">
            </div>
          </div>

          <hr class="section-divider">

          <!-- จำนวน + เงิน + แหล่งเงิน -->
          <div class="row form-row">
            <div class="col-md-3">
              <label class="field-label">จำนวน (รายการ)</label>
              <input class="form-control" type="number" name="NumList" id="NumList"
                value="<?php echo $NumList; ?>" min="0" placeholder="0">
            </div>

            <div class="col-md-3">
              <label class="field-label">จำนวนเงิน (บาท)</label>
              <input class="form-control" type="text" name="Price" id="Price"
                value="<?php echo number_format($Price, 2, '.', ''); ?>" placeholder="0.00">
            </div>

            <div class="col-md-6">
              <label class="field-label">แหล่งเงิน</label>
              <div class="radio-group">
                <label>
                  <input type="radio" name="source" value="1" <?php echo ($Source === 1 ? 'checked' : ''); ?>> เงินบำรุง
                </label>
                <label>
                  <input type="radio" name="source" value="2" <?php echo ($Source === 2 ? 'checked' : ''); ?>> งบประมาณ
                </label>
              </div>
            </div>
          </div>

          <!-- VAT -->
          <div class="row form-row">
            <div class="col-md-4">
              <label class="field-label">VAT</label>
              <div class="radio-group">
                <label>
                  <input type="radio" name="vatOption" id="vatOn" value="on" <?php echo ($Vat > 0 ? 'checked' : ''); ?>> คำนวณ VAT 7%
                </label>
                <label>
                  <input type="radio" name="vatOption" id="vatOff" value="off" <?php echo ($Vat <= 0 ? 'checked' : ''); ?>> ไม่คำนวณ
                </label>
              </div>
            </div>

            <div class="col-md-4">
              <label class="field-label">VAT (บาท)</label>
              <input class="form-control" type="text" name="Vat" id="Vat"
                value="<?php echo number_format($Vat, 2, '.', ''); ?>" readonly>
            </div>

            <div class="col-md-4">
              <label class="field-label">รวม VAT (บาท)</label>
              <input class="form-control" type="text" name="Amount" id="Amount"
                value="<?php echo number_format($Amount, 2, '.', ''); ?>" readonly>
            </div>
          </div>

          <!-- Buttons -->
          <div class="btn-actions">
            <button type="button" class="btn btn-primary" id="btnSave">
              <i class="bi bi-floppy2"></i> บันทึกการแก้ไข
            </button>
            <button type="button" class="btn btn-secondary" id="btnReset">
              <i class="bi bi-arrow-counterclockwise"></i> คืนค่าเดิม
            </button>
            <a href="accounting.php" class="btn btn-secondary">
              <i class="bi bi-list-ul"></i> กลับหน้ารายการ
            </a>
          </div>
        </form>
      </div>
    </div>

    <div style="height:16px;"></div>
  </div>

  <!-- jQuery, Bootstrap 5 JS, SweetAlert loaded by header.php -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

  <script>
  $(function(){
    // Thai Buddhist Flatpickr
    (function initThaiDP(selector) {
      document.querySelectorAll(selector).forEach(function(el) {
        var iv = el.value, initDate = null;
        if (iv) { var p=iv.split('/'); if(p.length===3){var y=parseInt(p[2]);if(y>2500)y-=543;initDate=new Date(y,parseInt(p[1])-1,parseInt(p[0]));} }
        flatpickr(el, { locale:'th', dateFormat:'d/m/Y', defaultDate:initDate||undefined, disableMobile:false, allowInput:true,
          onReady:function(sd,ds,inst){if(initDate){var d=initDate;inst.element.value=String(d.getDate()).padStart(2,'0')+'/'+String(d.getMonth()+1).padStart(2,'0')+'/'+(d.getFullYear()+543);}},
          onChange:function(sd,ds,inst){if(sd[0]){var d=sd[0];inst.element.value=String(d.getDate()).padStart(2,'0')+'/'+String(d.getMonth()+1).padStart(2,'0')+'/'+(d.getFullYear()+543);}},
          parseDate:function(s){if(!s)return null;var p=s.split('/');if(p.length!==3)return null;var y=parseInt(p[2]);if(y>2500)y-=543;return new Date(y,parseInt(p[1])-1,parseInt(p[0]));}
        });
      });
    })('#DateIn');

    // ── VAT Calc ──
    function recalcVat() {
      var price = parseFloat($('#Price').val().replace(/,/g,'')) || 0;
      var useVat = $('#vatOn').is(':checked');
      var vat    = useVat ? +(price * 7 / 100).toFixed(2) : 0;
      var total  = +(price + vat).toFixed(2);
      $('#Vat').val(vat.toFixed(2));
      $('#Amount').val(total.toFixed(2));
    }
    $('#Price').on('input', recalcVat);
    $('input[name="vatOption"]').on('change', recalcVat);

    // ── Save ──
    $('#btnSave').on('click', function(){
      var checks = [
        { el: '#Dept',    msg: 'กลุ่มงานที่ส่งหลักฐาน' },
        { el: '#DateIn',  msg: 'วันที่รับเอกสาร' },
        { el: '#Types',   msg: 'หมวด' },
        { el: '#PlanPay', msg: 'แผนที่ตัดจ่าย' },
        { el: '#Company', msg: 'บริษัท/ร้านค้า' },
        { el: '#Detail',  msg: 'เลขที่ใบส่งของ' },
        { el: '#NumList', msg: 'จำนวน' },
        { el: '#Price',   msg: 'จำนวนเงิน' }
      ];

      for (var i = 0; i < checks.length; i++) {
        var val = $(checks[i].el).val();
        if (!val || val.toString().trim() === '') {
          (function(check){
            Swal.fire({
              icon: 'warning',
              title: 'กรุณาตรวจสอบข้อมูล',
              html: 'กรุณากรอก <strong>' + check.msg + '</strong> ให้ครบถ้วน',
              confirmButtonText: 'เข้าใจ',
              confirmButtonColor: '#5b9bd5'
            }).then(function(){ $(check.el).focus(); });
          })(checks[i]);
          return;
        }
      }

      var priceVal = parseFloat($('#Price').val().replace(/,/g,''));
      if (isNaN(priceVal) || priceVal <= 0) {
        Swal.fire({
          icon: 'warning',
          title: 'กรุณาตรวจสอบข้อมูล',
          html: '<strong>จำนวนเงิน</strong> ต้องเป็นตัวเลขมากกว่า 0',
          confirmButtonText: 'เข้าใจ',
          confirmButtonColor: '#5b9bd5'
        }).then(function(){ $('#Price').focus(); });
        return;
      }

      Swal.fire({
        title: 'ยืนยันการแก้ไข',
        html: 'คุณต้องการบันทึกการเปลี่ยนแปลงรายการ <strong>#<?php echo $PayId; ?></strong><br>'
            + 'จำนวน <strong>' + $('#Amount').val() + '</strong> บาท ใช่หรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="glyphicon glyphicon-floppy-disk"></i> บันทึก',
        cancelButtonText:  '<i class="glyphicon glyphicon-remove"></i> ยกเลิก',
        confirmButtonColor: '#0B6E4F',
        cancelButtonColor:  '#6b778c'
      }).then(function(result){
        if (result.isConfirmed) {
          Swal.fire({
            title: 'กำลังบันทึกการแก้ไข...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: function(){ Swal.showLoading(); }
          });
          document.getElementById('editForm').submit();
        }
      });
    });

    // ── คืนค่าเดิม ──
    $('#btnReset').on('click', function(){
      Swal.fire({
        title: 'คืนค่าเดิม',
        text: 'คุณต้องการยกเลิกการเปลี่ยนแปลงทั้งหมดและกลับเป็นค่าเดิมใช่หรือไม่?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'คืนค่าเดิม',
        cancelButtonText:  'ยกเลิก',
        confirmButtonColor: '#ef4444',
        cancelButtonColor:  '#6b778c'
      }).then(function(result){
        if (result.isConfirmed) {
          document.getElementById('editForm').reset();
          $('#Vat').val('<?php echo number_format($Vat, 2, '.', ''); ?>');
          $('#Amount').val('<?php echo number_format($Amount, 2, '.', ''); ?>');
          Swal.fire({
            icon: 'success',
            title: 'คืนค่าเดิมเรียบร้อย',
            timer: 1200,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
          });
        }
      });
    });
  });
  </script>
</body>
</html>
