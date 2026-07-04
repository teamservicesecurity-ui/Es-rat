<?php
/**
 * C2-Empyrean - RBAC, rate limiting
 */

class MiddlewareHandler
{
    private AuthHandler $auth;
    private JsonDatabase $db;

    public function __construct(JsonDatabase $db)
    {
        $this->db = $db;
        $this->auth = new AuthHandler($db);
    }

    public function requireAuth(): ?array
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (empty($header) && isset($_COOKIE['access_token'])) {
            $header = 'Bearer ' . $_COOKIE['access_token'];
        }
        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            $this->halt(401, 'Authentication required');
        }
        $payload = $this->auth->validateToken($matches[1]);
        if (!$payload) {
            $refreshHeader = $_SERVER['HTTP_X_REFRESH_TOKEN'] ?? $_COOKIE['refresh_token'] ?? null;
            if ($refreshHeader) {
                $newTokens = $this->auth->refreshToken($refreshHeader);
                if ($newTokens) {
                    $this->setAuthCookies($newTokens);
                    $payload = $this->auth->validateToken($newTokens['access_token']);
                }
            }
            if (!$payload) $this->halt(401, 'Invalid or expired token');
        }
        return $payload;
    }

    public function checkRateLimit(string $actionType): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $key = $actionType . '_' . $ip;
        $window = RATE_LIMIT_WINDOW;
        $maxRequests = RATE_LIMIT_MAX_REQUESTS;
        if (strpos($actionType, 'api/') === 0) $maxRequests = API_RATE_LIMIT_MAX;
        $now = time();
        $store = $this->db->read('rate_limits');
        foreach ($store as $k => $entry) {
            if ($entry['reset'] < $now) unset($store[$k]);
        }
        $entry = $store[$key] ?? ['count' => 0, 'reset' => $now + $window];
        if ($entry['count'] >= $maxRequests) {
            $retryAfter = $entry['reset'] - $now;
            header('Retry-After: ' . $retryAfter);
            $this->halt(429, 'Rate limit exceeded. Retry in ' . $retryAfter . ' seconds');
        }
        $entry['count']++;
        $store[$key] = $entry;
        $this->db->write('rate_limits', $store);
    }

    public function setAuthCookies(array $tokens): void
    {
        $secure = isset($_SERVER['HTTPS']);
        setcookie('access_token', $tokens['access_token'], [
            'expires' => time() + JWT_EXPIRY, 'path' => '/',
            'secure' => $secure, 'httponly' => true, 'samesite' => 'Strict',
        ]);
        setcookie('refresh_token', $tokens['refresh_token'], [
            'expires' => time() + JWT_REFRESH_EXPIRY, 'path' => '/',
            'secure' => $secure, 'httponly' => true, 'samesite' => 'Strict',
        ]);
    }

    public function isDeviceRequest(): bool
    {
        $token = $_SERVER['HTTP_X_DEVICE_TOKEN'] ?? null;
        if (!$token) return false;
        $devices = $this->db->read('devices');
        foreach ($devices as $device) {
            if (hash_equals($device['auth_token'] ?? '', $token)) return true;
        }
        return false;
    }

    private function halt(int $statusCode, string $message): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 'error' => $message,
            'code' => $statusCode, 'timestamp' => time(),
        ]);
        exit;
    }
}
