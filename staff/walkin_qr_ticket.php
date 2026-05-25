<?php
require_once '../includes/staff_auth.php';
require_once '../config/db.php';
$booking_id = $_GET['booking_id'] ?? 0;
if(!$booking_id) die("Booking ID required.");
$booking = $conn->query("SELECT wb.id, wb.booking_code, wb.customer_name, wb.payment_status, wb.qr_used, wb.adult_qty, wb.senior_qty, wb.student_qty, wb.children_qty, m.title, s.show_date, s.show_time, br.name AS branch_name, GROUP_CONCAT(CONCAT((SELECT seat_number FROM seats WHERE id = wbs.seat_id), ' (', COALESCE(wbs.ticket_type,'Unknown'), ')') SEPARATOR ', ') AS seats FROM walkin_bookings wb LEFT JOIN walkin_booking_seats wbs ON wb.id = wbs.walkin_booking_id LEFT JOIN seats se ON wbs.seat_id = se.id JOIN showtimes s ON wb.showtime_id = s.id JOIN movies m ON s.movie_id = m.id JOIN branches br ON s.branch_id = br.id WHERE wb.id = " . intval($booking_id) . " AND wb.payment_status = 'Paid' GROUP BY wb.id")->fetch_assoc();
if(!$booking) die("Ticket not available.");
$showDatetime = strtotime($booking['show_date'] . ' ' . $booking['show_time']);
$expiryTime = $showDatetime + (60 * 60);
$remaining = max(0, $expiryTime - time());
$isExpired = $remaining <= 0;
$qr_data = "WALKIN:" . $booking['booking_code'];
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qr_data);
?>
<!DOCTYPE html>
<html>
<head><title>GSC Ticket</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><style>
body{ background:#f5f5f5; font-family:'Segoe UI',sans-serif; }
.ticket-container{ max-width:700px; margin:40px auto; }
.ticket-card{ background:white; border-radius:25px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.15); }
.ticket-header{ background:#f5c518; padding:25px; text-align:center; }
.ticket-header h1{ margin:0; font-size:40px; font-weight:800; }
.ticket-body{ padding:35px; }
.info-row{ display:flex; justify-content:space-between; align-items:flex-start; gap:20px; border-bottom:1px solid rgba(0,0,0,0.08); padding:14px 0; }
.label{ color:#666; }
.value{ font-weight:700; color:#111; text-align:right; max-width:60%; word-break:break-word; }
.qr-box{ text-align:center; margin-top:35px; }
.qr-box img{ width:230px; }
.ticket-id{ margin-top:15px; font-size:18px; font-weight:700; color:#444; }
.btn-print{ background:#f5c518; border:none; color:#111; font-weight:700; border-radius:30px; padding:14px 40px; transition:0.25s; }
.btn-print:hover{ transform:scale(1.03); background:#ffd53d; }
.btn-back{ background:#333; border:none; color:white; font-weight:700; border-radius:30px; padding:14px 40px; text-decoration:none; }
.btn-back:hover{ transform:scale(1.03); background:#ffd53d; }
@media print{ .no-print{ display:none; } body{ background:white; } .ticket-card{ box-shadow:none; } }
.ticket-status{ text-align:center; padding:16px; border-radius:18px; font-size:24px; font-weight:800; margin-bottom:25px; }
.valid{ background:linear-gradient(135deg,#22b156,#31d56d); color:white; box-shadow:0 10px 25px rgba(34,197,94,0.3); }
.used{ background:linear-gradient(135deg,#e34545,#ef4444); color:white; box-shadow:0 10px 25px rgba(239,68,68,0.3); }
.expiry-box{ background:#fff3cd; color:#9a6a00; padding:10px 18px; border-radius:14px; font-weight:700; margin:0 auto 20px; width:fit-content; }
</style></head>
<body><div class="ticket-container"><div class="ticket-card"><div class="ticket-header"><h1>🎟️ GSC E-Ticket</h1></div><div class="ticket-body">
<?php if($booking['qr_used']==1): ?><div class="ticket-status used">❌ TICKET USED</div><?php elseif($isExpired): ?><div class="ticket-status used">⌛ QR CODE EXPIRED</div><?php else: ?><div class="ticket-status valid">✅ VALID TICKET</div><?php endif; ?>
<div class="info-row"><span class="label">Customer</span><span class="value"><?= htmlspecialchars($booking['customer_name']) ?></span></div>
<div class="info-row"><span class="label">Movie</span><span class="value"><?= htmlspecialchars($booking['title']) ?></span></div>
<div class="info-row"><span class="label">Cinema</span><span class="value"><?= htmlspecialchars($booking['branch_name']) ?></span></div>
<div class="info-row"><span class="label">Date</span><span class="value"><?= date('d M Y', strtotime($booking['show_date'])) ?></span></div>
<div class="info-row"><span class="label">Time</span><span class="value"><?= date('h:i A', strtotime($booking['show_time'])) ?></span></div>
<div class="info-row"><span class="label">Seats</span><span class="value"><?= htmlspecialchars($booking['seats']) ?></span></div>
<?php $hours = floor($remaining/3600); $minutes = floor(($remaining%3600)/60); $seconds = $remaining%60; ?>
<div style="display:block; width:100%; text-align:center; margin-top:50px;"><?php if(!$isExpired && $booking['qr_used']==0): ?><div class="expiry-box">⏳ QR expires in: <span id="countdown"><?= $hours ?>h <?= $minutes ?>m <?= $seconds ?>s</span></div><?php endif; ?><img src="<?= $qr_url ?>" style="width:230px; display:block; margin:0 auto;"><div class="ticket-id">Ticket #<?= htmlspecialchars($booking['booking_code']) ?></div></div>
<div class="text-center mt-5 no-print"><button onclick="window.print()" class="btn btn-print">🖨️ Print Ticket</button><a href="<?= BASE_URL ?>/staff/walkin_bookings.php" class="btn btn-back ms-2">Back</a></div>
</div></div></div>
<script>let remaining = <?= max($remaining,0) ?>; const countdownEl = document.getElementById('countdown'); function updateCountdown(){ if(!countdownEl) return; if(remaining<=0){ countdownEl.innerHTML="Expired"; return; } let hours=Math.floor(remaining/3600); let minutes=Math.floor((remaining%3600)/60); let seconds=remaining%60; countdownEl.innerHTML=hours+"h "+minutes+"m "+seconds+"s"; remaining--; } updateCountdown(); setInterval(updateCountdown,1000);</script>
</body></html>