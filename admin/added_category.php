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
    <title>Seraph Beauty - Danh Mục</title>
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





    <div class="makeup-container">
        <div class="product-grid">
            <?php
            // Hàm để hiển thị sản phẩm (category)
            function displayCategory($category_id, $category_name, $category_image)
            {
                echo '<div class="product">';
                echo '<a href="http://localhost/NLCSN/admin/added_subcategory.php?category_id=' . urlencode($category_id) . '">'; // Link tới trang category
                echo '<img src="../image/' . $category_image . '" alt="' . htmlspecialchars($category_name) . '">'; // Hiển thị hình ảnh
                echo '</a>';
                echo '<p>' . htmlspecialchars($category_name) . '</p>'; // Hiển thị tên danh mục
                echo '</div>';
            }

            // Query để lấy tất cả dữ liệu từ bảng categories
            $sql = "SELECT category_id, category_name, category_image FROM categories"; // Thêm category_id vào câu truy vấn
            $result = mysqli_query($conn, $sql);

            if ($result) {
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        displayCategory($row["category_id"], $row["category_name"], $row["category_image"]); // Hiển thị từng category
                    }
                } else {
                    echo "No categories found.";
                }
            } else {
                echo "Error executing query: " . mysqli_error($conn);
            }
            ?>
        </div>
    </div>



</html>