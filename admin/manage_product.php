<?php
include '../connection/connection.php';
// Đảm bảo không có khoảng trắng, ký tự hoặc output nào trước khi gọi session_start()
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


                <a href="admin_warehouse.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_products = mysqli_query($conn, "SELECT * FROM inventory_entries") or die('Query failed');
                        $num_of_products = mysqli_num_rows($select_products);
                        ?>
                        <img src="../image/icons/productinventory.png" alt="" style="width: 40px; height: 40px;">

                        <p>Nhập kho</p>
                        <img src="../image/icons/add.png" alt="" style="width: 40px; height: 40px;">
                    </div>
                </a>
                <a href="add_product.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_products = mysqli_query($conn, "SELECT * FROM inventory_entries") or die('Query failed');
                        $num_of_products = mysqli_num_rows($select_products);
                        ?>
                        <img src="../image/icons/productinventory.png" alt="" style="width: 40px; height: 40px;">

                        <p>Thêm sản phẩm</p>
                        <img src="../image/icons/add.png" alt="" style="width: 40px; height: 40px;">
                    </div>
                </a>



                <a href="product_inventory.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_products = mysqli_query($conn, "SELECT * FROM inventory_entries") or die('Query failed');
                        $num_of_products = mysqli_num_rows($select_products);
                        ?>
                        <img src="../image/icons/productinventory.png" alt="" style="width: 40px; height: 40px;">

                        <p>Sản phẩm tồn kho</p>
                        <h3><?php echo $num_of_products; ?></h3>
                    </div>
                </a>


                <a href="view_product_added.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_products = mysqli_query($conn, "SELECT * FROM products WHERE quantity_in_stock > 0") or die('Query failed');
                        $num_of_products = mysqli_num_rows($select_products);
                        ?>
                        <img src="../image/icons/addedproduct.png" alt="" style="width: 40px; height: 40px;">

                        <p>Sản phẩm đã thêm</p>
                        <h3><?php echo $num_of_products; ?></h3>
                    </div>
                </a>



                <a href="outstock.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_products = mysqli_query($conn, "SELECT * FROM products WHERE quantity_in_stock <= 15") or die('Query failed');
                        $num_of_products = mysqli_num_rows($select_products);
                        ?>
                        <img src="../image/icons/outstockproduct.png" alt="" style="width: 40px; height: 40px;">

                        <p>Sản phẩm sắp hết hàng</p>
                        <h3><?php echo $num_of_products; ?></h3>
                    </div>
                </a>




                <a href="detail_import.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_products = mysqli_query($conn, "SELECT * FROM detail_import") or die('Query failed');
                        $num_of_products = mysqli_num_rows($select_products);
                        ?>
                        <img src="../image/icons/productinventory.png" alt="" style="width: 40px; height: 40px;">

                        <p>Chi tiết nhập hàng</p>
                        <h3><?php echo $num_of_products; ?></h3>
                    </div>
                </a>

                <a href="detail_export.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_products = mysqli_query($conn, "SELECT * FROM detail_import_product") or die('Query failed');
                        $num_of_products = mysqli_num_rows($select_products);
                        ?>
                        <img src="../image/icons/productinventory.png" alt="" style="width: 40px; height: 40px;">

                        <p>Chi tiết xuất hàng</p>
                        <h3><?php echo $num_of_products; ?></h3>
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