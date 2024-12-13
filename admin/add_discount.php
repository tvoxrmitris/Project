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


if (isset($_POST['create_discount'])) {
    // Lấy dữ liệu từ form
    $code_discount = $_POST['code_discount'];
    $discount_percent = $_POST['discount_percent'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $usage_limit = $_POST['usage_limit'];


    // Truy vấn để insert dữ liệu vào bảng promotions
    $query = "INSERT INTO promotions (code_discount, discount_percent, start_date, end_date, usage_limit) 
                  VALUES ('$code_discount', $discount_percent, '$start_date', '$end_date', $usage_limit)";

    if (mysqli_query($conn, $query)) {
        echo "Thêm giảm giá thành công!";
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

.back-arrow {
    text-decoration: none;
    font-size: 1.5rem;
    color: #333;
    margin-bottom: 5rem !important;
    z-index: 10;
}

.back-arrow:hover {
    color: #000;
    /* Màu sắc khi hover */
    cursor: pointer;
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
        <h2 style="font-size:50px;">Tạo giảm giá</h2>
    </div>

    <section class="add-products form-container">


        <form method="POST" action="" enctype="multipart/form-data">
            <a href="admin_discount.php" class="back-arrow"
                style="text-decoration: none; font-size: 1.5rem; ">&#8592;</a>

            <div class="input-field">
                <label>Mã giảm giá<span>*</span></label>
                <input type="text" name="code_discount" required>
            </div>
            <div class="input-field">
                <label>Phần trăm giảm<span>*</span></label>
                <input type="number" name="discount_percent" required>
            </div>
            <div class="input-field">
                <label>Ngày bắt đầu<span>*</span></label>
                <input type="date" name="start_date" required>
            </div>

            <div class="input-field">
                <label>Ngày kết thúc<span>*</span></label>
                <input type="date" name="end_date" required>
            </div>


            <div class="input-field">
                <label>Số lượng áp dụng<span>*</span></label>
                <input type="number" name="usage_limit" required>
            </div>










            <div class="action-buttons">
                <input type="submit" name="create_discount" value="Thêm giảm giá">
                <!-- <button type="button" onclick="window.location.href='admin_discount.php';"
                    class="cancel-button">Hủy</button> -->
            </div>


        </form>
    </section>



</html>