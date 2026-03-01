<?php
session_start();
require_once '../core/database.php';

if($_SESSION['role'] !== 'admin'){
  header("Location: ../auth/login.php");
  exit;
}

$q = mysqli_query($conn,
 "SELECT * FROM bookings WHERE refund_request = 'pending'"
);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Refund</title>
  <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
</head>
<body class="p-4">

<!-- BOOTSTRAP ALERT HERE -->
<?php if(isset($_GET['msg'])): ?>
<div class="alert alert-<?= $_GET['type']; ?> alert-dismissible fade show" role="alert">
  <?= htmlspecialchars($_GET['msg']); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card p-4 shadow">
<h3>Pending Refund Requests</h3>

<table class="table table-bordered">
<thead>
<tr>
  <th>Booking ID</th>
  <th>Guest</th>
  <th>Room</th>
  <th>Amount</th>
  <th>Action</th>
</tr>
</thead>
<tbody>
<?php while($b = mysqli_fetch_assoc($q)): ?>
<tr>
  <td><?= $b['booking_id'] ?></td>
  <td><?= $b['guest_name'] ?></td>
  <td><?= $b['room_number'] ?></td>
  <td><?= $b['total_amount'] ?></td>
  <td>
    <a href="approve_refund.php?id=<?= $b['booking_id'] ?>" class="btn btn-success btn-sm">Approve</a>
    <a href="reject_refund.php?id=<?= $b['booking_id'] ?>" class="btn btn-danger btn-sm">Reject</a>
  </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<a href="../admin/admin.php" class="btn btn-danger">Back</a>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
