<?php
include '../connection/connection.php';
session_start();

if (!isset($_SESSION['employee_id']) || $_SESSION['employee_type'] !== 'NVNK') {
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
?>
</style>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap');

/* Áp dụng font Poppins cho toàn bộ trang */
body {
    font-family: 'Gucci Sans:', sans-serif;
}

.blur {
    filter: blur(5px);
    transition: filter 0.3s ease;
}

.box p,
.box h3 {
    color: black;
    /* Màu đen */
}

body {
    background-color: #f9f1f1;
}

.warehouse h3 {
    font-style: italic;
    color: #bd0100;
    /* Màu chữ đen */
    font-weight: bold;
    text-transform: uppercase;
    font-family: 'Helvetica', sans-serif;
    letter-spacing: 1px;
    /* Khoảng cách giữa các chữ */
    text-align: center;
    /* Căn giữa */
    padding: 10px 15px;
    /* Padding nhỏ gọn */
    transition: all 0.3s ease-in-out;
    position: relative;
    font-size: 18px;
    /* Kích thước chữ */
    animation: importantNotice 3s infinite alternate;
    /* Áp dụng animation */
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
    <?php include '../NVNK/NVNK_header.php'; ?>
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










                <a href="warehouse.php" style="text-decoration: none;" class="warehouse">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_products = mysqli_query($conn, "SELECT * FROM low_quantity_stock") or die('Query failed');
                        $num_of_products = mysqli_num_rows($select_products);
                        ?>
                        <img src="../image/icons/productinventory.png" alt="" style="width: 40px; height: 40px;">

                        <p>Nhập kho</p>
                        <h3><?php echo $num_of_products; ?></h3>
                    </div>
                </a>

                <a href="update_product.php" style="text-decoration: none;" class="warehouse">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_products = mysqli_query($conn, "SELECT * FROM low_stock_requests") or die('Query failed');
                        $num_of_products = mysqli_num_rows($select_products);
                        ?>
                        <img src="../image/icons/productinventory.png" alt="" style="width: 40px; height: 40px;">

                        <p>Xuất kho</p>
                        <h3><?php echo $num_of_products; ?></h3>
                    </div>
                </a>


                <a href="add_product.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_discount = mysqli_query($conn, "SELECT * FROM promotions") or die('Query failed');
                        $num_of_discount = mysqli_num_rows($select_discount);
                        ?>
                        <img src="../image/icons/addedproduct.png" alt="" style="width: 40px; height: 40px;">

                        <p>Thêm sản phẩm</p>
                        <img src="../image/icons/add.png" alt="" style="width: 40px; height: 40px; margin-top: 0.3rem;">
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