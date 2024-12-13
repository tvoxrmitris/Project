<?php
include '../connection/connection.php';
session_start();
if (!isset($_SESSION['employee_email']) || $_SESSION['employee_type'] !== 'super admin') {
    header('location:../components/admin_login.php');
    exit;
}



// Xử lý đăng xuất
if (isset($_POST['logout'])) {
    session_destroy();
    header('location:../components/admin_login.php');
    exit;
}
?>
<style type="text/css">
    <?php include '../CSS/style.css'

    ?>.box p,
    .box h3 {
        color: black;
        /* Màu đen */
    }
</style>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Barlow:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Lora:ital,wght@0,400..700;1,400..700&family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap');
</style>
<style>
    /* CSS của bạn ở đây */
    .blur {
        filter: blur(5px);
        transition: filter 0.3s ease;
    }
</style>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" type="text/css" href="../CSS/style.css?v=1.1 <?php echo time(); ?>">
    <title>Seraph Beauty - Trang Chủ</title>
</head>

<body>
    <?php include '../admin/admin_header.php'; ?>
    <?php
    if (isset($_POST[''])) {
        foreach ($message as $message) {
            echo '
                    <div class="message">
                        <span>' . $message . '</span>
                        <i class="bi bi-x-circle" onclick="this.parentElement.remove()"></i>
                    </div>
                ';
        }
    }
    ?>






    <div id="content-wrapper">




        <section class="dashboard">
            <div class="box-container">
                <!-- Box Thêm người dùng -->
                <a href="admin_register.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        // Tính số lượng các chương trình khuyến mãi (nếu cần, có thể sửa lại câu query)
                        $select_promotions = mysqli_query($conn, "SELECT * FROM promotions") or die('Query failed');
                        $num_of_promotions = mysqli_num_rows($select_promotions);
                        ?>
                        <img src="../image/icons/useraccount.png" alt="" style="width: 40px; height: 40px;">
                        <p>Thêm người dùng</p>
                        <img src="../image/icons/add.png" alt="" style="width: 30px; height: 30px;">
                    </div>
                </a>

                <!-- Box Tài khoản nhân viên -->
                <a href="admin_staff.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        // Tính số lượng tài khoản nhân viên từ bảng employees
                        $select_employees = mysqli_query($conn, "SELECT * FROM employees WHERE employee_type != 'super admin'") or die('Query failed');
                        $num_of_employees = mysqli_num_rows($select_employees);
                        ?>
                        <img src="../image/icons/useraccount.png" alt=""
                            style="width: 40px; height: 40px; margin-top: 0.3rem;">
                        <p>Tài khoản nhân viên</p>
                        <h3><?php echo $num_of_employees; ?></h3>
                    </div>
                </a>

                <!-- Box Tài khoản người dùng -->
                <a href="admin_user.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        // Tính số lượng tài khoản người dùng từ bảng users
                        $select_users = mysqli_query($conn, "SELECT * FROM users") or die('Query failed');
                        $num_of_users = mysqli_num_rows($select_users);
                        ?>
                        <img src="../image/icons/useraccount.png" alt="" style="width: 40px; height: 40px;">
                        <p>Tài khoản người dùng</p>
                        <h3><?php echo $num_of_users; ?></h3>
                    </div>
                </a>
            </div>
        </section>

    </div>




    <script>
        document.getElementById("myForm").onsubmit = function() {
            window.location = this.action;
            return false;
        };
    </script>
    <script type="text/javascript" src="../js/script.js"></script>
</body>

</html>