<?php
session_start();
require_once "../core/database.php";

$message = "";

if (isset($_POST['submit'])) {
    $email = $_POST['email'];

    // check user email exists
    $query = mysqli_query($conn, "SELECT * FROM user WHERE email='$email'");
    if (mysqli_num_rows($query) == 1) {

        // create 6-digit OTP
        $otp = rand(100000, 999999);

        // store OTP in session
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_otp'] = $otp;

        // TODO: send OTP via email (later)
        // For now, display OTP for testing
        $_SESSION['reset_otp'] = $otp;

        // Show OTP on verify page using GET just for testing
        header("Location: verify_otp.php?otp=$otp");
        exit;

    } else {
        $message = "Email not found!";
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
    <h3>Forgot Password</h3>

    <?php if ($message != ""): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Email:</label>
        <input type="email" name="email" class="form-control mb-3" required>

        <button class="btn btn-primary" name="submit">Send OTP</button>
    </form>

    <a href="../login.php" class="btn btn-link">Back to Login</a>
</div>

</body>
</html>
