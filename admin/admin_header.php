<?php
// Kết nối đến cơ sở dữ liệu
include $_SERVER['DOCUMENT_ROOT'] . '/NLCSN/connection/connection.php';



// Kiểm tra xem người dùng đã đăng nhập chưa (email lưu trong session)
if (isset($_SESSION['employee_email'])) {
    $employee_email = $_SESSION['employee_email'];

    // Truy vấn bảng employees để lấy thông tin người dùng
    $query = "SELECT * FROM employees WHERE employee_email = '$employee_email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    // Kiểm tra xem có người dùng nào tồn tại với email này không
    if (mysqli_num_rows($result) > 0) {
        $employee = mysqli_fetch_assoc($result);
        $user_name = $employee['employee_name'];  // Lấy tên người dùng
        $user_email = $employee['employee_email'];  // Lấy email người dùng
    } else {
        // Nếu không tìm thấy email trong cơ sở dữ liệu
        $user_name = 'Không xác định';
        $user_email = 'Không xác định';
    }
} else {
    // Nếu chưa đăng nhập
    $user_name = 'Không xác định';
    $user_email = 'Không xác định';
}
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" type="text/css" href="../CSS/style.css?v=1.1 <?php echo time(); ?>">
</head>
<style>
@import url('https://fonts.googleapis.com/css2?family=Barlow:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Lora:ital,wght@0,400..700;1,400..700&family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap');
</style>

<body>
    <div class="line"></div>

    <header class="header">
        <div class="flex">
            <img src="../image/seraph.png" width="100">
            <nav class="navbar">
                <a href="../admin/admin_pannel.php">Trang chủ</a>
                <a href="../admin/admin_warehouse.php">Nhập hàng</a>
                <a href="../admin/add_product.php">Thêm sản phẩm</a>
                <a href="../admin/admin_order.php">Đơn hàng</a>
                <a href="../admin/admin_user.php">Người dùng</a>

                <i class="close-btn bi bi-x"></i>
            </nav>
            <div class="icons">
                <i class="bi bi-person" id="user-btn"></i>
                <i class="bi bi-list" id="menu-btn"></i>
            </div>
            <div class="user-box" style="border: 2px solid black;">
                <p>Tên tài khoản: <span><?php echo $user_name; ?></span></p>
                <p>Email: <span><?php echo $user_email; ?></span></p>

                <form method="post" class="btn-container">
                    <button type="submit" name="profile" class="profile-btn">Hồ sơ</button>
                    <button type="submit" name="logout" class="logout-btn">Đăng xuất</button>
                </form>
            </div>

        </div>
    </header>

    <script>
    // Sự kiện khi bấm vào nút mở menu
    document.getElementById('menu-btn').addEventListener('click', function() {
        const navbar = document.querySelector('.navbar');
        const contentWrapper = document.getElementById('content-wrapper');

        // Chuyển trạng thái navbar
        navbar.classList.toggle('active');

        // Điều chỉnh margin cho content wrapper dựa trên trạng thái của navbar
        if (navbar.classList.contains('active')) {
            contentWrapper.style.marginLeft = '350px'; // Đặt margin khi navbar mở
        } else {
            contentWrapper.style.marginLeft = '0'; // Khôi phục margin khi navbar đóng
        }
    });

    // Sự kiện cho nút đóng
    document.querySelector(".close-btn").addEventListener("click", function() {
        const navbar = document.querySelector('.navbar');
        const contentWrapper = document.getElementById('content-wrapper');

        // Đóng navbar
        navbar.classList.remove('active');

        // Lấy giá trị hiện tại của margin-left và cộng thêm 350px khi nhấn nút close
        const currentMarginLeft = parseInt(contentWrapper.style.marginLeft) || 0;
        contentWrapper.style.marginLeft = (currentMarginLeft + 350) + 'px'; // Tăng thêm 350px
    });

    // Chuyển đổi hiển thị của user box
    document.getElementById('user-btn').addEventListener('click', function() {
        const userBox = document.querySelector('.user-box');
        userBox.style.display = userBox.style.display === 'block' ? 'none' : 'block';
    });
    </script>

    <script>
    document.getElementById('menu-btn').addEventListener('click', function() {
        const navbar = document.querySelector('.navbar');
        const contentWrapper = document.getElementById('content-wrapper');

        // Chỉ thay đổi trạng thái thu nhỏ hoặc mở rộng navbar khi bấm vào nút
        if (navbar.classList.contains('active')) {
            navbar.classList.remove('active');
            contentWrapper.style.marginLeft = '350px'; // Giữ khoảng cách khi menu ở trạng thái bình thường
        } else {
            navbar.classList.add('active');
            contentWrapper.style.marginLeft = '350px'; // Giữ khoảng cách cố định cho content-wrapper
        }
    });

    // Thêm sự kiện cho nút đóng (close-btn)
    document.querySelector(".close-btn").addEventListener("click", function() {
        const navbar = document.querySelector('.navbar');
        const contentWrapper = document.getElementById('content-wrapper');

        navbar.classList.remove('active'); // Ẩn navbar
        navbar.classList.remove('active');
        contentWrapper.style.marginLeft = '350px'; // Giữ margin cố định
    });
    </script>

    <script src="../js/script.js"></script>
</body>

</html>