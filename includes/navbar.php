<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<style>
.custom-navbar{ background:linear-gradient(90deg,rgba(10,10,10,0.95),rgba(28,28,28,0.95)); padding:20px 0 !important; margin:10px; border-radius:18px; backdrop-filter:blur(10px); box-shadow:0 8px 25px rgba(0,0,0,0.25); border:1px solid rgba(255,255,255,0.05); z-index:1000; }
.navbar-brand{ color:#f5c518 !important; font-size:24px; font-weight:700; letter-spacing:0.5px; transition:0.3s; }
.navbar-brand:hover{ transform:scale(1.03); color:#ffd84d !important; }
.btn-outline-light{ border-radius:12px; padding:7px 18px; border:1px solid rgba(255,255,255,0.5); transition:0.3s; }
.btn-outline-light:hover{ background:white; color:black !important; transform:translateY(-1px); }
.btn-warning{ background-color:#f5c518; border:none; border-radius:30px; padding:8px 22px; font-weight:600; transition:0.3s; }
.btn-warning:hover{ background-color:#e0b400; transform:scale(1.05); }
.profile-avatar{ width:42px; height:42px; border-radius:50%; background:#f5c518; color:black; font-weight:bold; font-size:18px; display:flex; align-items:center; justify-content:center; text-decoration:none; box-shadow:0 4px 12px rgba(245,197,24,0.35); transition:0.3s; }
.profile-avatar:hover{ transform:scale(1.08); }
.notification-bell{ font-size:22px; text-decoration:none; transition:0.3s; }
.notification-bell:hover{ transform:scale(1.15); }
.menu-btn{ border-radius:12px; padding:7px 14px; }
</style>
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">GSC Cinema</a>
    <div class="d-flex align-items-center">
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php
            $unread_count = 0;
            require_once __DIR__ . '/../config/db.php';
            $res = $conn->query("SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = {$_SESSION['user_id']} AND is_read = 0");
            $unread_count = $res->fetch_assoc()['cnt'] ?? 0;
            ?>
            <a href="<?= BASE_URL ?>/customer/notifications.php" class="position-relative me-2 text-light" title="Notifications">🔔
                <?php if ($unread_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.6rem;"><?= $unread_count ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= BASE_URL ?>/customer/profile.php" class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold text-decoration-none" style="width:40px;height:40px;font-size:18px;"><?= strtoupper(substr($_SESSION['full_name'] ?? 'U',0,1)) ?></a>
            <button class="btn btn-outline-light ms-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#functionMenu" title="Menu">☰</button>
            <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-sm btn-outline-light ms-2">Logout</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/login.php" class="btn btn-sm btn-outline-light me-2">Sign In</a>
            <a href="<?= BASE_URL ?>/register.php" class="btn btn-sm btn-warning">Register</a>
        <?php endif; ?>
    </div>
  </div>
</nav>
<?php if (isset($_SESSION['user_id'])): ?>
<div class="offcanvas offcanvas-end" tabindex="-1" id="functionMenu">
  <div class="offcanvas-header"><h5 class="offcanvas-title">Menu</h5><button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button></div>
  <div class="offcanvas-body">
    <div class="list-group">
      <a href="<?= BASE_URL ?>/customer/movies.php" class="list-group-item list-group-item-action">Browse Movies</a>
      <a href="<?= BASE_URL ?>/customer/history.php" class="list-group-item list-group-item-action">My Bookings</a>
      <a href="<?= BASE_URL ?>/customer/profile.php" class="list-group-item list-group-item-action">My Profile</a>
      <a href="<?= BASE_URL ?>/change_password.php" class="list-group-item list-group-item-action">Change Password</a>
      <a href="<?= BASE_URL ?>/auth/logout.php" class="list-group-item list-group-item-action text-danger">Sign Out</a>
    </div>
  </div>
</div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>