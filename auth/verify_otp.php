<?php
session_start();
require_once __DIR__ . '/../core/database.php';
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit;
}

$message = "";

if (isset($_POST['verify'])) {
    $otp = $_POST['otp'];

    if ($otp == $_SESSION['reset_otp']) {
        header("Location: reset_password.php");
        exit;
    } else {
        $message = "Invalid OTP!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
</head>
<body class="p-4">

<div class="container mt-5 w-50">
    <?php if (isset($_GET['otp'])): ?>
    <div class="alert alert-info">
        Your OTP is: <b><?= $_GET['otp'] ?></b>
    </div>
    <?php endif; ?>

    <h3>Enter OTP</h3>

    <?php if ($message != ""): ?>
        <div class="alert alert-danger"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>OTP:</label>
        <input type="text" name="otp" class="form-control mb-3" required>

        <button class="btn btn-primary" name="verify">Verify OTP</button>
    </form>

</div>

</body>
</html>
