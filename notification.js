// ==================== 工具函数 ====================
function getBaseUrl() {
    // 自动识别本地或线上环境
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        // 本地 XAMPP：项目可能放在子目录下，例如 /GSC-Movie-ticket-Online-Booking-System
        let path = window.location.pathname;
        let parts = path.split('/');
        if (parts.length > 1 && parts[1].includes('GSC')) {
            return '/' + parts[1];
        }
        return '';
    }
    // 线上环境：假设项目在根目录，直接返回空字符串；如果有子目录请手动修改
    return '';
}

// ==================== Toast 提示（页面内浮动条） ====================
function showToast(message, duration = 5000) {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.gap = '10px';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.style.background = '#ff6b6b';
    toast.style.color = 'white';
    toast.style.padding = '12px 20px';
    toast.style.borderRadius = '8px';
    toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    toast.style.fontSize = '14px';
    toast.style.fontWeight = 'bold';
    toast.style.minWidth = '250px';
    toast.style.maxWidth = '350px';
    toast.style.wordWrap = 'break-word';
    toast.style.cursor = 'pointer';
    toast.style.transition = 'all 0.3s ease';
    toast.style.animation = 'slideInRight 0.3s ease';
    toast.textContent = message;
    toast.onclick = () => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    };
    container.appendChild(toast);
    setTimeout(() => {
        if (toast && toast.remove) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }
    }, duration);
}

// 添加动画样式（如果页面没有定义）
if (!document.querySelector('#toast-keyframes')) {
    const style = document.createElement('style');
    style.id = 'toast-keyframes';
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
}

// ==================== 桌面通知 ====================
function requestNotificationPermission() {
    if (Notification.permission === 'default') {
        Notification.requestPermission().then(permission => {
            console.log('Notification permission:', permission);
        });
    }
}

// 显示桌面通知（像 WhatsApp 那样）
function showSystemNotification(title, body) {
    if (Notification.permission === 'granted') {
        new Notification(title, { body: body, icon: '/favicon.ico', requireInteraction: true });
    } else {
        // 如果用户拒绝了桌面通知，至少用 toast 提示
        showToast(title + '\n' + body, 8000);
    }
}

// ==================== 轮询提醒 ====================
let reminderInterval = null;
let notificationInterval = null;

function checkMovieReminders() {
    const baseUrl = getBaseUrl();
    fetch(baseUrl + '/check_reminder.php')
        .then(response => {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.reminders && data.reminders.length) {
                data.reminders.forEach(rem => {
                    // rem.message 已经是英文提醒文案（来自 check_reminder.php）
                    showSystemNotification('Movie Reminder', rem.message);
                    showToast(rem.message, 8000);
                });
            }
        })
        .catch(err => console.error('Movie reminder error:', err));
}

function checkGeneralNotifications() {
    const baseUrl = getBaseUrl();
    fetch(baseUrl + '/api/get_new_notifications.php')
        .then(response => {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.notifications && data.notifications.length) {
                data.notifications.forEach(notif => {
                    showSystemNotification('GSC Notification', notif.message);
                    showToast(notif.message, 8000);
                });
            }
        })
        .catch(err => console.error('General notification error:', err));
}

// ==================== 启动所有通知功能 ====================
function startNotifications() {
    // 请求权限（只在用户首次访问时弹出）
    if (window.Notification && Notification.permission !== 'denied') {
        requestNotificationPermission();
    }
    // 立即执行一次
    checkMovieReminders();
    checkGeneralNotifications();
    // 设置轮询（30秒一次）
    if (reminderInterval) clearInterval(reminderInterval);
    if (notificationInterval) clearInterval(notificationInterval);
    reminderInterval = setInterval(checkMovieReminders, 30000);
    notificationInterval = setInterval(checkGeneralNotifications, 30000);
}

// 页面加载完成后启动
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startNotifications);
} else {
    startNotifications();
}