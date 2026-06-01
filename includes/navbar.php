<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
.custom-navbar{

    background:
    linear-gradient(
        90deg,
        rgba(15,15,15,.96),
        rgba(30,30,30,.96)
    );

    padding:15px 0;

    margin:0;

    border-radius:0;

    border:none;

    box-shadow:
    0 8px 20px rgba(0,0,0,.15);
}

.navbar-brand{

    margin-left:20px;
    
    text-decoration:none;

    display:flex;

    align-items:center;
}

.navbar-brand:hover{ transform:scale(1.03); color:#ffd84d !important; }
.btn-outline-light{ border-radius:12px; padding:7px 18px; border:1px solid rgba(255,255,255,0.5); transition:0.3s; }
.btn-outline-light:hover{ background:white; color:black !important; transform:translateY(-1px); }
.btn-warning{ background-color:#f5c518; border:none; border-radius:30px; padding:8px 22px; font-weight:600; transition:0.3s; }
.btn-warning:hover{ background-color:#e0b400; transform:scale(1.05); }

.profile-avatar{

    width:44px;

    height:44px;

    border-radius:50%;

    background:
    linear-gradient(
        135deg,
        #f5c518,
        #ffdd57
    );

    color:#111;

    font-weight:800;

    font-size:18px;

    box-shadow:
    0 6px 16px rgba(245,197,24,.4);

    transition:.3s;
}

.profile-avatar:hover{

    transform:scale(1.08);
}

.notification-btn{

    color:white;

    font-size:22px;

    margin-right:15px;

    position:relative;

    transition:.3s;
}

.notification-btn:hover{

    color:#f5c518;

    transform:translateY(-2px);
}

.notification-badge{

    position:absolute;

    top:-6px;

    right:-10px;

    background:#dc3545;

    color:white;

    font-size:11px;

    min-width:18px;

    height:18px;

    border-radius:50%;

    display:flex;

    align-items:center;

    justify-content:center;
}

.menu-btn{

    width:44px;

    height:44px;

    border:none;

    border-radius:12px;

    background:
    rgba(255,255,255,.08);

    color:white;

    margin-left:10px;

    transition:.3s;
}

.menu-btn:hover{

    background:
    rgba(245,197,24,.15);

    color:#f5c518;

    transform:
    translateY(-2px);
}

.brand-text{

    font-size:32px;

    font-weight:900;

    background:
    linear-gradient(
        135deg,
        #f5c518,
        #ffe27a
    );

    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;

    letter-spacing:1px;
}

.navbar-brand:hover .brand-text{

    color:#ffd84d;

    transform:translateX(2px);
}

.offcanvas{

    background:
    linear-gradient(
        180deg,
        #181818,
        #242424
    );

    color:white;

    border-left:
    1px solid rgba(245,197,24,.15);

    box-shadow:
    -10px 0 30px rgba(0,0,0,.35);
}

.menu-link{

    background:
    rgba(255,255,255,.04);

    border:
    1px solid rgba(255,255,255,.05);

    color:white;

    text-decoration:none;

    color:#f5f5f5;

    display:flex;

    align-items:center;

    gap:12px;

    border-radius:14px;

    padding:15px 18px;

    font-weight:600;

    transition:.25s;

    backdrop-filter:blur(10px);

    box-shadow:
    0 4px 10px rgba(0,0,0,.08);
}

.menu-link i{

    font-size:18px;

    width:22px;

    text-align:center;

    flex-shrink:0;
}

.menu-link:hover{

    background:
    rgba(245,197,24,.12);

    border-color:
    rgba(245,197,24,.25);

    color:#f5c518;

    transform:
    translateX(4px);
}

.user-card{

    text-align:center;

    padding:25px;

    margin-bottom:24px;

    border-radius:20px;

    background:
    linear-gradient(
        135deg,
        rgba(245,197,24,.08),
        rgba(255,255,255,.04)
    );

    border:
    1px solid rgba(245,197,24,.12);

    backdrop-filter:blur(10px);
}

.user-avatar{

    width:70px;

    height:70px;

    border-radius:50%;

    margin:auto;

    margin-bottom:15px;

    background:#f5c518;

    color:#111;

    font-size:28px;

    font-weight:800;

    display:flex;

    align-items:center;

    justify-content:center;
}

.offcanvas-header{

    border-bottom:1px solid rgba(255,255,255,.08);

    padding:20px;
}

.offcanvas-title{

    font-weight:700;

    color:#f5c518;
}

.logout-link{

    background:
    rgba(220,53,69,.12);

    color:#ff6b6b !important;
}

.logout-link:hover{

    background:#dc3545 !important;

    color:white !important;
}

.menu-footer{

    position:absolute;

    bottom:30px;

    left:25px;

    right:25px;

    text-align:center;

    color:rgba(255,255,255,.45);

    font-size:13px;
}

</style>
<nav class="navbar navbar-dark custom-navbar">
  <div class="container-fluid px-5">

    <a class="navbar-brand d-flex align-items-center"
    href="<?= BASE_URL ?>/index.php">

      <span class="brand-text">
          GSC Cinema
      </span>

  </a>
  
  <div class="d-flex align-items-center">
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php
            $unread_count = 0;
            require_once __DIR__ . '/../config/db.php';
            $res = $conn->query("SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = {$_SESSION['user_id']} AND is_read = 0");
            $unread_count = $res->fetch_assoc()['cnt'] ?? 0;
            ?>

            <a href="<?= BASE_URL ?>/customer/notifications.php"
              class="notification-btn position-relative">

                <i class="bi bi-bell-fill"></i>

                <?php if($unread_count > 0): ?>

                    <span class="notification-badge">

                        <?= $unread_count ?>

                    </span>

                <?php endif; ?>
            </a>

            <a href="<?= BASE_URL ?>/customer/profile.php"
              class="profile-avatar d-flex align-items-center justify-content-center text-decoration-none">

                <?= strtoupper(substr($_SESSION['full_name'] ?? 'U',0,1)) ?>

            </a>
            
            <button
              class="menu-btn"
              data-bs-toggle="offcanvas"
              data-bs-target="#functionMenu">

              <i class="bi bi-grid-3x3-gap-fill"></i>

          </button>

          <?php else: ?>
            <a href="<?= BASE_URL ?>/login.php" class="btn btn-sm btn-outline-light me-2">Sign In</a>
            <a href="<?= BASE_URL ?>/register.php" class="btn btn-sm btn-warning">Register</a>
        <?php endif; ?>
    </div>
  </div>
</nav>
<?php if (isset($_SESSION['user_id'])): ?>

<div class="offcanvas offcanvas-end" tabindex="-1" id="functionMenu">
  <div class="offcanvas-header"><h5 class="offcanvas-title">Menu</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button></div>

  <div class="offcanvas-body">

      <div class="user-card">

          <div class="user-avatar">

              <?= strtoupper(substr($_SESSION['full_name'],0,1)) ?>

          </div>

          <h5 class="mt-3 mb-1">

              <?= htmlspecialchars($_SESSION['full_name']) ?>

          </h5>

          <small class="text-light opacity-75">

              GSC Member

          </small>

      </div>

      <div class="list-group border-0">

          <a href="<?= BASE_URL ?>/customer/movies.php" class="menu-link">
            <i class="bi bi-film me-2"></i>
            Browse Movies
        </a>

        <a href="<?= BASE_URL ?>/customer/history.php" class="menu-link">
            <i class="bi bi-ticket-perforated me-2"></i>
            My Bookings
        </a>

        <a href="<?= BASE_URL ?>/customer/profile.php" class="menu-link">
            <i class="bi bi-person-circle me-2"></i>
            My Profile
        </a>

        <a href="<?= BASE_URL ?>/change_password.php" class="menu-link">
            <i class="bi bi-shield-lock me-2"></i>
            Change Password
        </a>

        <a href="<?= BASE_URL ?>/auth/logout.php"
            class="menu-link logout-link">
            <i class="bi bi-box-arrow-right me-2"></i>
            Sign Out
        </a>

      </div>

      <div class="menu-footer">

        <small>
            GSC Movie Ticket Booking System
        </small>

    </div>

  </div>
</div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>