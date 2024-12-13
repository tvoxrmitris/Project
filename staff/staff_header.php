<?php
// Kết nối đến cơ sở dữ liệu
include '../connection/connection.php';
session_start();

if (!isset($_SESSION['employee_id']) || $_SESSION['employee_type'] !== 'staff') {
    header('location:../components/admin_login.php');
    exit;
}

$employee_id = $_SESSION['employee_id']; // Lấy employee_id từ session
$employee_name = "Không xác định";
$employee_email = "Không xác định";

// Truy vấn thông tin nhân viên từ bảng employees
$query = "SELECT employee_name, employee_email FROM employees WHERE employee_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $employee_name = $row['employee_name'];
    $employee_email = $row['employee_email'];
}

// Xử lý đăng xuất
if (isset($_POST['logout'])) {
    session_destroy();
    header('location:../components/admin_login.php');
    exit;
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


                <a href="../staff/order.php">Đơn hàng</a>
                <i class="close-btn bi bi-x"></i>
            </nav>
            <div class="icons">
                <i class="bi bi-person" id="user-btn"></i>
                <i class="bi bi-list" id="menu-btn"></i>
            </div>
            <div class="user-box" style="border: 2px solid black;">
                <p>Tên tài khoản: <span><?php echo $employee_name; ?></span></p>
                <p>Email: <span><?php echo $employee_email; ?></span></p>
                <form method="post" class="btn-container">
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