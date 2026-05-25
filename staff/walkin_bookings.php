<?php
require_once '../includes/staff_auth.php';
require_once '../config/db.php';
if ($_SESSION['role'] !== 'staff') { die("Access denied."); }
if(isset($_GET['delete'])){ $id = $_GET['delete']; $stmt = $conn->prepare("DELETE FROM walkin_bookings WHERE booking_code = ?"); $stmt->bind_param("s", $id); if($stmt->execute()){ echo "<script>alert('Booking deleted successfully!'); window.location.href='".BASE_URL."/staff/walkin_bookings.php';</script>"; exit(); } $stmt->close(); }
$search = $_GET['search'] ?? '';
$sql = "SELECT wb.*, m.title AS movie_title, s.show_date, s.show_time FROM walkin_bookings wb JOIN showtimes s ON wb.showtime_id = s.id JOIN movies m ON s.movie_id = m.id";
if($search != ''){ $search = $conn->real_escape_string($search); $sql .= " WHERE wb.booking_code LIKE '%$search%'"; }
$sql .= " ORDER BY wb.id DESC";
$walkinBookings = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head><title>Walk-in Booking - GSC</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><style>
body{ margin:0; font-family:'Segoe UI',sans-serif; background:linear-gradient(rgba(245,242,234,0.92),rgba(255,220,164,0.92)); min-height:100vh; }
.page-container{ padding:40px; }
.top-bar{ display:flex; justify-content:space-between; margin-bottom:35px; position:relative; }
.top-bar .page-title{ position:absolute; left:50%; top:30px; transform:translateX(-50%); font-size:55px; font-weight:700; color:#f5c518; margin:0; }
.back-btn{ text-decoration:none; background:#2f2f2f; color:white; padding:10px 20px; border-radius:12px; font-weight:600; transition:0.25s; display:inline-block; width:auto; }
.back-btn:hover{ background:#f5c518; color:#111; }
.add-btn{ text-decoration:none; background:#ffd332; color:#111; padding:10px 22px; border-radius:12px; font-weight:700; transition:0.25s; }
.add-btn:hover{ background:#ffdc5f; color:#111; }
.search-bar{ display:flex; justify-content:center; align-items:center; gap:12px; margin-top:80px; margin-bottom:50px; }
.search-bar input{ max-width:350px; height:46px; }
.table-card{ background:rgba(255,255,255,0.8); border-radius:24px; padding:25px; box-shadow:0 10px 30px rgba(0,0,0,0.12); }
.table{ margin-bottom:0; }
.table thead th{ background:#ffd53b !important; color:#111; border:none; padding:16px; }
.table tbody td{ padding:16px; vertical-align:middle; background:rgba(255,255,255,0.65); border-color:rgba(0,0,0,0.06); }
.table tbody tr:hover td{ background:rgba(245,197,24,0.12); }
.badge{ padding:8px 14px; border-radius:20px; font-size:14px; }
.action-group{ display:flex; gap:8px; flex-wrap:wrap; }
.btn-action{ text-decoration:none; padding:7px 14px; border-radius:8px; font-size:14px; font-weight:600; transition:0.25s; }
.btn-view{ background:#ffe082; color:#111; }
.btn-edit{ background:#fff3cd; color:#111; }
.btn-delete{ background:#f8d7da; color:#842029; }
.btn-view:hover,.btn-edit:hover,.btn-delete:hover{ transform:scale(1.04); }
.btn-qr{ background:#8fe388 !important; color:#111 !important; }
.btn-qr:hover{ background:#39c933 !important; color:white !important; transform:scale(1.04) !important; }
</style></head>
<body><div class="page-container"><div class="top-bar"><a href="<?= BASE_URL ?>/staff/staff_dashboard.php" class="back-btn">← Back Dashboard</a><h1 class="page-title">Walk-in Bookings</h1><a href="<?= BASE_URL ?>/staff/add_walkin_booking.php" class="add-btn">+ Add Booking</a></div>
<form method="GET" class="search-bar"><input type="text" name="search" class="form-control" placeholder="Search Booking ID" value="<?= htmlspecialchars($search) ?>"><button type="submit" class="btn btn-warning fw-bold px-4">Search</button><?php if($search != ''): ?><a href="<?= BASE_URL ?>/staff/walkin_bookings.php" class="btn btn-dark">Reset</a><?php endif; ?></form>
<div class="table-card"><table class="table table-bordered align-middle"><thead><tr><th>No</th><th>Booking ID</th><th>Customer</th><th>Date</th><th>Status</th><th width="280">Action</th></tr></thead><tbody>
<?php $no=1; while($booking = $walkinBookings->fetch_assoc()): $statusClass = 'bg-warning text-dark'; if($booking['payment_status']=='Paid') $statusClass='bg-success'; if($booking['payment_status']=='Cancelled') $statusClass='bg-danger'; ?>
<tr><td><?= $no++ ?></td><td><?= $booking['booking_code'] ?></td><td><div><strong><?= $booking['customer_name'] ?></strong><br><span class="text-muted"><?= $booking['movie_title'] ?></span><br><span class="text-muted"><?= date('d M Y', strtotime($booking['show_date'])) ?> <?= date('h:i A', strtotime($booking['show_time'])) ?></span></div></td><td><?= date('d M Y', strtotime($booking['show_date'])) ?></td><td><span class="badge <?= $statusClass ?>"><?= $booking['payment_status'] ?></span></td>
<td><div class="action-group"><a href="<?= BASE_URL ?>/staff/view_walkin_booking.php?id=<?= $booking['id'] ?>" class="btn-action btn-view">View</a><a href="<?= BASE_URL ?>/staff/edit_walkin_booking.php?id=<?= $booking['id'] ?>" class="btn-action btn-edit">Edit</a><?php if($booking['payment_status'] == 'Paid'): ?><a href="<?= BASE_URL ?>/staff/walkin_qr_ticket.php?booking_id=<?= $booking['id'] ?>" class="btn-action btn-qr">View QR</a><?php endif; ?><a href="<?= BASE_URL ?>/staff/walkin_bookings.php?delete=<?= $booking['booking_code'] ?>" class="btn-action btn-delete" onclick="return confirm('Delete this booking?')">Delete</a></div></td></tr>
<?php endwhile; ?>
</tbody></table></div></div></body></html>