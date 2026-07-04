<?php
/**
 * C2-Empyrean - JWT Auth
 */

class AuthHandler
{
    private JsonDatabase $db;
    private string $secret;

    public function __construct(JsonDatabase $db)
    {
        $this->db = $db;
        $this->secret = JWT_SECRET;
    }

    public function login(string $username, string $password): ?array
    {
        if ($username !== ADMIN_USERNAME) {
            $this->logAttempt($username, false);
            return null;
        }
        if (!password_verify($password, ADMIN_PASSWORD_HASH)) {
            $this->logAttempt($username, false);
            return null;
        }
        $this->logAttempt($username, true);
        return $this->generateTokens($username);
    }

    public function generateTokens(string $username): array
    {
        $now = time();
        $accessPayload = [
            'sub' => $username, 'iat' => $now,
            'exp' => $now + JWT_EXPIRY,
            'jti' => bin2hex(random_bytes(16)), 'type' => 'access',
        ];
        $refreshPayload = [
            'sub' => $username, 'iat' => $now,
            'exp' => $now + JWT_REFRESH_EXPIRY,
            'jti' => bin2hex(random_bytes(16)), 'type' => 'refresh',
        ];
        $accessToken = $this->encodeJWT($accessPayload);
        $refreshToken = $this->encodeJWT($refreshPayload);
        $this->storeRefreshToken($refreshPayload['jti'], $username, $now + JWT_REFRESH_EXPIRY);
        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => JWT_EXPIRY,
        ];
    }

    public function refreshToken(string $refreshToken): ?array
    {
        $payload = $this->decodeJWT($refreshToken);
        if (!$payload || ($payload['type'] ?? '') !== 'refresh') return null;
        $tokens = $this->db->read('refresh_tokens');
        $tokenId = $payload['jti'] ?? '';
        if (!isset($tokens[$tokenId]) || $tokens[$tokenId]['revoked'] ?? false) return null;
        if (($tokens[$tokenId]['expires'] ?? 0) < time()) {
            unset($tokens[$tokenId]);
            $this->db->write('refresh_tokens', $tokens);
            return null;
        }
        $tokens[$tokenId]['revoked'] = true;
        $this->db->write('refresh_tokens', $tokens);
        return $this->generateTokens($payload['sub']);
    }

    public function validateToken(string $token): ?array
    {
        $payload = $this->decodeJWT($token);
        if (!$payload || ($payload['type'] ?? '') !== 'access') return null;
        if (($payload['exp'] ?? 0) < time()) return null;
        return $payload;
    }

    public function getCurrentUser(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) return null;
        $payload = $this->validateToken($matches[1]);
        return $payload['sub'] ?? null;
    }

    private function encodeJWT(array $payload): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $segments = [];
        $segments[] = $this->base64UrlEncode(json_encode($header));
        $segments[] = $this->base64UrlEncode(json_encode($payload));
        $signingInput = implode('.', $segments);
        $signature = hash_hmac('sha256', $signingInput, $this->secret, true);
        $segments[] = $this->base64UrlEncode($signature);
        return implode('.', $segments);
    }

    private function decodeJWT(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;
        [$header, $payload, $signature] = $parts;
        $signingInput = $header . '.' . $payload;
        $expectedSignature = $this->base64UrlEncode(hash_hmac('sha256', $signingInput, $this->secret, true));
        if (!hash_equals($expectedSignature, $signature)) return null;
        $decoded = json_decode($this->base64UrlDecode($payload), true);
        return is_array($decoded) ? $decoded : null;
    }

    private function storeRefreshToken(string $tokenId, string $username, int $expires): void
    {
        $tokens = $this->db->read('refresh_tokens');
        $tokens[$tokenId] = [
            'username' => $username, 'expires' => $expires,
            'revoked' => false, 'created_at' => time(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ];
        $this->db->write('refresh_tokens', $tokens);
    }

    private function logAttempt(string $username, bool $success): void
    {
        $logs = $this->db->read('auth_logs');
        $logs[] = [
            'username' => $username, 'success' => $success,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => time(),
        ];
        if (count($logs) > 100) $logs = array_slice($logs, -100);
        $this->db->write('auth_logs', $logs);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
