<?php
// moph_alert_config.php
declare(strict_types=1);

// Endpoint ตามตัวอย่างของคุณ
define('MOPH_ALERT_URL', 'https://morpromt2f.moph.go.th/api/notify/send?messages=yes');

// ใส่ค่าจริงของคุณ (อย่า commit ค่าเหล่านี้สู่ repo สาธารณะ)
define('MOPH_CLIENT_KEY', '5f9f001dbabc7794ebbe5769a02dfc636782e1f2');
define('MOPH_SECRET_KEY', 'YLNQE2A65PEIZQXA72JMQ7CQEDYY');

define('MOPH_ALERT_CONNECT_TIMEOUT', 2);
define('MOPH_ALERT_TIMEOUT', 4);

// --- CI settings (ปรับให้ตรง CI ของหน่วยงาน) ---
// โทนสีหลัก/รอง/เน้น (จาก login.php ของคุณ)
define('CI_COLOR_PRIMARY', '#0B6E4F');   // เขียวกระทรวง (primary)
define('CI_COLOR_SECONDARY', '#08A045'); // เขียวอ่อน (secondary)
define('CI_COLOR_ACCENT', '#FFB81C');    // ทอง (accent)
define('CI_COLOR_TEXT', '#2D2D2D');      // สีข้อความหลัก
define('CI_BG_LIGHT', '#E8F5E9');        // พื้นหลังอ่อน

// โลโก้สำหรับ Flex header (ใช้ URL ที่สามารถเข้าถึงได้)
define('CI_LOGO_URL', 'https://cdns.yellow-idea.com/moph/20250602/moph-flex-header-1.png');

// ขนาดตัวอักษร (ค่าประมาณสำหรับ Flex; ปรับได้ตามต้องการ)
define('CI_FONTSIZE_TITLE', '18px');     // หัวข้อ
define('CI_FONTSIZE_LABEL', '12px');     // ป้าย label เล็ก
define('CI_FONTSIZE_VALUE', '14px');     // ค่า / รายละเอียด
