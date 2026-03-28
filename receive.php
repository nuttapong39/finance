<?php
// receive.php (Card UI + No warnings + Prepared Statements)
if (!ob_get_level()) { ob_start(); }
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Bangkok');

/**
 * ดึง header.php มาเก็บไว้ก่อน (เพื่อกัน header.php พ่น HTML ก่อน <head>)
 * และยังให้ session/auth ใน header.php ทำงานตามปกติ
 */
$__header_html = '';
ob_start();
require_once __DIR__ . '/header.php';
$__header_html = ob_get_clean();

require_once __DIR__ . '/connect_db.php';
if (!isset($conn) || !($conn instanceof mysqli)) {
  // กันพังแบบไม่สร้าง Warning
  $conn = null;
}

if ($conn) {
  @mysqli_set_charset($conn, 'utf8mb4');
}

/* =========================
   Helpers
========================= */
if (!function_exists('h')) {
  function h($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
  }
}

function thaiDateShort(?string $ymd): string {
  if (!$ymd || $ymd === '0000-00-00') return '-';
  $ts = strtotime($ymd);
  if (!$ts) return '-';
  $y = (int)date('Y', $ts) + 543;
  $m = (int)date('n', $ts);
  $d = (int)date('j', $ts);
  $mcut = ["","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค."];
  $mth = $mcut[$m] ?? '';
  return $d . "&nbsp;&nbsp;" . $mth . "&nbsp;&nbsp;" . $y;
}

function diffThaiText(string $startYmd, string $endYmd): string {
  try {
    $a = new DateTime($startYmd);
    $b = new DateTime($endYmd);
    $diff = $a->diff($b);

    $parts = [];
    if ($diff->y > 0) $parts[] = $diff->y . " ปี";
    if ($diff->m > 0) $parts[] = $diff->m . " เดือน";
    $parts[] = $diff->d . " วัน";
    return implode(' ', $parts);
  } catch (Exception $e) {
    return "-";
  }
}

function buildQuery(array $extra = []): string {
  $base = $_GET ?? [];
  foreach ($extra as $k => $v) $base[$k] = $v;
  return http_build_query($base);
}

/**
 * bind_param แบบ dynamic (ต้องใช้ reference)
 */
function bindParams(mysqli_stmt $stmt, string $types, array $values): bool {
  $bind = [];
  $bind[] = &$types;
  foreach ($values as $k => $v) {
    $bind[] = &$values[$k];
  }
  return call_user_func_array([$stmt, 'bind_param'], $bind);
}

/* =========================
   Inputs (กัน Warning)
========================= */
$Keyword = trim($_GET['Keyword'] ?? '');
$TypeKey = (int)($_GET['TypeKey'] ?? 0);
$pn      = max(1, (int)($_GET['pn'] ?? 1));
$page_rows = 25;

switch ($TypeKey) {
  case 1: $TypeKeyName = "บริษัท/ร้านค้า"; break;
  case 2: $TypeKeyName = "เลขที่ใบส่งของ"; break;
  case 3: $TypeKeyName = "รหัสรายการ"; break;
  case 4: $TypeKeyName = "เลขที่รับเอกสาร"; break;
  default: $TypeKeyName = "เลือกประเภทการค้น";
}

/* =========================
   Build WHERE (Prepared)
   - ถ้าไม่ค้นหา: แสดงเฉพาะที่ยังไม่ลงรับเอกสาร
   - ถ้าค้นหา: ทำเหมือนของเดิม (ค้นได้กว้างขึ้น)
========================= */
$where = [];
$params = [];
$types  = '';

$join = " LEFT JOIN company c ON p.CompanyId = c.CompanyId ";

if ($Keyword === '') {
  $where[] = "(p.DateReceive IS NULL OR p.DateReceive='' OR p.DateReceive='0000-00-00')";
} else {
  switch ($TypeKey) {
    case 1:
      $where[] = "c.CompanyName LIKE ?";
      $types  .= "s";
      $params[] = "%" . $Keyword . "%";
      break;
    case 2:
      $where[] = "p.Detail LIKE ?";
      $types  .= "s";
      $params[] = "%" . $Keyword . "%";
      break;
    case 3:
      $where[] = "p.PayId = ?";
      $types  .= "i";
      $params[] = (int)$Keyword;
      break;
    case 4:
    default:
      $where[] = "p.ReceiveNo LIKE ?";
      $types  .= "s";
      $params[] = "%" . $Keyword . "%";
      break;
  }
}

