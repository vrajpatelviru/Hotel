<?php
session_start();
require_once __DIR__ . '/../core/database.php';

if(!isset($_GET['hotel_id'])){
  header("Location: home.php");
  exit;
}

$hotel_id = intval($_GET['hotel_id']);

//Fetch hotel details
$hotel_query = "select * from hotel where hotel_id = $hotel_id";
$hotel_result = mysqli_query($conn,$hotel_query);
$hotel = mysqli_fetch_assoc($hotel_result);

if(!$hotel){
  echo "<h2>Hotel is not found</h2>";
  exit;
}

// Fectch room for this hotel
$room_query = "select * from rooms where hotel_id=$hotel_id";
$room_result = mysqli_query($conn , $room_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Details</title>
  <!--BOOTSTRAP CSS-->
  <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
  <!--BOOTSTRAP JS-->
  <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <style>
    .hotel-header{
      margin-top: 30px;
    }
    /*NAVBAR*/
    .navbar{
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    background: #fff;
    }
    
    .navbar-brand img{
    height: 40px;
    }
    .hotel-image{
      border-radius: 10px;
      height: 350px;
      width: 100%;
      object-fit: cover;
    }
    .reating-badge{
      background-color: #198754;
      color: white;
      border-radius: 5px;
      padding: 3px 8px;
      font-weight: 600;
    }
    .service{
      font-size: 1.5rem;
    }
    .room-card img{
      height: 200px;
      object-fit: cover;
    }
  </style>
</head>
<body>
  <!--NAV BAR -->
  <nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
      <a href="#" class="navbar-brand d-flex align-items-center">
        <img src="Picture/logo.png" alt="stay ease">
        <span class="ms-2 fw-semibold text-dark"></span>
      </a> 
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
        <span class="navbar-toggler-icon"></span>
      </button> 
      <div class="collapse navbar-collapse justify-content-end" id="navmenu">
        <ul class="navbar-nav align-items-center">
          <li class="nav-item"><a href="home.php" class="nav-link fw-semibold">Home</a></li>
          <li class="nav-item"><a href="#" class="nav-link fw-semibold">About</a></li>
          <li class="nav-item"><a href="hotel_list.php" class="nav-link fw-semibold">HOtels</a></li>

          <!-- Profile dropdown -->
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
  <div class="container hotel-header">
    <!--Top Section-->
    <div class="row">
      <div class="col-md-8">
        <img src="../uploads/hotels/<?php echo htmlspecialchars($hotel['hotel_image']); ?>"
             alt="hotel image"
             class="hotel-image shadow-sm"
             onerror="this.src='Picture/login-bg.jpg';">
      </div>
      <div class="col-md-4 d-flex flex-column justify-content-center">
        <span class="badge bg-danger mb-2 mt-3 d-flex align-items-center justify-content-center" style="height: 30px; width: 65px;">
          Luxury
        </span>
        <h2 class="fw-bold"><?php echo htmlspecialchars($hotel['name']);?></h2>
        <div class="mb-2">
          ⭐<?php echo htmlspecialchars($hotel['rateing']);?>/5
          <span class="rating-badge ms-2">Wonderful</span>
        </div>

        <p class="text-muted mb-2">
          <?php echo htmlspecialchars($hotel['location']);?>
        </p>
        <p class="small">
          <?php echo htmlspecialchars($hotel['description']);?>
        </p>
        <p class="fw-semibold">
          <?php echo htmlspecialchars($hotel['address']);?>
        </p>
      </div>
    </div>
    <!-- About hotel -->
     <div class="mt-4">
      <h4>About this property</h4>
      <p class="text-secondary">
        Enjoy a relaxing stay with morden amenities and great hospitality.
        Perfect for business, leisure, or family trips.
      </p>
      <div class="row text-center mt-3">

        <div class="col-md-3 col-6 mb-3">
          <div class="border rounded p-3 bg-white shadow-sm">
            <div class="service">🏊</div>
            <p class="mt-2 mb-0 small">Outdoor Pool</p>
          </div>
        </div>

        <div class="col-md-3 col-6 mb-3">
          <div class="border rounded p-3 bg-white shadow-sm">
            <div class="service">🍽️</div>
            <p class="mt-2 mb-0 small">Restaurant</p>
          </div>
        </div>

        <div class="col-md-3 col-6 mb-3">
          <div class="border rounded p-3 bg-white shadow-sm">
            <div class="service">🛜</div>
            <p class="mt-2 mb-0 small">Free-wifi</p>
          </div>
        </div>

        <div class="col-md-3 col-6 mb-3">
          <div class="border rounded p-3 bg-white shadow-sm">
            <div class="service">🅿️</div>
            <p class="mt-2 mb-0 small">Parking</p>
          </div>
        </div>

      </div>
     </div>
    <hr class="my-4">
    <!-- Available Rooms -->
     <h3 class="mb-3">Available Rooms</h3>
     <div class="row">
      <?php while ($room = mysqli_fetch_assoc($room_result)):?>
        <?php
          $room_id = $room['room_id'];
          $unit_query = "select count(*) as available_units from room_units
                          where hotel_id=$hotel_id and room_id=$room_id and status='available'" ;
          $unit_result = mysqli_query($conn, $unit_query);
          $unit_data = mysqli_fetch_assoc($unit_result);
          $available_units = $unit_data['available_units']; 
        ?>
      <div class="col-md-4 mb-4">
        <div class="card room-card shadow-sm">
          <img src="../uploads/rooms/<?php echo htmlspecialchars($room['room_image']); ?>" 
            alt="Room Image" 
            class="card-img-top"
            onerror="this.src='Picture/login-bg.jpg';">

          <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($room['type_name']);?></h5>
            <p class="card-text mb-1"><strong>Price:</strong> ₹ <?php echo htmlspecialchars($room['price']);?></p>
            <p class="card-text mb-1"><strong>Capacity:</strong> <?php echo htmlspecialchars($room['capacity']);?> Guest</p>
            <p class="card-text"><strong>Available:</strong> <?php echo $available_units;?> rooms</p>
            <?php if($available_units > 0):?>
              <a href="book_room.php?room_id=<?php echo $room_id;?> &hotel_id=<?php echo $hotel_id;?>" class="btn btn-primary w-100">Book Now</a>
            <?php else:?>
              <button class="btn btn-secondary w-100 disabled">Fully Booked</button>
            <?php endif;?>
          </div>
        </div>
      </div>
      <?php endwhile;?>
     </div>
  </div>
  <!-- FOOTER -->
   <footer class="bg-dark text-white text-center mt-5">
    &copy; <?php echo date('Y');?> Hotel Management System | All Rights Reserved
   </footer>
</body>
</html>