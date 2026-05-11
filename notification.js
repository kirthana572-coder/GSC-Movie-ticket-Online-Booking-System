// Toast 函数
function showToast(message, duration = 4000) {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    toast.addEventListener('click', () => {
        toast.classList.add('fade-out');
        setTimeout(() => toast.remove(), 300);
    });
    container.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('fade-out');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// 请求通知权限
if (Notification.permission !== "granted" && Notification.permission !== "denied") {
    Notification.requestPermission();
}

// 通用提醒函数（桌面通知 + 浮动条）
function sendAlert(title, body, toastMsg) {
    if (Notification.permission === "granted") {
        new Notification(title, { body: body });
    }
    showToast(toastMsg || body, 5000);
}

// ========== 1. 电影开场提醒 ==========
setInterval(() => {
    fetch('/GSC-Movie-ticket-Online-Booking-System/check_reminder.php')
        .then(res => res.json())
        .then(data => {
            if (data.reminders && data.reminders.length) {
                data.reminders.forEach(rem => {
                    const msg = `🎬 ${rem.title} starts at ${rem.start_time}. Please collect your tickets!`;
                    sendAlert("Movie Starting Soon", msg, msg);
                });
            }
        })
        .catch(err => console.log("Session reminder error", err));
}, 30000);

// ========== 2. 通用通知（订单创建/取消/过期等） ==========
setInterval(() => {
    fetch('/GSC-Movie-ticket-Online-Booking-System/api/get_new_notifications.php')
        .then(res => res.json())
        .then(data => {
            if (data.notifications && data.notifications.length) {
                data.notifications.forEach(notif => {
                    const msg = notif.message;
                    sendAlert("GSC Notification", msg, msg);
                });
            }
        })
        .catch(err => console.log("General notification error", err));
}, 30000);