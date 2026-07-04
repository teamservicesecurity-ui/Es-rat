<?php
/**
 * C2-Empyrean - API: Verify Token
 * GET /api/auth/verify
 * Header: Authorization: Bearer <token>
 */

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$user = $auth->getCurrentUser();

if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid or expired token']);
    exit;
}

echo json_encode([
    'success' => true,
    'data' => [
        'username' => $user,
        'expires_in' => JWT_EXPIRY,
        'app' => APP_NAME,
        'version' => APP_VERSION,
    ],
]);
