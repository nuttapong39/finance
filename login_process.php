<?php
// login_process.php (ปรับ: เพิ่ม IP และสวยงามกว่าเดิม)
declare(strict_types=1);
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Bangkok');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/connect_db.php';
require_once __DIR__ . '/moph_alert_config.php'; // ต้องมี MOPH_ALERT_URL, MOPH_CLIENT_KEY, MOPH_SECRET_KEY, timeouts

function is_hash(string $p): bool {
    return str_starts_with($p, '$2y$') || str_starts_with($p, '$2a$') || str_starts_with($p, '$argon2');
}

/**
 * อ่าน IP ของ client อย่างปลอดภัย (รองรับ proxy)
 */
function get_client_ip(): string {
    $keys = [
        'HTTP_X_FORWARDED_FOR',
        'HTTP_CLIENT_IP',
        'HTTP_X_REAL_IP',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'REMOTE_ADDR'
    ];
    foreach ($keys as $k) {
        if (!empty($_SERVER[$k])) {
            $val = $_SERVER[$k];
            // HTTP_X_FORWARDED_FOR อาจมีหลายค่า เช่น "ip1, ip2"
            if (strpos($val, ',') !== false) {
                $parts = array_map('trim', explode(',', $val));
                foreach ($parts as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
                // ถ้าไม่มี public IP ใน list ให้ fallback ไปยัง first valid IP (แม้เป็น private)
                foreach ($parts as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            } else {
                if (filter_var($val, FILTER_VALIDATE_IP)) {
                    return $val;
                }
            }
        }
    }
    return 'unknown';
}

// ===== Day greeting =====
$dayIndex = (int) date('N');
$days = [
    1 => 'วันจันทร์',
    2 => 'วันอังคาร',
    3 => 'วันพุธ',
    4 => 'วันพฤหัสบดี',
    5 => 'วันศุกร์',
    6 => 'วันเสาร์',
    7 => 'วันอาทิตย์',
];
$day = $days[$dayIndex] ?? '';

// Greeting text
$greeting = "สวัสดี{$day} ยินดีต้อนรับเข้าสู่ระบบ";

/**
 * สร้าง messages array สำหรับ MOPH ALERT (summary text + improved flex bubble)
 * - ใส่ IP ของผู้ใช้
 * - ใช้ emoji เพื่อเป็นสัญลักษณ์บ่งบอก
 */
function build_moph_messages(string $username, string $names, string $Position, string $TypeUser , string $ip): array {
      $dayIndex = (int) date('N');
    $days = [
        1=>'วันจันทร์',2=>'วันอังคาร',3=>'วันพุธ',
        4=>'วันพฤหัสบดี',5=>'วันศุกร์',6=>'วันเสาร์',7=>'วันอาทิตย์'
      ];
    $day = $days[$dayIndex] ?? '';
    $dt = new DateTimeImmutable('now', new DateTimeZone('Asia/Bangkok'));
    $date = $dt->format('Y-m-d'); // ตัวอย่าง: 2026-02-02
    $time = $dt->format('H:i:s');

    // 1) ข้อความสรุป (text)
    $summaryText = sprintf(
        "✅ แจ้งเตือนการเข้าสู่ระบบ\n ------------------------\n👤 ชื่อผู้ใช้งาน: %s\n🧾 ชื่อ-สกุล: %s\n🏷️ ตำแหน่ง: %s\n ------------------------\n 🖥️ สิทธิ์การใช้งาน: %s\n ------------------------\n📅 วันที่: %s\n⏰ เวลา: %s\n🌐 IP: %s\n ------------------------",
        $username,
        $names,
        $Position,
        $TypeUser,
        $date,
        $time,
        $ip
    );

    // 2) Flex bubble — ปรับสวย: badge, icons, แถวจัดชัด
    $bubble = [
        "type" => "bubble",
        "size" => "giga",
        "header" => [
            "type" => "box",
            "layout" => "vertical",
            "paddingAll" => "0px",
            "contents" => [
                [
                    "type" => "image",
                    "url" => "https://cdns.yellow-idea.com/moph/20250602/moph-flex-header-1.png",
                    "size" => "full",
                    "aspectMode" => "cover",
                    "aspectRatio" => "3120:885"
                ]
            ]
        ],
        "body" => [
            "type" => "box",
            "layout" => "vertical",
            "spacing" => "md",
            "contents" => [
                // Title / Badge row
                [
                  "type" => "box",
                  "layout" => "vertical",
                  "margin" => "sm",
                  "contents" => [
                    [
                      "type" => "text",
                      "text" => "👋 สวัสดี{$day}",
                      "size" => "lg",
                      "weight" => "bold",
                      "color" => "#0B6E4F",
                      "align" => "center"
                    ],
                    [
                      "type" => "text",
                      "text" => "ยินดีต้อนรับเข้าสู่ระบบ",
                      "size" => "lg",
                      "color" => "#666666",
                      "align" => "center",
                      "margin" => "xs"
                    ]
                  ]
                ],

                [
                    "type" => "box",
                    "layout" => "baseline",
                    "contents" => [
                        [
                            "type" => "text",
                            "text" => "🔔 แจ้งเตือนการเข้าสู่ระบบ",
                            "weight" => "bold",
                            "size" => "lg",
                            "flex" => 0
                        ],
                        [
                            "type" => "text",
                            "text" => "สำเร็จ",
                            "size" => "sm",
                            "color" => "#0B6E4F",
                            "align" => "end",
                            "weight" => "bold"
                        ]
                    ]
                ],

                // Divider-like spacer
                [
                    "type" => "separator",
                    "margin" => "sm"
                ],

                // User info block with icons
                [
                    "type" => "box",
                    "layout" => "vertical",
                    "spacing" => "sm",
                    "contents" => [
                        [
                            "type" => "box",
                            "layout" => "baseline",
                            "contents" => [
                                ["type" => "text", "text" => "👤", "size" => "sm", "flex" => 0],
                                ["type" => "text", "text" => "ชื่อผู้ใช้งาน", "size" => "sm", "color" => "#8a8a8a", "flex" => 2],
                                ["type" => "text", "text" => $username, "size" => "sm", "align" => "end", "weight" => "bold", "flex" => 3]
                            ]
                        ],
                        [
                            "type" => "box",
                            "layout" => "baseline",
                            "contents" => [
                                ["type" => "text", "text" => "🧾", "size" => "sm", "flex" => 0],
                                ["type" => "text", "text" => "ชื่อ-สกุล", "size" => "sm", "color" => "#8a8a8a", "flex" => 2],
                                ["type" => "text", "text" => $names, "size" => "sm", "align" => "end", "weight" => "bold", "flex" => 3]
                            ]
                        ],
                        [
                            "type" => "box",
                            "layout" => "baseline",
                            "contents" => [
                                ["type" => "text", "text" => "🏷️", "size" => "sm", "flex" => 0],
                                ["type" => "text", "text" => "ตำแหน่ง", "size" => "sm", "color" => "#8a8a8a", "flex" => 2],
                                ["type" => "text", "text" => $Position, "size" => "sm", "align" => "end", "weight" => "bold", "flex" => 3]
                            ]
                            ],
                        [
                            "type" => "box",
                            "layout" => "baseline",
                            "contents" => [
                                ["type" => "text", "text" => "🖥️", "size" => "sm", "flex" => 0],
                                ["type" => "text", "text" => "สิทธื์การใช้งาน", "size" => "sm", "color" => "#8a8a8a", "flex" => 2],
                                ["type" => "text", "text" => $TypeUser, "size" => "sm", "align" => "end", "weight" => "bold", "flex" => 3]
                            ]
                            ]
                    ]
                ],

                // Small separator
                [
                    "type" => "separator",
                    "margin" => "sm"
                ],

                // Date / Time / IP row
                [
                    "type" => "box",
                    "layout" => "baseline",
                    "contents" => [
                        ["type" => "text", "text" => "📅วันที่: " . $date, "size" => "sm", "color" => "#8a8a8a" , "flex" => 2],
                        ["type" => "text", "text" => "⏰เวลา: " . $time, "size" => "sm", "color" => "#8a8a8a" , "align" => "end", "flex" => 3],
                        // ["type" => "text", "text" => "🌐 " . $ip, "size" => "sm", "align" => "end", "flex" => 3]
                    ]
                ],

                // Footer small note
                [
                    "type" => "box",
                    "layout" => "vertical",
                    "margin" => "md",
                    "contents" => [
                        [
                            "type" => "text",
                            "text" => "หากท่านไม่ได้ทำการเข้าสู่ระบบ โปรดติดต่อผู้ดูแลระบบทันที",
                            "size" => "xs",
                            "color" => "#8a8a8a",
                            "wrap" => true
                        ]
                    ]
                  ],

                [
                    "type" => "separator",
                    "margin" => "sm"
                ]
            ]
        ]
    ];

    return [
        [
            "type" => "text",
            "text" => $summaryText
        ],
        [
            "type" => "flex",
            "altText" => "แจ้งเตือนการเข้าสู่ระบบ",
            "contents" => $bubble
        ]
    ];
}

/**
 * ส่ง POST ไปยัง MOPH ALERT ตามตัวอย่าง API
 */
function send_moph_alert(array $messages): bool {
    $payload = ['messages' => $messages];
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, MOPH_ALERT_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

    $headers = [
        'Content-Type: application/json',
        'client-key: ' . MOPH_CLIENT_KEY,
        'secret-key: ' . MOPH_SECRET_KEY
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, MOPH_ALERT_CONNECT_TIMEOUT);
    curl_setopt($ch, CURLOPT_TIMEOUT, MOPH_ALERT_TIMEOUT);

    $resp = curl_exec($ch);
    $errno = curl_errno($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno !== 0) {
        error_log("MOPH ALERT cURL error ({$errno}): " . substr((string)$resp, 0, 2000));
        return false;
    }

    if ($httpCode >= 200 && $httpCode < 300) {
        return true;
    }

    error_log("MOPH ALERT failed HTTP {$httpCode} resp: " . substr((string)$resp, 0, 2000));
    return false;
}

/* ---------- login flow (เหมือนเดิม) ---------- */
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = trim((string)($_POST['Username'] ?? ($_POST['username'] ?? '')));
$password = (string)($_POST['Password'] ?? ($_POST['password'] ?? ''));

$_SESSION['old_username'] = $username;

if ($username === '' || $password === '') {
    header('Location: login.php?prv=1');
    exit;
}

$sql = "SELECT Username, Password, Names, Position, TypeUser
        FROM employee
        WHERE Username = ? AND Status = '1'
        LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("DB prepare failed: " . $conn->error);
    header('Location: login.php?prv=1');
    exit;
}
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
    $stmt->close();
    header('Location: login.php?prv=1');
    exit;
}

