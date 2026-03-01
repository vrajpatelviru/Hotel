<?php
require_once '../database.php';

if(isset($_POST['book'])){
  $hotel_id = $_POST['hotel_id'];
  $room_id = $_POST['room_id'];
  $unit_id = $_POST['unit_id'];
  $guest_name = $_POST['guest_name'];
  $guest_idproof = $_POST['guest_idproof'];
  $guest_phone = $_POST['guest_phone'];
  $check_in = $_POST['check_in'];
  $check_out = $_POST['check_out'];
  $payment_type = $_POST['payment_type'];

  //match guest to an existing user
  $user_check = mysqli_query($conn,"select user_id from user where phone = '$guest_phone' or name = '$guest_name' limit 1");
  if(mysqli_num_rows($user_check) > 0){
    $user_row = mysqli_fetch_assoc($user_check);
    $user_id = $user_row['user_id'];
  }else{
    $user_id = null;
  }

  // --- Fetch the selected unit row from DB so we can get room_number ---
  if (!empty($unit_id)) {
      $unit_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT room_number FROM room_units WHERE unit_id='$unit_id'"));
      if ($unit_row) {
          $room_number = $unit_row['room_number'];
      } else {
          // unit not found (maybe deleted or invalid) — stop and show error
          echo "<div class='alert alert-danger'>Selected room unit not found. Please choose another.</div>";
          exit;
      }
  } else {
      echo "<div class='alert alert-danger'>Please select a room unit before booking.</div>";
      exit;
  }
  //get room price:
  $room = mysqli_fetch_assoc(mysqli_query($conn,"select price from rooms where room_id='$room_id'"));
  $price = $room['price'];

  //calculate totel price nights
  $days = (strtotime($check_out) - strtotime($check_in)) / (60*60*24);
  $total_amount = $price * max($days,1);

  //insert booking record;
  $insert = "insert into bookings(user_id,hotel_id,room_id,unit_id,guest_name,guest_idproof,guest_phone,check_in,check_out,status,payment_type,total_amount,room_number)
              values(". ($user_id === null ? "NULL" : "'$user_id'") . ",'$hotel_id','$room_id','$unit_id','$guest_name','$guest_idproof','$guest_phone','$check_in','$check_out','checked_in','$payment_type','$total_amount','$room_number')";
  mysqli_query($conn,$insert);
  
  //get booking id
  $booking_id = mysqli_insert_id($conn);
  
  //insert billing record
  mysqli_query($conn,"
                      insert into billing(user_id,booking_id,guest_name,room_number,total_amount,payment_type,payment_status,bill_date)
                      values(". ($user_id === null ? "NULL" : "'$user_id'") . ",'$booking_id','$guest_name','$room_number','$total_amount','$payment_type','paid',now())");


  //update room unit status
  mysqli_query($conn,"update room_units set status='booked' where unit_id='$unit_id'");

  //show sucess + bill link
  echo "<div class='alert alert-success'>
        Booking successful for <b>$guest_name Redirecting To the bill...</b>!
        </div>
        <script>
          setTimeout(function(){
            window.location.href = 'bill.php?booking_id=$booking_id';
          },3000);
        </script>";
  
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
  <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body class='p-4 bg-light'>
  <div class="container">
  <div class="card p-4 shadow">
  <h3 class="text-center">Admin Booking</h3><hr>
  <form action="" method="post">
    <div class='mb-3'>
      <label>Hotel:</label>
      <select name="hotel_id" id="hotel_id" class="form-select" onchange="this.form.submit()">
        <option value="">--select hotel--</option>
        <?php
          $hotels = mysqli_query($conn,'select * from hotel');
          while($h = mysqli_fetch_assoc($hotels)){
            // Check if this hotel was selected before
          if(isset($_POST['hotel_id']) && $_POST['hotel_id'] == $h['hotel_id']) {
              $selected = 'selected';
          } else {
              $selected = '';
          }

          // Print the option
          echo "<option value='{$h['hotel_id']}' $selected>{$h['name']}</option>";
          }
         
        ?>
      </select>
      
    </div>
    <?php if(!empty($_POST['hotel_id'])): ?>
    <div class='mb-3'>
      <label>Room Type:</label>
      <select name="room_id" id="room_id" class="form-select" onchange="this.form.submit()">
        <option value="">--select room type--</option>
        <?php
          $hotel_id = $_POST['hotel_id'];
          $rooms = mysqli_query($conn,"select * from rooms where hotel_id='$hotel_id'");
          while($r= mysqli_fetch_assoc($rooms)){
            // Check if this hotel was selected before
          if(isset($_POST['room_id']) && $_POST['room_id'] == $r['room_id']) {
              $selected = 'selected';
          } else {
              $selected = '';
          }

          // Print the option
          echo "<option value='{$r['room_id']}' $selected>{$r['type_name']} - ₹{$r['price']}</option>";
          }
         
        ?>
      </select>
      </div>
      <?php endif;?>

      <?php if(!empty($_POST['room_id'])):?>
        <div class='mb-3'>
        <label>Room Number:</label>
        <select name="unit_id" id="unit_id" class="form-select" onchange="this.form.submit()">
          <option value="">--select room unit--</option>
          <?php
            $room_id = $_POST['room_id'];
            $units = mysqli_query($conn,"select * from room_units where room_id='$room_id' AND status='available'");
            while($u= mysqli_fetch_assoc($units)){
              // Check if this hotel was selected before
            if(isset($_POST['unit_id']) && $_POST['unit_id'] == $u['unit_id']) {
                $selected = 'selected';
            } else {
                $selected = '';
            }

            // Print the option
            echo "<option value='{$u['unit_id']}'$selected>room no: {$u['room_number']}</option>";
            }
          ?>
        </select>
        </div>
        <?php endif;?>

        <?php if (!empty($_POST['room_id'])): ?>
          <div class="mb-3">
            <label>Guest Name:</label>
            <input type="text" name="guest_name" class="form-control" required>
          </div>

          <div class="mb-3">
            <label>Phone Number:</label>
            <input type="text" name="guest_phone" class="form-control" required>
          </div>

          <div class="mb-3">
            <label>ID Proof (Aadhaar / Passport / License):</label>
            <input type="text" name="guest_idproof" class="form-control" required>
          </div>

          <div class="mb-3">
            <label>Check-In Date:</label>
            <input type="date" id="check_in" name="check_in" class="form-control" required onclick="this.showPicker()" min="<?php echo date('Y-m-d'); ?>">
          </div>
          
          <div class="mb-3">
            <label>Check-Out Date:</label>
            <input type="date" id="check_out" name="check_out" class="form-control" required onclick="this.showPicker()" min="<?php echo date('Y-m-d'); ?>">
          </div>
          <div class="mb-3">
            <label>Payment Type:</label>
            <select name="payment_type" class="form-select" required>
              <option value="offline">Offline</option>
              <option value="online">Online</option>
            </select>
          </div>
          <button type="submit" name="book" class="btn btn-primary">Confirm Booking</button>
          <?php endif;?>
          <a href="../admin.php" class="btn btn-lm btn-danger ">Back</a>
      </div>
      
      </div>
      
  </form>
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