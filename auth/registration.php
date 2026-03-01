<?php
session_start();
require_once __DIR__ . '/../core/database.php';
if (isset($_SESSION['user'])) {
    header('Location : ../user/home.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="../assets/css/registration.css"></link>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="../assets/bootstrap/icon/bootstrap-icons.min.css">
    <!-- Bootstrap js -->
    <script src="bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center py-5">
        <div class="card shadow p-4 rounded-4 login-card">
            <h2 class="text-center mb-4">Registration</h2>
            <form id="loginform" action="registration.php" method="post">
                <!-- PHP CODE-->
                <?php
                if (isset($_POST['register'])) {
                    $name        = $_POST['name'];
                    $email       = $_POST['email'];
                    $phone       = $_POST['phone'];
                    $idproof     = $_POST['idproof'];
                    $password    = $_POST['password'];
                    $cnfpassword = $_POST['cnfpassword'];

                    // secure password
                    $passwordhash = password_hash($password, PASSWORD_DEFAULT);

                    // for validations
                    $errors = array();

                    // check if any field is empty
                    if (empty($name) or empty($email) or empty($phone) or empty($idproof) or empty($password) or empty($cnfpassword)) {
                        array_push($errors, "All fields are required");
                    }

                    // check for valid mail
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        array_push($errors, "Enter valid email");
                    }

                    // check for the password
                    if (strlen($password) < 8) {
                        array_push($errors, "password must be have 8 character");
                    }

                    // check cnfpassword
                    if ($password != $cnfpassword) {
                        array_push($errors, "password doen't match");
                    }

                    // check for email is exist or not
                    require_once __DIR__ . '/../core/database.php';
                    $sql      = "select * from user where email='$email'";
                    $result   = mysqli_query($conn, $sql);
                    $rowcount = mysqli_num_rows($result);

                    if ($rowcount > 0) {
                        array_push($errors, "email is alredy exist");
                    }

                    // if errors occurs then show errors to user
                    if (count($errors) > 0) {
                        foreach ($errors as $error) {
                            echo "<div class='alert alert-danger'>$error</div>";
                        }
                    } else {
                        // insert the date to database
                        //$require_once 'database.php';
                        $sql = "insert into user (name, email, phone, id_proof, password) values (?, ?, ?, ?, ?) ";
                        $stmt = mysqli_stmt_init($conn);
                        $preparestmt = mysqli_stmt_prepare($stmt, $sql);

                        if ($preparestmt) {
                            mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $phone, $idproof, $passwordhash);
                            mysqli_stmt_execute($stmt);
                            echo "<div class='alert alert-success'> Youregistered success fully</div>";
                        } else {
                            die("something went wrong");
                        }
                    }
                }
                ?>
                <!-- Name -->
                <div class="mb-2">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter name">
                </div>

                <!-- Email -->
                <div class="mb-2">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter email address">
                    <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>

                <!-- Phone Number -->
                <div class="mb-2">
                    <label for="number" class="form-label">Phone Number</label>
                    <input type="tel" id="number" name="phone" class="form-control" placeholder="Enter phone number">
                    <div class="invalid-feedback">Please enter a valid 10-digit phone number.</div>
                </div>

                <!-- ID Proof -->
                <div class="mb-2">
                    <label for="idproof" class="form-label">ID Proof Number</label>
                    <input type="text" id="idproof" name="idproof" class="form-control" placeholder="Enter your ID proof number">
                    <div class="invalid-feedback">Please enter your ID proof number.</div>
                </div>

                <!-- Password -->
                <div class="mb-3">
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

                <!-- Confirm Password -->

                <div class="mb-3">
                    <label for="cnfpassword" class="form-label">Confirm Password</label>

                    <div style="position: relative;">
                        <input type="password" id="cnfpassword" name="cnfpassword"
                            class="form-control" placeholder="Enter password">

                        <i class="bi bi-eye position-absolute top-50 end-0 translate-middle-y me-3"
                        onclick="cnfpassword.type = cnfpassword.type==='password' ? 'text' : 'password'"
                        style="cursor: pointer; ">
                        </i>
                     <div class="invalid-feedback">Please confirm your password.</div>
                    </div>
                </div>

                

                <!-- Submit -->
                <button type="submit" class="btn btn-primary w-100 btn-effect" name="register">Register</button>
                <div class="text-center mt-3">
                    <p>Already have an account? <a href="login.php">Login</a></p>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
