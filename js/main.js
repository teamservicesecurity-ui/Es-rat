/**
 * C2-Empyrean - Main JavaScript
 * Core utilities, API client, and shared helpers
 */

const API = {
    base: '/api',

    async request(endpoint, options = {}) {
        const token = this.getToken();
        const headers = {
            'Content-Type': 'application/json',
            ...(token ? { 'Authorization': 'Bearer ' + token } : {}),
            ...options.headers
        };

        try {
            const response = await fetch(this.base + endpoint, { ...options, headers });

            if (response.status === 401) {
                const refreshed = await this.refreshToken();
                if (refreshed) {
                    headers['Authorization'] = 'Bearer ' + this.getToken();
                    const retry = await fetch(this.base + endpoint, { ...options, headers });
                    return await retry.json();
                }
                window.location.href = '/admin/login';
                return null;
            }

            if (response.status === 429) {
                showToast('Rate limited. Slow down.', 'error');
                return { success: false, error: 'rate_limited' };
            }

            return await response.json();
        } catch (err) {
            console.error('[API Error]', err);
            return { success: false, error: err.message };
        }
    },

    get(endpoint) { return this.request(endpoint, { method: 'GET' }); },

    post(endpoint, data = {}) {
        return this.request(endpoint, { method: 'POST', body: JSON.stringify(data) });
    },

    put(endpoint, data = {}) {
        return this.request(endpoint, { method: 'PUT', body: JSON.stringify(data) });
    },

    delete(endpoint) { return this.request(endpoint, { method: 'DELETE' }); },

    getToken() {
        const match = document.cookie.match(new RegExp('(^| )access_token=([^;]+)'));
        return match ? decodeURIComponent(match[2]) : null;
    },

    async refreshToken() {
        const refresh = this.getRefreshToken();
        if (!refresh) return false;
        try {
            const r = await fetch('/api/auth/refresh', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ refresh_token: refresh })
            });
            const result = await r.json();
            if (result.success) {
                document.cookie = 'access_token=' + encodeURIComponent(result.data.access_token) +
                    '; path=/; max-age=3600; SameSite=Strict';
                return true;
            }
        } catch (err) {}
        return false;
    },

    getRefreshToken() {
        const match = document.cookie.match(new RegExp('(^| )refresh_token=([^;]+)'));
        return match ? decodeURIComponent(match[2]) : null;
    }
};

// ───── Cookie Helpers ─────
function getCookie(name) {
    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? decodeURIComponent(match[2]) : null;
}

function setCookie(name, value, maxAge) {
    document.cookie = name + '=' + encodeURIComponent(value) +
        '; path=/; max-age=' + maxAge + '; SameSite=Strict';
}

function deleteCookie(name) {
    document.cookie = name + '=; path=/; max-age=0';
}

// ───── Formatting ─────
function timeAgo(ts) {
    if (!ts) return 'never';
    const s = Math.floor(Date.now() / 1000 - ts);
    if (s < 10) return 'just now';
    if (s < 60) return s + 's ago';
    if (s < 3600) return Math.floor(s / 60) + 'm ago';
    if (s < 86400) return Math.floor(s / 3600) + 'h ago';
    if (s < 2592000) return Math.floor(s / 86400) + 'd ago';
    return new Date(ts * 1000).toLocaleDateString();
}

function formatTime(ts) {
    if (!ts) return '—';
    return new Date(ts * 1000).toLocaleString();
}

function formatBytes(bytes) {
    if (!bytes || bytes === 0) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + units[i];
}

function escapeHtml(str) {
    if (!str) return '';
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

function debounce(fn, wait = 300) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), wait); };
}

// ───── Toast ─────
function showToast(msg, type = 'info', duration = 3000) {
    document.querySelectorAll('.toast').forEach(el => el.remove());
    const t = document.createElement('div');
    t.className = 'toast ' + type;
    t.textContent = msg;
    document.body.appendChild(t);
    requestAnimationFrame(() => t.classList.add('show'));
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, duration);
}

// ───── Auto Refresh ─────
class AutoRefresh {
    constructor(cb, interval = 10000) {
        this.cb = cb;
        this.interval = interval;
        this.timer = null;
        this.active = false;
    }
    start() { if (this.active) return; this.active = true; this.timer = setInterval(() => { if (this.active) this.cb(); }, this.interval); }
    stop() { this.active = false; if (this.timer) { clearInterval(this.timer); this.timer = null; } }
}

// ───── Pagination ─────
class Pagination {
    constructor(containerId, onChange) {
        this.el = document.getElementById(containerId);
        this.onChange = onChange;
        this.page = 1;
        this.total = 1;
    }
    render(pg) {
        if (!this.el || !pg || pg.pages <= 1) { if (this.el) this.el.innerHTML = ''; return; }
        this.total = pg.pages;
        let h = '';
        h += `<button ${this.page <= 1 ? 'disabled' : ''} onclick="pagination.go(${this.page - 1})">←</button>`;
        for (let i = Math.max(1, this.page - 2); i <= Math.min(this.total, this.page + 2); i++)
            h += `<button class="${i === this.page ? 'active' : ''}" onclick="pagination.go(${i})">${i}</button>`;
        h += `<span style="color:rgba(255,255,255,0.3);font-size:13px;padding:0 8px;">${this.page}/${this.total}</span>`;
        h += `<button ${this.page >= this.total ? 'disabled' : ''} onclick="pagination.go(${this.page + 1})">→</button>`;
        this.el.innerHTML = h;
    }
    go(p) { if (p < 1 || p > this.total) return; this.page = p; if (this.onChange) this.onChange(p); }
    reset() { this.page = 1; }
}

document.addEventListener('DOMContentLoaded', () => {
    // Token refresh every 25 minutes
    setInterval(async () => {
        if (API.getToken()) await API.refreshToken();
    }, 25 * 60 * 1000);
});
