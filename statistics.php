<?php
// statistics.php
require_once __DIR__ . '/config.php';
checkAuth();

// ── ดึง planpay list สำหรับ autocomplete ──
$planPayList = [];
$stmt = $conn->prepare("SELECT PlanPayId, PlanPayName FROM planpay ORDER BY PlanPayId");
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $planPayList[] = $r;
}
$stmt->close();

// ── ดึง employee list สำหรับ dropdown ──
$employees = [];
$stmt = $conn->prepare("SELECT Username, Names FROM employee ORDER BY Names");
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $employees[] = $r;
}
$stmt->close();

// ── วันที่ default (Thai) ──
$thaiDefault = date("d/m/") . (date("Y") + 543);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="pic/fms.png">
    <title>สถิติและรายงาน – MOPH</title>

    <!-- Bootstrap Icons & Flatpickr (Bootstrap5, SweetAlert, theme loaded by header.php) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">

<style>
:root {
    --moph-primary:#0B6E4F; --moph-secondary:#08A045; --moph-dark:#1a4d2e;
    --moph-light:#e8f5e9; --white:#fff; --gray-50:#f8f9fa; --gray-100:#f1f3f5;
    --gray-200:#e9ecef; --gray-600:#6c757d; --gray-700:#495057; --gray-800:#343a40;
    --shadow-md:0 4px 12px rgba(11,110,79,.12); --shadow-lg:0 8px 24px rgba(11,110,79,.15);
}
body { font-family:'Sarabun',-apple-system,sans-serif; background:linear-gradient(135deg,var(--moph-light) 0%,#fff 50%,var(--gray-50) 100%); min-height:100vh; color:var(--gray-800); }
.main-container { max-width:1200px; margin:0 auto; padding:24px; }

/* Page Header */
.page-header { background:linear-gradient(135deg,var(--moph-primary),var(--moph-secondary)); border-radius:20px; padding:28px 32px; margin-bottom:24px; box-shadow:var(--shadow-lg); position:relative; overflow:hidden; }
.page-header::before { content:''; position:absolute; top:-40%; right:-8%; width:350px; height:350px; background:radial-gradient(circle,rgba(255,255,255,.12) 0%,transparent 70%); border-radius:50%; }
.page-header h1 { color:#fff; font-size:26px; font-weight:700; margin:0; position:relative; z-index:1; }
.page-header p { color:rgba(255,255,255,.9); font-size:15px; margin:6px 0 0; position:relative; z-index:1; }
.badge-moph { display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,.2); color:#fff; padding:6px 14px; border-radius:20px; font-size:13px; font-weight:500; margin-top:10px; position:relative; z-index:1; }

/* Tabs */
.nav-tabs-moph { display:flex; gap:8px; flex-wrap:wrap; border-bottom:2px solid var(--gray-200); margin-bottom:24px; }
.nav-tabs-moph a { text-decoration:none; color:var(--gray-600); font-weight:600; font-size:15px; padding:12px 20px; border-radius:12px 12px 0 0; border:2px solid transparent; border-bottom:none; background:var(--gray-100); transition:all .25s; display:flex; align-items:center; gap:8px; }
.nav-tabs-moph a:hover { background:var(--moph-light); color:var(--moph-dark); }
.nav-tabs-moph a.active { background:#fff; color:var(--moph-primary); font-weight:700; border-color:var(--moph-primary); border-bottom-color:#fff; margin-bottom:-2px; }

/* Card */
.card-moph { background:#fff; border-radius:16px; border:1px solid var(--gray-200); box-shadow:var(--shadow-md); margin-bottom:24px; overflow:hidden; }
.card-header-moph { background:linear-gradient(135deg,rgba(11,110,79,.08),rgba(8,160,69,.05)); border-bottom:1px solid var(--gray-200); padding:16px 24px; display:flex; align-items:center; gap:10px; }
.card-header-moph i { color:var(--moph-primary); font-size:20px; }
.card-header-moph span { font-weight:700; font-size:16px; color:var(--moph-dark); }
.card-body-moph { padding:24px; }

/* Report Buttons Grid */
.report-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; max-width:700px; margin:0 auto; }
.report-btn {
    display:flex; align-items:center; gap:14px; padding:18px 20px;
    background:var(--white); border:2px solid var(--gray-200); border-radius:14px;
    cursor:pointer; transition:all .25s; text-decoration:none; color:var(--gray-800);
}
.report-btn:hover { border-color:var(--moph-primary); background:var(--moph-light); transform:translateY(-2px); box-shadow:var(--shadow-md); }
.report-btn .btn-icon {
    width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center;
    font-size:22px; flex-shrink:0;
}
.report-btn .btn-icon.pdf  { background:rgba(220,53,69,.1); color:#dc3545; }
.report-btn .btn-icon.xls  { background:rgba(33,122,60,.1); color:#217a3c; }
.report-btn .btn-text { text-align:left; }
.report-btn .btn-title { font-size:14px; font-weight:700; color:var(--gray-800); line-height:1.3; }
.report-btn .btn-sub { font-size:12px; color:var(--gray-600); margin-top:2px; }
.report-btn:hover .btn-title { color:var(--moph-primary); }

/* Modal */
.modal-content { border-radius:16px; overflow:hidden; border:none; box-shadow:0 20px 60px rgba(11,110,79,.2); }
.modal-header-moph { background:linear-gradient(135deg,var(--moph-primary),var(--moph-secondary)); color:#fff; padding:18px 24px; display:flex; align-items:center; justify-content:space-between; }
.modal-header-moph h5 { margin:0; font-weight:700; font-size:18px; display:flex; align-items:center; gap:8px; }
.modal-header-moph .btn-close { filter:invert(1); opacity:.8; }
.modal-header-moph .btn-close:hover { opacity:1; }
.modal-body-moph { padding:24px; }
.modal-body-moph .form-label { font-weight:600; color:var(--gray-700); font-size:14px; margin-bottom:6px; display:block; }
.modal-body-moph .form-control { border:2px solid var(--gray-200); border-radius:10px; height:42px; font-size:15px; transition:all .25s; }
.modal-body-moph .form-control:focus { border-color:var(--moph-primary); box-shadow:0 0 0 3px rgba(11,110,79,.12); outline:none; }
.modal-footer-moph { padding:16px 24px; border-top:1px solid var(--gray-200); display:flex; justify-content:flex-end; gap:10px; }
.btn-modal-primary { padding:8px 22px; background:var(--moph-primary); color:#fff; border:none; border-radius:10px; font-size:15px; font-weight:600; cursor:pointer; transition:all .2s; display:inline-flex; align-items:center; gap:6px; }
.btn-modal-primary:hover { background:var(--moph-dark); }
.btn-modal-cancel { padding:8px 22px; background:#fff; color:var(--gray-700); border:2px solid var(--gray-200); border-radius:10px; font-size:15px; font-weight:600; cursor:pointer; transition:all .2s; display:inline-flex; align-items:center; gap:6px; }
.btn-modal-cancel:hover { background:var(--gray-50); }

/* Radio group */
.radio-group { display:flex; gap:24px; margin-top:8px; }
.radio-group label { display:flex; align-items:center; gap:8px; font-size:15px; color:var(--gray-700); cursor:pointer; }
.radio-group input[type="radio"] { accent-color:var(--moph-primary); width:18px; height:18px; cursor:pointer; }

/* Autocomplete override */
.ui-autocomplete { z-index:9999 !important; border-radius:10px; box-shadow:var(--shadow-lg); }
.ui-menu-item a { padding:8px 14px; font-size:14px; }

/* Input group search */
.input-group-moph { position:relative; }
.input-group-moph .form-control { padding-right:44px; }
.input-group-moph .input-icon { position:absolute; right:12px; top:50%; transform:translateY(-50%); color:var(--gray-500); font-size:18px; pointer-events:none; }

@media(max-width:768px) {
    .report-grid { grid-template-columns:1fr; }
    .main-container { padding:12px; }
    .page-header { padding:20px; border-radius:14px; }
}

/* SweetAlert2 */
.swal2-popup { border-radius:16px; font-family:'Sarabun',sans-serif; }
.swal2-title { color:var(--gray-800); font-weight:700; }
.swal2-confirm { background:var(--moph-primary)!important; border-radius:10px; font-weight:600; }
.swal2-cancel { border-radius:10px; font-weight:600; }
</style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="main-container">
    <!-- Page Header -->
    <div class="page-header" style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px;">
        <div style="position:relative; z-index:1;">
          <h1 style="display:flex; align-items:center; gap:8px;">
            <span class="msi msi-28">bar_chart</span> สถิติและรายงาน
          </h1>
          <p>รายงานต่าง ๆ ของระบบบริหารจัดการการเงินและบัญชี</p>
          <span class="badge-moph"><span class="msi msi-18">verified</span> กระทรวงสาธารณสุข</span>
        </div>
        <a href="main.php" class="btn-go-back" style="position:relative; z-index:1; border-color:rgba(255,255,255,.4); color:rgba(255,255,255,.9); background:rgba(255,255,255,.15);" onmouseover="this.style.background='rgba(255,255,255,.25)'" onmouseout="this.style.background='rgba(255,255,255,.15)'">
          <span class="msi">arrow_back</span> กลับหน้าหลัก
        </a>
    </div>

    <!-- Tabs -->
    <div class="nav-tabs-moph">
        <a href="plan.php"><i class="bi bi-list"></i> รายการค่าใช้จ่ายตามแผนเงินบำรุง</a>
        <a href="planbudget.php"><i class="bi bi-tasks"></i> รายการค่าใช้จ่ายตามแผนเงินงบประมาณ</a>
        <a href="statistics.php" class="active"><i class="bi bi-bar-chart"></i> สถิติและรายงาน</a>
    </div>

    <!-- Report List -->
    <div class="card-moph">
        <div class="card-header-moph">
            <i class="bi bi-file-earmark-text"></i>
            <span>รายงานต่าง ๆ</span>
        </div>
        <div class="card-body-moph">
            <div class="report-grid">

                <!-- 1: เจ้าหนี้การค้า PDF -->
                <button type="button" class="report-btn" onclick="openModal('modalReport1')">
                    <div class="btn-icon pdf"><i class="bi bi-filetype-pdf"></i></div>
                    <div class="btn-text">
                        <div class="btn-title">เจ้าหนี้การค้าตามประเภทค่าใช้จ่าย</div>
                        <div class="btn-sub">รูปแบบ PDF</div>
                    </div>
                </button>

                <!-- 2: เจ้าหนี้การค้า Excel -->
                <button type="button" class="report-btn" onclick="openModal('modalReport3')">
                    <div class="btn-icon xls"><i class="bi bi-file-earmark-excel"></i></div>
                    <div class="btn-text">
                        <div class="btn-title">เจ้าหนี้การค้าตามประเภทค่าใช้จ่าย</div>
                        <div class="btn-sub">รูปแบบ Excel</div>
                    </div>
                </button>

                <!-- 3: รายการจ่าย PDF -->
                <button type="button" class="report-btn" onclick="openModal('modalReport2')">
                    <div class="btn-icon pdf"><i class="bi bi-filetype-pdf"></i></div>
                    <div class="btn-text">
                        <div class="btn-title">รายการจ่ายตามประเภทค่าใช้จ่าย</div>
                        <div class="btn-sub">รูปแบบ PDF</div>
                    </div>
                </button>

                <!-- 4: รายการจ่าย Excel -->
                <button type="button" class="report-btn" onclick="openModal('modalReport4')">
                    <div class="btn-icon xls"><i class="bi bi-file-earmark-excel"></i></div>
                    <div class="btn-text">
                        <div class="btn-title">รายการจ่ายตามประเภทค่าใช้จ่าย</div>
                        <div class="btn-sub">รูปแบบ Excel</div>
                    </div>
                </button>

                <!-- 5: รายละเอียดการจ่าย PDF -->
                <button type="button" class="report-btn" onclick="openModal('modalReport5')">
                    <div class="btn-icon pdf"><i class="bi bi-filetype-pdf"></i></div>
                    <div class="btn-text">
                        <div class="btn-title">รายละเอียดการจ่ายตามประเภทค่าใช้จ่าย</div>
                        <div class="btn-sub">รูปแบบ PDF</div>
                    </div>
                </button>

                <!-- 6: รายละเอียดการจ่าย Excel -->
                <button type="button" class="report-btn" onclick="openModal('modalReport6')">
                    <div class="btn-icon xls"><i class="bi bi-file-earmark-excel"></i></div>
                    <div class="btn-text">
                        <div class="btn-title">รายละเอียดการจ่ายตามประเภทค่าใช้จ่าย</div>
                        <div class="btn-sub">รูปแบบ Excel</div>
                    </div>
                </button>

            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     MODALS – ใช้ PHP loop สร้างเพื่อลดการซ้ำ
     กรุณาเข้าใจว่า action แต่ละตัวต่างกัน
     ═══════════════════════════════════════════════════════════ -->
<?php
// config array สำหรับ modal แต่ละตัว
$modals = [
    ['id'=>'modalReport1', 'action'=>'report3.php', 'title'=>'เจ้าหนี้การค้า (PDF)', 'dateLabel'=>'วันที่บันทึกรายการ', 'hasAutocomplete'=>true, 'hasSource'=>true, 'hasWorker'=>true, 'hasAudit'=>true],
    ['id'=>'modalReport2', 'action'=>'report4.php', 'title'=>'รายการจ่าย (PDF)',     'dateLabel'=>'วันที่ตัดจ่าย',       'hasAutocomplete'=>true, 'hasSource'=>true, 'hasWorker'=>true, 'hasAudit'=>true],
    ['id'=>'modalReport3', 'action'=>'report5.php', 'title'=>'เจ้าหนี้การค้า (Excel)','dateLabel'=>'วันที่บันทึกรายการ', 'hasAutocomplete'=>true, 'hasSource'=>true, 'hasWorker'=>false,'hasAudit'=>false],
    ['id'=>'modalReport4', 'action'=>'report6.php', 'title'=>'รายการจ่าย (Excel)',   'dateLabel'=>'วันที่ตัดจ่าย',       'hasAutocomplete'=>true, 'hasSource'=>true, 'hasWorker'=>false,'hasAudit'=>false],
    ['id'=>'modalReport5', 'action'=>'report7.php', 'title'=>'รายละเอียดจ่าย (PDF)', 'dateLabel'=>'วันที่บันทึกรายการ', 'hasAutocomplete'=>false,'hasSource'=>true, 'hasWorker'=>false,'hasAudit'=>false],
    ['id'=>'modalReport6', 'action'=>'report8.php', 'title'=>'รายละเอียดจ่าย (Excel)','dateLabel'=>'วันที่บันทึกรายการ','hasAutocomplete'=>false,'hasSource'=>true, 'hasWorker'=>false,'hasAudit'=>false],
];

foreach ($modals as $idx => $m):
    $startId = 'dp_start_' . $m['id'];
    $endId   = 'dp_end_'   . $m['id'];
    $autoId  = 'auto_'     . $m['id'];
?>
<div class="modal fade" id="<?php echo $m['id']; ?>" tabindex="-1">
    <div class="modal-dialog" style="max-width:520px;">
        <div class="modal-content">
            <div class="modal-header-moph">
                <h5><i class="bi bi-list-alt"></i> <?php echo htmlspecialchars($m['title']); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="get" action="<?php echo htmlspecialchars($m['action']); ?>" target="_blank" class="report-form" id="form_<?php echo $m['id']; ?>">
                <div class="modal-body-moph">

                    <!-- Date Range -->
                    <label class="form-label">ระหว่างวันที่ (<?php echo htmlspecialchars($m['dateLabel']); ?>) :</label>
                    <input type="text" class="form-control thai-dp" name="DateStart" id="<?php echo $startId; ?>" placeholder="dd/mm/yyyy" value="<?php echo $thaiDefault; ?>">

                    <label class="form-label" style="margin-top:14px;">ถึงวันที่ :</label>
                    <input type="text" class="form-control thai-dp" name="DateEnd" id="<?php echo $endId; ?>" placeholder="dd/mm/yyyy" value="<?php echo $thaiDefault; ?>">

                    <!-- Autocomplete: ประเภทค่าใช้จ่าย -->
                    <?php if ($m['hasAutocomplete']): ?>
                    <label class="form-label" style="margin-top:14px;">ประเภทค่าใช้จ่าย :</label>
                    <div class="input-group-moph">
                        <input type="text" class="form-control autocomplete-planpay" name="PlanPay" id="<?php echo $autoId; ?>" placeholder="ค้นหาแผน...">
                        <i class="input-icon bi bi-search"></i>
                    </div>
                    <?php endif; ?>

                    <!-- แหล่งเงิน -->
                    <?php if ($m['hasSource']): ?>
                    <label class="form-label" style="margin-top:14px;">แหล่งเงิน :</label>
                    <div class="radio-group">
                        <label><input type="radio" name="Source" value="1" checked> เงินบำรุง</label>
                        <label><input type="radio" name="Source" value="2"> งบประมาณ</label>
                    </div>
                    <?php endif; ?>

                    <!-- ผู้จัดทำ -->
                    <?php if ($m['hasWorker']): ?>
                    <label class="form-label" style="margin-top:14px;">ผู้จัดทำ :</label>
                    <select class="form-control" name="Worker">
                        <option value=" ">-- เลือกผู้จัดทำ --</option>
                        <?php foreach ($employees as $emp): ?>
                        <option value="<?php echo htmlspecialchars($emp['Username']); ?>">
                            <?php echo htmlspecialchars($emp['Names']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>

                    <!-- ผู้ตรวจ -->
                    <?php if ($m['hasAudit']): ?>
                    <label class="form-label" style="margin-top:14px;">ผู้ตรวจ :</label>
                    <select class="form-control" name="Audit">
                        <option value=" ">-- เลือกผู้ตรวจ --</option>
                        <?php foreach ($employees as $emp): ?>
                        <option value="<?php echo htmlspecialchars($emp['Username']); ?>">
                            <?php echo htmlspecialchars($emp['Names']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>

                </div>
                <div class="modal-footer-moph">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> ยกเลิก</button>
                    <button type="submit" class="btn-modal-primary" onclick="return validateReport(event, '<?php echo $m['id']; ?>');"><i class="bi bi-box-arrow-up-right"></i> ส่งออกรายงาน</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Flatpickr (jQuery, Bootstrap5 JS, SweetAlert loaded by header.php) -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
<script>
// ── Thai Buddhist Flatpickr helper ──
function thaiDatepickerBE(selector) {
  document.querySelectorAll(selector).forEach(function(el) {
    var iv = el.value;
    var initDate = null;
    if (iv) {
      var p = iv.split('/');
      if (p.length === 3) { var y = parseInt(p[2]); if (y > 2500) y -= 543; initDate = new Date(y, parseInt(p[1])-1, parseInt(p[0])); }
    }
    flatpickr(el, {
      locale: 'th', dateFormat: 'd/m/Y', defaultDate: initDate || undefined,
      disableMobile: false, allowInput: true,
      onReady: function(sd, ds, inst) {
        if (initDate) { var d=initDate; inst.element.value = String(d.getDate()).padStart(2,'0')+'/'+String(d.getMonth()+1).padStart(2,'0')+'/'+(d.getFullYear()+543); }
      },
      onChange: function(sd, ds, inst) {
        if (sd[0]) { var d=sd[0]; inst.element.value = String(d.getDate()).padStart(2,'0')+'/'+String(d.getMonth()+1).padStart(2,'0')+'/'+(d.getFullYear()+543); }
      },
      parseDate: function(s) {
        if (!s) return null;
        var p = s.split('/'); if (p.length !== 3) return null;
        var y = parseInt(p[2]); if (y > 2500) y -= 543;
        return new Date(y, parseInt(p[1])-1, parseInt(p[0]));
      }
    });
  });
}

// ── Autocomplete source (built once) ──
<?php
$autoSource = [];
foreach ($planPayList as $p) {
    $autoSource[] = $p['PlanPayId'] . '. ' . $p['PlanPayName'];
}
?>
const planPaySource = <?php echo json_encode($autoSource, JSON_UNESCAPED_UNICODE); ?>;

$(document).ready(function() {
    // Init ALL thai datepickers at once
    thaiDatepickerBE('.thai-dp');

    // Init ALL autocomplete fields at once
    $('.autocomplete-planpay').autocomplete({ source: planPaySource });
});

// ── Open modal helper ──
function openModal(id) {
    var m = new bootstrap.Modal(document.getElementById(id));
    m.show();
}

// ── Build query string from form ──
function formToQueryString(form) {
    var data = new FormData(form);
    var params = new URLSearchParams();
    data.forEach(function(val, key) { params.append(key, val); });
    return params.toString();
}

// ── Validate before export ──
function validateReport(e, modalId) {
    e.preventDefault();
    var form   = document.getElementById('form_' + modalId);
    var start  = form.querySelector('[name="DateStart"]').value.trim();
    var end    = form.querySelector('[name="DateEnd"]').value.trim();

    if (!start || !end) {
        Swal.fire({
            icon:'warning', title:'กรุณาตรวจสอบ',
            text:'กรุณากรอกวันที่ เริ่มต้น และ สิ้นสุด ให้ครบถ้วน',
            confirmButtonColor:'#0B6E4F', confirmButtonText:'ตรวจสอบ'
        });
        return false;
    }

    // Build export URL before async (so popup blocker can't block it)
    var exportUrl = form.getAttribute('action') + '?' + formToQueryString(form);

    Swal.fire({
        title:'ยืนยันการส่งออกรายงาน',
        text:'คุณต้องการส่งออกรายงานใช่หรือไม่?',
        icon:'question', showCancelButton:true,
        confirmButtonColor:'#0B6E4F', cancelButtonColor:'#6c757d',
        confirmButtonText:'<i class="bi bi-check-circle"></i> ยืนยัน',
        cancelButtonText:'<i class="bi bi-x-circle"></i> ยกเลิก',
        reverseButtons:true
    }).then(function(result) {
        if (result.isConfirmed) {
            // Open in new tab — use window.open with the pre-built URL
            var win = window.open(exportUrl, '_blank');
            if (!win) {
                // Fallback: navigate current tab if popup blocked
                window.location.href = exportUrl;
            }
            // Show success toast
            Swal.fire({
                icon:'success', title:'กำลังจัดทำรายงาน...',
                text:'รายงานกำลังเปิดในแท็บใหม่',
                timer:2000, showConfirmButton:false,
                toast:true, position:'top-end'
            });
            // Close the modal
            var modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
            if (modal) modal.hide();
        }
    });
    return false;
}
</script>
</body>
</html>