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
            <a href="/GSC-Movie-ticket-Online-Booking-System/signin.php"
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