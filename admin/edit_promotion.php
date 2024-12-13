<?php
// Kết nối cơ sở dữ liệu và bắt đầu phiên
include '../connection/connection.php';
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

// Kiểm tra xem có yêu cầu cập nhật không
if (isset($_POST['update_discount'])) {
    // Lấy dữ liệu từ form
    $promotion_id = $_GET['promotion_id']; // Lấy promotion_id từ URL
    $code_discount = $_POST['code_discount'];
    $discount_percent = $_POST['discount_percent'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $usage_limit = $_POST['usage_limit'];

    // Truy vấn để cập nhật dữ liệu vào bảng promotions
    $query = "UPDATE promotions SET 
                code_discount = '$code_discount', 
                discount_percent = $discount_percent, 
                start_date = '$start_date', 
                end_date = '$end_date', 
                usage_limit = $usage_limit 
              WHERE promotion_id = $promotion_id";

    if (mysqli_query($conn, $query)) {
        echo "Cập nhật giảm giá thành công!";
    } else {
        echo "Lỗi: " . mysqli_error($conn);
    }
}
?>

<style type="text/css">
<?php include '../CSS/style.css';

?>.table-container {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

img {
    width: 100px;
    /* Kích thước hình ảnh */
    height: auto;
}

.star-filled {
    color: gold;
    /* Màu sao đầy */
}

.star-empty {
    color: lightgray;
    /* Màu sao rỗng */
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
    <title>Seraph Beauty - Sản Phẩm Đã Thêm</title>
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
        <h2 style="font-size:50px;">Cập nhật giảm giá</h2>
    </div>

    <?php
    // Lấy promotion_id từ URL
    if (isset($_GET['promotion_id'])) {
        $promotion_id = $_GET['promotion_id'];

        // Truy vấn để lấy dữ liệu từ bảng promotions
        $query = "SELECT * FROM promotions WHERE promotion_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $promotion_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Kiểm tra xem có dữ liệu không
        if ($result->num_rows > 0) {
            $promotion = $result->fetch_assoc();
        } else {
            echo "Không tìm thấy khuyến mãi.";
            exit;
        }
    } else {
        echo "promotion_id không hợp lệ.";
        exit;
    }
    ?>

    <section class="add-products form-container">

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="input-field">
                <label>Mã giảm giá<span>*</span></label>
                <input type="text" name="code_discount" required
                    value="<?php echo htmlspecialchars($promotion['code_discount']); ?>">
            </div>
            <div class="input-field">
                <label>Phần trăm giảm<span>*</span></label>
                <input type="number" name="discount_percent" required
                    value="<?php echo htmlspecialchars($promotion['discount_percent']); ?>">
            </div>
            <div class="input-field">
                <label>Ngày bắt đầu<span>*</span></label>
                <input type="date" name="start_date" required
                    value="<?php echo htmlspecialchars($promotion['start_date']); ?>">
            </div>
            <div class="input-field">
                <label>Ngày kết thúc<span>*</span></label>
                <input type="date" name="end_date" required
                    value="<?php echo htmlspecialchars($promotion['end_date']); ?>">
            </div>
            <div class="input-field">
                <label>Số lượng áp dụng<span>*</span></label>
                <input type="number" name="usage_limit" required
                    value="<?php echo htmlspecialchars($promotion['usage_limit']); ?>">
            </div>

            <div class="action-buttons">
                <input type="submit" name="update_discount" value="Cập nhật giảm giá">
                <!-- <button type="button" onclick="window.location.href='added_discount.php';"
                    class="cancel-button">Hủy</button> -->
            </div>
        </form>
    </section>
</body>

</html>