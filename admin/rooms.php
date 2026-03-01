<?php
require_once '../core/database.php';

$activetab = 'update';   // default active tab
$alertmsg  = '';
$alerttype = '';
$redirect  = '../admin/admin.php';

// ----------------- -----
// ADD ROOM
// ----------------------
if (isset($_POST['add'])) {
    $activetab = 'add';
    $hotel_id   = $_POST['hotel_id'];
    $type_name  = $_POST['type_name'];
    $price   = $_POST['price'];
    $capacity    = $_POST['capacity'];
    $total_room= $_POST['total_room'];
    $maintenance_room = $_POST['maintenance_room'];

    // ----------------------
    // IMAGE UPLOAD
    // ----------------------
    $room_image = "";
    if (!empty($_FILES['room_image']['name'])) {

        $uploadDir = "../uploads/rooms/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $ext = strtolower(pathinfo($_FILES['room_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];

        if (in_array($ext, $allowed)) {
            $room_image = time() . "_" . uniqid() . "." . $ext;
            move_uploaded_file($_FILES['room_image']['tmp_name'], $uploadDir . $room_image);
        } else {
            $alertmsg = "Invalid image format! Allowed: JPG, JPEG, PNG, WEBP";
            $alerttype = "danger";
        }
    }

    if (empty($hotel_id) || empty($type_name) || empty($price) || empty($capacity) || empty($total_room)) {
        $alertmsg  = "All fields are required!";
        $alerttype = "danger";

    } else {
        $sql = "INSERT INTO rooms (hotel_id, type_name, price, capacity, total_room, maintenance_room, room_image) 
                VALUES ('$hotel_id', '$type_name', '$price', '$capacity', '$total_room', '$maintenance_room', '$room_image')";

        if (mysqli_query($conn, $sql)) {
            $room_id = mysqli_insert_id($conn); // get new room ID

            // Find last room number for this hotel
            $lastRoomQuery = mysqli_query($conn,
                "SELECT MAX(room_number) AS last_num 
                FROM room_units 
                WHERE hotel_id = '$hotel_id'"
            );
            $lastRow = mysqli_fetch_assoc($lastRoomQuery);

            $start_number = $lastRow['last_num'] ? $lastRow['last_num'] + 1 : 1;

            // Insert room units with correct numbering
            for ($i = 0; $i < $total_room; $i++) {

                $room_number = $start_number + $i;

                mysqli_query($conn,
                    "INSERT INTO room_units (hotel_id, room_id, room_number, status)
                    VALUES ('$hotel_id', '$room_id', '$room_number', 'available')"
                );
            }

            $alertmsg  = "Room added successfully! Redirecting...";
            $alerttype = "success";
        }

    }
}

// ----------------------
// UPDATE ROOMS
// ----------------------
if (isset($_POST['update'])) {
    $activetab = 'update';
    $room_id = $_POST['room_id'];
    $fields = [];

    if(!empty($_POST['type_name'])) $fields[] = "type_name='". mysqli_real_escape_string($conn, $_POST['type_name'])."'";
    if(!empty($_POST['price'])) $fields[] = "price='". mysqli_real_escape_string($conn, $_POST['price'])."'";
    if(!empty($_POST['capacity'])) $fields[] = "capacity='". mysqli_real_escape_string($conn, $_POST['capacity'])."'";

    if(!empty($fields)) {
        $sql = "UPDATE rooms SET ". implode(", ", $fields) ." WHERE room_id='$room_id'";
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
    <div class="container mt-5">
        <div class="card p-3">
            <h2 class="text-center mb-4">Hotel Management</h2>

            <!-- Alert at top -->
            <?php if($alertmsg != ''): ?>
            <div class="alert alert-<?php echo $alerttype; ?> text-center">
                <?php echo $alertmsg; ?>
            </div>
            <script>
            setTimeout(function() {
                window.location.href = '<?php echo $redirect; ?>';
            }, 3000);
            </script>
            <?php endif; ?>

            <!-- Tabs -->
            <ul class="nav nav-tabs" id="hotelTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php if($activetab=='update') echo 'active'; ?>" data-bs-toggle="tab"
                        data-bs-target="#update">Update Room</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php if($activetab=='add') echo 'active'; ?>" data-bs-toggle="tab"
                        data-bs-target="#add">Add Room</button>
                </li>
                
            </ul>

            <!-- Tab Content -->
            <div class="tab-content p-4">

                <!-- Add Room -->
                <div class="tab-pane fade <?php if($activetab=='add') echo 'show active'; ?>" id="add">
                    <form method="POST" enctype="multipart/form-data">

                        <select name="hotel_id" class="form-select mb-3">
                            <option value=""><---Select Hotel---></option>
                            <?php
                            $hotels = mysqli_query($conn, "SELECT hotel_id, name FROM hotel");
                            while($row = mysqli_fetch_assoc($hotels)){
                                echo "<option value='".$row['hotel_id']."'>".$row['name']."</option>";
                            }
                            ?>
                        </select>

                        <input type="text" name="type_name" class="form-control mb-3" placeholder="Room Type">
                        <input type="text" name="price" class="form-control mb-3" placeholder="Price per Night">
                        <input type="text" name="capacity" class="form-control mb-3" placeholder="Capacity (Guest)">
                        <input type="text" name="total_room" class="form-control mb-3" placeholder="Total Rooms">
                        <input type="text" name="maintenance_room" class="form-control mb-3" placeholder="Maintenance rooms" value=0>

                        <!-- ROOM IMAGE UPLOAD -->
                        <label class="form-label">Room Image</label>
                        <input type="file" name="room_image" class="form-control mb-3" accept="image/*" required>

                        <button type="submit" name="add" class="btn btn-primary">Add Room</button>
                        <a href="../admin/admin.php" class="btn btn-danger">Back</a>

                    </form>
                </div>

                <!-- Update Rooms -->
                <div class="tab-pane fade <?php if($activetab=='update') echo 'show active'; ?>" id="update">
                    <form method="POST">

                        <select name="hotel_id" class="form-select mb-3" onchange="this.form.submit()">
                            <option value="">--Select Hotel--</option>
                            <?php
                            $hotels = mysqli_query($conn, "SELECT hotel_id, name FROM hotel");
                            $selected_hotel = $_POST['hotel_id'] ?? '';
                            while($row = mysqli_fetch_assoc($hotels)){
                                $sel = $selected_hotel == $row['hotel_id'] ? "selected" : "";
                                echo "<option value='{$row['hotel_id']}' $sel>{$row['name']}</option>";
                            }
                            ?>
                        </select>

                        <select name="room_id" class="form-select mb-3" required>
                            <option value="">--Select Room Type--</option>
                            <?php
                            if(!empty($selected_hotel)){
                                $rooms = mysqli_query($conn, "SELECT room_id, type_name FROM rooms WHERE hotel_id='$selected_hotel'");
                                while($row = mysqli_fetch_assoc($rooms)){
                                    echo "<option value='".$row['room_id']."'>".$row['type_name']."</option>";
                                }
                            }
                            ?>
                        </select>

                        <input type="text" name="type_name" class="form-control mb-3" placeholder="New Room type">
                        <input type="text" name="price" class="form-control mb-3" placeholder="New price">
                        <input type="text" name="capacity" class="form-control mb-3" placeholder="New capacity">

                        <button type="submit" name="update" class="btn btn-warning">Update Room</button>
                        <a href="../admin/admin.php" class="btn btn-danger">Back</a>

                    </form>
                </div>

            </div>
        </div>
    </div>
</body>

</html>
