document.addEventListener("DOMContentLoaded", function () {
    // 1. 先跟使用者請求通知權限
    if (Notification.permission !== "granted" && Notification.permission !== "denied") {
        Notification.requestPermission().then(permission => {
            if (permission === "granted") {
                console.log("系統提示：使用者已允許通知權限！");
            }
        });
    }

    // 2. 網頁載入時先執行一次檢查
    checkMovieReminders();

    // 3. 設定定時器：每隔 60 秒自動向後端查詢一次
    setInterval(checkMovieReminders, 60000);
});

/**
 * 自動偵測當前網站路徑 (相容 XAMPP 與 InfinityFree)
 */
function getBaseUrl() {
    const pathParts = window.location.pathname.split('/');
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        return '/' + pathParts[1]; // XAMPP 本地環境
    }
    return ''; // InfinityFree 線上環境
}

function checkMovieReminders() {
    const siteUrl = getBaseUrl();
    const requestUrl = siteUrl + '/check_reminder.php';

    fetch(requestUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error("網頁請求失敗，狀態碼：" + response.status);
            }
            return response.json(); 
        })
        .then(data => {
            // 假設後端回傳提醒資料格式
            if (data && data.reminders && data.reminders.length > 0) {
                data.reminders.forEach(reminder => {
                    showSystemNotification("電影即將開場！", `您的電影 ${reminder.title} 即將在 ${reminder.start_time} 開始，請準備入場。`);
                });
            }
        })
        .catch(error => {
            console.error("[通知模組錯誤] 無法連接:", error);
        });
}

function showSystemNotification(title, message) {
    if (Notification.permission === "granted") {
        const options = {
            body: message,
            requireInteraction: true 
        };
        const notification = new Notification(title, options);
        notification.onclick = function () {
            window.focus();
        };
    } else {
        alert(title + "\n\n" + message);
    }
}