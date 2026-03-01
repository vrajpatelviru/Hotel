<?php
session_start();
require_once '../core/database.php'; 
if ($_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4 text-center">Payments</h2>

        <!-- filering details -->
         <form action="" method="get" class="card p-4">
            <div class="row g-3">
                <!-- Hotels -->
                 <div class="col-md-3">
                    <label for="" class="form-label fw-bold">Hotel</label>
                    <select name="hotel_id" id="" class="form-select">
                        <option value="">All Hotles</option>
                        <?php
                            $hotels = mysqli_query($conn,"select * from hotel order by name");
                            while($h = mysqli_fetch_assoc($hotels)){
                                // Check if this hotel was selected before
                                if(isset($_GET['hotel_id']) && $_GET['hotel_id'] == $h['hotel_id']) {
                                    $selected = 'selected';
                                } else {
                                    $selected = '';
                                }
                                echo "<option value='{$h['hotel_id']}' $selected>{$h['name']}</option>";
                            }
                        ?>
                    </select>
                 </div>
                
                <!-- payment type -->
                 <div class="col-md-2">
                    <label class="form-label fw-bold">Type</label>
                    <select name="type" id="" class="form-select">
                        <option value="">All</option>
                        <option value="online"<?php if(isset($_GET['type']) && $_GET['type'] == 'online') echo 'selected';?>>online</option>
                        <option value="offline"<?php if(isset($_GET['type']) && $_GET['type'] == 'offline') echo 'selected';?>>offline</option>
                    </select>
                 </div>
                
                <!-- payment status -->
                 <div class="col-md-2">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" id="" class="form-select">
                        <option value="">ALL</option>
                        <option value="paid"<?php if(isset($_GET['status']) && $_GET['status'] == 'paid') echo 'selected'; ?>>paid</option>
                        <option value="unpaid"<?php if(isset($_GET['status']) && $_GET['status'] == 'unpaid') echo 'selected'; ?>>unpaid</option>
                        <option value="pending"<?php if(isset($_GET['status']) && $_GET['status'] == 'pending') echo 'selected'; ?>>pending</option>
                    </select>
                 </div>
                
                <!-- Date Range -->
                 <div class="col-md-2">
                    <label class="form-label fw-bold">From</label>
                    <input type="date" name="from" id="" class="form-control" value="<?= $_GET['from'] ?? '' ?>">
                 </div>
                 <div class="col-md-2">
                    <label class="form-label fw-bold">To</label>
                    <input type="date" name="to" id="" class="form-control" value="<?= $_GET['to'] ?? '' ?>">
                 </div>
                 <div class="col-md-1 align-self-end">
                    <button class="btn btn-primary w-100">Go</button>
                 </div>
            </div>
         </form>

        <!-- Payment Table -->
            <?php
            // Build filters
                $query = "select p.*, b.hotel_id,b.guest_name,b.check_in,b.check_out,h.name as hotel_name
                from billing p,hotel h,bookings b
                where p.booking_id = b.booking_id and
                b.hotel_id = h.hotel_id
                and 1";
                
                // filter
                if(!empty($_GET['hotel_id'])){
                    $hotel_id = intval($_GET['hotel_id']);
                    $query.= " and b.hotel_id = $hotel_id";
                }
                if(!empty($_GET['type'])){
                    $type = mysqli_real_escape_string($conn, $_GET['type']);
                    $query.= " and p.payment_type = '$type'";
                }
                if(!empty($_GET['status'])){
                    $status = mysqli_real_escape_string($conn, $_GET['status']);
                    $query.= " and p.payment_status = '$status'";
                }
                if(!empty($_GET['from']) && !empty($_GET['to'])){
                    $from = $_GET['from'];
                    $to = $_GET['to'];
                    if($from > $to){
                        echo "<div class='alert alert-danger text-center'> Invalid date range</div>";
                        exit;
                    }
                    $query.= " and date(p.bill_date) between '$from' and '$to'";
                }
                $query.= " order by p.bill_date desc";
                $result = mysqli_query($conn,$query);                         
            ?>
        <h5 class="text-center mb-3">Payment Records</h5>
        <?php
            if($result && mysqli_num_rows($result)>0){
                echo "<div class='table-responsive'>
              <table class='table table-bordered table-striped align-middle'>
                <thead class='table-dark'>
                  <tr>
                    <th>Bill ID</th>
                    <th>Guest</th>
                    <th>Hotel</th>
                    <th>Room No.</th>
                    <th>Amount (₹)</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Bill Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>";
                $total =0;
                while($row = mysqli_fetch_assoc($result)){
                    $total += $row['total_amount'];
                    $statusColor = match ($row['payment_status']) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'unpaid' => 'danger',
                        default => 'secondary'
                    };

                    echo "<tr>
                        <td>{$row['bill_id']}</td>
                        <td>{$row['guest_name']}</td>
                        <td>{$row['hotel_name']}</td>
                        <td>{$row['room_number']}</td>
                        <td>₹{$row['total_amount']}</td>
                        <td>{$row['payment_type']}</td>
                        <td><span class='badge bg-$statusColor text-uppercase'>{$row['payment_status']}</span></td>
                        <td>{$row['bill_date']}</td>
                    </tr>";
                }
                echo "</tbody></table></div>";
                echo "<h5 class='text-end fw-bold mt-3>Total Revenue:  ₹$total</h5>";
            }else {
                echo "<div class='alert alert-warning text-center mt-3'>No payments found for this selection.</div>";
            }
        ?>
    </div>
</body>
</html>