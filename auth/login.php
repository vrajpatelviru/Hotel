<?php
session_start();
require_once __DIR__ . '/../core/database.php';

// If session already exists → redirect normally
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/admin.php");
    } else {
        header("Location: ../user/home.php");
    }
    exit;
}

// REMEMBER ME (ONLY if session does NOT exist)
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {

    $token = $_COOKIE['remember_token'];

    if (!empty($token)) {

        $sql = "SELECT * FROM user WHERE remember_token='$token' LIMIT 1";
        $result = mysqli_query($conn, $sql);
        $user = mysqli_fetch_assoc($result);

        if ($user) {

            // create session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role']    = $user['role'];

            // redirect by role
            if ($user['role'] === 'admin') {
                header("Location: ../admin/admin.php");
            } else {
                header("Location: ../user/home.php");
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link rel="stylesheet" href="../assets/css/login.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="../assets/bootstrap/icon/bootstrap-icons.min.css">
    <!-- Bootstrap JS -->
    <script src="../assets/bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow p-4 rounded-4 login-card">
            <h2 class="text-center mb-4">Login</h2>

            <form id="loginform" action="../auth/login.php" method="post">
                <?php
                if (isset($_POST['submit'])) {
                    $email    = $_POST['email'];
                    $password = $_POST['password'];

                    require_once __DIR__ . '/../core/database.php';
                    $sql    = "select * from user where email='$email'";
                    $result = mysqli_query($conn, $sql);
                    $user   = mysqli_fetch_array($result, MYSQLI_ASSOC);

                    if ($user) {
                        if (password_verify($password, $user['password'])) {
                            //sstore session info
                            $_SESSION['user_id'] = $user['user_id'];
                            $_SESSION['role'] = $user['role'];
                            
                            if (isset($_POST['remember'])) {

                                // create random token
                                $token = bin2hex(random_bytes(16));

                                // store in DB
                                $uid = $user['user_id'];
                                $q = "UPDATE user SET remember_token='$token' WHERE user_id='$uid'";
                                mysqli_query($conn, $q);

                                // set cookie for 30 days
                                setcookie(
                                    "remember_token",
                                    $token,
                                    time() + (30*24*60*60),
                                    "/",
                                    "",
                                    false,
                                    true
                                );
                            }
                            
                            
                            //redirect to role;
                            
                            if($user['role'] == 'admin'){
                                header('Location: ../admin/admin.php');
                            }else{
                                header('Location: ../user/home.php');
                            }
                            exit;
                        } else {
                            echo "<div class='alert alert-danger'>password does not match</div>";
                        }
                    } else {
                        echo "<div class='alert alert-danger'>email does not match</div>";
                    }
                }
                ?>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="useremail" name="email" placeholder="Enter your email">
                </div>

                <!-- Password -->
                <div class="mb-2">
                    <label for="password" class="form-label">Password</label>

                    <div style="position: relative;">
                        <input type="password" id="password" name="password"
                            class="form-control" placeholder="Enter password">

                        <i class="bi bi-eye position-absolute top-50 end-0 translate-middle-y me-3"
                        onclick="password.type = password.type==='password' ? 'text' : 'password'"
                        style="cursor: pointer; ">
                        </i>
                    </div>
                </div>



                <!-- Forgot / Remember -->
                <div class="forgot d-flex justify-content-between align-items-center mb-3">
                    <div class="remember">
                        <input type="checkbox" name="remember" value="1"> Remember me
                    </div>
                    <a href="forgot/forgot_password.php">Forgot Password</a>
                </div>

                <!-- Button -->
                <button type="submit" class="btn btn-primary w-100 btn-effect" name="submit">Login</button>

                <div class="text-center mt-3">
                    <p>I don’t have an account <a href="registration.php">Sign Up</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
