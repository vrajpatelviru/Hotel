<?php
session_start();
require_once __DIR__ . '/../core/database.php';

// Check user login
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get booking ID
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    echo "<script>alert('Invalid booking request.'); window.location='profile.php';</script>";
    exit;
}

$booking_id = (int)$_GET['id'];

// Verify: booking belongs to user + status='booked' + no existing refund request
$query = mysqli_query($conn,
    "SELECT * FROM bookings 
     WHERE booking_id = $booking_id 
       AND user_id = $user_id 
       AND status = 'booked'
       AND refund_request = 'none'"
);

if(mysqli_num_rows($query) == 0){
    header("Location: profile.php?msg=Refund request not allowed for this booking!&type=danger");
    exit;

}

// Update refund_request → pending
$update = mysqli_query($conn,
    "UPDATE bookings 
     SET refund_request = 'pending' 
     WHERE booking_id = $booking_id"
);

if($update){
    header("Location: profile.php?msg=Refund request sent successfully!&type=success");
    exit;
} else {
    header("Location: profile.php?msg=Something went wrong. Try again later.&type=danger");
    exit;
}

?>
