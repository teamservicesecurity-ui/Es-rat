/**
 * C2-Empyrean - Command Console Scripts
 * Terminal-style interface for sending commands to devices
 */

let selectedDeviceId = null;
let commandHistory = [];
let historyIndex = -1;

document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const preSelected = params.get('device');
    loadDeviceList();
    if (preSelected) {
        setTimeout(() => selectDevice(preSelected), 1000);
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'l') {
            e.preventDefault();
            clearConsole();
        }
    });
});

async function loadDeviceList() {
    try {
        const result = await API.get('/devices/list?page=1&per_page=100');
        if (!result || !result.success) return;

        const list = document.getElementById('deviceList');
        const devices = result.data.devices || [];

        if (devices.length === 0) {
            list.innerHTML = '<div class="empty-state">No devices found</div>';
            return;
        }

        list.innerHTML = devices.map(d => `
            <div class="device-list-item ${d.device_id === selectedDeviceId ? 'active' : ''}"
                 onclick="selectDevice('${d.device_id}')">
                <span class="dli-status">${d.is_online ? '🟢' : '🔴'}</span>
                <div>
                    <div class="dli-id">${escapeHtml(d.device_id)}</div>
                    <div class="dli-model">${escapeHtml(d.model || '—')}</div>
                </div>
            </div>
        `).join('');
    } catch (err) {}
}

function selectDevice(deviceId) {
    selectedDeviceId = deviceId;
    document.getElementById('activeDeviceDisplay').textContent = 'Target: ' + deviceId;
    document.getElementById('commandInput').disabled = false;
    document.getElementById('executeBtn').disabled = false;
    document.getElementById('commandInput').focus();

    document.querySelectorAll('.device-list-item').forEach(el => {
        el.classList.toggle('active', el.textContent.includes(deviceId));
    });

    appendToConsole('system', 'Selected device: ' + deviceId);
}

async function executeCommand() {
    const input = document.getElementById('commandInput');
    const command = input.value.trim();
    if (!command || !selectedDeviceId) return;

    input.value = '';
    appendToConsole('input', '$ ' + command);

    // Parse command and parameters
    const parts = command.split(' ');
    const cmd = parts[0];
    let params = {};
    if (parts.length > 1) {
        params.value = parts.slice(1).join(' ');
    }

    commandHistory.push(command);
    historyIndex = commandHistory.length;

    try {
        const result = await API.post('/device/command', {
            device_id: selectedDeviceId,
            command: cmd,
            params: params
        });

        if (result && result.success) {
            appendToConsole('success', '✅ Command queued (ID: ' + result.data.command_id + ')');
        } else {
            appendToConsole('error', '❌ ' + (result?.error || 'Command failed'));
        }
    } catch (err) {
        appendToConsole('error', '❌ Connection error: ' + err.message);
    }

    scrollToBottom();
}

function insertCommand(cmd) {
    document.getElementById('commandInput').value = cmd;
    document.getElementById('commandInput').focus();
}

function appendToConsole(type, text) {
    const output = document.getElementById('consoleOutput');
    const line = document.createElement('div');
    line.className = 'line ' + type;
    line.textContent = text;
    output.appendChild(line);
    scrollToBottom();
}

function scrollToBottom() {
    const output = document.getElementById('consoleOutput');
    output.scrollTop = output.scrollHeight;
}

function clearConsole() {
    const output = document.getElementById('consoleOutput');
    output.innerHTML = `
        <div class="line system">╔══════════════════════════════════════╗</div>
        <div class="line system">║     C2-Empyrean Command Console     ║</div>
        <div class="line system">╚══════════════════════════════════════╝</div>
        <div class="line system">Console cleared.</div>`;
}

// Keyboard navigation for command history
document.addEventListener('keydown', function(e) {
    const input = document.getElementById('commandInput');
    if (!input || document.activeElement !== input) return;

    if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (historyIndex > 0) {
            historyIndex--;
            input.value = commandHistory[historyIndex];
        }
    } else if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (historyIndex < commandHistory.length - 1) {
            historyIndex++;
            input.value = commandHistory[historyIndex];
        } else {
            historyIndex = commandHistory.length;
            input.value = '';
        }
    }
});
