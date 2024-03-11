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

    

    //delete product from database
    if(isset($_GET['delete'])){
        $delete_id = $_GET['delete'];
        
        mysqli_query($conn,"DELETE FROM `orders` WHERE id = $delete_id") or die('query failed');
        $message[]='Đơn hàng đã được xóa thành công';
        header('location:admin_order.php');
    }

    //updateig payment status
    if(isset( $_POST['update_order'])){
        $order_id = $_POST['order_id'];
        $update_payment = $_POST['update-payment'];

        mysqli_query($conn, "UPDATE `orders` SET payment_status = '$update_payment' WHERE id=$order_id") or die('query failed');
    }

?>
<style type="text/css">
    <?php
        include 'style.css';
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
    <title>Admin Orders</title>
</head>
<body>
    <?php include 'admin_header.php';?>
    <?php
        if(isset($message)){
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
    <section class="order-container">
        <h1 class="title">Tổng tài khoản đơn hàng</h1>
        <div class="box-container">
            <?php
                $select_orders = mysqli_query($conn, "SELECT * FROM orders") or die('query failed');
                if(mysqli_num_rows($select_orders) > 0){
                    while($fetch_orders = mysqli_fetch_assoc($select_orders)){

            ?>
            <div class="box">
                <p>Tên người dùng: <span><?php echo $fetch_orders['name']; ?></span></p>
                <p>Id người dùng: <span><?php echo $fetch_orders['user_id']; ?></span></p>
                <p>Đăt trên: <span><?php echo $fetch_orders['placed_on']; ?></span></p>
                <p>Số điện thoại: <span><?php echo $fetch_orders['number']; ?></span></p>
                <p>email: <span><?php echo $fetch_orders['email']; ?></span></p>
                <p>Tổng giá tiền: <span><?php echo $fetch_orders['total_price']; ?></span></p>
                <p>Phương thức thanh toán: <span><?php echo $fetch_orders['method']; ?></span></p>
                <p>Địa chỉ: <span><?php echo $fetch_orders['address']; ?></span></p>
                <p>Tổng số lượng sản phẩm: <span><?php echo $fetch_orders['total_products']; ?></span></p>
                <form method="post">
                    <input type="hidden" name="order_id" value="<?php echo $fetch_orders['id'];?>">
                    <select name="update-payment">
                        <option disabled selected><?php echo $fetch_orders['payment_status'];?></option>
                        <option value="Đang xử lí">Đang xử lí</option>
                        <option value="Hoàn thành">Hoàn thành</option>

                    </select>
                    <!-- <input type="submit" name="update_order" value="update_payment" class="btn"> -->
                    <input type="submit" name="update_order" value="Cập nhật" class="btn-update-payment">
                    <a class="btn-delete" href="admin_order.php?delete=<?php echo $fetch_orders['id'];?>;" onclick="return confirm('Xóa đơn hàng này');">Xóa</a>
                    
                </form>
                
            </div>
            <?php 
                    }
                }else{
                    echo'
                        <div class="empty">
                            <p>Chưa có đơn hàng nào được đặt!</p>
                        </div>
                    ';            
                }
            ?>
        </div>
    </section>
    <div class="line"></div>
    <script src="script.js"></script>
</body>
</html>