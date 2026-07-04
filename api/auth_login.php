<?php
/**
 * C2-Empyrean - API: Admin Login
 * POST /api/auth/login
 * Body: { "username": "...", "password": "..." }
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['username']) || empty($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Username and password required']);
    exit;
}

$tokens = $auth->login($input['username'], $input['password']);

if (!$tokens) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
    exit;
}

$middleware->setAuthCookies($tokens);

echo json_encode([
    'success' => true,
    'data' => $tokens,
    'message' => 'Login successful',
]);
