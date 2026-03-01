<?php
session_start();
require_once __DIR__ . '/../core/database.php';

//login
if(!isset($_SESSION['user_id'])){
  header("Location: ../auth/login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// ===========================
// USER INFO
// ===========================

$userquery = "select * from user where user_id=$user_id";
$userresult = mysqli_query($conn, $userquery);
$user = mysqli_fetch_assoc($userresult);

// ===========================
// BOOKING QUERY (INCLUDING refund_request)
// ===========================

$bookingQuery = "select b.*, h.name as hotel_name, r.type_name
                 from bookings b, hotel h, rooms r
                 where b.hotel_id = h.hotel_id
                 and b.room_id = r.room_id
                 and b.user_id = $user_id
                 order by b.check_in ASC";
$bookingResult = mysqli_query($conn,$bookingQuery);

// ===========================
// BILLING QUERY
// ===========================

$billingQuery = "select b.booking_id,h.name as hotel_name,
                b.room_number,b.total_amount,
                b.payment_type,b.status as booking_status,
                bill.payment_status,bill.bill_date
                from bookings b,hotel h,billing bill
                where b.hotel_id = h.hotel_id and
                b.booking_id = bill.booking_id and 
                b.user_id = $user_id
                order by b.check_in ASC";

$billingResult = mysqli_query($conn,$billingQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile</title>
  <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
  <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>
  <!-- Navbar -->
   <nav class="navbar navbar-expand-lg sticky-top bg-light">
    <div class="container">
      <a href="home.php" class="navbar-brand fw-semibold">StayEase</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>
    <div class="collapse navbar-collapse justify-content-end" id="navmenu">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item"><a href="home.php" class="nav-link fw-semibold">Home</a></li>
        <li class="nav-item"><a href="hotel_list.php" class="nav-link fw-semibold">Hotels</a></li>
        <li class="nav-item dropdown">
          <a href="" class="nav-link dropdown-toggle fw-semibold" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?= htmlspecialchars($user['name'])?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
            <li><a href="profile.php" class="dropdown-item">Personal Info</a></li>
            <li><a href="profile.php#bookings" class="dropdown-item">Previous Booking</a></li>
            <li><a href="profile.php#billing" class="dropdown-item">Billing</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a href="../auth/logout.php" class="dropdown-item text-danger">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>
  <!-- Alert content -->
   <?php if(isset($_GET['msg'])): ?>
  <div class="alert alert-<?php echo $_GET['type']; ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($_GET['msg']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <?php endif; ?>

   <!-- content -->
    <div class="container mt-4">
      <h2>Welcome, <?= htmlspecialchars($user['name'])?></h2>
      <hr>

      <!-- personal info -->
       <section id="personal-info" class="mb-5">
        <h4>Personal Information</h4>
        <table class="table table-bordered w-75">
          <tr><th>Name</th><td><?= htmlspecialchars($user['name'])?></td></tr>
          <tr><th>Email</th><td><?= htmlspecialchars($user['email'])?></td></tr>
          <tr><th>Phone</th><td><?= htmlspecialchars($user['phone'])?></td></tr>
          <tr><th>Id Proof</th><td><?= htmlspecialchars($user['id_proof'])?></td></tr>
        </table>
       </section>

       <!-- booking -->
        <section id="bookings" class="mb-5">
          <h4>Previous Bookings</h4>

          <?php if(mysqli_num_rows($bookingResult) > 0):?>
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Hotel</th>
                  <th>Room Type</th>
                  <th>Check In</th>
                  <th>Check Out</th>
                  <th>Status</th>
                  <th>Payment Type</th>
                  <th>Total</th>
                  <th>Action</th>
                </tr>
              </thead>

              <tbody>
                <?php while($row = mysqli_fetch_assoc($bookingResult)):?>
                  <tr>
                    <td><?= htmlspecialchars($row['hotel_name'])?></td>
                    <td><?= htmlspecialchars($row['type_name'])?></td>
                    <td><?= htmlspecialchars($row['check_in'])?></td>
                    <td><?= htmlspecialchars($row['check_out'])?></td>
                    <td><?= htmlspecialchars($row['status'])?></td>
                    <td><?= htmlspecialchars($row['payment_type'])?></td>
                    <td><?= htmlspecialchars($row['total_amount'])?></td>

                    <!-- REFUND LOGIC -->
                    
                      <td>

                        <?php if ($row['refund_request'] == 'pending'): ?>
                            <span class="badge bg-info">Refund Pending</span>

                        <?php elseif ($row['refund_request'] == 'approved'): ?>
                            <span class="badge bg-success">Refund Approved</span>

                        <?php elseif ($row['refund_request'] == 'rejected'): ?>
                            <span class="badge bg-danger">Refund Rejected</span>

                        <?php else: ?>

                            <?php if ($row['status'] == 'booked'): ?>
                                <a href="request_refund.php?id=<?= $row['booking_id'] ?>"
                                  class="btn btn-sm btn-warning">
                                  Request Refund
                                </a>

                            <?php else: ?>
                                <span class="badge bg-secondary">Not Allowed</span>
                            <?php endif; ?>

                        <?php endif; ?>

                      </td>

                

                  </tr>
                <?php endwhile;?>
              </tbody>
            </table>
          <?php else:?>
            <p class="text-muted">No booking found</p>
          <?php endif;?>
        </section>

        <!-- Billing -->
        <section id="billing">
          <h4>Billing</h4>

          <?php if(mysqli_num_rows($billingResult) > 0):?>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Hotel</th>
                  <th>RoomNO</th>
                  <th>Total Amount</th>
                  <th>Payment Type</th>
                  <th>Status</th>
                  <th>Bill Date</th>
                  <th>Print</th>
                </tr>
              </thead>

              <tbody>
                <?php while($row = mysqli_fetch_assoc($billingResult)):?>
                  <tr>
                    <td><?= htmlspecialchars($row['hotel_name'])?></td>
                    <td><?= htmlspecialchars($row['room_number'])?></td>
                    <td>₹<?= htmlspecialchars($row['total_amount'])?></td>
                    <td><?= htmlspecialchars($row['payment_type'])?></td>
                    <td>
                      <?php
                        if(!empty($row['payment_status'])){
                          echo htmlspecialchars($row['payment_status']);
                        }else{
                          echo "<span class='text-muted'>Unbilled (online booking)</span>";
                        }
                      ?>
                    </td>
                    <td><?= htmlspecialchars($row['bill_date'])?></td>
                    <td>
                      <?php if (!empty($row['payment_status'])):?>
                        <a href="../admin/bill.php?booking_id=<?= $row['booking_id'] ?>" 
                           class="btn btn-sm btn-outline-primary">
                          Print Bill
                        </a>
                      <?php else:?>
                        <span class="text-muted">Not Available</span>
                      <?php endif;?>
                    </td>
                  </tr>
                <?php endwhile;?>
              </tbody>
            </table>

          <?php else:?>
            <p class="text-muted">No billing or booking record found.</p>
          <?php endif;?>
        </section>

    </div>
</body>
</html>
