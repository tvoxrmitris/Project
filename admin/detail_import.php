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

// Lấy giá trị lọc danh mục từ GET
$subcategory_filter = '';
if (isset($_GET['subcategory']) && !empty($_GET['subcategory'])) {
    $subcategory_filter = mysqli_real_escape_string($conn, $_GET['subcategory']);
}

// Lấy giá trị lọc ngày từ GET
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Khởi tạo mảng điều kiện lọc
$filter_conditions = [];

// Lọc theo danh mục
if ($subcategory_filter) {
    $filter_conditions[] = "di.subcategory_name = '$subcategory_filter'";
}

// Lọc theo ngày
if (!empty($start_date)) {
    $filter_conditions[] = "di.date_received >= '$start_date'";
}
if (!empty($end_date)) {
    $filter_conditions[] = "di.date_received <= '$end_date'";
}

// Tạo điều kiện WHERE từ các bộ lọc
$conditions = $filter_conditions ? "WHERE " . implode(' AND ', $filter_conditions) : "";

// Truy vấn tổng số sản phẩm dựa trên điều kiện lọc
$total_products_query = "
    SELECT COUNT(*) AS total_products
    FROM detail_import di
    JOIN suplier s ON di.suplier_id = s.suplier_id
    JOIN subcategory sub ON di.subcategory_name = sub.subcategory_id
    $conditions
";
$result = mysqli_query($conn, $total_products_query);
$row = mysqli_fetch_assoc($result);
$total_products = $row['total_products'] ?? 0;

