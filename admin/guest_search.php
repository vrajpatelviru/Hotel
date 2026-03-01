<?php
session_start();
require_once '../core/database.php';

if ($_SESSION['role'] !== 'admin') {
  header("Location: ../auth/login.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Find Guest | Admin</title>
  <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h2 class="mb-4 text-center">👤 Find Guest</h2>

  <!-- Search Form -->
  <form method="get" class="card p-4 shadow-sm mb-4">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-bold">Search by Name / Phone / ID Proof</label>
        <input type="text" name="search" class="form-control" placeholder="Enter guest name, phone or ID proof" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" required>
      </div>
      <div class="col-md-2 align-self-end">
        <button class="btn btn-primary w-100">Search</button>
      </div>
      <div class="col-md-2 align-self-end">
        <a href="../admin/admin.php" class="btn btn-danger w-100">Back</a>
      </div>
    </div>
  </form>

  <?php
  if (!empty($_GET['search'])) {
      $search = mysqli_real_escape_string($conn, $_GET['search']);

      // Search guests by name / phone / ID
      $query = "
        SELECT DISTINCT guest_name, guest_phone, guest_idproof, user_id
        FROM bookings
        WHERE guest_name LIKE '%$search%' 
           OR guest_phone LIKE '%$search%' 
           OR guest_idproof LIKE '%$search%'
      ";

      $guest_result = mysqli_query($conn, $query);

      if ($guest_result && mysqli_num_rows($guest_result) > 0) {
          while ($guest = mysqli_fetch_assoc($guest_result)) {
              echo "<div class='card shadow-sm mb-4 '>
                      <div class='card-header bg-dark text-white'>
                        <h5 class='mb-0'>Guest: {$guest['guest_name']}</h5>
                      </div>
                      <div class='card-body'>
                        <div class='row mb-3'>
                          <div class='col-md-4'><strong>📞 Phone:</strong> {$guest['guest_phone']}</div>
                          <div class='col-md-4'><strong>🪪 ID Proof:</strong> {$guest['guest_idproof']}</div>
                          <div class='col-md-4'><strong>User ID:</strong> " . ($guest['user_id'] ?? 'N/A') . "</div>
                        </div>";

              $guest_name = mysqli_real_escape_string($conn, $guest['guest_name']);

              // Booking history with room number
              $bookings = mysqli_query($conn, "
                SELECT b.*, h.name AS hotel_name, r.type_name
                FROM bookings b
                JOIN hotel h ON b.hotel_id = h.hotel_id
                JOIN rooms r ON b.room_id = r.room_id
                WHERE b.guest_name = '$guest_name'
                ORDER BY b.check_in DESC
              ");

              if ($bookings && mysqli_num_rows($bookings) > 0) {
                  echo "<h6 class='fw-bold mb-2'>🏨 Booking History</h6>
                        <div class='table-responsive'>

                        <table class='table table-bordered table-striped'>
                          <thead class='table-dark'>
                            <tr>
                              <th>Booking ID</th>
                              <th>Hotel</th>
                              <th>Room Type</th>
                              <th>Room No.</th>
                              <th>Check-In</th>
                              <th>Check-Out</th>
                              <th>Status</th>
                              <th>Payment Type</th>
                              <th>Total (₹)</th>
                            </tr>
                          </thead><tbody>";

                  while ($b = mysqli_fetch_assoc($bookings)) {
                      echo "<tr>
                              <td>{$b['booking_id']}</td>
                              <td>{$b['hotel_name']}</td>
                              <td>{$b['type_name']}</td>
                              <td>{$b['room_number']}</td>
                              <td>{$b['check_in']}</td>
                              <td>{$b['check_out']}</td>
                              <td>{$b['status']}</td>
                              <td>{$b['payment_type']}</td>
                              <td>₹{$b['total_amount']}</td>
                            </tr>";
                  }
                  echo "</tbody></table></div>";
              } else {
                  echo "<div class='alert alert-warning'>No bookings found for this guest.</div>";
              }
              echo "<hr>";
              // Billing / payment history with room number
              $billing = mysqli_query($conn, "
                SELECT p.*, h.name AS hotel_name
                FROM billing p
                JOIN bookings b ON p.booking_id = b.booking_id
                JOIN hotel h ON b.hotel_id = h.hotel_id
                WHERE p.guest_name = '$guest_name'
                ORDER BY p.bill_date DESC
              ");

              if ($billing && mysqli_num_rows($billing) > 0) {
                  echo "<h6 class='fw-bold mt-3 mb-2'>💳 Billing History</h6>
                        <div class='table-responsive'>
                        <table class='table table-bordered table-striped'>
                          <thead class='table-dark'>
                            <tr>
                              <th>Bill ID</th>
                              <th>Hotel</th>
                              <th>Room No.</th>
                              <th>Amount (₹)</th>
                              <th>Payment Type</th>
                              <th>Status</th>
                              <th>Bill Date</th>
                            </tr>
                          </thead><tbody>";

                  while ($p = mysqli_fetch_assoc($billing)) {
                      $color = match($p['payment_status']) {
                          'paid' => 'success',
                          'pending' => 'warning',
                          'unpaid' => 'danger',
                          default => 'secondary',
                      };
                      echo "<tr>
                              <td>{$p['bill_id']}</td>
                              <td>{$p['hotel_name']}</td>
                              <td>{$p['room_number']}</td>
                              <td>₹{$p['total_amount']}</td>
                              <td>{$p['payment_type']}</td>
                              <td><span class='badge bg-$color text-uppercase'>{$p['payment_status']}</span></td>
                              <td>{$p['bill_date']}</td>
                            </tr>";
                  }
                  echo "</tbody></table></div>";
              } else {
                  echo "<div class='alert alert-info'>No billing records found for this guest.</div>";
              }

              echo "</div></div>";
          }
      } else {
          echo "<div class='alert alert-danger text-center mt-3'>
                  ❌ No guest found matching “<b>$search</b>”.
                </div>";
      }
  }
  ?>
</div>
</body>
</html>
