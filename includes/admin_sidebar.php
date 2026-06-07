<style>

.sidebar{
    position:fixed;
    left:0;
    top:0;

    width:280px;
    height:100vh;

    box-sizing:border-box;

    overflow-y:hidden;

    background:
    linear-gradient(
        180deg,
        #252525,
        #313131
    );

    padding:40px 24px;

    border-right:
    1px solid rgba(255,255,255,.05);

    box-shadow:
    4px 0 25px rgba(0,0,0,.25);

    display:flex;
    flex-direction:column;
}

.sidebar-header{
    text-align:center;
    margin-bottom:35px;
}

.sidebar h2{
    color:#f5c518;
    font-size:30px;
    font-weight:800;
    letter-spacing:2px;
    margin-bottom:4px;
}

.panel-label{
    color:#9b9b9b;

    font-size:11px;

    font-weight:600;

    letter-spacing:3px;

    text-transform:uppercase;

    margin-top:6px;
}

.sidebar-divider{
    height:1px;

    margin:0 0 25px;

    background:
    linear-gradient(
        90deg,
        transparent,
        rgba(245,197,24,.25),
        transparent
    );
}

.menu-group{
    flex:1;
}

.sidebar a{
    display:block;

    padding:14px 20px;

    border-left:4px solid transparent;

    text-decoration:none;

    color:#d6d6d6;

    border-radius:10px;

    margin-bottom:8px;

    font-weight:600;

    transition:.25s;
}

.sidebar a:hover{
    background:
    rgba(245,197,24,.12);

    color:#f5c518;
}

.sidebar a.active{
    background:
    linear-gradient(
        90deg,
        rgba(245,197,24,.22),
        rgba(245,197,24,.06)
    );

    color:#f5c518;

    border-left:4px solid #f5c518;

    box-shadow:
    inset 0 0 10px rgba(245,197,24,.08);
}

.logout-section{
    margin-top:20px;

    padding-top:25px;

    border-top:
    1px solid rgba(255,255,255,.08);
}

.logout-btn{
    text-align:center;

    background:
    rgba(220,53,69,.15);

    margin-top:20px !important;

    border:
    1px solid rgba(220,53,69,.25);

    color:#ff8b8b !important;
    padding:14px 20px !important;
    border-radius:10px !important;
}

.logout-btn:hover{
    background:
    rgba(220,53,69,.85) !important;

    color:white !important;

    transform:none !important;
}

</style>

<button class="admin-sidebar-toggle">
    <i class="bi bi-app-indicator"></i>
</button>

<div class="sidebar-overlay"></div>

<div class="sidebar">

    <div class="sidebar-header">

        <h2>GSC</h2>

        <div class="panel-label">
            ADMIN CONTROL PANEL
        </div>

    </div>

    <div class="sidebar-divider"></div>

    <div class="menu-group">

    <a 
            href="<?= BASE_URL ?>/admin/admin_dashboard.php"

            class="<?= basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : '' ?>"
        >
            Dashboard
        </a>

        <?php
            $currentPage = basename($_SERVER['PHP_SELF']);
            ?>

            <a 
                href="<?= BASE_URL ?>/admin/movies/admin_movies.php"

                class="<?= 
                    in_array(
                        $currentPage,
                        [
                            'admin_movies.php',
                            'add_movie.php',
                            'delete_movie.php',
                            'edit_movie.php',
                            'view_movie.php'
                        ]
                    )
                    ? 'active'
                    : ''
                ?>"
            >
                Movies
            </a>

        <?php
            $currentPage = basename($_SERVER['PHP_SELF']);
            ?>

            <a 
                href="<?= BASE_URL ?>/admin/showtimes/admin_showtimes.php"

                class="<?= 
                    in_array(
                        $currentPage,
                        [
                            'admin_showtimes.php',
                            'add_showtime.php',
                            'delete_showtime.php',
                            'edit_showtime.php',
                            'view_showtime.php'
                        ]
                    )
                    ? 'active'
                    : ''
                ?>"
            >
                Showtimes 
            </a>

        <?php
            $currentPage = basename($_SERVER['PHP_SELF']);
            ?>

            <a 
                href="<?= BASE_URL ?>/admin/seats/admin_seats.php"

                class="<?= 
                    in_array(
                        $currentPage,
                        [
                            'admin_seats.php',
                            'manage_seats.php'
                            
                        ]
                    )
                    ? 'active'
                    : ''
                ?>"
            >
                Seats 
            </a>

        <?php
            $currentPage = basename($_SERVER['PHP_SELF']);
            ?>

            <a
                href="<?= BASE_URL ?>/admin/users/users.php"

                class="<?= 
                    in_array(
                        $currentPage,
                        [
                            'users.php',
                            'view_user.php',
                            'view_booking.php',
                            'toggle_user.php'
                        ]
                    )
                    ? 'active'
                    : ''
                ?>"
            >
                Customer Management
            </a>


        <?php
            $currentPage = basename($_SERVER['PHP_SELF']);
            ?>

            <a
                href="<?= BASE_URL ?>/admin/staff/staffs.php"

                class="<?= 
                    in_array(
                        $currentPage,
                        [
                            'toggle_staff.php',
                            'view_staff.php',
                            'add_staff.php',
                            'edit_staff.php',
                            'staffs.php'
                        ]
                    )
                    ? 'active'
                    : ''
                ?>"
            >
                Staff Management
            </a>


        <?php
            $currentPage = basename($_SERVER['PHP_SELF']);
            ?>

            <a 
                href="<?= BASE_URL ?>/admin/admin_profile.php"

                class="<?= 
                    in_array(
                        $currentPage,
                        [
                            'admin_profile.php',
                            'change_password.php'
                        ]
                    )
                    ? 'active'
                    : ''
                ?>"
            >
                Profile
            </a>
        </div>

    <div class="logout-section">

        <a
            href="<?= BASE_URL ?>/auth/logout.php"
            class="logout-btn"
        >
            Logout
        </a>

    </div>

</div>

<script>
const sidebar =
document.querySelector('.sidebar');

const overlay =
document.querySelector('.sidebar-overlay');

const toggle =
document.querySelector('.admin-sidebar-toggle');

toggle.addEventListener('click', () => {

    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');

});

overlay.addEventListener('click', () => {

    sidebar.classList.remove('active');
    overlay.classList.remove('active');

});
</script>