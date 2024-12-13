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

// Lấy dữ liệu từ bảng promotions
$select_promotions = mysqli_query($conn, "
    SELECT code_discount, discount_percent, start_date, end_date, usage_limit, created_at
    FROM promotions
") or die('Truy vấn thất bại: ' . mysqli_error($conn));

// Khởi tạo biến đếm cho số thứ tự
$count = 1;
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
</style>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" type="text/css" href="../CSS/style.css?v=1.1 <?php echo time(); ?>">
    <title>Seraph Beauty - Khuyến mãi</title>
</head>

<body>
    <?php include '../admin/admin_header.php'; ?>
    <div class="title">
        <h2 style="font-size:50px;">Danh sách Khuyến mãi</h2>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Số thứ tự</th>
                    <th>Mã giảm giá</th>
                    <th>Phần trăm giảm giá</th>
                    <th>Ngày bắt đầu</th>
                    <th>Ngày kết thúc</th>
                    <th>Giới hạn sử dụng</th>
                    <th>Ngày tạo</th>
                    <th>Hành động</th> <!-- Thêm cột hành động -->
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($select_promotions) > 0) {
                    while ($fetch_promotions = mysqli_fetch_assoc($select_promotions)) {
                ?>
                        <tr>
                            <td><?php echo $count++; ?></td>
                            <td><?php echo htmlspecialchars($fetch_promotions['code_discount']); ?></td>
                            <td><?php echo htmlspecialchars($fetch_promotions['discount_percent']); ?>%</td>
                            <td><?php echo htmlspecialchars($fetch_promotions['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($fetch_promotions['end_date']); ?></td>
                            <td><?php echo htmlspecialchars($fetch_promotions['usage_limit']); ?></td>
                            <td><?php echo htmlspecialchars($fetch_promotions['created_at']); ?></td>
                            <td>
                                <a href="apply_promotion.php?promotion_id=" class="edit-button">Áp dụng</a>
                                <a href="edit_promotion.php?promotion_id=" class="edit-button">Sửa</a>
                                <a href="delete_promotion.php?promotion_id=" class="delete-button"
                                    onclick="return confirm('Bạn có chắc chắn muốn xóa khuyến mãi này?');">Xóa</a>
                            </td>
                        </tr>
                    <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="8">
                            <div class="container">
                                <div class="no-products-message">
                                    Không có khuyến mãi nào.
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>

</body>

</html>