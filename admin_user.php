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
        
        mysqli_query($conn,"DELETE FROM users WHERE id = $delete_id") or die('query failed');
        $message[]='Đã xóa tài khoản thành công';
        header('location:admin_user.php');
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
    <title>Admin User</title>
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
<!-- <div class="line4"></div> -->
    <section class="message-container">
        <h1 class="title">Tổng số tài khoản</h1>
        <div class="box-container">
            <?php
                $select_users = mysqli_query($conn, "SELECT * FROM `users`") or die('query failed');
                if(mysqli_num_rows($select_users) > 0){
                    while($fetch_users = mysqli_fetch_assoc($select_users)){

            ?>
            <div class="box">
                <p>Id người dùng: <span><?php echo $fetch_users['id']; ?></span></p>
                <p>Tên: <span><?php echo $fetch_users['name']; ?></span></p>
                <p>Email: <span><?php echo $fetch_users['email']; ?></span></p>
                <p>Loại tài khoản: <span style="color: <?php if($fetch_users['user_type']=='admin'){echo 'orange';};?>"><?php echo $fetch_users['user_type']; ?></span></p>
                <a class="btn-delete" href="admin_user.php?delete=<?php echo $fetch_users['id'];?>;" onclick="return confirm('Xóa tài khoản người dùng này');">Xóa</a>
            </div>
            <?php 
                    }
                }else{
                    echo'
                        <div class="empty">
                            <p>Chưa có người dùng được tạo</p>
                        </div>
                    ';            
                }
            ?>
        </div>
    </section>
    <!-- <div class="line"></div> -->
    <script src="script.js"></script>
</body>
</html>