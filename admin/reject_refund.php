<?php
session_start();
require_once '../core/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: refund_requests.php?msg=Invalid refund request&type=danger");
    exit;
}

$booking_id = (int)$_GET['id'];

$query = mysqli_query($conn,
    "UPDATE bookings 
     SET refund_request = 'rejected'
     WHERE booking_id = $booking_id
     AND refund_request = 'pending'"
);

if (mysqli_affected_rows($conn) > 0) {
    header("Location: refund_requests.php?msg=Refund request rejected&type=warning");
} else {
    header("Location: refund_requests.php?msg=Request cannot be rejected&type=danger");
}
exit;
?>
