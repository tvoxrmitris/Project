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

                <a href="profile_admin.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_profile = mysqli_query($conn, "SELECT * FROM employees WHERE employee_email='$employee_email'") or die('query failed');
                        $num_of_profile = mysqli_num_rows($select_profile);
                        ?>
                        <img src="../image/icons/viewprofile.png" alt="" style="width: 40px; height: 40px;">

                        <p>Xem hồ sơ</p>
                        <h3><?php echo $num_of_profile; ?></h3>
                    </div>
                </a>

                <a href="admin_order.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_order = mysqli_query($conn, "SELECT * FROM orders") or die('query failed');
                        $num_of_order = mysqli_num_rows($select_order);
                        ?>
                        <img src="../image/icons/order.png" alt="" style="width: 40px; height: 40px;">

                        <p>Tổng đơn hàng</p>
                        <h3><?php echo $num_of_order; ?></h3>
                    </div>
                </a>

                <a href="manage_product.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_products = mysqli_query($conn, "SELECT * FROM inventory_entries") or die('Query failed');
                        $num_of_products = mysqli_num_rows($select_products);
                        ?>
                        <img src="../image/icons/box.png" alt="" style="width: 40px; height: 40px;">

                        <p>Quản lí sản phẩm</p>
                        <h3><?php echo $num_of_products; ?></h3>
                    </div>
                </a>





                <a href="admin_discount.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_discount = mysqli_query($conn, "SELECT * FROM promotions") or die('Query failed');
                        $num_of_discount = mysqli_num_rows($select_discount);
                        ?>
                        <img src="../image/icons/discount.png" alt="" style="width: 40px; height: 40px;">

                        <p>Giảm giá</p>
                        <h3><?php echo $num_of_discount; ?></h3>
                    </div>
                </a>


                <a href="admin_brands.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_brand = mysqli_query($conn, "SELECT * FROM brands") or die('query failed');
                        $num_of_brand = mysqli_num_rows($select_brand);
                        ?>
                        <img src="../image/icons/brand.png" alt="" style="width: 40px; height: 40px;">

                        <p>Thương hiệu</p>
                        <h3><?php echo $num_of_brand; ?></h3>
                    </div>
                </a>

                <a href="admin_categories.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_category = mysqli_query($conn, "SELECT * FROM categories") or die('query failed');
                        $num_of_category = mysqli_num_rows($select_category);
                        ?>
                        <img src="../image/icons/category.png" alt="" style="width: 40px; height: 40px;">

                        <p>Danh mục chính</p>
                        <h3><?php echo $num_of_category; ?></h3>
                    </div>
                </a>

                <a href="admin_subcategory.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_subcategory = mysqli_query($conn, "SELECT * FROM subcategory") or die('query failed');
                        $num_of_subcategory = mysqli_num_rows($select_subcategory);
                        ?>
                        <img src="../image/icons/subcategory.png" alt="" style="width: 40px; height: 40px;">

                        <p>Danh mục phụ</p>
                        <h3><?php echo $num_of_subcategory; ?></h3>
                    </div>
                </a>

                <a href="add_tag.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_tag = mysqli_query($conn, "SELECT * FROM tags") or die('query failed');
                        $num_of_tag = mysqli_num_rows($select_tag);
                        ?>
                        <img src="../image/icons/tag.png" alt="" style="width: 40px; height: 40px;">

                        <p>Nhãn</p>
                        <h3><?php echo $num_of_tag; ?></h3>
                    </div>
                </a>

                <a href="revenue_date.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">

                        <img src="../image/icons/revenue.png" alt="" style="width: 40px; height: 40px;">

                        <p>Thống kê doanh thu</p>
                        <img src="../image/icons/revenue1.png" alt="" style="width: 40px; height: 40px;">
                    </div>
                </a>

                <a href="manage_user.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_users = mysqli_query($conn, "SELECT * FROM users") or die('query failed');
                        $num_of_users = mysqli_num_rows($select_users);
                        ?>
                        <img src="../image/icons/useraccount.png" alt="" style="width: 40px; height: 40px;">

                        <p>Quản lí người dùng</p>
                        <h3><?php echo $num_of_users; ?></h3>
                    </div>
                </a>

                <a href="add_color.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_users = mysqli_query($conn, "SELECT * FROM code_color") or die('query failed');
                        $num_of_users = mysqli_num_rows($select_users);
                        ?>
                        <img src="../image/icons/add.png" alt="" style="width: 40px; height: 40px;">

                        <p>Thêm mã màu</p>
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