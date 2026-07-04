<?php
/**
 * C2-Empyrean - API: Device Heartbeat
 * POST /api/heartbeat
 * Body: { "device_id": "...", "auth_token": "...", "battery": 85, ... }
 * No JWT required - device-facing endpoint
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['device_id']) || empty($input['auth_token'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Device ID and auth token required']);
    exit;
}

$deviceId = $input['device_id'];
$authToken = $input['auth_token'];
$now = time();

// Verify device exists and token matches
$devices = $db->read('devices');
$deviceIndex = null;
$device = null;

foreach ($devices as $i => $d) {
    if (($d['device_id'] ?? '') === $deviceId) {
        if (!hash_equals($d['auth_token'] ?? '', $authToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid auth token']);
            exit;
        }
        $deviceIndex = $i;
        $device = $d;
        break;
    }
}

if (!$device) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Device not registered. Send registration first.']);
    exit;
}

// Update device info
$updateData = [
    'last_seen' => $now,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? $device['ip'],
    'battery_level' => $input['battery'] ?? $device['battery_level'] ?? 0,
    'battery_charging' => $input['charging'] ?? $device['battery_charging'] ?? false,
    'network_type' => $input['network'] ?? $device['network_type'] ?? 'unknown',
    'signal_strength' => $input['signal'] ?? $device['signal_strength'] ?? 0,
    'is_charging' => $input['charging'] ?? $device['is_charging'] ?? false,
    'current_wifi' => $input['wifi'] ?? $device['current_wifi'] ?? '',
    'heartbeat_count' => ($device['heartbeat_count'] ?? 0) + 1,
];

foreach ($updateData as $key => $value) {
    $devices[$deviceIndex][$key] = $value;
}
$db->write('devices', $devices);

// Check for pending commands for this device
$commands = $db->read('commands');
$pendingCommands = [];
foreach ($commands as &$cmd) {
    if (($cmd['device_id'] ?? '') === $deviceId && ($cmd['status'] ?? '') === 'pending') {
        $cmd['status'] = 'delivered';
        $cmd['delivered_at'] = $now;
        $pendingCommands[] = [
            '_id' => $cmd['_id'],
            'command' => $cmd['command'],
            'params' => $cmd['params'] ?? [],
            'priority' => $cmd['priority'] ?? 'normal',
        ];
    }
}
$db->write('commands', $commands);

echo json_encode([
    'success' => true,
    'data' => [
        'server_time' => $now,
        'heartbeat_interval' => HEARTBEAT_INTERVAL,
        'pending_commands' => $pendingCommands,
        'pending_count' => count($pendingCommands),
    ],
]);
