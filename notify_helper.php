<?php
// notify_helper.php — ฟังก์ชัน MOPH ALERT ร่วมสำหรับการแจ้งเตือนสถานะรายการ
declare(strict_types=1);

require_once __DIR__ . '/moph_alert_config.php';

/**
 * ส่ง MOPH ALERT
 */
function moph_send(array $messages): bool {
    $json = json_encode(['messages' => $messages], JSON_UNESCAPED_UNICODE);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, MOPH_ALERT_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'client-key: ' . MOPH_CLIENT_KEY,
        'secret-key: '  . MOPH_SECRET_KEY,
    ]);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, MOPH_ALERT_CONNECT_TIMEOUT);
    curl_setopt($ch, CURLOPT_TIMEOUT,        MOPH_ALERT_TIMEOUT);
    $resp     = curl_exec($ch);
    $errno    = curl_errno($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($errno !== 0) {
        error_log("MOPH ALERT cURL error ({$errno}): " . substr((string)$resp, 0, 500));
        return false;
    }
    if ($httpCode >= 200 && $httpCode < 300) return true;
    error_log("MOPH ALERT HTTP {$httpCode}: " . substr((string)$resp, 0, 500));
    return false;
}

/**
 * สร้าง Flex Bubble แจ้งเตือนการเปลี่ยนสถานะ
 *
 * @param int    $payId        รหัสรายการ
 * @param string $statusText   ข้อความสถานะ เช่น "รอรับเอกสารการเงิน"
 * @param string $statusEmoji  emoji ประจำสถานะ เช่น "🟡"
 * @param string $companyName  ชื่อบริษัท/ร้านค้า
 * @param string $detail       เลขที่ใบส่งของ
 * @param string $amount       จำนวนเงินรวม (รูปแบบ "1,234.56")
 * @param string $actor        ชื่อผู้ดำเนินการ (จาก session)
 * @param string $extra        ข้อมูลเพิ่มเติม เช่น "เลขเช็ค: 001234" (ว่างได้)
 */
