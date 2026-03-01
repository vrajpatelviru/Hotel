<?php
session_start();
require_once '../core/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'] ?? 0;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: refund_requests.php?msg=Invalid refund request&type=danger");
    exit;
}

$booking_id = (int)$_GET['id'];

$booking = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM bookings WHERE booking_id = $booking_id"
));

if (!$booking || $booking['refund_request'] !== 'pending') {
    header("Location: refund_requests.php?msg=Refund already processed or invalid&type=danger");
    exit;
}

$total = $booking['total_amount'];
$refund_amount = $total * 0.80;
$refund_amount = number_format($refund_amount, 2, '.', '');
$reason = "20% cancellation fee deducted";

// Update bookings
mysqli_query($conn,
    "UPDATE bookings SET status='cancelled', refund_request='approved'
     WHERE booking_id=$booking_id"
);

// Free room
mysqli_query($conn,
    "UPDATE room_units SET status='available'
     WHERE unit_id={$booking['unit_id']}"
);

// Insert to refunds table
mysqli_query($conn,
    "INSERT INTO refunds (booking_id, refund_amount, refund_reason, processed_by)
     VALUES ($booking_id, '$refund_amount', '$reason', '$admin_id')"
);

// Update billing table after refund
mysqli_query($conn, "
    UPDATE billing 
    SET refund_amount = '$refund_amount',
        cancle_charge = total_amount - '$refund_amount',
        refund_status = 'approved'
    WHERE booking_id = $booking_id
");


header("Location: refund_requests.php?msg=Refund Approved! User refunded ₹$refund_amount&type=success");
exit;
?>
