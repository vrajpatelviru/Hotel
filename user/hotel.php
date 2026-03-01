<?php
session_start();
require_once '../database.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$activetab = 'add';
$alertmsg  = '';
$alerttype = '';
$redirect  = '../admin.php';

/* ----------------------
   ADD HOTEL (WITH IMAGE)
------------------------- */
if (isset($_POST['add'])) {
    $activetab = 'add';
    $owner_id   = $_SESSION['user_id'];
    $name       = $_POST['name'];
    $location   = $_POST['location'];
    $address    = $_POST['address'];
    $description= $_POST['description'];
    $rate       = 3.3;

    // IMAGE UPLOAD
    $imageName = "";
    if (!empty($_FILES['hotel_image']['name'])) {

        $uploadDir = "../uploads/hotels/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['hotel_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];

        if (in_array($ext, $allowed)) {
            $imageName = time() . "_" . uniqid() . "." . $ext;
            move_uploaded_file($_FILES['hotel_image']['tmp_name'], $uploadDir . $imageName);
        } else {
            $alertmsg = "Invalid image format (allowed: JPG, PNG, WEBP)";
            $alerttype = "danger";
        }
    }

    if (empty($owner_id) || empty($name) || empty($location) || empty($address) || empty($description)) {
        $alertmsg  = "All fields are required!";
        $alerttype = "danger";
    } else {
        $sql = "INSERT INTO hotel (name, location, address, description, owner_id, rateing, hotel_image) 
                VALUES ('$name', '$location', '$address', '$description', '$owner_id', '$rate', '$imageName')";
        if (mysqli_query($conn, $sql)) {
            $alertmsg  = "Hotel added successfully! Redirecting...";
            $alerttype = "success";
        } else {
            $alertmsg  = "Error: " . mysqli_error($conn);
            $alerttype = "danger";
        }
    }
}

/* ----------------------
   UPDATE HOTEL (UNCHANGED)
------------------------- */
if (isset($_POST['update'])) {
    $activetab = 'update';
    $hotel_id = $_POST['hotel_id'];
    $fields = [];

    if(!empty($_POST['name'])) $fields[] = "name='". mysqli_real_escape_string($conn, $_POST['name'])."'";
    if(!empty($_POST['location'])) $fields[] = "location='". mysqli_real_escape_string($conn, $_POST['location'])."'";
    if(!empty($_POST['address'])) $fields[] = "address='". mysqli_real_escape_string($conn, $_POST['address'])."'";
    if(!empty($_POST['description'])) $fields[] = "description='". mysqli_real_escape_string($conn, $_POST['description'])."'";

    if(!empty($fields)) {
        $sql = "UPDATE hotel SET ". implode(", ", $fields) ." WHERE hotel_id='$hotel_id'";
        if(mysqli_query($conn,$sql)) {
            $alertmsg  = "Hotel updated successfully! Redirecting...";
            $alerttype = "success";
        } else {
            $alertmsg  = "Error: " . mysqli_error($conn);
            $alerttype = "danger";
        }
    } else {
        $alertmsg  = "No fields to update!";
        $alerttype = "warning";
    }
}

/* ----------------------
   DELETE HOTEL (UNCHANGED)
------------------------- */
if (isset($_POST['delete'])) {
    $activetab = 'delete';
    if (!empty($_POST['hotel_id'])) {
        $hotel_id = $_POST['hotel_id'];
        $sql = "DELETE FROM hotel WHERE hotel_id='$hotel_id'";
        if (mysqli_query($conn, $sql)) {
            $alertmsg  = "Hotel deleted successfully! Redirecting...";
            $alerttype = "success";
        } else {
            $alertmsg  = "Error: " . mysqli_error($conn);
            $alerttype = "danger";
        }
    } else {
        $alertmsg  = "No hotel selected to delete!";
        $alerttype = "warning";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Hotels</title>
<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-3">
    <div class="card p-3">
        <h2 class="text-center mb-4">Hotel Management</h2>

        <!-- Alert -->
        <?php if($alertmsg != ''): ?>
            <div class="alert alert-<?php echo $alerttype; ?> text-center">
                <?php echo $alertmsg; ?>
            </div>
            <script>
                setTimeout(function(){
                    window.location.href = '<?php echo $redirect; ?>';
                }, 3000);
            </script>
        <?php endif; ?>

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="hotelTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php if($activetab=='add') echo 'active'; ?>" data-bs-toggle="tab" data-bs-target="#add">Add Hotel</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php if($activetab=='update') echo 'active'; ?>" data-bs-toggle="tab" data-bs-target="#update">Update Hotel</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php if($activetab=='delete') echo 'active'; ?>" data-bs-toggle="tab" data-bs-target="#delete">Delete Hotel</button>
            </li>
        </ul>

        <div class="tab-content p-4">

            <!-- ADD HOTEL -->
            <div class="tab-pane fade <?php if($activetab=='add') echo 'show active'; ?>" id="add">
                <form method="POST" enctype="multipart/form-data">

                    <input type="text" name="name" class="form-control mb-3" placeholder="Hotel Name">
                    <input type="text" name="location" class="form-control mb-3" placeholder="Location">
                    <input type="text" name="address" class="form-control mb-3" placeholder="Address">
                    <textarea name="description" class="form-control mb-3" rows="3" placeholder="Description"></textarea>

                    <!-- IMAGE UPLOAD FIELD -->
                    <label class="form-label">Hotel Image</label>
                    <input type="file" name="hotel_image" class="form-control mb-3" accept="image/*" required>

                    <button type="submit" name="add" class="btn btn-primary">Add Hotel</button>
                    <a href="../admin.php" class="btn btn-danger">Back</a>
                </form>
            </div>

            <!-- UPDATE HOTEL (unchanged) -->
            <div class="tab-pane fade <?php if($activetab=='update') echo 'show active'; ?>" id="update">
                <form method="POST">
                    <select name="hotel_id" class="form-select mb-3">
                        <option value="">--Choose Hotel--</option>
                        <?php
                        $hotels = mysqli_query($conn, "SELECT hotel_id, name FROM hotel");
                        while($row = mysqli_fetch_assoc($hotels)){
                            echo "<option value='".$row['hotel_id']."'>".$row['name']."</option>";
                        }
                        ?>
                    </select>
                    <input type="text" name="name" class="form-control mb-3" placeholder="New Hotel Name">
                    <input type="text" name="location" class="form-control mb-3" placeholder="New Location">
                    <input type="text" name="address" class="form-control mb-3" placeholder="New Address">
                    <textarea name="description" class="form-control mb-3" rows="3" placeholder="New Description"></textarea>

                    <button type="submit" name="update" class="btn btn-warning">Update Hotel</button>
                    <a href="../admin.php" class="btn btn-danger">Back</a>
                </form>
            </div>

            <!-- DELETE HOTEL (unchanged) -->
            <div class="tab-pane fade <?php if($activetab=='delete') echo 'show active'; ?>" id="delete">
                <form method="POST">
                    <select name="hotel_id" class="form-select mb-3">
                        <option value="">--Choose Hotel--</option>
                        <?php
                        $hotels = mysqli_query($conn, "SELECT hotel_id, name FROM hotel");
                        while($row = mysqli_fetch_assoc($hotels)){
                            echo "<option value='".$row['hotel_id']."'>".$row['name']."</option>";
                        }
                        ?>
                    </select>
                    <button type="submit" name="delete" class="btn btn-danger">Delete Hotel</button>
                    <a href="../admin.php" class="btn btn-danger">Back</a>
                </form>
            </div>

        </div>
    </div>
</div>
</body>
</html>