$whereSql = $where ? (" WHERE " . implode(" AND ", $where)) : "";

/* =========================
   Count rows (pagination)
========================= */
$total_rows = 0;
$db_ok = (bool)$conn;

if ($db_ok) {
  $sqlCount = "SELECT COUNT(*) AS cnt FROM payment p {$join} {$whereSql}";
  $stmtC = $conn->prepare($sqlCount);
  if ($stmtC) {
    if ($types !== '') {
      bindParams($stmtC, $types, $params);
    }
    if ($stmtC->execute()) {
      $res = $stmtC->get_result();
      if ($res) {
        $row = $res->fetch_assoc();
        $total_rows = (int)($row['cnt'] ?? 0);
      }
    }
    $stmtC->close();
  }
}

$last = max(1, (int)ceil($total_rows / $page_rows));
if ($pn > $last) $pn = $last;
$offset = ($pn - 1) * $page_rows;

/* =========================
   Fetch rows
========================= */
$rowsData = [];
if ($db_ok && $total_rows > 0) {
  $sqlData = "
    SELECT
      p.PayId, p.DateIn, p.DateReceive, p.ReceiveNo,
      p.Detail, p.Amount, p.CompanyId,
      c.CompanyName
    FROM payment p
    {$join}
    {$whereSql}
    ORDER BY p.DateIn DESC
    LIMIT ?, ?
  ";
  $stmtD = $conn->prepare($sqlData);
  if ($stmtD) {
    $types2 = $types . "ii";
    $params2 = array_merge($params, [$offset, $page_rows]);
    bindParams($stmtD, $types2, $params2);

    if ($stmtD->execute()) {
      $resD = $stmtD->get_result();
      if ($resD) {
        while ($r = $resD->fetch_assoc()) {
          $rowsData[] = $r;
        }
      }
    }
    $stmtD->close();
  }
}

/* =========================
   Pagination controls
========================= */
$paginationCtrls = '<nav aria-label="Page navigation"><ul class="pagination" style="margin:8px 0;">';
if ($last != 1) {
  if ($pn > 1) {
    $previous = $pn - 1;
    $paginationCtrls .= '<li><a href="?'.h(buildQuery(['pn'=>$previous])).'" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
    for ($i = $pn - 4; $i < $pn; $i++) {
      if ($i > 0) $paginationCtrls .= '<li><a href="?'.h(buildQuery(['pn'=>$i])).'">'.(int)$i.'</a></li>';
    }
  }

  $paginationCtrls .= '<li class="active"><a href="#">'.(int)$pn.' <span class="sr-only">(current)</span></a></li>';

  for ($i = $pn + 1; $i <= $last; $i++) {
    $paginationCtrls .= '<li><a href="?'.h(buildQuery(['pn'=>$i])).'">'.(int)$i.'</a></li>';
    if ($i >= $pn + 4) break;
  }

  if ($pn != $last) {
    $next = $pn + 1;
    $paginationCtrls .= '<li><a href="?'.h(buildQuery(['pn'=>$next])).'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
  }
}
$paginationCtrls .= '</ul></nav>';

