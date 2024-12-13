<?php
include '../connection/connection.php';
session_start();

// Kiểm tra xem người dùng đã đăng nhập và có quyền là 'super admin' hay không
if (!isset($_SESSION['employee_email']) || $_SESSION['employee_type'] !== 'super admin') {
    header('location:../components/admin_login.php');
    exit;
}

// Lấy email từ session để tìm thông tin người dùng
$employee_email = $_SESSION['employee_email'];

// Truy vấn để lấy thông tin từ bảng employees
$query = "SELECT * FROM employees WHERE employee_email = '$employee_email' LIMIT 1";
$result = mysqli_query($conn, $query);

// Kiểm tra nếu có kết quả
if (mysqli_num_rows($result) > 0) {
    // Lấy dữ liệu của người dùng
    $fetch_user = mysqli_fetch_assoc($result);
} else {
    // Nếu không tìm thấy người dùng
    $fetch_user = [
        'employee_name' => 'Không xác định',
        'employee_email' => 'Không xác định',
        'employee_number' => 'Không xác định',
        'employee_address' => 'Không xác định',
        'employee_type' => 'Không xác định'
    ];
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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css"
        integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css"
        integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js"
        integrity="sha512-HGOnQO9+SP1V92SrtZfjqxxtLmVzqZpjFFekvzZVWoiASSQgSr4cw9Kqd2+l8Llp4Gm0G8GIFJ4ddwZilcdb8A=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" type="text/css" href="../CSS/style.css?v=1.1 <?php echo time(); ?>">
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seraph Beauty - Hồ Sơ</title>
</head>

<body>
    <?php include '../admin/admin_header.php'; ?>

    <div class="about-us">
        <div class="row">
            <div class="user-box">
                <div class="img-box">
                    <img class="imgshop" src="../image/seraphh.png" alt="User Image">
                </div>
                <div class="detail">
                    <p><strong style="font-size: 30px; font-weight: bold;">Hồ Sơ</strong></p>
                    <p>Tên tài khoản: <span><?php echo $fetch_user['employee_name']; ?></span></p>
                    <p>Email: <span><?php echo $fetch_user['employee_email']; ?></span></p>
                    <p>Số điện thoại: <span><?php echo $fetch_user['employee_number']; ?></span></p>
                    <p>Địa chỉ: <span><?php echo $fetch_user['employee_address']; ?></span></p>
                    <p>Loại tài khoản: <span><?php echo $fetch_user['employee_type']; ?></span></p>
                    <form method="post" action="reset_profile.php" class="btn-container">
                        <!-- <button type="submit" name="update" class="update-btn">Chỉnh sửa</button> -->
                    </form>
                </div>
            </div>
        </div>
    </div>




    <script>
    $('.img-about').slick({
        infinite: true,
        autoplay: true,
        autoplaySpeed: 2000,
        lazyLoad: 'ondemand',
        slidesToShow: 1,
        adaptiveHeight: true
    });
    </script>

    <script type="text/javascript" src="../js/script2.js"></script>
</body>

</html>