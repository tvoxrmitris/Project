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



                <a href="added_discount.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_products = mysqli_query($conn, "SELECT * FROM promotions") or die('Query failed');
                        $num_of_products = mysqli_num_rows($select_products);
                        ?>
                        <img src="../image/icons/listdiscount.png" alt="" style="width: 40px; height: 40px;">


                        <p>Danh sách mã giảm giá</p>
                        <h3><?php echo $num_of_products; ?></h3>
                    </div>
                </a>

                <a href="add_discount.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_products = mysqli_query($conn, "SELECT * FROM promotions") or die('Query failed');
                        $num_of_products = mysqli_num_rows($select_products);
                        ?>
                        <img src="../image/icons/adddiscount.png" alt=""
                            style="width: 40px; height: 40px; margin-top: 0.3rem;">


                        <p>Thêm giảm giá</p>
                        <img src="../image/icons/add.png" alt="" style="width: 30px; height: 30px;">

                    </div>
                </a>


                <a href="apply_promotion.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_products = mysqli_query($conn, "SELECT * FROM products WHERE quantity_in_stock > 0") or die('Query failed');
                        $num_of_products = mysqli_num_rows($select_products);
                        ?>
                        <img src="../image/icons/applydiscount.png" alt="" style="width: 40px; height: 40px;">


                        <p>Áp dụng mã giảm giá</p>
                        <img src="../image/icons/apply.png" alt="" style="width: 30px; height: 30px;">
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