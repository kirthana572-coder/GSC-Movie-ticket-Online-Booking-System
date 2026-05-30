<style>

.sidebar{
    width:280px;
    min-height:100vh;

    background:
    linear-gradient(
        180deg,
        #111827,
        #1f2937
    );

    padding:30px 20px;

    position:fixed;
    left:0;
    top:0;

    box-shadow:
    4px 0 25px rgba(0,0,0,0.2);
}

.logo{
    color:#f5c518;
    font-size:35px;
    font-weight:500;
    margin-bottom:40px;
}

.sidebar a{
    display:block;

    text-decoration:none;

    color:white;

    padding:12px 16px;

    border-radius:14px;

    margin-bottom:3px;

    transition:0.25s;

    font-weight:500;

}

.sidebar a:hover{
    background:rgba(255,255,255,0.08);
    transform:translateX(5px);
}

.sidebar a.active{
    background:rgb(255, 231, 46);
    color:#111 !important;
}

.logout-btn{
    margin-top:40px !important;

    background:rgba(213, 213, 213, 0.72);
    color:rgb(10, 10, 10) !important;
}

.logout-btn:hover{
    background:rgba(255, 253, 207, 0.89) !important;
    color:#000 !important;
}

</style>

<div class="sidebar">

    <div class="logo">
        GSC ADMIN
    </div>

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
    Users
</a>

    <a href="#">
        Staff
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

    <a 
        href="<?= BASE_URL ?>/auth/logout.php"
        class="logout-btn"
    >
        Logout
    </a>

</div>