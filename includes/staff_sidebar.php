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

.menu-group{
    flex:1;
}

.sidebar-header{
    text-align:center;
    margin-bottom:30px;
}

.sidebar h2{
    color:#f5c518;
    font-size:28px;
    font-weight:800;
    letter-spacing:2px;
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
}

.logout-section{

    margin-top:50px;

    padding-top:25px;

    border-top:
    1px solid rgba(255,255,255,.08);
}

.logout-btn{

    text-align:center;

    background:
    rgba(220,53,69,.15);

    border:
    1px solid rgba(220,53,69,.25);

    color:#ff8b8b !important;
}

.logout-btn:hover{

    background:
    rgba(220,53,69,.85) !important;

    color:white !important;

    transform:none !important;
}

.panel-label{

    color:#9b9b9b;

    font-size:11px;

    font-weight:600;

    letter-spacing:3px;

    text-transform:uppercase;
}

.sidebar-divider{

    height:1px;

    margin:0px 0 25px;

    background:
    linear-gradient(
        90deg,
        transparent,
        rgba(245,197,24,.25),
        transparent
    );
}

</style>

    <div class="sidebar">

        <div class="sidebar-header">

            <h2>GSC</h2>

            <div class="panel-label">
                CINEMA STAFF PANEL
            </div>

        </div>

        <div class="sidebar-divider"></div>


    <div class="menu-group">
        <a
            href="<?= BASE_URL ?>/staff/staff_dashboard.php"
            class="<?= basename($_SERVER['PHP_SELF']) == 'staff_dashboard.php' ? 'active' : '' ?>"
        >
            Dashboard
        </a>

        <?php
            $currentPage = basename($_SERVER['PHP_SELF']);
        ?>

            <a 
                href="<?= BASE_URL ?>/staff/customer_bookings.php"

                class="<?= 
                    in_array(
                        $currentPage,
                        [
                            'customer_bookings.php',
                            'booking_details.php',
                            'generate_ticket.php'
                        ]
                    )
                    ? 'active'
                    : ''
                ?>"
            >
                Customer Bookings
            </a>


        <?php
            $currentPage = basename($_SERVER['PHP_SELF']);
        ?>

            <a 
                href="<?= BASE_URL ?>/staff/walkin_bookings.php"

                class="<?= 
                    in_array(
                        $currentPage,
                        [
                            'walkin_bookings.php',
                            'add_walkin_booking.php',
                            'edit_walkin_booking.php',
                            'view_walkin_booking.php',
                            'walkin_qr_ticket.php'
                        ]
                    )
                    ? 'active'
                    : ''
                ?>"
            >
                Walk-In Booking
            </a>

        <?php
            $currentPage = basename($_SERVER['PHP_SELF']);
        ?>

            <a 
                href="<?= BASE_URL ?>/staff/update_payment.php"

                class="<?= 
                    in_array(
                        $currentPage,
                        [
                            'update_payment.php'
                        ]
                    )
                    ? 'active'
                    : ''
                ?>"
            >
                Update Payment Status
            </a>

        
        <?php
            $currentPage = basename($_SERVER['PHP_SELF']);
        ?>

            <a 
                href="<?= BASE_URL ?>/staff/scan_qr.php"

                class="<?= 
                    in_array(
                        $currentPage,
                        [
                            'scan_qr.php'
                        ]
                    )
                    ? 'active'
                    : ''
                ?>"
            >
                Scan QR Ticket
            </a>


        <?php
            $currentPage = basename($_SERVER['PHP_SELF']);
        ?>

            <a 
                href="<?= BASE_URL ?>/staff/profile.php"

                class="<?= 
                    in_array(
                        $currentPage,
                        [
                            'profile.php',
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
