/**
 * C2-Empyrean - Dashboard Page Scripts
 * Statistics, charts, and overview widgets
 */

document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
    const refresh = new AutoRefresh(loadDashboardStats, 15000);
    refresh.start();
});

async function loadDashboardStats() {
    try {
        const result = await API.get('/analytics/stats?range=24h');
        if (!result || !result.success) return;

        const stats = result.data.stats || {};
        const charts = result.data.charts || {};

        // Update stat cards
        setText('statTotalDevices', stats.total_devices || 0);
        setText('statOnlineNow', stats.online_now || 0);
        setText('statCommandsSent', formatNumber(stats.total_commands || 0));
        setText('statKeylogsCaptured', formatNumber(stats.total_keylogs || 0));
        setText('statSmsIntercepted', formatNumber(stats.total_sms || 0));
        setText('statFilesExfiltrated', formatNumber(stats.total_files || 0));

        // Update charts
        if (charts.devices_over_time) renderDevicesChart(charts.devices_over_time);
        if (charts.commands_by_type) renderCommandsChart(charts.commands_by_type);
        if (charts.geo_distribution) renderGeoChart(charts.geo_distribution);

        // Top devices table
        renderTopDevices(result.data.top_devices || []);

        // Recent activity
        renderRecentActivity(result.data.recent_activity || []);

    } catch (err) {
        console.error('Dashboard load error:', err);
    }
}

function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
}

function formatNumber(n) {
    if (!n) return '0';
    if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
    if (n >= 1000) return (n / 1000).toFixed(1) + 'k';
    return n.toString();
}

let devicesChart = null;
function renderDevicesChart(data) {
    const ctx = document.getElementById('devicesChart');
    if (!ctx) return;
    if (devicesChart) devicesChart.destroy();
    devicesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: 'Devices',
                data: data.data || [],
                borderColor: '#7b2ff7',
                backgroundColor: 'rgba(123, 47, 247, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: 'rgba(255,255,255,0.5)' } }
            },
            scales: {
                x: { ticks: { color: 'rgba(255,255,255,0.3)' }, grid: { color: 'rgba(255,255,255,0.03)' } },
                y: { ticks: { color: 'rgba(255,255,255,0.3)' }, grid: { color: 'rgba(255,255,255,0.03)' }, beginAtZero: true }
            }
        }
    });
}

let commandsChart = null;
function renderCommandsChart(data) {
    const ctx = document.getElementById('commandsChart');
    if (!ctx) return;
    if (commandsChart) commandsChart.destroy();
    commandsChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels || [],
            datasets: [{
                data: data.data || [],
                backgroundColor: ['#7b2ff7', '#00d4ff', '#ff9f0a', '#34c759', '#ff453a', '#5e5ce6']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: 'rgba(255,255,255,0.5)', padding: 12 }
                }
            }
        }
    });
}

let geoChart = null;
function renderGeoChart(data) {
    // Placeholder for geographic chart
}

function renderTopDevices(devices) {
    const tbody = document.getElementById('topDevicesBody');
    if (!tbody) return;
    if (!devices.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="empty-state">No data</td></tr>';
        return;
    }
    tbody.innerHTML = devices.map(d => `
        <tr>
            <td style="font-family:monospace;color:#7b2ff7;">${escapeHtml(d.device_id)}</td>
            <td>${escapeHtml(d.model || '—')}</td>
            <td>${d.commands || 0}</td>
            <td>${d.keylogs || 0}</td>
            <td>${(d.data_mb || 0).toFixed(1)} MB</td>
        </tr>
    `).join('');
}

function renderRecentActivity(activities) {
    const container = document.getElementById('recentActivity');
    if (!container) return;
    if (!activities.length) {
        container.innerHTML = '<div class="empty-state">No recent activity</div>';
        return;
    }
    container.innerHTML = activities.map(a => `
        <div style="padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.03);font-size:13px;">
            <span style="color:rgba(255,255,255,0.3);font-size:11px;">${timeAgo(a.timestamp)}</span>
            <span style="color:rgba(255,255,255,0.7);margin-left:8px;">${escapeHtml(a.description)}</span>
        </div>
    `).join('');
}
