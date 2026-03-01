<?php
session_start();
require_once __DIR__ . '/../core/database.php';

// If user is logged in
if (isset($_SESSION['user_id'])) {

    $uid = $_SESSION['user_id'];

    // 1. Remove remember token from DB
    $sql = "UPDATE user SET remember_token=NULL WHERE user_id='$uid'";
    mysqli_query($conn, $sql);
}

// 2. Delete cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie("remember_token", "", time() - 3600, "/");
}

// 3. Destroy session
session_unset();
session_destroy();

// 4. Redirect to login
header("Location: ../auth/login.php");
exit;
?>
