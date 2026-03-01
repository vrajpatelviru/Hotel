<?php
require_once '../core/database.php';

// When check-in button is clicked
if (isset($_POST['checkin'])) {
    $booking_id = $_POST['booking_id'];
    mysqli_query($conn, "UPDATE bookings SET status='checked_in' WHERE booking_id='$booking_id'");
    echo "<div class='alert alert-success m-4'>Guest Checked In Successfully!</div>";
}

// Show list of booked guests ready for check-in
$bookings = mysqli_query($conn, "
    SELECT b.booking_id, b.guest_name, b.check_in, b.check_out, r.room_number
    FROM bookings b
    JOIN room_units r ON b.unit_id = r.unit_id
    WHERE b.status='booked'
    ORDER BY check_in ASC
");
?>

<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <title>Guest Check-In</title>
  <link rel='stylesheet' href='../assets/bootstrap/css/bootstrap.min.css'>
  <style>
  .card{
    
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  }
  </style>
</head>
<body class='p-4'>
<div class='container'>
  <h2>Guest Check-In</h2>
  <div class="card p-3">
  <table class='table table-bordered mt-3'>
    <thead class='table-light'>
      <tr>
        <th>Booking ID</th>
        <th>Guest Name</th>
        <th>Room No</th>
        <th>Check-In Date</th>
        <th>Check-Out Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = mysqli_fetch_assoc($bookings)): ?>
        <tr>
          <td><?= $row['booking_id'] ?></td>
          <td><?= $row['guest_name'] ?></td>
          <td><?= $row['room_number'] ?></td>
          <td><?= $row['check_in'] ?></td>
          <td><?= $row['check_out'] ?></td>
          <td>
            <form method="post">
              <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">
              <button type="submit" name="checkin" class="btn btn-success btn-sm">
                Check In
              </button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <form action="" method="post">
    <a href="../admin/admin.php" class="btn btn-lm btn-danger ">Back</a>
  </form>
  </div>
</div>
</body>
</html>
