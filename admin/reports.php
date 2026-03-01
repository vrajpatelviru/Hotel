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
  <title>Reports | Admin</title>
  <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <h2 class="mb-4 text-center">📊 Reports Dashboard</h2>

    <!-- Filter Form -->
    <form method="get" class="card p-4 shadow-sm mb-4">
      <div class="row g-3">

        <!-- Report Type -->
        <div class="col-md-3">
          <label class="form-label fw-bold">Report Type</label>
          <select name="report_type" id="report_type" class="form-select" required>
            <option value="">Select</option>
            <option value="monthly" <?= ($_GET['report_type'] ?? '') == 'monthly' ? 'selected' : '' ?>>Monthly</option>
            <option value="yearly" <?= ($_GET['report_type'] ?? '') == 'yearly' ? 'selected' : '' ?>>Yearly</option>
            <option value="custom" <?= ($_GET['report_type'] ?? '') == 'custom' ? 'selected' : '' ?>>Custom</option>
          </select>
        </div>

        <!-- Hotel Filter -->
        <div class="col-md-3">
          <label class="form-label fw-bold">Select Hotel</label>
          <select name="hotel_id" class="form-select">
            <option value="">All Hotels</option>
            <?php
            $hotels = mysqli_query($conn, "SELECT * FROM hotel ORDER BY name");
            while ($h = mysqli_fetch_assoc($hotels)) {
              $sel = ($_GET['hotel_id'] ?? '') == $h['hotel_id'] ? 'selected' : '';
              echo "<option value='{$h['hotel_id']}' $sel>{$h['name']} ({$h['location']})</option>";
            }
            ?>
          </select>
        </div>

        <!-- Month -->
        <div class="col-md-3" id="monthDiv" style="display:none;">
          <label class="form-label fw-bold">Select Month</label>
          <input type="month" name="month" class="form-control" value="<?= $_GET['month'] ?? '' ?>">
        </div>

        <!-- Year -->
        <div class="col-md-3" id="yearDiv" style="display:none;">
          <label class="form-label fw-bold">Select Year</label>
          <input type="number" name="year" class="form-control" min="2000" max="2100" value="<?= $_GET['year'] ?? '' ?>">
        </div>

        <!-- From Date -->
        <div class="col-md-3" id="fromDiv" style="display:none;">
          <label class="form-label fw-bold">From Date</label>
          <input type="date" name="from_date" class="form-control" value="<?= $_GET['from_date'] ?? '' ?>">
        </div>

        <!-- To Date -->
        <div class="col-md-3" id="toDiv" style="display:none;">
          <label class="form-label fw-bold">To Date</label>
          <input type="date" name="to_date" class="form-control" value="<?= $_GET['to_date'] ?? '' ?>">
        </div>

        <div class="col-md-3 align-self-end">
          <button type="submit" class="btn btn-primary w-100">Generate</button>
        </div>

        <div class="col-md-2 align-self-end">
          <a href="../admin/admin.php" class="btn btn-danger w-100">Back</a>
        </div>

      </div>
    </form>

    <!-- Results -->
    <?php
    if (isset($_GET['report_type'])) {

      $type = $_GET['report_type'];
      $hotel_id = $_GET['hotel_id'] ?? '';
      $where = [];

      // ===========================
      // REPORT FILTERS
      // ===========================

      if ($type == 'monthly' && !empty($_GET['month'])) {
        $month = $_GET['month'];
        $where[] = "DATE_FORMAT(check_in, '%Y-%m') = '$month'";
        $title = "Monthly Report for " . date('F Y', strtotime($month));

      } elseif ($type == 'yearly' && !empty($_GET['year'])) {
        $year = intval($_GET['year']);
        $where[] = "YEAR(check_in) = $year";
        $title = "Yearly Report for $year";

      } elseif ($type == 'custom' && !empty($_GET['from_date']) && !empty($_GET['to_date'])) {
        $from = $_GET['from_date'];
        $to = $_GET['to_date'];
        $where[] = "check_in BETWEEN '$from' AND '$to'";
        $title = "Custom Report from $from to $to";

      } else {
        $title = "All Bookings Report";
      }

      // HOTEL FILTER
      if ($hotel_id != '') {
        $where[] = "hotel_id = $hotel_id";
        $hotelname = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM hotel WHERE hotel_id=$hotel_id"))['name'] ?? '';
        $title .= " — Hotel: $hotelname";
      }

      // BUILD QUERY
      $whereSQL = !empty($where) ? "WHERE " . implode(' AND ', $where) : '';
      $query = "SELECT * FROM bookings $whereSQL ORDER BY check_in ASC";

      $result = mysqli_query($conn, $query);
      echo "<h4 class='mt-4 text-center'>$title</h4>";

      if ($result && mysqli_num_rows($result) > 0) {

        echo "<table class='table table-bordered table-striped mt-3'>
                <thead class='table-dark'>
                  <tr>
                    <th>Booking ID</th>
                    <th>Guest</th>
                    <th>Hotel</th>
                    <th>Room</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Status</th>
                    <th>Payment Type</th>
                    <th>Payment (₹)</th>
                    <th>Refund</th>
                    <th>Revenue (₹)</th>
                  </tr>
                </thead>
                <tbody>";

        $totalRevenue = 0;

        while ($row = mysqli_fetch_assoc($result)) {

          $hotel = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM hotel WHERE hotel_id={$row['hotel_id']}"))['name'] ?? '';
          $room = mysqli_fetch_assoc(mysqli_query($conn, "SELECT type_name FROM rooms WHERE room_id={$row['room_id']}"))['type_name'] ?? '';

          // GET REFUND AMOUNT
          $refund = mysqli_fetch_assoc(mysqli_query(
            $conn, "SELECT refund_amount FROM refunds WHERE booking_id={$row['booking_id']}"
          ));

          $refundAmount = $refund['refund_amount'] ?? 0;
          $paymentAmount = $row['total_amount'];

          // CALCULATE FINAL REVENUE
          $revenue = $paymentAmount;

          if ($row['refund_request'] == 'approved') {
            $revenue = $paymentAmount - $refundAmount;
          }

          $totalRevenue += $revenue;

          echo "<tr>
                  <td>{$row['booking_id']}</td>
                  <td>{$row['guest_name']}</td>
                  <td>$hotel</td>
                  <td>$room</td>
                  <td>{$row['check_in']}</td>
                  <td>{$row['check_out']}</td>
                  <td>{$row['status']}</td>
                  <td>{$row['payment_type']}</td>

                  <td>₹$paymentAmount</td>

                  <td>";

          if ($row['refund_request'] == 'approved') {
            echo "<span class='badge bg-success'>₹$refundAmount</span>";

          } elseif ($row['refund_request'] == 'pending') {
            echo "<span class='badge bg-warning text-dark'>Pending</span>";

          } elseif ($row['refund_request'] == 'rejected') {
            echo "<span class='badge bg-secondary'>Rejected</span>";

          } else {
            echo "<span class='text-muted'>None</span>";
          }

          echo "</td>

                <td>₹$revenue</td>

              </tr>";
        }

        echo "</tbody></table>";
        echo "<h5 class='text-end fw-bold mt-3'>Total Revenue: ₹$totalRevenue</h5>";

      } else {
        echo "<div class='alert alert-warning mt-3'>No bookings found.</div>";
      }
    }
    ?>
  </div>

  <script>
    const typeSelect = document.getElementById('report_type');
    const monthDiv = document.getElementById('monthDiv');
    const yearDiv = document.getElementById('yearDiv');
    const fromDiv = document.getElementById('fromDiv');
    const toDiv = document.getElementById('toDiv');

    function toggleFields() {
      monthDiv.style.display = yearDiv.style.display = fromDiv.style.display = toDiv.style.display = 'none';

      if (typeSelect.value === 'monthly') monthDiv.style.display = 'block';
      if (typeSelect.value === 'yearly') yearDiv.style.display = 'block';
      if (typeSelect.value === 'custom') {
        fromDiv.style.display = 'block';
        toDiv.style.display = 'block';
      }
    }

    toggleFields();
    typeSelect.addEventListener('change', toggleFields);
  </script>

</body>
</html>