$row = $res->fetch_assoc();
$stmt->close();

$stored = (string)($row['Password'] ?? '');
$ok = false;

if ($stored !== '' && is_hash($stored)) {
    $ok = password_verify($password, $stored);
} else {
    $ok = ($stored !== '') && hash_equals($stored, $password);
    if ($ok) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        if ($up = $conn->prepare("UPDATE employee SET Password=? WHERE Username=? LIMIT 1")) {
            $up->bind_param("ss", $newHash, $username);
            $up->execute();
            $up->close();
        } else {
            error_log("Password upgrade prepare failed: " . $conn->error);
        }
    }
}

if (!$ok) {
    header('Location: login.php?prv=1');
    exit;
}

// login success
session_regenerate_id(true);
$_SESSION['Username'] = (string)$row['Username'];
$_SESSION['Names']    = (string)($row['Names'] ?? '');
$_SESSION['Position'] = (string)($row['Position'] ?? '');
$_SESSION['TypeUser'] = (string)($row['TypeUser'] ?? '');
unset($_SESSION['old_username']);

// ส่งแจ้งเตือนพร้อม IP (ไม่ขัดขวางการ login หากส่งไม่สำเร็จ)
try {
    $ip = get_client_ip();
    $messages = build_moph_messages($_SESSION['Username'], $_SESSION['Names'], $_SESSION['Position'], $_SESSION['TypeUser'], $ip);
    $sent = send_moph_alert($messages);
    if (!$sent) {
        // error logged ภายใน send_moph_alert()
    }
} catch (Throwable $e) {
    error_log("Exception while sending MOPH alert: " . $e->getMessage());
}

// redirect ไป main
header('Location: main.php');
exit;
