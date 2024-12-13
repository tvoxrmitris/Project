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

<style type="text/css">
    <?php include '../CSS/style.css';
    ?>
</style>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="../CSS/style.css?v=1.1 <?php echo time(); ?>">
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <title>Seraph Beauty - Danh Mục Phụ</title>
</head>

<body>
    <?php include '../admin/admin_header.php'; ?>
    <?php
    if (isset($message)) {
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
    <div class="title">
        <h2 style="font-size:50px;">Sản phẩm đã thêm</h2>
    </div>

    <div class="added-subcategory">
        <div class="product-grid">
            <?php
            // Nhận category_id từ URL
            if (isset($_GET['category_id'])) {
                // Lấy category_id từ URL
                $category_id = intval($_GET['category_id']); // Chuyển đổi thành số nguyên để bảo mật

                // Truy vấn để lấy các subcategory dựa trên category_id
                $sql = "SELECT subcategory_name, subcategory_image FROM subcategory WHERE category_id = ?"; // Giả sử bạn có cột category_id trong bảng subcategory
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, 'i', $category_id); // Sử dụng 'i' cho kiểu dữ liệu integer
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Kiểm tra và hiển thị các subcategory
                if ($result && mysqli_num_rows($result) > 0) {
                    echo '<div class="subcategory-grid">';
                    while ($row = mysqli_fetch_assoc($result)) {
                        $subcategory_name = htmlspecialchars($row['subcategory_name']);
                        echo '<div class="product">';
                        echo '<a href="view_product_added.php?subcategory_name=' . urlencode($subcategory_name) . '">';
                        echo '<img src="../image/' . htmlspecialchars($row['subcategory_image']) . '" alt="' . $subcategory_name . '">';
                        echo '<p>' . $subcategory_name . '</p>';
                        echo '</a>';
                        echo '</div>';
                    }
                    echo '</div>';
                } else {
                    echo "No subcategories found for this category.";
                }

                // Đóng kết nối
                mysqli_close($conn);
            } else {
                echo "No category selected.";
            }
            ?>
        </div>




</html>