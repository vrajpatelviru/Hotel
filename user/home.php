<?php
session_start();
require_once __DIR__ . '/../core/database.php';

$hotel_query = "select * from hotel order by create_at desc";
$hotel_result = mysqli_query($conn, $hotel_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home page</title>

  <link rel="stylesheet" href="../assets/css/home.css">
  <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
  <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>

  <!-- NAV BAR -->
  <nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
      <a href="#" class="navbar-brand d-flex align-items-center">
        <img src="../assets/images/logo.png" alt="stay ease">
        <span class="ms-2 fw-semibold text-dark"></span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-end" id="navmenu">
        <ul class="navbar-nav align-items-center">
          <li class="nav-item"><a href="home.php" class="nav-link fw-semibold">Home</a></li>
          <li class="nav-item"><a href="#" class="nav-link fw-semibold">About</a></li>
          <li class="nav-item"><a href="hotel_list.php" class="nav-link fw-semibold">Hotels</a></li>

          <!-- Profile dropdown -->
          <?php if(isset($_SESSION['user_id'])): ?>
              <!-- SHOW PROFILE DROPDOWN WHEN LOGGED IN -->
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
            <!-- SHOW LOGIN & SIGNUP WHEN NOT LOGGED IN -->
              <li class="nav-item">
                <a href="../auth/login.php" class="nav-link fw-semibold">Login</a>
              </li>
              <li class="nav-item">
                <a href="../auth/registration.php" class="nav-link fw-semibold">Signup</a>
              </li>

          <?php endif; ?>


        </ul>
      </div>
    </div>
  </nav>

  <!-- HERO SECTION -->
  <section class="hero">
    <h1>Find your perfect stay</h1>
    <p>Book hotels, resorts and unique stays anywhere in India</p>

    <form action="hotel_list.php" class="search-card mt-4">
      <div class="row g-3 align-items-center">
        <div class="col-md-4">
          <input type="text" name="location" class="form-control border-0" placeholder="Enter Location" list="cities">
          <datalist id="cities">
            <option value="ahmedabad"></option>
            <option value="mumbai"></option>
            <option value="goa"></option>
            <option value="anand"></option>
            <option value="jaipur"></option>
          </datalist>
        </div>
        <div class="col-md-3">
          <input type="date" id="check_in" name="check_in" class="form-control border-0" onclick="this.showPicker()" min="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="col-md-3">
          <input type="date" id="check_out" name="check_out" class="form-control border-0" onclick="this.showPicker()" min="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="col-md-2 text-md-end">
          <button class="btn btn-dark px-4 rounded-pill">Search</button>
        </div>
      </div>
    </form>
  </section>

  <!-- DESTINATION STAY -->
  <section class="container mt-5 pt-5">
    <h2 class="fw-bold mb-3">Explore Destination Stays</h2>
    <div class="scroll-container d-flex flex-nowrap gap-3">

      <?php if(mysqli_num_rows($hotel_result) > 0): ?>
        <?php while($hotel = mysqli_fetch_assoc($hotel_result)) : ?>
          <?php
              $hotel_id = $hotel['hotel_id'];
              $price_query = "select min(price) as min_price from rooms where hotel_id = $hotel_id";
              $price_result = mysqli_query($conn, $price_query);
              $price_data = mysqli_fetch_assoc($price_result);
              $min_price = $price_data['min_price'] ?? 0;
          ?>

          <div class="card hotel-card">
            
            <!-- SHOW HOTEL IMAGE -->
            <img src="../uploads/hotels/<?= htmlspecialchars($hotel['hotel_image']); ?>" 
                 class="card-img-top"
                 onerror="this.src='Picture/login-bg.jpg';">

            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($hotel['name']); ?></h5>
              <p class="text-muted mb-1"><?= htmlspecialchars($hotel['location']); ?></p>

              <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold text-primary">₹<?= $min_price; ?>/ night</span>
                <a href="hotel_details.php?hotel_id=<?= $hotel['hotel_id']; ?>" class="btn btn-sm btn-outline-dark rounded-pill">View</a>
              </div>
            </div>
          </div>

        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-muted">No hotels found in the database.</p>
      <?php endif; ?>

    </div>
  </section>

  <!-- UNIQUE STAYS -->
  <section class="container mt-5">
    <h2 class="fw-bold mb-3">Explore Unique Stays</h2>
    <div class="scroll-container d-flex flex-nowrap gap-3">

      <?php
      $unique_query = "select * from hotel order by rand() limit 3";
      $unique_result = mysqli_query($conn,$unique_query);

      while($unique = mysqli_fetch_assoc($unique_result)):
        $uid = $unique['hotel_id'];

        $price_q = "select min(price) as min_price from rooms where hotel_id=$uid";
        $price_r = mysqli_query($conn,$price_q);
        $price_d = mysqli_fetch_assoc($price_r);
        $uprice = $price_d['min_price'] ?? 0;
      ?>
        <div class="card hotel-card">

          <!-- SHOW IMAGE HERE -->
          <img src="../uploads/hotels/<?= htmlspecialchars($unique['hotel_image']); ?>"
               class="card-img-top"
               onerror="this.src='Picture/login-bg.jpg';">

          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($unique['name']); ?></h5>
            <p class="text-muted mb-1"><?= htmlspecialchars($unique['location']); ?></p>

            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-bold text-primary">₹<?= $uprice; ?>/ night</span>
              <a href="hotel_details.php?hotel_id=<?= $unique['hotel_id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill">Book</a>
            </div>
          </div>
        </div>

      <?php endwhile; ?>

    </div>
  </section>

  <!-- FOOTER -->
  <footer>
    <p>© 2025 StayEase. All rights reserved.</p>
  </footer>

</body>
<script>
document.getElementById("check_in").addEventListener("change", function () {
    let checkInDate = this.value;
    let checkOut = document.getElementById("check_out");

    // set minimum checkout date
    checkOut.min = checkInDate;

    // if checkout is earlier than checkin → reset it
    if (checkOut.value < checkInDate) {
        checkOut.value = checkInDate;
    }
});
</script>

</html>
