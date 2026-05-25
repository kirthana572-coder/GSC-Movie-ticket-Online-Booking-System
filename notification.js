document.addEventListener("DOMContentLoaded", function () {
    if (Notification.permission !== "granted" && Notification.permission !== "denied") {
        Notification.requestPermission().then(permission => {
            if (permission === "granted") console.log("通知权限已允许");
        });
    }
    checkMovieReminders();
    checkGeneralNotifications();
    setInterval(checkMovieReminders, 60000);
    setInterval(checkGeneralNotifications, 60000);
});

function getBaseUrl() {
    const pathParts = window.location.pathname.split('/');
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        return '/' + pathParts[1];
    }
    return '';
}

function checkMovieReminders() {
    const siteUrl = getBaseUrl();
    fetch(siteUrl + '/check_reminder.php')
        .then(response => response.ok ? response.json() : Promise.reject('HTTP error'))
        .then(data => {
            if (data && data.reminders && data.reminders.length) {
                data.reminders.forEach(reminder => {
                    showSystemNotification("电影即将开场", `您的电影「${reminder.title}」即将在 ${reminder.start_time} 开场，请尽快取票。`);
                });
            }
        })
        .catch(error => console.error("电影提醒错误:", error));
}

function checkGeneralNotifications() {
    const siteUrl = getBaseUrl();
    fetch(siteUrl + '/api/get_new_notifications.php')
        .then(response => response.ok ? response.json() : Promise.reject('HTTP error'))
        .then(data => {
            if (data && data.notifications && data.notifications.length) {
                data.notifications.forEach(notif => {
                    showSystemNotification("GSC 通知", notif.message);
                });
            }
        })
        .catch(error => console.error("通用通知错误:", error));
}

function showSystemNotification(title, message) {
    if (Notification.permission === "granted") {
        new Notification(title, { body: message, requireInteraction: true });
    } else {
        alert(title + "\n\n" + message);
    }
}