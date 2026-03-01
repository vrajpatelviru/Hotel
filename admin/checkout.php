<?php
require_once '../core/database.php';
//btn click 
if(isset($_POST['checkout'])){
    $booking_id = $_POST['booking_id'];
    $unit_id = $_POST['unit_id'];

    //update booking ststus
    mysqli_query($conn,"update bookings set status='checked_out' where booking_id = '$booking_id'");

    //update room unit status
    mysqli_query($conn,"update room_units set status='available' where unit_id='$unit_id'");

    //update billing payments
    mysqli_query($conn, "update billing set payment_status='paid' where booking_id= '$booking_id'" );

    echo "<div class='alert alert-success m-4'>Guest Check Out Sucessfully!</div>";
}   
    //show list of booked guest list

    $bookings = mysqli_query($conn,"
                select b.booking_id,b.guest_name,b.check_in,b.check_out,b.unit_id,r.room_number
                from bookings b, room_units r
                where b.unit_id = r.unit_id and b.status = 'checked_in'
                ORDER BY check_in ASC");
    

    

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <style>
    .card{
        
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>
<body class='p-4'>
<div class="container">
    <h2>Guest Checkout</h2>
    <div class="card p-3 n">
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Guest name</th>
                <th>Room NO</th>
                <th>Check-In</th>
                <th>Check-Out</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($bookings)):?>
                <tr>
                    <td><?= $row['booking_id']?></td>
                    <td><?= $row['guest_name']?></td>
                    <td><?= $row['room_number']?></td>
                    <td><?= $row['check_in']?></td>
                    <td><?= $row['check_out']?></td>
                    <td>
                       <form method="POST">
                        <input type="hidden" name="booking_id" value="<?= $row['booking_id']?>">
                        <input type="hidden" name="unit_id" value="<?= $row['unit_id']?>">
                        <button type="submit" name="checkout" class="btn btn-danger btn-sm">
                            Check Out
                        </button>
                       </form>  
                    </td>
                </tr>
        
            <?php endwhile;?>
        </tbody>
    </table>
    <form action="" method="post">
        <a href="../admin/admin.php" class="btn btn-lm btn-danger ">Back</a>
    </form>
    </div>
</div>  
</body>
</html>