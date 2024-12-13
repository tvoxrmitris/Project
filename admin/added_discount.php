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

// Kiểm tra nếu có yêu cầu xóa khuyến mãi
if (isset($_GET['delete_id'])) {
    $promotion_id = mysqli_real_escape_string($conn, $_GET['delete_id']);

    // Thực hiện truy vấn xóa
    $delete_query = "DELETE FROM promotions WHERE promotion_id = '$promotion_id'";
    if (mysqli_query($conn, $delete_query)) {
        echo "<script>alert('Khuyến mãi đã được xóa thành công');</script>";
    } else {
        echo "<script>alert('Lỗi khi xóa khuyến mãi');</script>";
    }
}
// Xử lý điều kiện lọc
$subcategory_filter = '';
if (isset($_GET['subcategory']) && !empty($_GET['subcategory'])) {
    $subcategory_filter = mysqli_real_escape_string($conn, $_GET['subcategory']);
}

// Lấy giá trị tìm kiếm từ form
$searchTerm = isset($_GET['search']) ? '%' . mysqli_real_escape_string($conn, $_GET['search']) . '%' : '';

// Khởi tạo phần điều kiện lọc
$filter_conditions = [];

// Kiểm tra và thêm điều kiện lọc theo loại sản phẩm
if ($subcategory_filter) {
    $filter_conditions[] = "ie.subcategory_name = '$subcategory_filter'";
}

// Kiểm tra và thêm điều kiện lọc theo ngày tháng
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

if ($date_from) {
    $filter_conditions[] = "ie.date_received >= '$date_from'";
}
if ($date_to) {
    $filter_conditions[] = "ie.date_received <= '$date_to'";
}

// Thêm điều kiện tìm kiếm vào lọc
if ($searchTerm) {
    $filter_conditions[] = "(ie.product_name LIKE '$searchTerm' OR ie.color_name LIKE '$searchTerm' OR ie.subcategory_name LIKE '$searchTerm' OR s.suplier_name LIKE '$searchTerm')";
}

// Chuyển đổi mảng điều kiện thành chuỗi điều kiện SQL
$conditions = implode(' AND ', $filter_conditions);

// Truy vấn để lấy tổng số khuyến mãi
$total_promotions_query = "SELECT COUNT(*) AS total FROM promotions";
$result = mysqli_query($conn, $total_promotions_query);
$row = mysqli_fetch_assoc($result);
$total_promotions = $row['total'];


// Lấy danh sách các loại sản phẩm để hiển thị trong dropdown
$subcategory_query = "SELECT DISTINCT subcategory_name FROM inventory_entries";
$subcategories = mysqli_query($conn, $subcategory_query);

// Lấy dữ liệu từ bảng inventory_entries, bảng supplier với điều kiện lọc
$select_products = mysqli_query($conn, "
    SELECT ie.*, s.suplier_name
    FROM inventory_entries ie
    JOIN suplier s ON ie.suplier_id = s.suplier_id
    " . ($conditions ? "WHERE " . $conditions : "") . "
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" type="text/css" href="../CSS/style.css?v=1.1 <?php echo time(); ?>">
    <title>Seraph Beauty - Sản Phẩm Tồn Kho</title>
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
        <h2 style="font-size:50px;">Giảm giá đã thêm</h2>
    </div>
    <div class="search-container">
        <form method="GET" action="">
            <input type="text" class="search" name="search" placeholder="Tìm kiếm sản phẩm..."
                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit">Tìm kiếm</button>
        </form>
    </div>

    <section class="shop">
        <div class="border-wrapper">
            <a href="admin_discount.php" class="back-arrow"
                style="text-decoration: none; font-size: 1.5rem; margin-right: 1rem;">&#8592;</a>
            <div class="total-quantity">
                <p style="margin-left: 2rem;"><?php echo number_format($total_promotions, 0, '.', '.'); ?> Khuyến Mãi
                </p>
            </div>
            <div class="sort-by">
                <form method="get" action="">
                    <label for="date_from">Từ ngày:</label>
                    <input type="date" name="date_from" id="date_from"
                        value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>">

                    <label for="date_to">Đến ngày:</label>
                    <input type="date" name="date_to" id="date_to"
                        value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>">

                    <input type="submit" value="Lọc">
                </form>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Mã khuyến mãi</th>
                        <th>Phần trăm giảm giá</th>
                        <th>Ngày bắt đầu</th>
                        <th>Ngày kết thúc</th>
                        <th>Trạng thái</th> <!-- Cột hạn sử dụng mới -->
                        <th>Giới hạn sử dụng</th>
                        <th>Hành động</th> <!-- Thêm cột hành động -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Truy vấn dữ liệu từ bảng promotions
                    $query = "SELECT * FROM promotions";

                    if (isset($_GET['date_from']) && isset($_GET['date_to'])) {
                        $date_from = mysqli_real_escape_string($conn, $_GET['date_from']);
                        $date_to = mysqli_real_escape_string($conn, $_GET['date_to']);
                        $query .= " WHERE start_date >= '$date_from' AND end_date <= '$date_to'";
                    }

                    $result = mysqli_query($conn, $query);
                    $count = 1;

                    if (mysqli_num_rows($result) > 0) {
                        while ($promotion = mysqli_fetch_assoc($result)) {
                            // Tính toán hạn sử dụng
                            $start_date = new DateTime($promotion['start_date']);
                            $end_date = new DateTime($promotion['end_date']);
                            $today = new DateTime();

                            if ($today < $start_date) {
                                // Nếu hôm nay chưa tới ngày bắt đầu
                                $days_left = "Chưa bắt đầu";
                            } elseif ($today >= $start_date && $today <= $end_date) {
                                // Nếu hôm nay nằm trong khoảng thời gian
                                $days_left = "Còn hạn";
                            } else {
                                // Nếu đã qua ngày kết thúc
                                $days_left = "Hết hạn";
                            }

                    ?>
                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td><?php echo htmlspecialchars($promotion['code_discount']); ?></td>
                        <td><?php echo htmlspecialchars($promotion['discount_percent']); ?>%</td>
                        <td><?php echo htmlspecialchars($promotion['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($promotion['end_date']); ?></td>
                        <td><?php echo $days_left; ?></td> <!-- Hiển thị hạn sử dụng -->
                        <td><?php echo htmlspecialchars($promotion['usage_limit']); ?></td>
                        <td>
                            <!-- Thêm các nút hành động -->
                            <a href="edit_promotion.php?promotion_id=<?php echo $promotion['promotion_id']; ?>"
                                class="edit-button">Sửa</a>
                            <a href="?delete_id=<?php echo $promotion['promotion_id']; ?>" class="delete-button"
                                onclick="return confirm('Bạn có chắc chắn muốn xóa khuyến mãi này?');">Xóa</a>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                        ?>
                    <tr>
                        <td colspan="8">
                            <!-- Cập nhật số cột để tương ứng với số lượng cột hiện có -->
                            <div class="container">
                                <div class="no-promotions-message">
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

    </section>

</body>

</html>