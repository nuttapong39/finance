<?php
/**
 * Debugging version of send_moph_alert()
 * - Writes detailed log to file moph_alert_debug.log in same dir
 * - Returns boolean success
 */
function send_moph_alert(array $messages): bool {
    $payload = ['messages' => $messages];
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);

    $logFile = __DIR__ . '/moph_alert_debug.log';
    $time = (new DateTimeImmutable('now', new DateTimeZone('Asia/Bangkok')))->format('Y-m-d H:i:s');

    // Build headers
    $clientKey = defined('MOPH_CLIENT_KEY') ? MOPH_CLIENT_KEY : '';
    $secretKey = defined('MOPH_SECRET_KEY') ? MOPH_SECRET_KEY : '';
    $headers = [
        'Content-Type: application/json',
        'client-key: ' . $clientKey,
        'secret-key: ' . $secretKey
    ];

    // Log request attempt
    file_put_contents($logFile, "===[$time] Attempt send MOPH ALERT ===\n", FILE_APPEND);
    file_put_contents($logFile, "URL: " . (defined('MOPH_ALERT_URL') ? MOPH_ALERT_URL : '') . "\n", FILE_APPEND);
    file_put_contents($logFile, "Headers: " . print_r($headers, true) . "\n", FILE_APPEND);
    file_put_contents($logFile, "Payload: " . $json . "\n", FILE_APPEND);

    // cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, defined('MOPH_ALERT_URL') ? MOPH_ALERT_URL : '');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, defined('MOPH_ALERT_CONNECT_TIMEOUT') ? MOPH_ALERT_CONNECT_TIMEOUT : 2);
    curl_setopt($ch, CURLOPT_TIMEOUT, defined('MOPH_ALERT_TIMEOUT') ? MOPH_ALERT_TIMEOUT : 4);

    // Uncomment for debug if SSL cert problems suspected (temporary only)
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // Enable verbose capture to temporary stream
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_STDERR, $verbose);

    $resp = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Capture verbose info
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    fclose($verbose);
    curl_close($ch);

    // Log response details
    file_put_contents($logFile, "cURL errno: $errno\n", FILE_APPEND);
    file_put_contents($logFile, "cURL error: $error\n", FILE_APPEND);
    file_put_contents($logFile, "HTTP code: $httpCode\n", FILE_APPEND);
    file_put_contents($logFile, "Response: " . ($resp === false ? 'FALSE' : substr((string)$resp, 0, 4000)) . "\n", FILE_APPEND);
    file_put_contents($logFile, "Verbose: " . $verboseLog . "\n", FILE_APPEND);
    file_put_contents($logFile, "=== End ===\n\n", FILE_APPEND);

    // Decide success
    if ($errno !== 0) {
        return false;
    }
    if ($httpCode < 200 || $httpCode >= 300) {
        return false;
    }
    return true;
}


?>