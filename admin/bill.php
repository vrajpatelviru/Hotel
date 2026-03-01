<?php
require_once '../core/database.php';

if (isset($_GET['booking_id'])) {
    $booking_id = intval($_GET['booking_id']);
    $bill = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT bill.*, u.name AS user_name, u.email, u.phone
        FROM billing bill
        LEFT JOIN user u ON bill.user_id = u.user_id
        WHERE bill.booking_id = '$booking_id'
    "));
} else {
    $bill = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
</head>
<body class="p-4 bg-light">
    <div class="container">
        <div class="card p-4 shadow">
            <h2 class="text-center mb-3">Hotel Invoice</h2>
            <hr>
            <?php if ($bill): ?>
                <p><strong>Guest Name:</strong> <?= htmlspecialchars($bill['guest_name']) ?></p>
                <p><strong>Room Number:</strong> <?= htmlspecialchars($bill['room_number']) ?></p>
                <p><strong>Total Amount:</strong> ₹<?= htmlspecialchars($bill['total_amount']) ?></p>
                <!-- TOTAL AMOUNT (Original Amount) -->
                <?php if ($bill['refund_amount'] > 0): ?>
                    <!-- REFUND SHOWN IF EXISTS -->
                    <p class="text-success"><strong>Refund Amount:</strong> ₹<?= htmlspecialchars($bill['refund_amount']) ?></p>
                    <p class="text-danger"><strong>Collected Charge :</strong> ₹<?= htmlspecialchars($bill['cancle_charge']) ?></p>
                    <p><strong>Refund Status:</strong> <?= htmlspecialchars($bill['refund_status']) ?></p>
                <?php else: ?>
                    <!-- IF NO REFUND, FINAL AMOUNT = TOTAL -->
                    <p><strong>Final Amount:</strong> ₹<?= htmlspecialchars($bill['total_amount']) ?></p>
                <?php endif; ?>

                <p><strong>Payment Type:</strong> <?= htmlspecialchars($bill['payment_type']) ?></p>
                <p><strong>Payment Status:</strong> <?= htmlspecialchars($bill['payment_status']) ?></p>
                <p><strong>Bill Date:</strong> <?= htmlspecialchars($bill['bill_date']) ?></p>

                

                <hr>
                <p>Thank you for staying with us!</p>
                <div class="text-center mt-3">
                    <button onclick="window.print()" class="btn btn-primary">Print Bill</button>
                    <a href="../admin/admin.php" class="btn btn-danger ms-2">Back</a>
                </div>
            <?php else: ?>
                <div class="alert alert-warning text-center mt-4">
                    No bill found or invalid booking ID.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
