<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/../core/database.php';



if(!isset($_GET['hotel_id']) || !isset($_GET['room_id'])){
  header("Location: home.php");
  exit;
}

if(!isset($_SESSION['user_id'])){
  header("Location: login.php");
  exit;
}


$user_id = $_SESSION['user_id'];
// fetched login user data
$userquery = "select * from user where user_id=$user_id";
$user_result = mysqli_query($conn,$userquery);
$user = mysqli_fetch_assoc($user_result);
$hotel_id = intval($_GET['hotel_id']);
$room_id = intval($_GET['room_id']);

//fetchh hotel and room info
$hotel_query = "select * from hotel where hotel_id=$hotel_id";
$hotel_result = mysqli_query($conn,$hotel_query);
$hotel = mysqli_fetch_assoc($hotel_result);

$room_query = "select * from rooms where room_id=$room_id and hotel_id=$hotel_id";
$room_result = mysqli_query($conn,$room_query);
$room = mysqli_fetch_assoc($room_result);

$msg = '';
// hendaling booking 
if(isset($_POST['book'])){
  $guest_name = $_POST['guest_name'];
  $guest_phone = $_POST['guest_phone'];
  $guest_idproof = $_POST['guest_idproof'];
  $check_in = $_POST['check_in'];
  $check_out = $_POST['check_out'];
  $payment_type = 'online';
  $status = 'booked';

  // find one available unit fro this room
  $unit_query = "select unit_id, room_number from room_units
                  where hotel_id = $hotel_id and room_id=$room_id and status='available' LIMIT 1";
  $unit_result = mysqli_query($conn, $unit_query);
  $unit = mysqli_fetch_assoc($unit_result);

  

  if($unit){
    $unit_id = $unit['unit_id'];
    $room_number = $unit['room_number'];
    //calculate totel price nights
    $days = (strtotime($check_out) - strtotime($check_in)) / (60*60*24);
    $total_amount = $room['price'] * max($days,1);

    // Insert boooking
    $insert_query = "insert into bookings(user_id,hotel_id,room_id,unit_id,guest_name,guest_idproof,guest_phone,check_in,check_out,status,payment_type,total_amount,room_number)
    values('$user_id','$hotel_id','$room_id','$unit_id','$guest_name','$guest_idproof','$guest_phone','$check_in','$check_out','$status','$payment_type','$total_amount','$room_number')";
    mysqli_query($conn,$insert_query);

    // Get booking ID
    $booking_id = mysqli_insert_id($conn);

    // ✅ Insert billing for online booking
    mysqli_query($conn, "
      INSERT INTO billing (
        user_id, booking_id, guest_name, room_number, total_amount, payment_type, payment_status, bill_date
      ) VALUES (
        '$user_id', '$booking_id', '$guest_name', '$room_number', '$total_amount', '$payment_type', 'paid', NOW()
      )
    ");


    // update room unit status
    mysqli_query($conn , "update room_units set status = 'booked' where unit_id = $unit_id");
    
    $msg = "<div class='alert alert-success text-center'>Booking successful! Redirecting...</div>
          <script>
            setTimeout(function(){
              window.location.href = 'home.php';
            }, 2000);
          </script>";
   
  } else {
    $msg = "<div class='alert alert-danger text-center'>No available rooms for this type!</div>";
  }
  
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Book Room</title>
  <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
  <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>
  
  <div class="container booking-container">
    <h3 class="text-center mb-4">
      Book your Room
    </h3>
    <?php if($hotel && $room): ?>
      <div class="mb-3">
        <h5 class="text-primary"><?php echo htmlspecialchars($hotel['name']);?></h5>
        <p class="mb-0"><strong>Room Type:</strong><?php echo htmlspecialchars($room['type_name']);?> </p>
        <p class="mb-0"><strong>Price:</strong><?php echo htmlspecialchars($room['price']);?> </p>
        <p class="mb-3"><strong>Location:</strong><?php echo htmlspecialchars($hotel['location']);?></p>
      </div>
    <?php endif;?>
    <?php if(!empty($msg)) echo $msg;?>
    <form  method="post">
      <div class="mb-3">
        <label for="" class="form-lable">Guest Name</label>
        <input type="text" name="guest_name" id=""  class="form-control" value="<?php echo htmlspecialchars($user['name']);?>">
      </div>

      <div class="mb-3">
        <label for="" class="form-lable">Phone</label>
        <input type="text" name="guest_phone" id="" class="form-control" value="<?php echo htmlspecialchars($user['phone']);?>">
      </div>

      <div class="mb-3">
        <label for="" class="form-lable">ID Proof</label>
        <input type="text" name="guest_idproof" id="" class="form-control" value="<?php echo htmlspecialchars($user['id_proof']);?>">
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-lable">check-in Date</label>
          <input type="date" name="check_in" id="check_in" class="form-control" onclick="this.showPicker()" min="<?php echo date('Y-m-d'); ?>">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-lable">check-out Date</label>
          <input type="date" name="check_out" id="check_out" class="form-control" onclick="this.showPicker()" min="<?php echo date('Y-m-d'); ?>">
        </div>
      </div>

      <div class="text-center">
        <button type="submit" class="btn btn-primary px-4" name="book">Confirm Booking</button>
        <a href="hotel_details.php?hotel_id=<?php echo $hotel_id;?>" class="btn btn-secondary ms-2">Cancle</a>
      </div>

    </form>
  </div>
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