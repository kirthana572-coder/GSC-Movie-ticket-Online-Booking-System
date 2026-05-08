<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$showtime_id = $_GET['showtime_id'] ?? 0;
if (!$showtime_id) {
    die("Invalid showtime.");
}

// 获取场次信息
$showtime = $conn->query("
    SELECT s.id, s.show_date, s.show_time, m.title, b.name AS branch_name
    FROM showtimes s
    JOIN movies m ON s.movie_id = m.id
    JOIN branches b ON s.branch_id = b.id
    WHERE s.id = " . intval($showtime_id)
)->fetch_assoc();

if (!$showtime) die("Showtime not found.");

// 获取该场次所有座位
$seats = $conn->query("SELECT * FROM seats WHERE showtime_id = " . intval($showtime_id) . " ORDER BY seat_number");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Select Seat - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{
            margin: 0;
            font-family: 'Segoe UI', sans-serif;

            background:
            linear-gradient(
            rgba(120, 114, 114, 0.66),
            rgba(20, 20, 20, 0.12)
            ),
            url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=1974&auto=format&fit=crop')
            center center / cover no-repeat fixed;

            min-height: 100vh;

            color: #fff;
        }

        .seat-container{
            max-width: 1000px;
            margin: auto;
            padding: 40px;
        }

        .booking-card{
            background: rgba(255,255,255,0.08);
            border-radius: 24px;
            padding: 35px;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow:
            0 10px 30px rgba(0,0,0,0.35);
        }

        .movie-title{
            font-size: 34px;
            font-weight: 700;
            color: #fbd342;
        }

        .movie-info{
            color: #ddd;
            margin-bottom: 30px;
        }

        /* ===== Screen ===== */
        .screen{
            width: 85%;
            height: 70px;

            margin: 0 auto 60px auto;

            background:
            linear-gradient(to bottom, #f8f8f8, #cfcfcf);

            border-radius: 14px 14px 50px 50px;

            text-align: center;

            line-height: 70px;

            color: #111;

            font-weight: 700;

            letter-spacing: 6px;

            box-shadow:
            0 10px 35px rgba(255,255,255,0.25);

            transform:
            perspective(500px)
            rotateX(-8deg);

           border: 2px solid rgba(255,255,255,0.4);
        }    

        /* ===== Seat Layout ===== */
        .seat-grid{
            display: grid;

            grid-template-columns:
            repeat(10, 1fr);
            gap: 15px;

            margin-bottom: 30px;

            justify-items: center;
        }

        .seat-btn{
            width: 58px;
            height: 58px;
            border: none;
            border-radius: 18px;
            font-weight: 700;
            transition: 0.25s;
        }

        .cinema-layout{
            display: flex;
            flex-wrap: wrap;

            justify-content: center;

            gap: 12px;

            max-width: 820px;

            margin: auto;
        }

        .aisle{
            width: 45px;
        }

        .row-break{
            flex-basis: 100%;
            height: 0;
        }

        /* Available */
        .available{
            background: #2ecc71;
            color: white;
        }

        .available:hover{
            transform: scale(1.08);

            box-shadow:
            0 8px 18px rgba(46,204,113,0.4);
        }

        /* Selected */
        .selected{
            background: #f5c518;
            color: #000;

            transform: scale(1.08);

            box-shadow:
            0 8px 18px rgba(245,197,24,0.5);
        }

        /* Booked */
        .booked,
        .pending{
            background: #e74c3c;
            color: white;

            cursor: not-allowed;

            opacity: 0.7;
        }

        /* ===== Legend ===== */
        .legend{
            display: flex;

            justify-content: center;

            gap: 20px;

            margin-top: 25px;
            margin-bottom: 25px;
        }

        .legend-item{
            display: flex;
            align-items: center;

            gap: 8px;
        }

        .legend-box{
            width: 22px;
            height: 22px;

            border-radius: 6px;
        }

        /* ===== Summary ===== */
        .summary-box{
            background: rgba(255,255,255,0.08);

            border-radius: 18px;

            padding: 14px;

            margin-top: 25px;
        }

        .selected-text{
            color: #edcc53;

            font-weight: 600;
        }

        .btn-warning{
            background: #fcd340 !important;

            border: none !important;

            color: #000 !important;

            font-weight: 700 !important;

            border-radius: 30px !important;

            padding: 16px 20px !important;

            transition: 0.3s !important;
        }

        .btn-warning:hover{
            background: #ffd84c;

            transform: scale(1.03);
        }

        .summary-box{
            text-align: center;
        }

        .booking-card{
            animation: fadeUp 0.6s ease;
        }

        @keyframes fadeUp{
            from{
                opacity: 0;
                transform: translateY(20px);
            }

            to{
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="seat-container">
    <div class="booking-card">

    <h2 class="movie-title">
        <?= htmlspecialchars($showtime['title']) ?>
    </h2>

    <div class="movie-info">
    📍 <?= htmlspecialchars($showtime['branch_name']) ?>

        <br>

        📅 <?= date('d M Y', strtotime($showtime['show_date'])) ?>

        &nbsp; | &nbsp;

        ⏰ <?= date('h:i A', strtotime($showtime['show_time'])) ?>
    </div>

    <form method="POST" action="booking.php">

    <div class="screen">
        SCREEN
    </div>

        <input type="hidden" name="showtime_id" value="<?= $showtime['id'] ?>">
            <div class="cinema-layout">

                <?php
                    $rowCounter = 0;
                ?>
                
                <?php while($seat = $seats->fetch_assoc()): 

                $status = $seat['status'];
                $disabled = ($status !== 'available') ? 'disabled' : '';
                $btnClass = 'available';

                if ($status === 'booked' || $status === 'pending'){
                    $btnClass = 'booked';
                }

                 // 每 5 个 seat 后加走道
                if($rowCounter % 10 == 5){
                    echo '<div class="aisle"></div>';
                }
                ?>

                <button type="button" 
                        class="seat-btn <?= $btnClass ?>"
                        data-seat-id="<?= $seat['id'] ?>"
                        data-seat-number="<?= $seat['seat_number'] ?>"
                        onclick="toggleSeat(this)"
                        <?= $disabled ?>>

                    <?= $seat['seat_number'] ?>
                </button>

                <?php
                $rowCounter++;

                if($rowCounter % 10 == 0){
                    echo '<div class="row-break"></div>';
                }
                ?>

            <?php endwhile; ?>
        </div>

        <div class="legend">

            <div class="legend-item">
                <div class="legend-box available"></div>
                Available
            </div>

            <div class="legend-item">
                <div class="legend-box selected"></div>
                Selected
            </div>

            <div class="legend-item">
                <div class="legend-box booked"></div>
                Booked
            </div>

        </div>
        
        <!-- 隐藏字段存储选中的座位ID -->
        <input type="hidden" name="seat_ids" id="seatIdsInput" value="">
        
        <div class="summary-box">
            Selected Seats:
            <span class="selected-text" id="selectedSeatsDisplay">
                None
            </span>
        </div>        
        
        <button type="submit" class="btn btn-warning btn-lg w-100 mt-4">
            Make Booking
        </button>    
    </form>
</div>


<script>
let selectedSeats = [];

function toggleSeat(btn) {
    const seatId = btn.dataset.seatId;
    const seatNum = btn.dataset.seatNumber;
    
    if (btn.classList.contains('selected')) {
        // 取消选择
        btn.classList.remove('selected');
        btn.classList.add('available');
        selectedSeats = selectedSeats.filter(s => s.id !== seatId);
    } else {
        // 选择
        btn.classList.remove('available');
        btn.classList.add('selected');
        selectedSeats.push({id: seatId, number: seatNum});
    }
    
    updateDisplay();
}

function updateDisplay() {
    document.getElementById('selectedSeatsDisplay').textContent = 
        selectedSeats.map(s => s.number).join(', ');
    document.getElementById('seatIdsInput').value = 
        selectedSeats.map(s => s.id).join(',');
}
</script>

</body>
</html> 