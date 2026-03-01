<?php
session_start();
require_once "../core/database.php";

if (!isset($_SESSION['reset_email'])) {
    header("Location: ../auth/forgot_password.php");
    exit;
}

$message = "";
$email = $_SESSION['reset_email'];

if (isset($_POST['submit'])) {
    $password = $_POST['password'];

    $hashed = password_hash($password, PASSWORD_BCRYPT);

    mysqli_query($conn, "UPDATE user SET password='$hashed' WHERE email='$email'");

    $message = "Password updated successfully! Redirecting to login...";
    
    // destroy reset session
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_otp']);
    
    // Auto redirect after 3 seconds
    echo "
        <script>
            setTimeout(function(){
                window.location.href = '../auth/login.php';
            }, 3000);
        </script>
    ";
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
</head>
<body class="p-4">

<div class="container mt-5 w-50">
    <h3>Reset Password</h3>

    <?php if ($message != ""): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>New Password:</label>
        <input type="password" name="password" class="form-control mb-3" required>

        <button class="btn btn-primary" name="submit">Update Password</button>
    </form>

</div>

</body>
</html>
