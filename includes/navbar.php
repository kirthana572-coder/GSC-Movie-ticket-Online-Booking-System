<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="/GSC-Movie-ticket-Online-Booking-System/index.php">GSC Cinema</a>

    <div class="d-flex align-items-center">
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- 通知铃铛 -->
            <?php
            $unread_count = 0;
            require_once __DIR__ . '/../config/db.php';
            $res = $conn->query("SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = {$_SESSION['user_id']} AND is_read = 0");
            $unread_count = $res->fetch_assoc()['cnt'] ?? 0;
            ?>
            <a href="/GSC-Movie-ticket-Online-Booking-System/customer/notifications.php"
               class="position-relative me-2 text-light" title="Notifications">
                🔔
                <?php if ($unread_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                          style="font-size: 0.6rem;">
                        <?= $unread_count ?>
                    </span>
                <?php endif; ?>
            </a>

            <!-- 圆形头像 -->
            <a href="/GSC-Movie-ticket-Online-Booking-System/customer/profile.php"
               class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold text-decoration-none"
               style="width: 40px; height: 40px; font-size: 18px;"
               title="My Profile">
                <?= strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)) ?>
            </a>

            <!-- 三条线菜单按钮 -->
            <button class="btn btn-outline-light ms-2" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#functionMenu"
                    title="Menu">
                ☰
            </button>

            <!-- 登出 -->
            <a href="/GSC-Movie-ticket-Online-Booking-System/auth/logout.php"
               class="btn btn-sm btn-outline-light ms-2">Logout</a>
        <?php else: ?>
            <a href="/GSC-Movie-ticket-Online-Booking-System/login.php"
               class="btn btn-sm btn-outline-light me-2">Sign In</a>
            <a href="/GSC-Movie-ticket-Online-Booking-System/register.php"
               class="btn btn-sm btn-warning">Register</a>
        <?php endif; ?>
    </div>
  </div>
</nav>

<!-- 侧滑功能菜单 -->
<?php if (isset($_SESSION['user_id'])): ?>
<div class="offcanvas offcanvas-end" tabindex="-1" id="functionMenu">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Menu</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <div class="list-group">
      <a href="/GSC-Movie-ticket-Online-Booking-System/customer/movies.php" class="list-group-item list-group-item-action">Browse Movies</a>
      <a href="/GSC-Movie-ticket-Online-Booking-System/customer/history.php" class="list-group-item list-group-item-action">My Bookings</a>
      <a href="/GSC-Movie-ticket-Online-Booking-System/customer/profile.php" class="list-group-item list-group-item-action">My Profile</a>
      <a href="/GSC-Movie-ticket-Online-Booking-System/change_password.php" class="list-group-item list-group-item-action">Change Password</a>
      <a href="/GSC-Movie-ticket-Online-Booking-System/auth/logout.php" class="list-group-item list-group-item-action text-danger">Sign Out</a>
    </div>
  </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>