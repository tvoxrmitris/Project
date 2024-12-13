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

ob_start();  // Đặt ở đầu file PHP



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
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" type="text/css" href="../CSS/style.css?v=1.1 <?php echo time(); ?>">
    <title>Seraph Beauty - Chỉnh sửa Mã Màu</title>
</head>

<body>
    <?php include '../admin/admin_header.php'; ?>

    <div class="title">
        <h2 style="font-size:50px;">Chỉnh sửa</h2>
    </div>
    <div class="line2"></div>
    <?php
// Kiểm tra nếu có `code_color_id` trong URL
if (isset($_GET['code_color_id'])) {
    $code_color_id = $_GET['code_color_id'];

    // Truy vấn bảng code_color để lấy thông tin liên quan đến code_color_id
    $query = "SELECT cc.color_name, cc.color_code, p.product_name 
              FROM code_color cc 
              JOIN products p ON cc.product_id = p.product_id
              WHERE cc.code_color_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $code_color_id); // Gắn giá trị vào câu truy vấn
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Lấy kết quả và gán vào biến
        $row = $result->fetch_assoc();
        $color_name = htmlspecialchars($row['color_name']);
        $color_code = htmlspecialchars($row['color_code']);
        $product_name = htmlspecialchars($row['product_name']); // Lấy tên sản phẩm
    } else {
        // Nếu không tìm thấy, thông báo lỗi
        echo "Không tìm thấy mã màu.";
        exit;
    }
} else {
    // Nếu không có `code_color_id` trên URL, thông báo lỗi
    echo "Không có mã màu được chọn.";
    exit;
}

if (isset($_POST['add_color'])) {
    // Lấy dữ liệu từ form
    $new_color_name = $_POST['color_name'];
    $new_color_code = $_POST['color_code'];

    // Truy vấn cập nhật dữ liệu vào bảng code_color
    $update_query = "UPDATE code_color SET color_name = ?, color_code = ? WHERE code_color_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssi", $new_color_name, $new_color_code, $code_color_id);

    // Thực thi câu lệnh cập nhật
    if ($update_stmt->execute()) {
        // Kiểm tra số lượng bản ghi bị thay đổi
        if ($update_stmt->affected_rows > 0) {
            echo "Cập nhật mã màu thành công.";
            // Reload lại trang hiện tại sử dụng JavaScript
            echo "<script>window.location.reload();</script>";
            exit; // Dừng thực thi sau khi gọi JavaScript
        } else {
            echo "Cập nhật mã màu thành công.";
        }
    } else {
        echo "Lỗi khi cập nhật mã màu.";
    }
}

?>

    <section class="add-products form-container">
        <form method="POST" action="" enctype="multipart/form-data">
            <a style="font-size: 25px;" href="add_color.php" class="back-arrow">&#8592;</a>

            <div class="input-field">
                <label>Tên sản phẩm<span>*</span></label>
                <input type="text" name="product_name" value="<?php echo isset($product_name) ? $product_name : ''; ?>"
                    required readonly>
            </div>

            <div class="input-field">
                <label>Tên màu sắc<span>*</span></label>
                <input type="text" name="color_name" value="<?php echo isset($color_name) ? $color_name : ''; ?>"
                    required>
            </div>

            <div class="input-field">
                <label>Mã màu tương ứng<span>*</span></label>
                <input type="text" min="1" name="color_code"
                    value="<?php echo isset($color_code) ? $color_code : ''; ?>" required>
            </div>

            <input type="submit" name="add_color" value="Chỉnh sửa mã màu">
        </form>
    </section>







</body>








</html>