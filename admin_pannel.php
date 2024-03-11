<?php
    include 'connection.php';
    session_start();
    $admin_id = $_SESSION['admin_name'];

    if(!isset($admin_id)){
        header('location:login.php');
    }

    if(isset($_POST['logout'])){
        session_destroy();
        header('location:login.php');
    }
?>
<style type="text/css">
    <?php
        include 'style.css'
    ?>
</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" type="text/css" href="style.css?v=1.1 <?php echo time();?>">
    <title>Admin pannel</title>
</head>
<body>
    <?php include 'admin_header.php';?>
    <?php
        if(isset($_POST[''])){
            foreach($message as $message){
                echo '
                    <div class="message">
                        <span>'.$message.'</span>
                        <i class="bi bi-x-circle" onclick="this.parentElement.remove()"></i>
                    </div>
                ';
            }
        }
    ?>
    <div class="line4"></div>
    <section class="dashboard">
        <div class="box-container">
            <div class="box">
                <?php
                    $total_pendings = 0;
                    $select_pendings = mysqli_query($conn, "SELECT * FROM `orders` WHERE payment_status = 'Đang xử lí'")
                        or die('query failed');
                    while($fetch_pending = mysqli_fetch_assoc($select_pendings)) {
                        $total_pendings += $fetch_pending['total_price'];
                    }
                        
                ?>
                <h3> <?php echo number_format($total_pendings, 0, '.', '.'); ?>VNĐ</h3>
                <p>Tổng đơn đang chờ xử lí</p>
            </div>

            <div class="box">
                <?php
                    $total_completes = 0;
                    $select_completes = mysqli_query($conn, "SELECT * FROM `orders` WHERE payment_status = 'Hoàn thành'")
                        or die('query failed');
                    while($fetch_completes = mysqli_fetch_assoc($select_completes)){
                        $total_completes += $fetch_completes['total_price'];
                    }
                ?>
                <h3><?php echo number_format($total_completes, 0, '.', '.'); ?>VNĐ</h3>
                <p>Tổng đơn đã xử lí</p>
            </div>

            <div class="box">
                <?php
                    $select_orders = mysqli_query($conn, "SELECT * FROM orders ") or die('query failed');
                    $num_of_orders = mysqli_num_rows($select_orders);
                ?>
                <h3><?php echo $num_of_orders; ?></h3>
                <p>Đơn hàng đã đặt</p>
            </div>

            <div class="box">
                <?php
                    $select_products = mysqli_query($conn, "SELECT * FROM products ") or die('query failed');
                    $num_of_products = mysqli_num_rows($select_products);
                ?>
                <h3><?php echo $num_of_products; ?></h3>
                <p>Sản phẩm đã thêm</p>
            </div>

            <div class="box">
                <?php
                    $select_users = mysqli_query($conn, "SELECT * FROM users WHERE user_type='user'") or die('query failed');
                    $num_of_users = mysqli_num_rows($select_users);
                ?>
                <h3><?php echo $num_of_users; ?></h3>
                <p>Tổng tài khoản thường</p>
            </div>

            <div class="box">
                <?php
                    $select_admins = mysqli_query($conn, "SELECT * FROM users WHERE user_type='admin'") or die('query failed');
                    $num_of_admins = mysqli_num_rows($select_admins);
                ?>
                <h3><?php echo $num_of_admins; ?></h3>
                <p>Tổng tài khoản quản trị</p>
            </div>

            <div class="box">
                <?php
                    $select_users = mysqli_query($conn, "SELECT * FROM users") or die('query failed');
                    $num_of_users = mysqli_num_rows($select_users);
                ?>
                <h3><?php echo $num_of_users; ?></h3>
                <p>Tổng tài khoản đã đăng ký</p>
            </div>

            <div class="box">
                <?php
                    $select_message = mysqli_query($conn, "SELECT * FROM message") or die('query failed');
                    $num_of_message = mysqli_num_rows($select_message);
                ?>
                <h3><?php echo $num_of_message; ?></h3>
                <p>Tin nhắn mới</p>
            </div>

        </div>

    </section>
    <script type="text/javascript" src="script.js"></script>
</body>
</html>