// default date (ไทย) สำหรับ modal
$todayYmd = date('Y-m-d');
$todayThai = date('d/m/') . (date('Y') + 543);
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
    body{
      font-family:'Sarabun', sans-serif;
      background:
        radial-gradient(1100px 600px at 12% 15%, rgba(91,155,213,.22), transparent 60%),
        radial-gradient(900px 520px at 92% 10%, rgba(0,176,80,.14), transparent 55%),
        linear-gradient(180deg, #f8fbff, #f6f8fc);
    }
    .container{ max-width: 1200px; }

    .page-titlebar{
      margin: 14px 0 16px;
      border-radius: 18px;
      padding: 14px 16px;
      background: rgba(255,255,255,.88);
      border: 1px solid #e9eef6;
      box-shadow: 0 12px 30px rgba(13,27,62,.08);
      text-align:center;
    }
    .page-titlebar h3{
      margin: 2px 0 0;
      font-weight: 800;
      color:#1f2a44;
      font-size: 20px;
    }
    .page-titlebar .sub{
      color:#6b778c;
      margin-top: 6px;
      font-size: 13px;
    }

    .card-panel{
      border-radius: 18px;
      border: 1px solid #e9eef6;
      background: rgba(255,255,255,.92);
      box-shadow: 0 12px 30px rgba(13,27,62,.08);
      overflow:hidden;
      margin-bottom: 14px;
    }
    .card-head{
      padding: 12px 14px;
      border-bottom: 1px solid #e9eef6;
      font-weight: 800;
      color:#1f2a44;
      background: linear-gradient(135deg, rgba(0,176,80,.16), rgba(0,176,80,.06));
    }
    .card-body{ padding: 14px 16px; }


    .form-control{
      height: 42px;
      border-radius: 12px;
      border: 1px solid #dfe7f3;
      box-shadow: none;
    }
    .form-control:focus{
      border-color: rgba(0,176,80,.55);
      box-shadow: 0 0 0 3px rgba(0,176,80,.18);
    }
    .btn{ border-radius: 12px; font-weight: 700; padding: 9px 14px; }
    .btn-success{ box-shadow: 0 10px 22px rgba(0,176,80,.16); }

    .table{
      background:#fff;
      border-radius: 14px;
      overflow:hidden;
      margin-bottom: 8px;
    }
    .table thead th{
      background:#e9f7ee;
      color:#1f2a44;
      font-weight:800;
      border-bottom: 1px solid #d6f0df !important;
      vertical-align: middle !important;
    }
    .table td{ vertical-align: middle !important; }

    .badge-pill{
      display:inline-block;
      padding: 6px 10px;
      border-radius: 999px;
      font-weight: 800;
      font-size: 12px;
      border: 1px solid #e2e8f0;
      background:#f8fafc;
      color:#334155;
    }
    .bd-wait{ background:#fff7ed; border-color:#fed7aa; color:#9a3412; }
    .bd-ok{ background:#f0fdf4; border-color:#bbf7d0; color:#166534; }

    .help-note{ color:#6b778c; font-size: 12px; margin-top: 8px; }
    .pagination > li > a, .pagination > li > span{
      border-radius: 10px !important;
      margin: 0 4px;
      border: 1px solid #e2e8f0;
      color:#1f2a44;
    }
    .pagination > .active > a{
      background:#00b050;
      border-color:#00b050;
    }
  </style>

</head>

<body>
  <?php echo $__header_html; ?>

  <div class="container">
    <div class="page-titlebar" style="display:flex; align-items:center; justify-content:space-between; gap:12px; text-align:left;">
      <div>
        <h3 style="margin:0; display:flex; align-items:center; gap:8px;">
          <span class="msi msi-24" style="color:#0B6E4F;">inbox</span> ลงรับเอกสารการเงิน
        </h3>
        <div class="sub">ค้นหา / ตรวจสอบรายการ และบันทึกเลขรับที่พร้อมวันที่ลงรับเอกสาร</div>
      </div>
      <a href="main.php" class="btn-go-back"><span class="msi">arrow_back</span> กลับหน้าหลัก</a>
    </div>

    <!-- Tabs -->
    <div class="card-panel">
      <div class="card-body" style="padding-bottom:0;">
        <ul class="nav-tabs-modern">
          <li role="presentation" class="active"><a href="receive.php"><span class="msi">inbox</span> ลงรับเอกสาร</a></li>
          <li role="presentation"><a href="finance.php"><span class="msi">check_circle</span> ขออนุมัติ</a></li>
          <li role="presentation"><a href="cheque.php"><span class="msi">credit_card</span> จัดทำเช็ค</a></li>
          <li role="presentation"><a href="printcheque.php"><span class="msi">print</span> พิมพ์เช็ค</a></li>
          <li role="presentation"><a href="control.php"><span class="msi">menu_book</span> ทะเบียนคุม</a></li>
          <li role="presentation"><a href="paidment.php"><span class="msi">book</span> ใบสำคัญ</a></li>
          <li role="presentation"><a href="paid.php"><span class="msi">task_alt</span> ตัดจ่ายเช็ค</a></li>
          <li role="presentation"><a href="daily.php"><span class="msi">calendar_today</span> รายงานประจำวัน</a></li>
          <li role="presentation"><a href="findpay.php"><span class="msi">search</span> ค้นหารายการ</a></li>
        </ul>
      </div>
    </div>

    <!-- Search card -->
    <div class="card-panel">
      <div class="card-head">
        <span class="msi">search</span> ค้นหารายการจ่าย
      </div>
      <div class="card-body">
        <form class="form-horizontal" method="get" action="<?php echo h($_SERVER['PHP_SELF']); ?>">
          <div class="row" style="margin:0;">
            <div class="col-md-3" style="padding-left:0;">
              <label style="font-weight:800;color:#1f2a44;">ค้นด้วย</label>
              <select class="form-control" name="TypeKey" id="TypeKey">
                <option value="0">เลือกประเภทการค้น</option>
                <option value="1" <?php echo $TypeKey===1?'selected':''; ?>>บริษัท/ร้านค้า</option>
                <option value="2" <?php echo $TypeKey===2?'selected':''; ?>>เลขที่ใบส่งของ</option>
                <option value="3" <?php echo $TypeKey===3?'selected':''; ?>>รหัสรายการ</option>
                <option value="4" <?php echo $TypeKey===4?'selected':''; ?>>เลขที่รับเอกสาร</option>
              </select>
            </div>

            <div class="col-md-7">
              <label style="font-weight:800;color:#1f2a44;">คำค้น</label>
              <input class="form-control" name="Keyword" id="Keyword" type="text"
                     value="<?php echo h($Keyword); ?>"
                     placeholder="ระบุคำค้น...">
              <div class="help-note">
                * หากไม่กรอกคำค้น ระบบจะแสดงเฉพาะรายการที่ “ยังไม่ลงรับเอกสาร”
              </div>
            </div>

            <div class="col-md-2" style="padding-right:0; padding-top:26px;">
              <button type="submit" class="btn btn-success" style="width:100%;">
                <span class="msi">search</span> ค้นหา
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Results card -->
    <div class="card-panel">
      <div class="card-head">
        <span class="msi">inbox</span> รายการลงรับเอกสารการเงิน
        <span style="font-weight:600;color:#64748b;">(ทั้งหมด <?php echo (int)$total_rows; ?> รายการ)</span>
      </div>

      <div class="card-body">
        <?php if (!$db_ok): ?>
          <div class="alert alert-danger" style="border-radius:14px;">
            ไม่สามารถเชื่อมต่อฐานข้อมูลได้ (ตรวจสอบ connect_db.php)
          </div>

        <?php elseif ($total_rows <= 0): ?>
          <div class="alert alert-danger" style="border-radius:14px; text-align:center;">
            ไม่พบข้อมูล
          </div>

        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th style="width:70px; text-align:center;">รหัสรายการ</th>
                  <th style="width:160px; text-align:center;">เลขที่รับ</th>
                  <th style="width:140px; text-align:center;">วันที่รับ</th>
                  <th style="width:220px; text-align:center;">เลขที่ใบส่งของ</th>
                  <th>รายการ/บริษัท</th>
                  <th style="width:140px; text-align:right;">จำนวนเงิน</th>
                  <th style="width:170px; text-align:center;">ระยะเวลา</th>
                  <th style="width:110px; text-align:center;">ลงรับเอกสาร</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($rowsData as $r): ?>
                <?php
                  $PayId = (int)($r['PayId'] ?? 0);
                  $DateIn = (string)($r['DateIn'] ?? '');
                  $DateReceive = (string)($r['DateReceive'] ?? '');
                  $ReceiveNo = (string)($r['ReceiveNo'] ?? '');
                  $Detail = (string)($r['Detail'] ?? '');
                  $CompanyName = (string)($r['CompanyName'] ?? '');
                  $Amount = (float)($r['Amount'] ?? 0);

                  $isReceived = ($DateReceive && $DateReceive !== '0000-00-00');
                  $displayDate = $isReceived ? thaiDateShort($DateReceive) : '<span class="badge-pill bd-wait">รอลงรับเอกสาร</span>';

                  $calcEnd = $isReceived ? $DateReceive : date('Y-m-d');
                  $longterm = ($DateIn ? diffThaiText($DateIn, $calcEnd) : '-');

                  $modalId = "modalReceive{$PayId}";
                ?>
                <tr>
                  <td style="text-align:center;"><?php echo $PayId; ?></td>
                  <td style="text-align:center;"><?php echo $ReceiveNo !== '' ? h($ReceiveNo) : '-'; ?></td>
                  <td style="text-align:center;"><?php echo $displayDate; ?></td>
                  <td style="text-align:center;"><?php echo h($Detail); ?></td>
                  <td><?php echo h($CompanyName); ?></td>
                  <td style="text-align:right;"><?php echo number_format($Amount, 2); ?></td>
                  <td style="text-align:center;"><?php echo h($longterm); ?></td>
                  <td style="text-align:center;">
                    <button type="button"
                            class="btn btn-success btn-open-receive"
                            data-payid="<?php echo $PayId; ?>">
                      <span class="msi">inbox</span>
                    </button>

                    <!-- Modal -->
                    <div class="modal fade" id="<?php echo h($modalId); ?>" tabindex="-1" role="dialog" aria-labelledby="<?php echo h($modalId); ?>Label">
                      <div class="modal-dialog modal-sm" role="document">
                        <div class="modal-content" style="border-radius:16px; overflow:hidden;">
                          <div class="modal-header" style="background:#e9f7ee;">
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title" id="<?php echo h($modalId); ?>Label">
                              <span class="msi">info</span> ลงรับเอกสาร
                            </h4>
                          </div>

                          <!-- *ใช้ GET เพื่อคงความเข้ากันได้กับ received.php เดิม -->
                          <form method="get" action="received.php">
                            <div class="modal-body">
                              <p style="margin-bottom:10px;">
                                ลงรับเอกสาร รหัสรายการที่ <b><?php echo $PayId; ?></b>
                              </p>

                              <div class="form-group">
                                <label class="control-label">เลขรับที่</label>
                                <input type="text" class="form-control" name="ReceiveNo" required>
                              </div>

                              <div class="form-group">
                                <label class="control-label">วันที่ลงรับเอกสาร</label>
                                <input type="text"
                                       class="form-control fp-date-th"
                                       name="DateReceive"
                                       value="<?php echo h($todayThai); ?>"
                                       placeholder="dd/mm/yyyy"
                                       required>
                              </div>

                              <input type="hidden" name="PayId" value="<?php echo $PayId; ?>">
                              <!-- เก็บ query เดิมไว้ เผื่อ received.php จะเอาไป redirect กลับ -->
                              <input type="hidden" name="return" value="receive.php?<?php echo h(buildQuery(['pn'=>$pn])); ?>">
                            </div>

                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                              <button type="submit" class="btn btn-success">บันทึก</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <div style="text-align:center; margin-top:6px;">
            <?php echo $paginationCtrls; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div style="height:16px;"></div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
  <script>
    // เปิด modal ตาม payid
    $(function(){
      $(document).on('click', '.btn-open-receive', function(){
        var payid = $(this).data('payid');
        var $modal = $('#modalReceive' + payid);
        $modal.modal('show');
        $modal.on('shown.bs.modal', function(){
          var dateEl = $modal.find('.fp-date-th')[0];
          if (dateEl && !dateEl._flatpickr) {
            flatpickr(dateEl, {
              locale:'th', dateFormat:'d/m/Y', allowInput:true,
              defaultDate: new Date(),
              onChange: function(sd,ds,inst){ if(!sd.length)return;var d=sd[0];inst.element.value=('0'+d.getDate()).slice(-2)+'/'+('0'+(d.getMonth()+1)).slice(-2)+'/'+(d.getFullYear()+543); },
              parseDate: function(s){ if(!s)return null;var p=s.split('/');if(p.length!==3)return null;var y=parseInt(p[2]);if(y>2500)y-=543;return new Date(y,parseInt(p[1])-1,parseInt(p[0])); }
            });
            // show BE year on init
            var d=new Date();
            dateEl.value=('0'+d.getDate()).slice(-2)+'/'+('0'+(d.getMonth()+1)).slice(-2)+'/'+(d.getFullYear()+543);
          }
        });
      });
    });
  </script>
</body>
</html>
