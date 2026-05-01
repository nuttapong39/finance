<?php
/**
 * backup.php — Database Backup & Export
 * Pure-PHP SQL dump: ทำงานได้บน shared hosting ทุกเจ้า
 * ไม่ต้องการ exec(), mysqldump, หรือ SSH
 */

/* ══════════════════════════════════════════════════════════
   ตรวจสอบ Action Download ก่อน output ทุกอย่าง
   ══════════════════════════════════════════════════════════ */
if (isset($_GET['do']) && $_GET['do'] === 'download') {

    // 1. Session + DB (ก่อน header.php เพื่อไม่ให้มี HTML output)
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    require_once __DIR__ . '/connect_db.php';
    require_once __DIR__ . '/notify_helper.php';

    // 2. Auth check
    if (empty($_SESSION['Username'])) {
        header('Location: login.php'); exit;
    }

    // 3. ล้าง output buffer ที่อาจค้างอยู่
    while (ob_get_level()) ob_end_clean();

    // 4. ชื่อไฟล์ + Headers
    $filename = 'finance_backup_' . date('Ymd_His') . '.sql';
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // 5. สร้าง SQL dump แบบ stream (flush ทีละก้อน — รองรับ DB ขนาดใหญ่)
    $dbName = $conn->query("SELECT DATABASE()")->fetch_row()[0];

    echo "-- =====================================================\n";
    echo "-- MOPH Finance System — Database Backup\n";
    echo "-- Database : $dbName\n";
    echo "-- Generated: " . date('Y-m-d H:i:s') . " (Asia/Bangkok)\n";
    echo "-- Server   : " . $conn->host_info . "\n";
    echo "-- Exported by: " . ($_SESSION['Names'] ?? $_SESSION['Username'] ?? 'Unknown') . "\n";
    echo "-- =====================================================\n\n";
    echo "SET FOREIGN_KEY_CHECKS = 0;\n";
    echo "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
    echo "SET time_zone = '+07:00';\n";
    echo "SET NAMES utf8mb4;\n";
    echo "SET CHARACTER SET utf8mb4;\n\n";
    flush();

    // ดึงรายชื่อ tables ทั้งหมด
    $tables = [];
    $res = $conn->query("SHOW TABLES");
    while ($row = $res->fetch_row()) $tables[] = $row[0];

    foreach ($tables as $table) {
        echo "\n-- ---------------------------------------------------\n";
        echo "-- Table: `$table`\n";
        echo "-- ---------------------------------------------------\n";
        echo "DROP TABLE IF EXISTS `$table`;\n";

        // CREATE TABLE statement
        $createRes = $conn->query("SHOW CREATE TABLE `" . $conn->real_escape_string($table) . "`");
        $createRow = $createRes->fetch_row();
        echo $createRow[1] . ";\n\n";
        flush();

        // INSERT DATA (batch ทีละ 200 rows เพื่อประหยัด memory)
        $dataRes = $conn->query("SELECT * FROM `" . $conn->real_escape_string($table) . "`");
        if ($dataRes && $dataRes->num_rows > 0) {
            $numFields = $dataRes->field_count;
            // ดึง column names
            $cols = [];
            $fields = $dataRes->fetch_fields();
            foreach ($fields as $f) $cols[] = "`" . $f->name . "`";
            $colStr = implode(', ', $cols);

            $batch = [];
            while ($row = $dataRes->fetch_row()) {
                $vals = [];
                for ($i = 0; $i < $numFields; $i++) {
                    if ($row[$i] === null) {
                        $vals[] = 'NULL';
                    } else {
                        $vals[] = "'" . $conn->real_escape_string($row[$i]) . "'";
                    }
                }
                $batch[] = '(' . implode(', ', $vals) . ')';

                // flush ทุก 200 rows
                if (count($batch) >= 200) {
                    echo "INSERT INTO `$table` ($colStr) VALUES\n";
                    echo implode(",\n", $batch) . ";\n";
                    flush();
                    $batch = [];
                }
            }
            // rows ที่เหลือ
            if (!empty($batch)) {
                echo "INSERT INTO `$table` ($colStr) VALUES\n";
                echo implode(",\n", $batch) . ";\n";
                flush();
            }
        }
        echo "\n";
    }

    echo "\nSET FOREIGN_KEY_CHECKS = 1;\n";
    echo "\n-- =====================================================\n";
    echo "-- Backup complete · " . date('Y-m-d H:i:s') . "\n";
    echo "-- =====================================================\n";
    flush();

    // ── ส่ง MOPH ALERT หลัง dump เสร็จสมบูรณ์ ──────────────
    // ignore_user_abort: ให้ PHP รันต่อแม้ browser ปิดการเชื่อมต่อแล้ว
    ignore_user_abort(true);
    try {
        $dt       = new DateTimeImmutable('now', new DateTimeZone('Asia/Bangkok'));
        $dateStr  = $dt->format('Y-m-d');
        $timeStr  = $dt->format('H:i:s');
        $actor    = trim(($_SESSION['Names'] ?? '') . ' (' . ($_SESSION['Username'] ?? '') . ')');
        $actor    = ($actor === ' ()') ? 'ระบบ' : $actor;
        $tblCount = count($tables);

        // ขนาด DB (MB) สำหรับแจ้งเตือน
        $szRow   = $conn->query("SELECT ROUND(SUM(data_length+index_length)/1024/1024,2) AS mb FROM information_schema.tables WHERE table_schema=DATABASE()")->fetch_assoc();
        $sizeMB  = number_format((float)($szRow['mb'] ?? 0), 2);

        $summaryText = sprintf(
            "✅ สำรองข้อมูลสำเร็จ\n ------------------------\n📁 ไฟล์: %s\n🗄️ ฐานข้อมูล: %s\n📊 จำนวนตาราง: %d tables\n💾 ขนาด: %s MB\n ------------------------\n👤 ผู้ดำเนินการ: %s\n📅 วันที่: %s  ⏰ เวลา: %s\n ------------------------",
            $filename, $dbName, $tblCount, $sizeMB, $actor, $dateStr, $timeStr
        );

        $bubble = [
            "type" => "bubble", "size" => "giga",
            "header" => [
                "type" => "box", "layout" => "vertical", "paddingAll" => "0px",
                "contents" => [["type" => "image", "url" => CI_LOGO_URL,
                    "size" => "full", "aspectMode" => "cover", "aspectRatio" => "3120:885"]]
            ],
            "body" => [
                "type" => "box", "layout" => "vertical", "spacing" => "md",
                "contents" => [
                    [
                        "type" => "box", "layout" => "vertical", "margin" => "sm",
                        "contents" => [
                            ["type" => "text", "text" => "✅ สำรองข้อมูลสำเร็จ",
                             "size" => "lg", "weight" => "bold", "color" => CI_COLOR_PRIMARY, "align" => "center"],
                            ["type" => "text", "text" => "Database Backup Complete",
                             "size" => "sm", "color" => "#666666", "align" => "center", "margin" => "xs"],
                        ]
                    ],
                    ["type" => "separator", "margin" => "sm"],
                    [
                        "type" => "box", "layout" => "vertical", "spacing" => "sm",
                        "contents" => [
                            [
                                "type" => "box", "layout" => "baseline",
                                "contents" => [
                                    ["type" => "text", "text" => "📁", "size" => "sm", "flex" => 0],
                                    ["type" => "text", "text" => "ชื่อไฟล์", "size" => "sm", "color" => "#8a8a8a", "flex" => 2],
                                    ["type" => "text", "text" => $filename, "size" => "sm", "align" => "end",
                                     "weight" => "bold", "flex" => 5, "wrap" => true, "color" => CI_COLOR_PRIMARY],
                                ]
                            ],
                            [
                                "type" => "box", "layout" => "baseline",
                                "contents" => [
                                    ["type" => "text", "text" => "🗄️", "size" => "sm", "flex" => 0],
                                    ["type" => "text", "text" => "ฐานข้อมูล", "size" => "sm", "color" => "#8a8a8a", "flex" => 2],
                                    ["type" => "text", "text" => $dbName, "size" => "sm", "align" => "end", "weight" => "bold", "flex" => 5],
                                ]
                            ],
                            [
                                "type" => "box", "layout" => "baseline",
                                "contents" => [
                                    ["type" => "text", "text" => "📊", "size" => "sm", "flex" => 0],
                                    ["type" => "text", "text" => "จำนวนตาราง", "size" => "sm", "color" => "#8a8a8a", "flex" => 2],
                                    ["type" => "text", "text" => $tblCount . " tables", "size" => "sm", "align" => "end", "weight" => "bold", "flex" => 5],
                                ]
                            ],
                            [
                                "type" => "box", "layout" => "baseline",
                                "contents" => [
                                    ["type" => "text", "text" => "💾", "size" => "sm", "flex" => 0],
                                    ["type" => "text", "text" => "ขนาด DB", "size" => "sm", "color" => "#8a8a8a", "flex" => 2],
                                    ["type" => "text", "text" => $sizeMB . " MB", "size" => "sm", "align" => "end", "weight" => "bold", "flex" => 5],
                                ]
                            ],
                            [
                                "type" => "box", "layout" => "baseline",
                                "contents" => [
                                    ["type" => "text", "text" => "👤", "size" => "sm", "flex" => 0],
                                    ["type" => "text", "text" => "ผู้ดำเนินการ", "size" => "sm", "color" => "#8a8a8a", "flex" => 2],
                                    ["type" => "text", "text" => $actor, "size" => "sm", "align" => "end", "weight" => "bold", "flex" => 5],
                                ]
                            ],
                        ]
                    ],
                    ["type" => "separator", "margin" => "sm"],
                    [
                        "type" => "box", "layout" => "baseline",
                        "contents" => [
                            ["type" => "text", "text" => "📅 " . $dateStr, "size" => "sm", "color" => "#8a8a8a", "flex" => 2],
                            ["type" => "text", "text" => "⏰ " . $timeStr, "size" => "sm", "color" => "#8a8a8a", "align" => "end", "flex" => 3],
                        ]
                    ],
                    ["type" => "separator", "margin" => "sm"],
                ]
            ]
        ];

        moph_broadcast([
            ["type" => "text", "text" => $summaryText],
            ["type" => "flex", "altText" => "สำรองข้อมูลสำเร็จ · " . $filename, "contents" => $bubble],
        ], $conn);
    } catch (Throwable $e) {
        error_log("MOPH ALERT backup exception: " . $e->getMessage());
    }
    // ─────────────────────────────────────────────────────────

    $conn->close();
    exit;
}

