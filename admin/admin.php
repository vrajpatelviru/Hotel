<?php
session_start();
require_once __DIR__ . '/../core/database.php';

// allow admin
if ($_SESSION['role'] !== 'admin') {
  header("Location: ../auth/login.php");
  exit;
}

// TOTAL HOTELS

$totalHotelsQuery = mysqli_query($conn,
  "SELECT COUNT(*) AS total_hotels FROM hotel"
);
$totalHotels = mysqli_fetch_assoc($totalHotelsQuery)['total_hotels'] ?? 0;


// TOTAL ROOMS

$totalRoomsQuery = mysqli_query($conn, 
  "SELECT COUNT(*) AS total FROM room_units"
);
$totalRooms = mysqli_fetch_assoc($totalRoomsQuery)['total'] ?? 0;


// AVAILABLE ROOMS

$availableRoomsQuery = mysqli_query($conn,
  "SELECT COUNT(*) AS available FROM room_units WHERE status = 'available'"
);
$availableRooms = mysqli_fetch_assoc($availableRoomsQuery)['available'] ?? 0;


// TODAY BOOKINGS

$today = date("Y-m-d");
$todayBookingQuery = mysqli_query($conn, 
  "SELECT COUNT(*) AS today_book 
   FROM bookings
   WHERE DATE(check_in) = '$today'"
);
$todayBooking = mysqli_fetch_assoc($todayBookingQuery)['today_book'] ?? 0;


// UPCOMING CHECKOUTS
$checkoutQuery = mysqli_query($conn,
  "SELECT COUNT(*) AS upcoming 
   FROM bookings 
   WHERE DATE(check_out) = '$today'
   AND status = 'checked_in'"
);
$upcomingCheckouts = mysqli_fetch_assoc($checkoutQuery)['upcoming'] ?? 0;


// REFUND REQUESTS (pending)
$refundQuery = mysqli_query($conn,
  "SELECT COUNT(*) AS pending_refunds 
   FROM bookings 
   WHERE refund_request = 'pending'"
);
$pendingRefunds = mysqli_fetch_assoc($refundQuery)['pending_refunds'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Pannel</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="../assets/bootstrap/icon/bootstrap-icons.min.css">
  <!-- Bootstrap JS -->
  <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
  <!-- navbar -->
   <nav class="navbar navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
      <button class="btn btn-light" id="toggleSidebar">
        <i class="bi bi-list"></i>
      </button>
      
      <span class="navbar-brand ">Admin Panel</span>
      <a href="../auth/logout.php" class="btn btn-danger"><i class="bi bi-box-arrow-right"></i></a>
    </div>
   </nav>

  <!-- sidebar -->
   <div class="sidebar bg-dark text-white" id="sidebar">
    <nav class="nav flex-column p-3">
      <a class="nav-link text-white active" href="#"><i class="bi bi-house"></i> Dashboard</a>
      <a class="nav-link text-white" href="../admin/hotel.php"><i class="bi bi-building"></i> Hotels</a>
      <a class="nav-link text-white" href="../admin/rooms.php"><i class="bi bi-door-open"></i> Rooms</a>
      <a class="nav-link text-white" href="../admin/booking.php"><i class="bi bi-journal"></i> Bookings</a>
      <a class="nav-link text-white" href="../admin/checkin.php"><i class="bi bi-person-check"></i> Check In</a>
      <a class="nav-link text-white" href="../admin/checkout.php"><i class="bi bi-person-dash"></i> Check Out</a>
      <a class="nav-link text-white" href="../admin/payment.php"><i class="bi bi-credit-card"></i> Payments</a>
      <a class="nav-link text-white" href="../admin/reports.php"><i class="bi bi-bar-chart"></i> Reports</a>
      <a class="nav-link text-white" href="../admin/guest_search.php"><i class="bi bi-gear"></i> Search</a>
      <a class="nav-link text-white" href="../admin/refund_requests.php"><i class="bi bi-arrow-repeat"></i> Refund Requests</a>
    </nav>
   </div>

  <!-- main content -->
  <div class="content p-4" id="main-content">
    <!-- Dashboard section -->
    <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
      <h2>Dashboard</h2>
      <div class="d-flex align-items-center">
        <span class="me-2">Admin</span>
        <i class="bi bi-person-circle fs-4"></i>
      </div>
    </div>

    <!--STATUS CARDS (UPDATED)-->
    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="card text-center p-3">
          <h6>Total Hotels</h6>
          <h2><?= $totalHotels ?></h2>
          <small>Registered Hotels</small>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card text-center p-3">
          <h6>Total Rooms</h6>
          <h2><?= $totalRooms ?></h2>
          <small>All Room Units</small>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card text-center p-3">
          <h6>Available Rooms</h6>
          <h2><?= $availableRooms ?></h2>
          <small>Ready to Allocate</small>
        </div>
      </div>

      <div class="col-md-3">
        <a href="../admin/refund_requests.php" style="text-decoration:none; color:inherit;">
          <div class="card text-center p-3">
            <h6>Refund Requests</h6>
            <h2><?= $pendingRefunds ?></h2>
            <small>Click to review</small>
          </div>
        </a>
      </div>

    </div>

    <!-- second row (existing cards) -->
    <div class="row g-4 mb-4">
      <div class="col-md-6">
        <div class="card text-center p-3">
          <h6>Today's Booking</h6>
          <h2><?= $todayBooking ?></h2>
        </div>
      </div>

      <div class="col-md-6">
        <a href="../admin/checkout.php" style="text-decoration:none; color:inherit;">
        <div class="card text-center p-3">
          <h6>Upcoming Check-outs</h6>
          <h2><?= $upcomingCheckouts ?></h2>
        </div>
      </div>

    </div>

  

  <!-- table of booking -->
    <div class="card p-3 ">
      <h5 class="mb-3">Recent Booking</h5>
      <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>Guest</th>
              <th>Room no</th>
              <th>check-in</th>
              <th>check-out </th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $recent = mysqli_query($conn,
                "SELECT guest_name, room_number, check_in, check_out, status
                FROM bookings
                WHERE DATE(check_in) = '$today'
                ORDER BY check_in ASC"
              );

              while ($row = mysqli_fetch_assoc($recent)):
            ?>
            <tr>
              <td><?= $row['guest_name'] ?></td>
              <td><?= $row['room_number'] ?></td>
              <td><?= $row['check_in'] ?></td>
              <td><?= $row['check_out'] ?></td>

              <td>
                <?php if($row['status'] == 'checked_in'): ?>
                  <span class="badge bg-success">Checked-In</span>

                <?php elseif($row['status'] == 'booked'): ?>
                  <span class="badge bg-warning">Booked</span>

                <?php elseif($row['status'] == 'checked_out'): ?>
                  <span class="badge bg-primary">Checked-Out</span>

                <?php elseif($row['status'] == 'cancelled'): ?>
                  <span class="badge bg-danger">Cancelled</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <!-- js -->
  <script>
    const sidebar = document.getElementById('sidebar');
    const toggleButton = document.getElementById('toggleSidebar');

    toggleButton.addEventListener('click',()=>{
      sidebar.classList.toggle('active');
    });
  </script>

</body>
</html>
