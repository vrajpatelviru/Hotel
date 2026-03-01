<?php
session_start();
require_once __DIR__ . '/../core/database.php';

//get date location from home.php
$location = $_GET['location'] ?? '';
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';

//fetch hote from db
if(!empty($location)){
  $query = "select * from hotel where location = '$location'";
}else{
  $query = "select * from hotel";
}
$result = mysqli_query($conn,$query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hotel List</title>
  
  <!-- CSS link-->
  <link rel="stylesheet" href="../assets/css/hotel_list.css">
  
  <!--BOOTSTRAP CSS-->
  <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
  <!--BOOTSTRAP JS-->
  <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>
  <nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a href="home.php" class="navbar-brand d-flex align-items-center">
      <img src="Picture/logo.png" alt="stay ease">
      <span class="ms-2 fw-semibold text-dark"></span>
    </a> 

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navmenu">
      <ul class="navbar-nav align-items-center">

        <li class="nav-item">
          <a href="home.php" class="nav-link fw-semibold">Home</a>
        </li>

        <li class="nav-item">
          <a href="#" class="nav-link fw-semibold">About</a>
        </li>

        <li class="nav-item">
          <a href="hotel_list.php" class="nav-link fw-semibold">Hotels</a>
        </li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- USER LOGGED IN -->
            <li class="nav-item dropdown">
              <a href="#" class="nav-link dropdown-toggle fw-semibold" id="profileDropdown" data-bs-toggle="dropdown">
                Profile
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a href="profile.php" class="dropdown-item">Personal Info</a></li>
                <li><a href="profile.php#bookings" class="dropdown-item">Previous Booking</a></li>
                <li><a href="profile.php#billing" class="dropdown-item">Billing</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a href="../auth/logout.php" class="dropdown-item text-danger">Logout</a></li>
              </ul>
            </li>
        <?php else: ?>
            <!-- USER NOT LOGGED IN -->
            <li class="nav-item">
              <a href="login.php" class="nav-link fw-semibold">Login</a>
            </li>
            <li class="nav-item">
              <a href="register.php" class="nav-link fw-semibold">Signup</a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>


  <!--location date -->
  <div class="container">
    <div class="filter-bar d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-1 fw-bold">
          Hotels in <?= htmlspecialchars($location ?: 'all locations')?>
        </h5>
        <small class="text-mutd">
          <?php if ($check_in && $check_out): ?>
            From <?= htmlspecialchars($check_in) ?> to <?= htmlspecialchars($check_out) ?>
            <?php else:?>
              Flexible Date
            <?php endif;?>
        </small>
      </div>
      <a href="home.php" class="btn btn-outline-secondary btn-sm">Change Search</a>
    </div>
  </div>

  <!--HOTEL LIST SECTION-->
  <div class="container mt-4">
    <div class="row g-4">
      <?php if(mysqli_num_rows($result)> 0):?>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <div class="col-md-4">
          <div class="card hotel-card">
           <!-- You can add a column 'image' in hotels table or use placeholder -->
            <img src="../uploads/hotels/<?= htmlspecialchars($row['hotel_image']); ?>"> 

            <div class="card-body">
              <h5 class="card-title mb-1"><?= htmlspecialchars($row['name']) ?></h5>
              <p class="text-muted mb-1"><i class="bi bi-geo-alt"></i><?= htmlspecialchars($row['location']) ?></p>
              <p class="text-warning mb-2">⭐<?= htmlspecialchars($row['rateing'] ?:'4.5') ?> /5</p>
              <p class="text-secondary small"><?= $row['description'] ?>...</p>
              <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold text-primary">Form ₹2,000 / night</span>
                <a href="hotel_details.php?hotel_id=<?= $row['hotel_id'] ?>" class="btn btn-sm btn-outline-dark rounded-pill">View</a>
              </div>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12 rext-center">
          <div class="alert alert-warning mt-4">
            No hotel found in this location
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <!--FOOTER-->
  <footer class="text-center mt-5 py-4 bg-dark text-light">
    <p>© 2025 StayEase. All rights reserved.</p>
  </footer>
</body>
</html>