// Truy vấn lấy danh sách sản phẩm dựa trên điều kiện lọc
$select_products = mysqli_query($conn, "
    SELECT di.*, s.suplier_name, sub.subcategory_name
    FROM detail_import di
    JOIN suplier s ON di.suplier_id = s.suplier_id
    JOIN subcategory sub ON di.subcategory_name = sub.subcategory_id
    $conditions
") or die('Truy vấn thất bại: ' . mysqli_error($conn));


// Khởi tạo biến đếm cho số thứ tự
$count = 1;
?>

<style type="text/css">
<?php include '../CSS/style.css';
?>

/* Thiết lập chiều rộng tối thiểu cho các cột nếu cần */
th:nth-child(1),
td:nth-child(1) {
    width: 60px;
}

th:nth-child(2),
td:nth-child(2) {
    width: 350px;
}

th:nth-child(3),
td:nth-child(3) {
    width: 150px;
}

th:nth-child(4),
td:nth-child(4) {
    width: 200px;
}

th:nth-child(5),
td:nth-child(5) {
    width: 120px;
}

th:nth-child(6),
td:nth-child(6) {
    width: 150px;
}

th:nth-child(7),
td:nth-child(7) {
    width: 130px;
}

th:nth-child(8),
td:nth-child(8) {
    width: 95px;
}

th:nth-child(9),
td:nth-child(9) {
    width: 130px;
}

th:nth-child(10),
td:nth-child(10) {
    width: 150px;
}

th:nth-child(11),
td:nth-child(11) {
    width: 150px;
}

.sort-by {
    margin-right: 3rem !important;
    width: 171px;
}

.back-arrow {
    text-decoration: none;
    font-size: 1.5rem;
    color: #333;
    margin-right: 1rem;
    z-index: 10;
}

.back-arrow:hover {
    color: #000;
    /* Màu sắc khi hover */
    cursor: pointer;
}

.table-container {
    overflow-x: auto;
    /* Thêm thanh cuộn ngang */
    white-space: nowrap;
    /* Giữ nội dung trên cùng một dòng */

    /* Thêm khoảng cách trên và dưới nếu cần */
    border: 1px solid #ddd;
    /* Thêm đường viền để dễ quan sát */
}

table {
    border-collapse: collapse;
    width: 100%;
    min-width: 800px;
    /* Đặt chiều rộng tối thiểu cho bảng nếu muốn */
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
    <title>Seraph Beauty - Chi Tiết Nhập Kho</title>
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
        <h2 style="font-size:50px;">Chi tiết nhập hàng</h2>
    </div>


    <section class="shop">
        <div class="border-wrapper">
            <a href="manage_product.php" class="back-arrow">&#8592;</a>
            <div class="total-quantity">
                <p style="margin-left: 2rem;"><?php echo number_format($total_products, 0, '.', '.'); ?> Sản Phẩm</p>
            </div>
            <div class="filter-form">
                <form id="filterForm" method="GET" action="" style="display: flex; align-items: center;">
                    <select name="subcategory" id="subcategory" class="subcategory-select" style="margin-right: 1rem; "
                        onchange="applyFilter();">
                        <option value="">Tất cả</option>
                        <?php
                        // Truy vấn lấy tất cả subcategory_name từ bảng subcategory
                        $subcategory_query = "SELECT subcategory_id, subcategory_name FROM subcategory";
                        $subcategory_result = mysqli_query($conn, $subcategory_query);
                        while ($row = mysqli_fetch_assoc($subcategory_result)) {
                            // Kiểm tra xem subcategory_name có trong GET không để chọn mục phù hợp
                            echo '<option value="' . htmlspecialchars($row['subcategory_id']) . '"';
                            if (isset($_GET['subcategory']) && $_GET['subcategory'] == $row['subcategory_id']) {
                                echo ' selected';
                            }
                            echo '>' . htmlspecialchars($row['subcategory_name']) . '</option>';
                        }
                        ?>
                    </select>
                </form>
                <!-- Lọc từ ngày -->
                <span>Từ ngày</span>
                <input type="date" name="start_date" id="start_date" class="date-select"
                    value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>"
                    style="margin-right: 1rem;" onchange="applyFilter();">
                <span>Đến ngày</span>
                <!-- Lọc đến ngày -->
                <input type="date" name="end_date" id="end_date" class="date-select"
                    value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>"
                    style="margin-right: 1rem;" onchange="applyFilter();">
            </div>

        </div>
        <script>
        function applyFilter() {
            var subcategory = document.getElementById("subcategory").value;
            var startDate = document.getElementById("start_date").value;
            var endDate = document.getElementById("end_date").value;

            // Lấy URL hiện tại mà không có tham số query
            var currentUrl = window.location.href.split('?')[0];
            var newUrl = currentUrl + "?";

            // Thêm tham số subcategory nếu có
            if (subcategory) {
                newUrl += "subcategory=" + encodeURIComponent(subcategory);
            }

            // Thêm tham số start_date và end_date nếu có
            if (startDate) {
                newUrl += (newUrl.endsWith("?") ? "" : "&") + "start_date=" + encodeURIComponent(startDate);
            }
            if (endDate) {
                newUrl += (newUrl.endsWith("?") ? "" : "&") + "end_date=" + encodeURIComponent(endDate);
            }

            // Chuyển hướng đến URL mới
            window.location.href = newUrl;
        }
        </script>


        <div class="table-container">
            <table>
                <colgroup>
                    <col style="width: 65px;">
                    <col style="width: 140px;">
                    <col style="width: 350px;">
                    <col style="width: 180px;">
                    <col style="width: 200px;">
                    <col style="width: 140px;">
                    <col style="width: 150px;">
                    <col style="width: 140px;">
                    <col style="width: 110px;">
                    <col style="width: 130px;">
                </colgroup>
                <tbody>
                    <?php
                    // Biến để lưu ngày hiện tại
                    $current_date = '';

                    if (mysqli_num_rows($select_products) > 0) {
                        while ($fetch_products = mysqli_fetch_assoc($select_products)) {

                            // Kiểm tra nếu ngày nhập thay đổi
                            if ($fetch_products['date_received'] != $current_date) {
                                $current_date = $fetch_products['date_received'];
                                // Reset lại biến đếm STT
                                $count = 1;

                                // Hiển thị dòng ngày nhập
                                echo '<tr><td colspan="10" class="date-header">Ngày nhập: ' . date('d/m/Y', strtotime($current_date)) . '</td></tr>';

                                // Dòng tiêu đề dưới ngày nhập
                                echo '<tr>
                                <th>STT</th>
                                <th>Người nhập</th>
                                <th>Tên sản phẩm</th>
                                <th>Màu sắc và thể tích</th>
                                <th>Mô tả màu sắc</th>
                                <th>Giá nhập</th>
                                <th>Loại sản phẩm</th>
                                <th>Nhà cung cấp</th>
                                <th>Số lượng</th>
                                <th>Hành động</th>
                              </tr>';
                            }

                            // Hiển thị dữ liệu sản phẩm
                            echo '<tr>';
                            echo '<td>' . $count++ . '</td>';
                            echo '<td>' . htmlspecialchars($fetch_products['importer']) . '</td>';
                            echo '<td>' . htmlspecialchars($fetch_products['product_name']) . '</td>';
                            echo '<td>' . htmlspecialchars($fetch_products['code_color']) .
                                (empty($fetch_products['capacity']) ? '' : ' | ' . htmlspecialchars($fetch_products['capacity'])) . '</td>';
                            echo '<td>' . htmlspecialchars($fetch_products['color_name']) . '</td>';
                            echo '<td>' . number_format($fetch_products['import_price'], 0, ',', '.') . ' VND</td>';

                            echo '<td>' . htmlspecialchars($fetch_products['subcategory_name']) . '</td>';
                            echo '<td>' . htmlspecialchars($fetch_products['suplier_name']) . '</td>';
                            echo '<td>' . htmlspecialchars($fetch_products['quantity_stock']) . '</td>';
                            echo '<td>';
                            echo '<a href="edit_import_product.php?detail_import_id=' . $fetch_products['detail_import_id'] . '" class="edit-button">Sửa</a>';
                            echo '<a href="delete_product.php?detail_import_id=' . $fetch_products['detail_import_id'] . '" class="delete-button" onclick="return confirm(\'Bạn có chắc chắn muốn xóa sản phẩm này?\');">Xóa</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="10"><div class="container"><div class="no-products-message">Không có sản phẩm nào.</div></div></td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </section>
</body>

</html>