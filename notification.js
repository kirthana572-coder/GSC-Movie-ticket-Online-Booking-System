document.addEventListener("DOMContentLoaded", function () {
    // 请求通知权限
    if (Notification.permission !== "granted" && Notification.permission !== "denied") {
        Notification.requestPermission().then(permission => {
            if (permission === "granted") {
                console.log("通知权限已允许");
            }
        });
    }

    // 启动两个轮询任务
    checkMovieReminders();
    checkGeneralNotifications();

    setInterval(checkMovieReminders, 60000);
    setInterval(checkGeneralNotifications, 60000);
});

/**
 * 自动侦测当前网站路径 (相容 XAMPP 与 InfinityFree)
 */
function getBaseUrl() {
    const pathParts = window.location.pathname.split('/');
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        return '/' + pathParts[1]; // XAMPP 本地环境
    }
    return ''; // InfinityFree 线上环境
}

/**
 * 电影开场提醒
 */
function checkMovieReminders() {
    const siteUrl = getBaseUrl();
    fetch(siteUrl + '/check_reminder.php')
        .then(response => {
            if (!response.ok) throw new Error("HTTP " + response.status);
            return response.json();
        })
        .then(data => {
            if (data && data.reminders && data.reminders.length > 0) {
                data.reminders.forEach(reminder => {
                    const msg = `您的电影「${reminder.title}」即将在 ${reminder.start_time} 开场，请尽快取票。`;
                    showSystemNotification("电影即将开场", msg);
                });
            }
        })
        .catch(error => console.error("电影提醒错误:", error));
}

/**
 * 通用通知（订单创建、取消、过期等）
 */
function checkGeneralNotifications() {
    const siteUrl = getBaseUrl();
    fetch(siteUrl + '/api/get_new_notifications.php')
        .then(response => {
            if (!response.ok) throw new Error("HTTP " + response.status);
            return response.json();
        })
        .then(data => {
            if (data && data.notifications && data.notifications.length > 0) {
                data.notifications.forEach(notif => {
                    showSystemNotification("GSC 通知", notif.message);
                });
            }
        })
        .catch(error => console.error("通用通知错误:", error));
}

/**
 * 显示桌面通知（若权限拒绝则降级为 alert）
 */
function showSystemNotification(title, message) {
    if (Notification.permission === "granted") {
        new Notification(title, {
            body: message,
            requireInteraction: true
        });
    } else {
        alert(title + "\n\n" + message);
    }
}