/* ══════════════════════════════════════════════════════════
   หน้าปกติ — แสดง UI
   ══════════════════════════════════════════════════════════ */
require_once __DIR__ . '/header.php';
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Bangkok');

/* ── DB Statistics ─────────────────────────────────────── */
// จำนวนตาราง
$tableCount = (int)$conn->query(
    "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()"
)->fetch_row()[0];

// ขนาด DB (MB)
$dbSizeRow = $conn->query(
    "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS mb
     FROM information_schema.tables WHERE table_schema = DATABASE()"
)->fetch_assoc();
$dbSizeMB = (float)($dbSizeRow['mb'] ?? 0);

// จำนวนรายการ payment
$payCount = (int)$conn->query("SELECT COUNT(*) FROM payment")->fetch_row()[0];

// วันที่บันทึกล่าสุด
$lastDateRow = $conn->query(
    "SELECT MAX(DateIn) AS ld FROM payment WHERE DateIn NOT IN ('0000-00-00','')"
)->fetch_assoc();
$lastDate = $lastDateRow['ld'] ?? null;
function bkDateThai(?string $d): string {
    if (!$d || $d === '0000-00-00') return '—';
    $ts = strtotime($d); if (!$ts) return '—';
    $m = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    return date('j',$ts).' '.$m[(int)date('n',$ts)].' '.(date('Y',$ts)+543);
}