function moph_status_messages(
    int    $payId,
    string $statusText,
    string $statusEmoji,
    string $companyName,
    string $detail,
    string $amount,
    string $actor = '',
    string $extra = ''
): array {
    $dt   = new DateTimeImmutable('now', new DateTimeZone('Asia/Bangkok'));
    $date = $dt->format('Y-m-d');
    $time = $dt->format('H:i:s');
    $actor = $actor ?: 'ระบบ';

    // สีตามสถานะ
    $statusColor = '#0B6E4F';
    if (mb_strpos($statusText, 'รอรับ') !== false)    $statusColor = '#F59E0B';
    if (mb_strpos($statusText, 'เช็ค') !== false)      $statusColor = '#7C3AED';
    if (mb_strpos($statusText, 'เบิกจ่าย') !== false) $statusColor = '#2563EB';
    if (mb_strpos($statusText, 'อนุมัติ') !== false)  $statusColor = '#EA580C';

    $summaryText = sprintf(
        "%s อัปเดตสถานะรายการ\n ------------------------\n🆔 รหัสรายการ: #%d\n🔄 สถานะ: %s %s\n🏢 บริษัท: %s\n📄 เลขที่ใบส่งของ: %s\n💵 ยอดรวม: %s บาท%s\n ------------------------\n👤 ผู้ดำเนินการ: %s\n📅 วันที่: %s  ⏰ เวลา: %s\n ------------------------",
        $statusEmoji, $payId, $statusEmoji, $statusText,
        $companyName, $detail, $amount,
        $extra ? "\n" . $extra : '',
        $actor, $date, $time
    );

    // ── Flex Bubble ──
    $dataRows = [
        [
            "type" => "box", "layout" => "baseline",
            "contents" => [
                ["type" => "text", "text" => "🆔", "size" => "sm", "flex" => 0],
                ["type" => "text", "text" => "รหัสรายการ", "size" => "sm", "color" => "#8a8a8a", "flex" => 3],
                ["type" => "text", "text" => "#" . $payId, "size" => "sm", "align" => "end", "weight" => "bold", "flex" => 4],
            ],
        ],
        [
            "type" => "box", "layout" => "baseline",
            "contents" => [
                ["type" => "text", "text" => "🏢", "size" => "sm", "flex" => 0],
                ["type" => "text", "text" => "บริษัท/ร้านค้า", "size" => "sm", "color" => "#8a8a8a", "flex" => 3],
                ["type" => "text", "text" => $companyName, "size" => "sm", "align" => "end", "weight" => "bold", "flex" => 4, "wrap" => true],
            ],
        ],
        [
            "type" => "box", "layout" => "baseline",
            "contents" => [
                ["type" => "text", "text" => "📄", "size" => "sm", "flex" => 0],
                ["type" => "text", "text" => "เลขที่ใบส่งของ", "size" => "sm", "color" => "#8a8a8a", "flex" => 3],
                ["type" => "text", "text" => $detail, "size" => "sm", "align" => "end", "weight" => "bold", "flex" => 4],
            ],
        ],
        [
            "type" => "box", "layout" => "baseline",
            "contents" => [
                ["type" => "text", "text" => "💵", "size" => "sm", "flex" => 0],
                ["type" => "text", "text" => "ยอดรวม", "size" => "sm", "color" => "#8a8a8a", "flex" => 3],
                ["type" => "text", "text" => $amount . " บาท", "size" => "sm", "align" => "end", "weight" => "bold", "flex" => 4, "color" => CI_COLOR_PRIMARY],
            ],
        ],
    ];

    // แทรกข้อมูลเพิ่มเติม (เช่น เลขเช็ค)
    if ($extra !== '') {
        $dataRows[] = [
            "type" => "box", "layout" => "baseline",
            "contents" => [
                ["type" => "text", "text" => "📝", "size" => "sm", "flex" => 0],
                ["type" => "text", "text" => "หมายเหตุ", "size" => "sm", "color" => "#8a8a8a", "flex" => 3],
                ["type" => "text", "text" => $extra, "size" => "sm", "align" => "end", "weight" => "bold", "flex" => 4, "wrap" => true],
            ],
        ];
    }

    $dataRows[] = [
        "type" => "box", "layout" => "baseline",
        "contents" => [
            ["type" => "text", "text" => "👤", "size" => "sm", "flex" => 0],
            ["type" => "text", "text" => "ผู้ดำเนินการ", "size" => "sm", "color" => "#8a8a8a", "flex" => 3],
            ["type" => "text", "text" => $actor, "size" => "sm", "align" => "end", "weight" => "bold", "flex" => 4],
        ],
    ];

    $bubble = [
        "type" => "bubble",
        "size" => "giga",
        "header" => [
            "type" => "box", "layout" => "vertical", "paddingAll" => "0px",
            "contents" => [[
                "type" => "image", "url" => CI_LOGO_URL,
                "size" => "full", "aspectMode" => "cover", "aspectRatio" => "3120:885",
            ]],
        ],
        "body" => [
            "type" => "box", "layout" => "vertical", "spacing" => "md",
            "contents" => [
                // หัวเรื่อง
                [
                    "type" => "box", "layout" => "vertical", "margin" => "sm",
                    "contents" => [
                        ["type" => "text", "text" => "🔄 อัปเดตสถานะรายการ", "size" => "lg", "weight" => "bold", "color" => CI_COLOR_PRIMARY, "align" => "center"],
                        ["type" => "text", "text" => $statusEmoji . " " . $statusText, "size" => "md", "weight" => "bold", "color" => $statusColor, "align" => "center", "margin" => "xs"],
                    ],
                ],
                ["type" => "separator", "margin" => "sm"],
                ["type" => "box", "layout" => "vertical", "spacing" => "sm", "contents" => $dataRows],
                ["type" => "separator", "margin" => "sm"],
                // วันที่/เวลา
                [
                    "type" => "box", "layout" => "baseline",
                    "contents" => [
                        ["type" => "text", "text" => "📅 " . $date, "size" => "sm", "color" => "#8a8a8a", "flex" => 2],
                        ["type" => "text", "text" => "⏰ " . $time, "size" => "sm", "color" => "#8a8a8a", "align" => "end", "flex" => 3],
                    ],
                ],
                ["type" => "separator", "margin" => "sm"],
            ],
        ],
    ];

    return [
        ["type" => "text", "text" => $summaryText],
        ["type" => "flex", "altText" => "อัปเดตสถานะ: " . $statusText, "contents" => $bubble],
    ];
}

/**
 * ดึงข้อมูลรายการจากตาราง payment (PayId, CompanyName, Detail, Amount)
 * คืนค่า array ['payId'=>int, 'company'=>string, 'detail'=>string, 'amount'=>string]
 * หรือ null หากไม่พบ
 */
function moph_get_payment_info(mysqli $conn, int $payId): ?array {
    $stmt = $conn->prepare(
        "SELECT p.PayId, p.Detail, p.Amount, p.CompanyId, c.CompanyName
         FROM payment p
         LEFT JOIN company c ON c.CompanyId = p.CompanyId
         WHERE p.PayId = ? LIMIT 1"
    );
    if (!$stmt) return null;
    $stmt->bind_param("i", $payId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) return null;
    return [
        'payId'   => (int)$row['PayId'],
        'company' => (string)($row['CompanyName'] ?? '—'),
        'detail'  => (string)($row['Detail']      ?? '—'),
        'amount'  => number_format((float)($row['Amount'] ?? 0), 2),
    ];
}
