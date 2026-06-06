<?php

// Include authentication and database
require_once '../includes/staff_auth.php';
require_once '../config/db.php';

if(isset($_GET['term'])){

    $term = trim($_GET['term']);

    $data = [];

    $stmt = $conn->prepare("
        SELECT 
            u.id AS user_id,
            u.full_name,
            MIN(b.id) AS booking_id
        FROM users u
        JOIN bookings b ON b.user_id = u.id
        WHERE
            CAST(b.id AS CHAR) LIKE ?
            OR u.full_name LIKE ?
        GROUP BY u.id, u.full_name
        ORDER BY u.full_name ASC
        LIMIT 8
    ");

$searchTerm = '%' . $term . '%';

$stmt->bind_param(
    "ss",
    $searchTerm,
    $searchTerm
);

    $stmt->execute();

    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()){

        $data[] = [
            'id' => $row['booking_id'],
            'name' => $row['full_name']
        ];
    }

    echo json_encode($data);
    exit();
}

// Update payment status
if(isset($_POST['update_status'])){

    $booking_id   = intval($_POST['booking_id']);
    $new_status   = $_POST['payment_status'];

    $current = $conn->query("
        SELECT payment_status, cancel_reason, cancelled_by 
        FROM bookings 
        WHERE id = $booking_id
    ")->fetch_assoc();

    if ($current['cancelled_by'] === 'admin') {
        $_SESSION['success'] = "This booking was cancelled by admin and cannot be modified.";
        header("Location: customer_bookings.php");
        exit();
    }

    if ($current['payment_status'] === 'Cancelled') {
        $_SESSION['success'] = "This booking is already cancelled and cannot be modified.";
        header("Location: customer_bookings.php");
        exit();
    }

    $new_status = $_POST['payment_status'];

    $cancel_reason = $_POST['cancel_reason'] ?? $current['cancel_reason'];

    $cancelled_by  = $current['cancelled_by'];

    if ($new_status === 'Cancelled') {

        $cancel_reason = $cancel_reason ?: "Cancelled by staff";
        $cancelled_by  = $cancelled_by ?: "staff";
    }


    $allowed = ['Pending', 'Paid', 'Cancelled', 'Expired'];


    if (!in_array($current['payment_status'], ['Pending', 'Paid'])) {
        $_SESSION['success'] = "This booking cannot be modified.";
        header("Location: customer_bookings.php");
        exit();
    }

    if(in_array($new_status, $allowed)){

        // 如果 staff cancel
        if($new_status === 'Cancelled'){

            $stmt = $conn->prepare("
                UPDATE bookings
                SET
                    payment_status = ?,
                    cancel_reason = ?,
                    cancelled_by = ?,
                    cancelled_at = NOW()
                WHERE id = ?
            ");

            $stmt->bind_param(
                "sssi",
                $new_status,
                $cancel_reason,
                $cancelled_by,
                $booking_id
            );

        } else {

            $stmt = $conn->prepare("

                UPDATE bookings

                SET payment_status = ?

                WHERE id = ?

            ");

            $stmt->bind_param(
                "si",
                $new_status,
                $booking_id
            );
        }

        $stmt->execute();

        /* PAID -> BOOKED */

        if($new_status === 'Paid'){

            $seatStmt = $conn->prepare("

                UPDATE seats s

                JOIN booking_seats bs
                ON s.id = bs.seat_id

                SET s.status = 'booked'

                WHERE bs.booking_id = ?

            ");

            $seatStmt->bind_param(
                "i",
                $booking_id
            );

            $seatStmt->execute();

            $seatStmt->close();
        }

        /* 如果是 Cancelled，要 release seats */
        if ($new_status === 'Cancelled') {

            $release = $conn->prepare("
                UPDATE seats s
                JOIN booking_seats bs ON s.id = bs.seat_id
                SET s.status = 'available'
                WHERE bs.booking_id = ?
            ");

            $release->bind_param("i", $booking_id);
            $release->execute();
            $release->close();
        }
        $stmt->close();

        header("Location: customer_bookings.php");
        exit();
    }
}

// Get search input
$search = $_GET['search'] ?? '';


// SQL query
$sql = "
    SELECT 
        b.id,
        b.payment_status,
        b.cancel_reason,
        b.cancelled_by,
        b.cancelled_at,
        b.booking_date,
        u.full_name,
        m.title,
        s.show_date,
        s.show_time,
        br.name AS branch_name

    FROM bookings b

    JOIN users u
    ON b.user_id = u.id

    JOIN showtimes s
    ON b.showtime_id = s.id

    JOIN movies m
    ON s.movie_id = m.id

    JOIN branches br
    ON s.branch_id = br.id
";


// Search by booking ID And Name
$params = [];
$types = '';

if (!empty($search)) {

    if (ctype_digit($search)) {

        $sql .= "
            WHERE b.id = ?
        ";

        $params[] = $search;
        $types .= "i";

    } else {

        $sql .= "
            WHERE u.full_name LIKE ?
        ";

        $params[] = "%{$search}%";
        $types .= "s";
    }
}


// Order latest booking first
$sql .= "
    ORDER BY b.booking_date DESC
";


// Execute query
$stmt = $conn->prepare($sql);

if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );
}

$stmt->execute();

$bookings = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Customer Bookings</title>

    <!-- Bootstrap CSS -->
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">
    <style>

        body{
            margin:0;
            font-family:'Segoe UI',sans-serif;
            background:#f6f7fb;
            min-height:100vh;
        }

        .container-box{
            margin-left:280px;
            padding:50px;
        }

        .page-header{
            margin-bottom:35px;
        }

        .page-header h1{
            font-size:40px;
            font-weight:800;
            color:#2f2f2f;

        }

        .page-header p{
            color:#777;
            margin:0;
        }

        .page-header-row{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:30px;
        }

        .mini-search{
            flex: 0 0 450px;
            max-width: 520px !important;
            transform: translateX(-40px);
        }

        .search-wrapper{
            display:flex;
            align-items:center;
            gap:12px;
            width:100%;
        }

        .mini-search .input-group{
            width:100%;
            display:flex;
            overflow:hidden;
            border-radius:14px;
            box-shadow:0 4px 15px rgba(0,0,0,.08);
        }

        .mini-search .form-control{
            flex:1;
            min-width:0;
            height:48px;
            border:none;
            box-shadow:none;
        }

        #bookingSearch:focus{
            box-shadow:none;
            border:none;
        }

        .btn-search:focus{
            box-shadow:none;
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

            transition:.2s;
        }

        .btn-reset:hover{
            background:#f8f9fa;
            color:#212529;
        }

        .search-bar input{

            max-width:350px;

            height:48px;

            border-radius:12px;
        }

        .search-wrapper{
            position:relative;
            transform:translateY(15px);
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
            box-shadow:0 10px 25px rgba(0,0,0,.12);

            z-index:9999;
            display:none;
            overflow:hidden;

            margin-top:6px;
        }

        .search-item{
            padding:12px 15px;
            cursor:pointer;
            transition:.2s;

            white-space: nowrap;        
            overflow: hidden;           
            text-overflow: ellipsis;
        }

        .search-item:hover{
            background:#f5f5f5;
        }

        .table-card{

            background:#fff;

            border-radius:22px;

            overflow:hidden;

            border:1px solid rgba(0,0,0,.05);

            box-shadow:
            0 10px 25px rgba(0,0,0,.08);
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

            vertical-align:middle;

            padding:18px;

            border-color:#eef1f5;
        }

        .table tbody tr:hover{

            background:#fafbfc;
        }

        .form-select-sm{

            border-radius:10px;

            font-size:14px;
        }

        .badge{
            padding:8px 14px;
            border-radius:20px;
        }

        .btn-details{

            background:#f5c518 !important;

            color:#111 !important;

            border:none !important;

            font-weight:700 !important;

            border-radius:10px !important;
        }

        .btn-details:hover{
            background:#ffd028 !important;
            transform:scale(1.05);
        }


        .btn-search{

            background:#212529;
            color:white;

            border:none;

            border-radius:12px;

            font-weight:700;
        }

        .btn-search:hover{
            background:#343a40 !important;

            color:white !important;
            transform:scale(1.05);
        }

        .btn-ticket{

            background:#212529 !important;

            color:#fff !important;

            border:none !important;

            border-radius:10px !important;
        }

        .btn-ticket:hover{
            background:#343a40 !important;

            color:white !important;
            transform:scale(1.05);
        }

        .btn-update{

            background:linear-gradient(
                135deg,
                #212529,
                #343a40
            );

            color:#fff;

            border:none;

            border-radius:10px;

            font-weight:700;

            height:38px;

            transition:.25s;
        }

        .btn-update:hover{

            background:linear-gradient(
                135deg,
                #343a40,
                #495057
            );

            transform:translateY(-2px);
        }

        .btn-update:disabled{

            background:#dee2e6;

            color:#868e96;

            cursor:not-allowed;

            transform:none;
        }

        .status-cell{
            min-width:240px;
        }

        .status-cell .form-select,
        .status-cell .form-control,
        .status-cell .btn{
            width:100%;
        }

        .action-cell{
            width:170px;
            min-width:170px;
        }

        .action-cell .btn{
            width:120px;
            text-align:center;
            font-weight:600;
            border-radius:10px;
        }

        .action-cell .btn + .btn{
            margin-top:8px;
        }

        

    </style>

</head>

<body class="staff-page customer-bookings-page">

<?php include '../includes/staff_sidebar.php'; ?>

    <div class="container-box">

        <div class="page-header-row">

            <div>

                <h1>Booking Management</h1>

                <p>
                    Monitor and manage customer bookings, payments and ticket issuance.
                </p>

            </div>


            <!-- Search form -->
            <form 
                method="GET" 
                class="mini-search"
            >

                <div class="search-wrapper">

                    <div class="search-box">
        
                        <div class="input-group">
                            
                            <input
                                type="text"
                                id="bookingSearch"
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
    
                    <!-- Reset button -->
                    <?php if ($search != ''): ?>

                        <a 
                            href="<?= BASE_URL ?>/staff/customer_bookings.php"
                            class="btn-reset"
                        >
                            Reset
                        </a>

                    <?php endif; ?>

                </div>

            </form>

        </div>



        <!-- Booking table -->
        <div class="table-card">
        <table class="table mb-0">

            <thead>

                <tr>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Movie</th>
                    <th>Branch</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody>

                <?php while ($b = $bookings->fetch_assoc()): ?>

                    <?php

                    // Status badge color
                    $statusClass = 'bg-warning text-dark';

                    if ($b['payment_status'] == 'Paid') {
                        $statusClass = 'bg-success';
                    }

                    if (
                        in_array(
                            $b['payment_status'],
                            ['Cancelled', 'Expired']
                        )
                    ) {
                        $statusClass = 'bg-danger';
                    }

                    ?>

                    <tr>

                        <td>
                            <span class="fw-bold text-dark">
                                #<?= $b['id'] ?>
                            </span>
                        </td>

                        <td>
                            <?= htmlspecialchars($b['full_name']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($b['title']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($b['branch_name']) ?>
                        </td>

                        <td>
                            <?= date('d M Y', strtotime($b['show_date'])) ?>
                        </td>

                        <td>
                            <?= date('h:i A', strtotime($b['show_time'])) ?>
                        </td>

                        <td class="status-cell">
                            <form method="POST" class="d-flex flex-column gap-2">

                                <input 
                                    type="hidden" 
                                    name="booking_id" 
                                    value="<?= $b['id'] ?>"
                                >

                                <select 
                                    name="payment_status"
                                    class="form-select form-select-sm"
                                    style="min-width:120px;"
                                >

                                <?php if($b['cancelled_by'] === 'admin'): ?>

                                    <option selected disabled>
                                        Cancelled by Admin
                                    </option>

                                <?php elseif($b['payment_status'] === 'Pending'): ?>

                                    <option value="Pending" selected>
                                        Pending
                                    </option>

                                    <option value="Paid">
                                        Paid
                                    </option>

                                    <option value="Cancelled">
                                        Cancelled
                                    </option>

                                <?php elseif($b['payment_status'] === 'Paid'): ?>

                                    <option value="Paid" selected>
                                        Paid
                                    </option>

                                <?php elseif($b['payment_status'] === 'Cancelled'): ?>

                                    <option value="Cancelled" selected>
                                        Cancelled
                                    </option>

                                <?php elseif($b['payment_status'] === 'Expired'): ?>

                                    <option value="Expired" selected>
                                        Expired
                                    </option>

                                <?php endif; ?>

                                </select>

                                <input
                                    type="text"
                                    name="cancel_reason"
                                    id="cancelReasonInput"
                                    class="form-control form-control-sm"
                                    placeholder="Cancel reason"
                                    style="display:none;"
                                />

                                <button 
                                    type="submit"
                                    name="update_status"
                                    class="btn btn-sm btn-update"

                                    <?= (
                                        $b['payment_status'] !== 'Pending'
                                        || $b['cancelled_by'] === 'admin'
                                    ) ? 'disabled' : '' ?>
                                >
                                    Update
                                </button>

                            </form>

                            <?php if ($b['payment_status'] === 'Cancelled'): ?>

                                <div class="small text-danger mt-1">
                                    <?= htmlspecialchars($b['cancel_reason'] ?? '') ?>
                                </div>

                            <?php endif; ?>

                        </td>

                        <td class="action-cell">

                            <a
                                href="<?= BASE_URL ?>/staff/booking_details.php?booking_id=<?= $b['id'] ?>"
                                class="btn btn-details"
                            >
                                Details
                            </a>

                            <?php if ($b['payment_status'] == 'Paid'): ?>

                                <a
                                    href="<?= BASE_URL ?>/staff/generate_ticket.php?booking_id=<?= $b['id'] ?>"
                                    class="btn btn-ticket"
                                >
                                    QR Ticket
                                </a>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endwhile; ?>

            </tbody>

        </table>
        </div>

    </div>

<script>

const searchInput = document.getElementById('bookingSearch');
const suggestions = document.getElementById('searchSuggestions');

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

        console.log(data);

        if(data.length === 0){

            suggestions.style.display = 'none';
            return;
        }

        suggestions.innerHTML = '';

        data.forEach(row => {

            const item = document.createElement('div');

            item.classList.add('search-item');

            if(isNaN(keyword)){

                item.textContent = row.name;

            }else{

                item.textContent =
                    '#' + row.id + ' - ' + row.name;

            }

            item.addEventListener('click', () => {

                if(isNaN(keyword)){

                    searchInput.value = row.name;

                }else{

                    searchInput.value = row.id;

                }

                suggestions.style.display = 'none';

            });

            suggestions.appendChild(item);

        });

        suggestions.style.display = 'block';

    })

    .catch(error => {

        console.error(error);

});
});

document.addEventListener('click',(e)=>{

    if(!e.target.closest('.search-wrapper')){
        suggestions.style.display='none';
    }

});


document.querySelectorAll('select[name="payment_status"]').forEach(select => {

    const form = select.closest('form');
    const reasonInput = form.querySelector('input[name="cancel_reason"]');

    // 初始化隐藏
    reasonInput.style.display = 'none';

    select.addEventListener('change', function () {

        if (this.value === 'Cancelled') {
            reasonInput.style.display = 'block';
        } else {
            reasonInput.style.display = 'none';
            reasonInput.value = '';
        }

    });

});

</script>

</body>
</html>