// รายชื่อตาราง + จำนวน rows
$tableList = [];
$res = $conn->query("SHOW TABLES");
while ($row = $res->fetch_row()) {
    $t = $row[0];
    $cnt = $conn->query("SELECT COUNT(*) FROM `" . $conn->real_escape_string($t) . "`")->fetch_row()[0];
    $szRow = $conn->query(
        "SELECT ROUND((data_length + index_length)/1024, 1) AS kb
         FROM information_schema.tables
         WHERE table_schema=DATABASE() AND table_name='" . $conn->real_escape_string($t) . "'"
    )->fetch_assoc();
    $tableList[] = ['name' => $t, 'rows' => (int)$cnt, 'kb' => (float)($szRow['kb'] ?? 0)];
}

$nowThai = bkDateThai(date('Y-m-d')) . ' เวลา ' . date('H:i:s');
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" href="pic/fms.png">
  <title>สำรองข้อมูล – ระบบบริหารจัดการการเงินและบัญชี</title>
  <style>
    .bk-wrap { padding: 22px 24px 48px; }

    /* Title */
    .page-titlebar { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:22px; }
    .page-titlebar h3 { margin:0; font-size:20px; font-weight:800; color:#1f2a44; display:flex; align-items:center; gap:8px; }
    .page-titlebar .sub { font-size:13px; color:#64748b; margin-top:3px; }

    /* KPI mini cards */
    .stat-row { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px; }
    .stat-mini { background:#fff; border-radius:14px; padding:16px 18px; box-shadow:0 2px 10px rgba(0,0,0,.07);
                 border-left:4px solid #0B6E4F; display:flex; align-items:center; gap:12px; }
    .stat-mini-icon { width:44px; height:44px; border-radius:10px; background:#e8f5e9; color:#0B6E4F;
                      display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .stat-mini-label { font-size:11px; color:#64748b; font-weight:600; margin-bottom:2px; }
    .stat-mini-value { font-size:20px; font-weight:800; color:#1f2a44; line-height:1; }
    .stat-mini-sub   { font-size:11px; color:#94a3b8; margin-top:2px; }

    /* Main download card */
    .bk-card { background:#fff; border-radius:18px; box-shadow:0 4px 24px rgba(0,0,0,.08); margin-bottom:18px; overflow:hidden; }
    .bk-card-head { padding:16px 22px; border-bottom:1px solid #f1f5f9;
                    display:flex; align-items:center; gap:8px; }
    .bk-card-head .title { font-size:15px; font-weight:800; color:#1f2a44; display:flex; align-items:center; gap:7px; }
    .bk-card-body { padding:24px 22px; }

    /* Download button */
    .btn-download {
      display: inline-flex; align-items: center; gap: 10px;
      background: linear-gradient(135deg, #0B6E4F, #08A045);
      color: #fff; border: none; padding: 15px 40px;
      border-radius: 14px; font-size: 16px; font-weight: 800;
      font-family: 'Sarabun', sans-serif;
      box-shadow: 0 6px 20px rgba(11,110,79,.35);
      cursor: pointer; text-decoration: none;
      transition: all .2s;
    }
    .btn-download:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(11,110,79,.45); color:#fff; text-decoration:none; }
    .btn-download:active { transform: scale(.97); }
    .btn-download .msi { font-size: 24px; }

    /* Info box */
    .info-box { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px;
                padding:14px 18px; margin-bottom:20px; }
    .info-box-title { font-weight:800; color:#166534; font-size:13px; display:flex; align-items:center; gap:6px; margin-bottom:8px; }
    .info-box ul { margin:0; padding-left:18px; font-size:13px; color:#166534; }
    .info-box ul li { margin-bottom:4px; }

    /* Warning box */
    .warn-box { background:#fffbeb; border:1px solid #fde68a; border-radius:12px;
                padding:14px 18px; margin-top:16px; }
    .warn-box-title { font-weight:800; color:#92400e; font-size:13px; display:flex; align-items:center; gap:6px; margin-bottom:8px; }
    .warn-box ul { margin:0; padding-left:18px; font-size:13px; color:#78350f; }
    .warn-box ul li { margin-bottom:4px; }

    /* Table list */
    .tbl-list { width:100%; border-collapse:collapse; font-size:13px; }
    .tbl-list th { font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.4px;
                   padding:9px 12px; border-bottom:2px solid #f1f5f9; text-align:left; }
    .tbl-list td { padding:9px 12px; border-bottom:1px solid #f8fafc; vertical-align:middle; }
    .tbl-list tr:last-child td { border:none; }
    .tbl-list tr:hover td { background:#f8fafc; }
    .tbl-name { font-weight:700; color:#0B6E4F; font-family:monospace; font-size:13px; }
    .badge-rows { display:inline-block; padding:2px 9px; border-radius:99px;
                  background:#e8f5e9; color:#166534; font-size:11px; font-weight:700; }

    /* Restore steps */
    .restore-steps { counter-reset:step; list-style:none; padding:0; margin:0; }
    .restore-steps li { display:flex; gap:12px; align-items:flex-start; margin-bottom:12px;
                        font-size:13px; color:#374151; }
    .restore-steps li::before { counter-increment:step; content:counter(step);
      min-width:24px; height:24px; border-radius:50%; background:#0B6E4F; color:#fff;
      font-size:12px; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:1px; }
    .restore-steps li code { background:#f1f5f9; padding:1px 6px; border-radius:5px;
                             font-size:12px; color:#0B6E4F; font-weight:700; }

    .divider { height:1px; background:#f1f5f9; margin:20px 0; }

    /* Loading state */
    .btn-download.loading { background:#64748b; pointer-events:none; }

    @media(max-width:768px) { .stat-row { grid-template-columns:repeat(2,1fr); } }
    @media(max-width:480px) { .stat-row { grid-template-columns:1fr; } .bk-wrap { padding:14px 12px 32px; } }
  </style>
</head>
<body>
<div class="bk-wrap">

  <!-- Title -->
  <div class="page-titlebar">
    <div>
      <h3><span class="msi msi-24" style="color:#0B6E4F;">backup</span> สำรองและกู้คืนข้อมูล</h3>
      <div class="sub">Export ฐานข้อมูลทั้งหมดเป็นไฟล์ .sql · <?= $nowThai ?></div>
    </div>
    <a href="main.php" class="btn-go-back"><span class="msi">arrow_back</span> กลับหน้าหลัก</a>
  </div>

  <!-- Stats -->
  <div class="stat-row">
    <div class="stat-mini">
      <div class="stat-mini-icon"><span class="msi">table_chart</span></div>
      <div>
        <div class="stat-mini-label">จำนวนตาราง</div>
        <div class="stat-mini-value"><?= number_format($tableCount) ?></div>
        <div class="stat-mini-sub">tables</div>
      </div>
    </div>
    <div class="stat-mini" style="border-color:#3B82F6;">
      <div class="stat-mini-icon" style="background:#eff6ff; color:#2563EB;"><span class="msi">storage</span></div>
      <div>
        <div class="stat-mini-label">ขนาดฐานข้อมูล</div>
        <div class="stat-mini-value"><?= number_format($dbSizeMB, 2) ?> <span style="font-size:13px; font-weight:400;">MB</span></div>
        <div class="stat-mini-sub"><?= number_format($dbSizeMB * 1024, 0) ?> KB</div>
      </div>
    </div>
    <div class="stat-mini" style="border-color:#F59E0B;">
      <div class="stat-mini-icon" style="background:#fef3c7; color:#D97706;"><span class="msi">receipt_long</span></div>
      <div>
        <div class="stat-mini-label">รายการ payment</div>
        <div class="stat-mini-value"><?= number_format($payCount) ?></div>
        <div class="stat-mini-sub">รายการทั้งหมด</div>
      </div>
    </div>
    <div class="stat-mini" style="border-color:#8B5CF6;">
      <div class="stat-mini-icon" style="background:#f5f3ff; color:#7C3AED;"><span class="msi">calendar_today</span></div>
      <div>
        <div class="stat-mini-label">บันทึกล่าสุด</div>
        <div class="stat-mini-value" style="font-size:14px;"><?= bkDateThai($lastDate) ?></div>
        <div class="stat-mini-sub">วันที่รับเอกสาร</div>
      </div>
    </div>
  </div>

  <div class="row g-3">

    <!-- ── Download Card ── -->
    <div class="col-lg-7">
      <div class="bk-card">
        <div class="bk-card-head">
          <div class="title"><span class="msi">cloud_download</span> Export / Download ข้อมูล</div>
        </div>
        <div class="bk-card-body">

          <div class="info-box">
            <div class="info-box-title"><span class="msi msi-18">check_circle</span> ไฟล์ที่จะได้รับ</div>
            <ul>
              <li>ไฟล์ <strong>.sql</strong> ขนาดประมาณ <?= number_format($dbSizeMB * 1.5, 1) ?> MB (ก่อน compress)</li>
              <li>ชื่อไฟล์: <code>finance_backup_<?= date('Ymd_His') ?>.sql</code></li>
              <li>ครอบคลุมทุก table — โครงสร้าง + ข้อมูลทั้งหมด</li>
              <li>รองรับ MySQL / MariaDB · charset UTF-8mb4</li>
            </ul>
          </div>

          <!-- Big Download Button -->
          <div style="text-align:center; padding:20px 0 16px;">
            <a href="backup.php?do=download"
               class="btn-download"
               id="btnDownload"
               onclick="startDownload(this)">
              <span class="msi">download</span>
              ดาวน์โหลด Backup ทันที
            </a>
            <div id="dlStatus" style="margin-top:14px; font-size:13px; color:#64748b; display:none;">
              <span class="msi msi-18" style="animation:spin .8s linear infinite; vertical-align:middle;">progress_activity</span>
              กำลังสร้างไฟล์... อาจใช้เวลาสักครู่ กรุณาอย่าปิดหน้าต่าง
            </div>
          </div>

          <div class="warn-box">
            <div class="warn-box-title"><span class="msi msi-18">warning</span> ข้อควรระวัง</div>
            <ul>
              <li>ไฟล์ backup มีข้อมูลทางการเงิน — <strong>เก็บในที่ปลอดภัย</strong></li>
              <li>ไม่ควร backup บ่อยเกินไปในช่วง server busy</li>
              <li>แนะนำ backup ก่อนทำการอัปเดตระบบหรือเปลี่ยน hosting ทุกครั้ง</li>
            </ul>
          </div>

        </div>
      </div>

      <!-- ── Restore Guide ── -->
      <div class="bk-card">
        <div class="bk-card-head">
          <div class="title"><span class="msi">settings_backup_restore</span> วิธีกู้คืนข้อมูล (Restore)</div>
        </div>
        <div class="bk-card-body">
          <ol class="restore-steps">
            <li>เข้า <strong>phpMyAdmin</strong> บน hosting ใหม่ที่ต้องการกู้คืน</li>
            <li>สร้างฐานข้อมูลใหม่ชื่อ <code>finance</code> (หรือชื่อเดิม) พร้อมตั้ง charset เป็น <code>utf8mb4_unicode_ci</code></li>
            <li>คลิกที่ฐานข้อมูลที่สร้าง → เลือกแท็บ <strong>Import</strong></li>
            <li>กด Choose File → เลือกไฟล์ <code>.sql</code> ที่ดาวน์โหลดมา</li>
            <li>กด <strong>Go / Execute</strong> รอจนเสร็จ</li>
            <li>แก้ไขค่า DB ใน <code>connect_db.php</code> ให้ตรงกับ hosting ใหม่</li>
          </ol>

          <div class="divider"></div>

          <div style="font-size:12px; color:#64748b; display:flex; align-items:center; gap:6px;">
            <span class="msi msi-18" style="color:#0B6E4F;">terminal</span>
            <span>หรือใช้ CLI: <code style="background:#f1f5f9; padding:2px 8px; border-radius:5px; color:#0B6E4F;">mysql -u root -p finance &lt; finance_backup_YYYYMMDD.sql</code></span>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Table List ── -->
    <div class="col-lg-5">
      <div class="bk-card" style="height:fit-content;">
        <div class="bk-card-head">
          <div class="title"><span class="msi">list_alt</span> ตารางที่จะ Export (<?= $tableCount ?> ตาราง)</div>
        </div>
        <div class="bk-card-body" style="padding:0; max-height:600px; overflow-y:auto;">
          <table class="tbl-list">
            <thead>
              <tr>
                <th>#</th>
                <th>ชื่อตาราง</th>
                <th class="text-end">Rows</th>
                <th class="text-end">ขนาด</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tableList as $i => $t): ?>
              <tr>
                <td style="color:#94a3b8; font-size:11px;"><?= $i+1 ?></td>
                <td><span class="tbl-name"><?= htmlspecialchars($t['name']) ?></span></td>
                <td class="text-end"><span class="badge-rows"><?= number_format($t['rows']) ?></span></td>
                <td class="text-end" style="color:#64748b; font-size:12px;">
                  <?= $t['kb'] >= 1024 ? number_format($t['kb']/1024,1).' MB' : number_format($t['kb'],1).' KB' ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- schedule tip -->
      <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px 16px; margin-top:14px;">
        <div style="font-size:12px; font-weight:800; color:#374151; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
          <span class="msi msi-18" style="color:#0B6E4F;">tips_and_updates</span> คำแนะนำ
        </div>
        <ul style="margin:0; padding-left:16px; font-size:12px; color:#64748b; line-height:1.9;">
          <li>Backup <strong>ก่อนทำ deploy</strong> ทุกครั้ง</li>
          <li>เก็บ backup <strong>อย่างน้อย 3 version</strong> ล่าสุด</li>
          <li>ทดสอบ restore บน local ก่อน deploy จริง</li>
          <li>ไฟล์ .sql สามารถเปิดดูด้วย text editor ได้</li>
        </ul>
      </div>
    </div>

  </div>
</div><!-- .bk-wrap -->

<style>
@keyframes spin { from { transform:rotate(0deg); } to { transform:rotate(360deg); } }
</style>

<script>
function startDownload(el) {
  var status = document.getElementById('dlStatus');
  el.classList.add('loading');
  el.innerHTML = '<span class="msi" style="animation:spin .6s linear infinite">progress_activity</span> กำลังเตรียมไฟล์...';
  status.style.display = 'block';

  // Reset หลัง 8 วินาที (download ควรเริ่มแล้ว)
  setTimeout(function() {
    el.classList.remove('loading');
    el.innerHTML = '<span class="msi">download</span> ดาวน์โหลด Backup ทันที';
    status.style.display = 'none';
  }, 8000);
}
</script>

</body>
</html>
