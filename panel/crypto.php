<?php
/**
 * C2-Empyrean - AES encrypt/decrypt
 */

class CryptoEngine
{
    private string $key;
    private int $ivLength = 16;
    private string $method = 'aes-256-cbc';

    public function __construct(?string $key = null)
    {
        $this->key = $key ?: ENCRYPTION_KEY;
        $this->key = hash('sha256', $this->key, true);
    }

    public function encrypt(string $data): string
    {
        $iv = random_bytes($this->ivLength);
        $ciphertext = openssl_encrypt($data, $this->method, $this->key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv) . '.' . base64_encode($ciphertext);
    }

    public function decrypt(string $encrypted): ?string
    {
        $parts = explode('.', $encrypted, 2);
        if (count($parts) !== 2) return null;
        $iv = base64_decode($parts[0]);
        $ciphertext = base64_decode($parts[1]);
        if ($iv === false || $ciphertext === false) return null;
        $decrypted = openssl_decrypt($ciphertext, $this->method, $this->key, OPENSSL_RAW_DATA, $iv);
        return $decrypted !== false ? $decrypted : null;
    }

    public function sign(string $data): string
    {
        return hash_hmac('sha256', $data, $this->key);
    }

    public function verify(string $data, string $signature): bool
    {
        return hash_equals($this->sign($data), $signature);
    }

    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    public function deriveDeviceKey(string $deviceId): string
    {
        return hash('sha256', $this->key . $deviceId, true);
    }

    public function encryptForDevice(string $deviceId, string $data): string
    {
        $deviceKey = $this->deriveDeviceKey($deviceId);
        $engine = new self(bin2hex($deviceKey));
        return $engine->encrypt($data);
    }

    public function decryptFromDevice(string $deviceId, string $encrypted): ?string
    {
        $deviceKey = $this->deriveDeviceKey($deviceId);
        $engine = new self(bin2hex($deviceKey));
        return $engine->decrypt($encrypted);
    }
}
