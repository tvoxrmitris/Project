<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- box icon link -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" type="text/css" href="style.css?v=1.1 <?php echo time();?>">

    <title>Admin header</title>
</head>
<body>
    <header class="header">
        <div class = "flex">
            <!-- <a href="admin_pannel.php" class ="logo"><img src="./image/logo.png"></a> -->
            <img src="./image/logo1.png" width="140">
            <nav class = "navbar">
                <a href="admin_pannel.php">Trang chủ</a>
                <a href="admin_product.php">Sản phẩm</a>
                <a href="admin_order.php">Đơn hàng</a>
                <a href="admin_user.php">Người dùng</a>
                <a href="admin_message.php">Tin nhắn</a>
                <a href="admin_brands.php">Thương hiệu</a>
                <a href="admin_categories.php">Danh mục</a>
            </nav>
            <div class="icons">
                <i class="bi bi-person" id="user-btn"></i>
                <i class="bi bi-list" id="menu-btn"></i>
            </div>

            <div class="user-box">
                <p>Tên tài khoản: <span><?php echo $_SESSION['admin_name']; ?></span></p>
                <p>Email: <span><?php echo $_SESSION['admin_email']; ?></span></p>
                <form method="post">
                    <button type="submit" name="logout" class="logout-btn">Đăng xuất</button>
                </form>
            </div>
        </div>
    </header>
    <div class="banner">
        <div class="detail">
            <h1>Trang quản trị viên</h1>
            <p>Đôi mắt là ngôn từ của trái tim.</p>
        </div>
    </div>
    <div class="line">

    </div>
    <script src="script.js"></script>
    
</body>
</html>