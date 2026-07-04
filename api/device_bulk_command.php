<?php
/**
 * C2-Empyrean - API: Bulk Command
 * POST /api/device/bulk-command
 * Body: { "device_ids": ["id1","id2"], "command": "...", "params": {} }
 * Or: { "filter": "all|online|offline", "command": "...", "params": {} }
 */

$middleware->requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['command'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Command required']);
    exit;
}

$command = $input['command'];
$params = $input['params'] ?? [];

// Resolve target devices
$targetDeviceIds = [];

if (!empty($input['device_ids']) && is_array($input['device_ids'])) {
    $targetDeviceIds = $input['device_ids'];
} elseif (!empty($input['filter'])) {
    $devices = $db->read('devices');
    $now = time();
    $timeout = HEARTBEAT_INTERVAL * 3;
    foreach ($devices as $device) {
        $isOnline = (($device['last_seen'] ?? 0) + $timeout) > $now;
        if ($input['filter'] === 'all') {
            $targetDeviceIds[] = $device['device_id'];
        } elseif ($input['filter'] === 'online' && $isOnline) {
            $targetDeviceIds[] = $device['device_id'];
        } elseif ($input['filter'] === 'offline' && !$isOnline) {
            $targetDeviceIds[] = $device['device_id'];
        }
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'device_ids or filter required']);
    exit;
}

if (empty($targetDeviceIds)) {
    echo json_encode(['success' => false, 'error' => 'No matching devices found']);
    exit;
}

// Queue command for each device
$commands = $db->read('commands');
$queued = 0;
$commandIds = [];

foreach ($targetDeviceIds as $deviceId) {
    $commandId = bin2hex(random_bytes(12));
    $commands[] = [
        '_id' => $commandId,
        'device_id' => $deviceId,
        'command' => $command,
        'params' => $params,
        'priority' => 'bulk',
        'status' => 'pending',
        'result' => null,
        'created_at' => time(),
        'executed_at' => null,
        'created_by' => $auth->getCurrentUser(),
    ];
    $commandIds[] = $commandId;
    $queued++;
}
$db->write('commands', $commands);

echo json_encode([
    'success' => true,
    'data' => [
        'total_targeted' => count($targetDeviceIds),
        'queued' => $queued,
        'command' => $command,
        'command_ids' => $commandIds,
        'message' => "Queued '{$command}' for {$queued} devices",
    ],
]);
