<?php
require_once __DIR__ . '/../middleware.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - C2-Empyrean</title>
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

        .settings-grid { max-width: 700px; }

        .settings-section {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
        }
        .settings-section .section-title {
            font-size: 16px; font-weight: 600; color: #fff;
            margin-bottom: 20px; padding-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block; font-size: 13px; color: rgba(255, 255, 255, 0.5);
            margin-bottom: 6px; font-weight: 500;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px; color: #fff; font-size: 13px; outline: none;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #7b2ff7; }
        .form-group select option { background: #1a1a2e; }
        .form-group .help-text { font-size: 11px; color: rgba(255, 255, 255, 0.2); margin-top: 4px; }

        .btn-save {
            padding: 10px 28px;
            background: linear-gradient(135deg, #7b2ff7, #00d4ff);
            border: none; border-radius: 8px; color: #fff;
            font-weight: 600; font-size: 14px; cursor: pointer;
        }
        .btn-save:hover { opacity: 0.9; }

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
        <nav class="sidebar">[sidebar with .active on settings]</nav>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">⚙️ Settings <span>panel configuration</span></h1>
            </div>

            <div class="settings-grid">
                <div class="settings-section">
                    <div class="section-title">🔐 Authentication</div>
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" id="currentPassword" placeholder="Enter current password">
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" id="newPassword" placeholder="Enter new password">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" id="confirmPassword" placeholder="Confirm new password">
                    </div>
                    <button class="btn-save" onclick="updatePassword()">Update Password</button>
                </div>

                <div class="settings-section">
                    <div class="section-title">⏱️ Heartbeat & Timeouts</div>
                    <div class="form-group">
                        <label>Heartbeat Interval (seconds)</label>
                        <input type="number" id="hbInterval" value="30" min="5" max="300">
                        <div class="help-text">How often devices check in (5-300s). Lower = faster response but more server load.</div>
                    </div>
                    <div class="form-group">
                        <label>Offline Threshold (seconds)</label>
                        <input type="number" id="offlineThreshold" value="120" min="30" max="3600">
                        <div class="help-text">Time without heartbeat before device is marked offline.</div>
                    </div>
                    <button class="btn-save" onclick="saveHeartbeatSettings()">Save Timeout Settings</button>
                </div>

                <div class="settings-section">
                    <div class="section-title">🔒 Encryption</div>
                    <div class="form-group">
                        <label>Encryption Key (AES-256)</label>
                        <input type="password" id="encKey" placeholder="64 hex characters (256-bit)" maxlength="64">
                        <div class="help-text">Leave empty to keep current key. Changing will break existing device connections.</div>
                    </div>
                    <button class="btn-save" onclick="updateEncryptionKey()">Update Key</button>
                </div>

                <div class="settings-section">
                    <div class="section-title">📊 Data Retention</div>
                    <div class="form-group">
                        <label>Retention Period</label>
                        <select id="retentionPeriod">
                            <option value="7">7 days</option>
                            <option value="30" selected>30 days</option>
                            <option value="90">90 days</option>
                            <option value="365">1 year</option>
                            <option value="0">Forever</option>
                        </select>
                        <div class="help-text">Older data will be automatically purged.</div>
                    </div>
                    <button class="btn-save" onclick="saveRetention()">Save Retention</button>
                </div>

                <div class="settings-section">
                    <div class="section-title">🔌 API Configuration</div>
                    <div class="form-group">
                        <label>JWT Secret</label>
                        <input type="password" id="jwtSecret" placeholder="Leave empty to keep current" maxlength="128">
                        <div class="help-text">Changing will invalidate all existing sessions.</div>
                    </div>
                    <div class="form-group">
                        <label>Access Token Expiry (seconds)</label>
                        <input type="number" id="tokenExpiry" value="3600" min="300" max="86400">
                    </div>
                    <div class="form-group">
                        <label>Refresh Token Expiry (seconds)</label>
                        <input type="number" id="refreshExpiry" value="86400" min="3600" max="2592000">
                    </div>
                    <button class="btn-save" onclick="saveApiSettings()">Save API Settings</button>
                </div>

                <div class="settings-section">
                    <div class="section-title">⚠️ Danger Zone</div>
                    <div class="form-group">
                        <label style="color:#ff453a;">Reset all data</label>
                        <div class="help-text" style="color:rgba(255,69,58,0.5);">This will delete ALL devices, keylogs, SMS, and files. Cannot be undone.</div>
                    </div>
                    <button class="btn-save" style="background:linear-gradient(135deg,#ff453a,#ff375f);" onclick="confirmReset()">🗑️ Reset All Data</button>
                </div>
            </div>
        </main>
    </div>

    <div class="toast" id="toast"></div>

    <script>
        document.addEventListener('DOMContentLoaded', loadSettings);

        async function loadSettings() {
            try {
                const response = await fetch('/api/settings/get', {
                    headers: { 'Authorization': 'Bearer ' + getCookie('access_token') }
                });
                const result = await response.json();
                if (!result.success) return;
                const s = result.data.settings || {};
                document.getElementById('hbInterval').value = s.heartbeat_interval || 30;
                document.getElementById('offlineThreshold').value = s.offline_threshold || 120;
                document.getElementById('retentionPeriod').value = s.retention_period || '30';
                document.getElementById('tokenExpiry').value = s.token_expiry || 3600;
                document.getElementById('refreshExpiry').value = s.refresh_expiry || 86400;
            } catch (err) {}
        }

        async function apiPost(endpoint, data) {
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + getCookie('access_token') },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                showToast(result.success ? '✅ ' + (result.message || 'Saved') : '❌ ' + (result.error || 'Error'), result.success);
            } catch (err) {
                showToast('❌ Connection error', false);
            }
        }

        function updatePassword() {
            const curr = document.getElementById('currentPassword').value;
            const pwd = document.getElementById('newPassword').value;
            const confirm = document.getElementById('confirmPassword').value;
            if (pwd !== confirm) { showToast('❌ Passwords do not match', false); return; }
            if (pwd.length < 8) { showToast('❌ Password must be at least 8 characters', false); return; }
            apiPost('/api/settings/update_password', { current_password: curr, new_password: pwd });
        }

        function saveHeartbeatSettings() {
            apiPost('/api/settings/update', {
                heartbeat_interval: parseInt(document.getElementById('hbInterval').value),
                offline_threshold: parseInt(document.getElementById('offlineThreshold').value)
            });
        }

        function updateEncryptionKey() {
            const key = document.getElementById('encKey').value;
            if (key && key.length !== 64) { showToast('❌ Key must be exactly 64 hex characters', false); return; }
            apiPost('/api/settings/update_encryption_key', { key: key || null });
        }

        function saveRetention() {
            apiPost('/api/settings/update', { retention_period: document.getElementById('retentionPeriod').value });
        }

        function saveApiSettings() {
            apiPost('/api/settings/update', {
                jwt_secret: document.getElementById('jwtSecret').value || null,
                token_expiry: parseInt(document.getElementById('tokenExpiry').value),
                refresh_expiry: parseInt(document.getElementById('refreshExpiry').value)
            });
        }

        function confirmReset() {
            if (confirm('⚠️ THIS WILL DELETE EVERYTHING. Are you absolutely sure?')) {
                if (confirm('All devices, logs, and files will be permanently removed. Continue?')) {
                    apiPost('/api/settings/reset_all', {});
                }
            }
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
