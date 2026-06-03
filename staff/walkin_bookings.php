<?php

// Include authentication and database
require_once '../includes/staff_auth.php';
require_once '../config/db.php';

if(isset($_GET['term'])){

    $term = trim($_GET['term']);

    $data = [];

    $searchTerm = '%' . $term . '%';

    if(
        preg_match('/[0-9]/', $term)
    ){

        $stmt = $conn->prepare("

            SELECT
                booking_code,
                customer_name

            FROM walkin_bookings

            WHERE booking_code LIKE ?

            ORDER BY id DESC

            LIMIT 8

        ");

        $stmt->bind_param(
            "s",
            $searchTerm
        );

    }else{

        $stmt = $conn->prepare("

            SELECT DISTINCT
                customer_name

            FROM walkin_bookings

            WHERE customer_name LIKE ?

            ORDER BY customer_name ASC

            LIMIT 8

        ");

        $stmt->bind_param(
            "s",
            $searchTerm
        );

    }

    $stmt->execute();

    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()){

        $data[] = [
            'booking_code' => $row['booking_code'] ?? '',
            'name' => $row['customer_name'] ?? $row['customer_name']
        ];
    }

    echo json_encode($data);
    exit();
}

// Check staff role
if ($_SESSION['role'] !== 'staff') {
    die("Access denied.");
}

// Delete booking
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];


    // GET WALKIN BOOKING
    $getStmt = $conn->prepare("

        SELECT id
        FROM walkin_bookings
        WHERE booking_code = ?

    ");

    $getStmt->bind_param(
        "s",
        $id
    );

    $getStmt->execute();

    $booking = $getStmt
        ->get_result()
        ->fetch_assoc();


    if($booking){

        $walkin_id = $booking['id'];


        // RELEASE SEATS
        $releaseStmt = $conn->prepare("

            UPDATE seats s

            JOIN walkin_booking_seats wbs
            ON s.id = wbs.seat_id

            SET s.status = 'available'

            WHERE wbs.walkin_booking_id = ?

        ");

        $releaseStmt->bind_param(
            "i",
            $walkin_id
        );

        $releaseStmt->execute();


        // DELETE SEAT RELATION
        $deleteSeatStmt = $conn->prepare("

            DELETE FROM walkin_booking_seats
            WHERE walkin_booking_id = ?

        ");

        $deleteSeatStmt->bind_param(
            "i",
            $walkin_id
        );

        $deleteSeatStmt->execute();


        // DELETE BOOKING
        $deleteStmt = $conn->prepare("

            DELETE FROM walkin_bookings
            WHERE id = ?

        ");

        $deleteStmt->bind_param(
            "i",
            $walkin_id
        );

        $deleteStmt->execute();
    }


    echo "
        <script>
            alert('Booking deleted successfully!');
            window.location.href='" . BASE_URL . "/staff/walkin_bookings.php';
        </script>
    ";

    exit();
}

// Search value
$search = $_GET['search'] ?? '';

// Main query
$sql = "
    SELECT 
        wb.*,
        m.title AS movie_title,
        s.show_date,
        s.show_time
    FROM walkin_bookings wb
    JOIN showtimes s
    ON wb.showtime_id = s.id
    JOIN movies m
    ON s.movie_id = m.id
";

// Search filter
if($search != ''){

    $search = $conn->real_escape_string($search);

    $sql .= "

        WHERE

            wb.booking_code LIKE '%$search%'

            OR

            wb.customer_name LIKE '%$search%'

    ";
}

// Order latest first
$sql .= "
    ORDER BY wb.id DESC
";

// Execute query
$walkinBookings = $conn->query($sql);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Walk-in Booking - GSC</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body{
            margin:0;
            font-family:'Segoe UI',sans-serif;
            background:#f6f7fb;
            min-height:100vh;
        }

        .page-container{
            margin-left:280px;
            padding:50px;
        }

        .top-bar{
            display:flex;
            justify-content:space-between;
            margin-bottom:35px;
            position:relative;
        }

        .page-header-row{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:30px;
        }

        .page-title{
            font-size:40px;
            font-weight:500;
            color:#2f2f2f;
            margin:0;
            line-height:1.2;
        }

        .page-subtitle{
            margin-top:8px;
            font-weight:400;
            font-size:15px;
            color:#6c757d;
        }

        .back-btn{
            text-decoration:none;
            background:#2f2f2f;
            color:white;
            padding:10px 20px;
            border-radius:12px;
            font-weight:600;
            transition:0.25s;
            display:inline-block;
            width:auto;
        }

        .back-btn:hover{
            background:#f5c518;
            color:#111;
        }

        .add-btn{
            text-decoration:none;
            background:#ffd332;
            color:#111;
            padding:10px 22px;
            border-radius:12px;
            font-weight:700;
            transition:0.25s;
        }

        .add-btn:hover{
            background:#ffdc5f;
            color:#111;
        }

        .mini-search{
            width:460px;
        }

        .mini-search .input-group{
            overflow:hidden;
            border-radius:14px;
            box-shadow:0 4px 15px rgba(0,0,0,.08);
        }

        .mini-search .form-control{
            border:none;
            height:48px;
            box-shadow:none;
        }

        .btn-search{
            background:#212529;
            color:white;
            border:none;
            border-radius:12px;
            font-weight:700;
        }

        .btn-search:hover{
            background:#343a40;
        }

        .btn-reset{
            height:48px;
            padding:0 18px;

            display:flex;
            align-items:center;
            justify-content:center;

            border-radius:14px;
            border:1px solid #dee2e6;

            background:#fff;
            color:#495057;

            text-decoration:none;
            font-weight:600;
        }

        .btn-reset:hover{
            background:#f8f9fa;
        }

        .table-card{

            background:#fff;

            border-radius:22px;

            overflow:hidden;

            border:1px solid rgba(0,0,0,.05);

            box-shadow:
            0 10px 25px rgba(0,0,0,.08);
        }

        .table{
            margin-bottom:0;
        }

        .table thead th{

            background:#f8f9fb;

            color:#444;

            font-size:14px;

            font-weight:700;

            border:none;

            padding:18px;
        }

        .table tbody td{
            padding:16px;
            vertical-align:middle;
            vertical-align:middle;

            padding:18px;

            border-color:#eef1f5;
            border-color:rgba(0,0,0,0.06);
        }

        .table tbody tr:hover{
            background:#fafbfc;
        }

        .customer-name{
            font-weight:700;
            color:#212529;
        }

        .movie-name{
            color:#6c757d;
            font-size:14px;
        }

        .showtime{
            color:#9aa0a6;
            font-size:13px;
        }

        .badge{
            padding:8px 14px;
            border-radius:20px;
            font-size:14px;
        }

        .badge-paid{
            background:#d1e7dd;
            color:#0f5132;
        }

        .badge-pending{
            background:#fff3cd;
            color:#664d03;
        }

        .badge-cancelled{
            background:#f8d7da;
            color:#842029;
        }

        .action-group{
            display:flex;
            gap:8px;
            flex-wrap:nowrap;
        }

        .btn-action{

            width:100px;
            height:38px;

            display:flex;

            align-items:center;
            justify-content:center;

            border:none !important;
            text-decoration:none !important;

            border-radius:10px;

            font-size:14px;
            font-weight:600;

            transition:.25s;
        }

        .btn-qr,
        .btn-delete,
        .btn-view,
        .btn-edit{

            border:none !important;
            text-decoration:none !important;

            display:flex;
            align-items:center;
            justify-content:center;
        }

        .btn-add{
            background:#f5c518;
            color:#111;
            border:none;
            border-radius:12px;
            font-weight:700;
            padding:12px 22px;
            text-decoration:none;
            transition:.25s;
        }

        .btn-add:hover{
            background:#ffd028;
            color:#111;
            transform:translateY(-2px);
        }

        .btn-view{
            background:#343a40;
            color:#fff;
        }

        .btn-view:hover{
            background:#212529;
            color:#fff;
        }

        .btn-edit{
            background:#212529;
            color:#fff;
        }

        .btn-delete{
            background:#dc3545;
            color:#fff;
        }

        .btn-delete:hover{
            background:#bb2d3b;
            color:#fff;
        }

        .btn-view:hover,
        .btn-edit:hover{
            transform:scale(1.04);
        }

        .btn-qr{
            background:#198754;
            color:#fff;
        }


        .btn-qr:hover{
            background:#157347;
            color:#fff !important;
            transform:scale(1.04);
        }

        .search-box{
            position:relative;
            width:100%;
        }

        .search-suggestions{
            position:absolute;
            top:100%;
            left:0;
            width:100%;

            background:#fff;

            border-radius:12px;

            box-shadow:
            0 10px 25px rgba(0,0,0,.12);

            z-index:9999;

            display:none;

            overflow:hidden;

            margin-top:6px;
        }

        .search-item{

            padding:12px 15px;

            cursor:pointer;

            transition:.2s;

            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .search-item:hover{
            background:#f5f5f5;
        }

    </style>

</head>

<body>

<?php include '../includes/staff_sidebar.php'; ?>

<div class="page-container">

    <!-- Top Bar -->
    <div class="page-header-row">

        <div>

            <h1 class="page-title">
                Walk-in Booking Management
            </h1>

            <p class="page-subtitle">
                Manage counter bookings, customer records and ticket issuance.
            </p>

        </div>

        <a
            href="<?= BASE_URL ?>/staff/add_walkin_booking.php"
            class="btn btn-add"
        >
            + Add Booking
        </a>

    </div>

    <!-- Search Form -->
    <form method="GET" class="mini-search mb-4">

        <div class="d-flex gap-2">

            <div class="search-box">

                <div class="input-group">

                    <input
                        type="text"
                        id="walkinSearch"
                        name="search"
                        class="form-control"
                        placeholder="Search Booking ID or Customer Name..."
                        autocomplete="off"
                        value="<?= htmlspecialchars($search) ?>"
                    >

                    <button
                        type="submit"
                        class="btn btn-search"
                    >
                        Search
                    </button>

                </div>

                <div
                    id="searchSuggestions"
                    class="search-suggestions"
                ></div>

            </div>

            <?php if($search != ''): ?>

                <a
                    href="<?= BASE_URL ?>/staff/walkin_bookings.php"
                    class="btn-reset"
                >
                    Reset
                </a>

                <?php endif; ?>

        </div>

    </form>

    <!-- Table -->
    <div class="table-card">

        <table class="table mb-0">

            <thead>

                <tr>
                    <th>No</th>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th width="420">Action</th>
                </tr>

            </thead>

            <tbody>

            <?php

            $no = 1;

            while ($booking = $walkinBookings->fetch_assoc()) :

                // Default status
                $statusClass = 'bg-warning text-dark';

                    $statusClass = 'badge-pending';

                    if($booking['payment_status']=='Paid'){
                        $statusClass='badge-paid';
                    }

                    if(
                        in_array(
                            $booking['payment_status'],
                            ['Cancelled','Expired']
                        )
                    ){
                        $statusClass='badge-cancelled';
                    }

            ?>

                <tr>

                    <td>
                        <?= $no++ ?>
                    </td>

                    <td>
                        <span class="fw-bold text-dark">
                            #<?= $booking['booking_code'] ?>
                        </span>
                    </td>

                    <td>

                        <div>

                            <div class="customer-name">
                                <?= $booking['customer_name'] ?>
                            </div>

                            <div class="movie-name">
                                <?= $booking['movie_title'] ?>
                            </div>

                            <div class="showtime">
                                <?= date('d M Y', strtotime($booking['show_date'])) ?>
                                ·
                                <?= date('h:i A', strtotime($booking['show_time'])) ?>
                            </div>

                        </div>

                    </td>

                    <td>
                        <?= date('d M Y', strtotime($booking['show_date'])) ?>
                    </td>

                    <td>

                        <span class="badge <?= $statusClass ?>">
                            <?= $booking['payment_status'] ?>

                            <?php if(
                                $booking['payment_status'] == 'Cancelled'
                                &&
                                $booking['cancelled_by'] == 'admin'
                            ): ?>

                                <br>
                                <small>
                                    by admin
                                </small>

                            <?php endif; ?>
                        </span>

                    </td>

                    <td>

                        <div class="action-group">

                            <!-- View -->
                            <a
                                href="<?= BASE_URL ?>/staff/view_walkin_booking.php?id=<?= $booking['id'] ?>"
                                class="btn-action btn-view"
                            >
                                View
                            </a>

                            <!-- QR -->
                            <?php if($booking['payment_status'] == 'Paid'): ?>

                                <a
                                    href="<?= BASE_URL ?>/staff/walkin_qr_ticket.php?booking_id=<?= $booking['id'] ?>"
                                    class="btn-action btn-qr"
                                >
                                    View QR
                                </a>

                            <?php endif; ?>

                            <!-- Delete -->
                            <a
                                href="<?= BASE_URL ?>/staff/walkin_bookings.php?delete=<?= $booking['booking_code'] ?>"
                                class="btn-action btn-delete"
                                onclick="return confirm('Delete this booking?')"
                            >
                                Delete
                            </a>

                        </div>

                    </td>

                </tr>

            <?php endwhile; ?>

            </tbody>

        </table>

    </div>

</div>

<script>

const searchInput =
    document.getElementById('walkinSearch');

const suggestions =
    document.getElementById('searchSuggestions');

searchInput.addEventListener('keyup', () => {

    const keyword = searchInput.value.trim();

    if(keyword.length < 2){

        suggestions.style.display = 'none';
        return;
    }

    fetch(
        `<?= basename($_SERVER['PHP_SELF']) ?>?term=${encodeURIComponent(keyword)}`
    )

    .then(res => res.json())

    .then(data => {

        if(data.length === 0){

            suggestions.style.display = 'none';
            return;
        }

        suggestions.innerHTML = '';

        const isBookingSearch =
            /\d/.test(keyword);

    data.forEach(row => {

        const item =
            document.createElement('div');

        item.classList.add('search-item');

        if(isBookingSearch){

            item.textContent =
                row.booking_code + ' - ' + row.name;

        }else{

            item.textContent =
                row.name;

        }

        item.addEventListener('click', () => {

            if(isBookingSearch){

                searchInput.value =
                    row.booking_code;

            }else{

                searchInput.value =
                    row.name;

            }

            suggestions.style.display =
                'none';

        });

        suggestions.appendChild(item);

    });
        suggestions.style.display = 'block';

    });

});

document.addEventListener('click', (e) => {

    if(!e.target.closest('.mini-search')){

        suggestions.style.display = 'none';
    }

});

</script>

</body>
</html>