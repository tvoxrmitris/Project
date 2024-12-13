<?php
// Kết nối cơ sở dữ liệu và bắt đầu phiên
include '../connection/connection.php';
session_start();
if (!isset($_SESSION['employee_id']) || $_SESSION['employee_type'] !== 'NVNK') {
    header('location:../components/admin_login.php');
    exit;
}

// Xử lý đăng xuất
if (isset($_POST['logout'])) {
    session_destroy();
    header('location:../components/admin_login.php');
    exit;
}

// Điều kiện lọc cho danh mục
$subcategory_filter = isset($_GET['subcategory']) ? mysqli_real_escape_string($conn, $_GET['subcategory']) : '';

// Cập nhật truy vấn để tính tổng số sản phẩm với điều kiện lọc
$total_products_query = "
    SELECT COUNT(*) AS total_products
    FROM products
    " . ($subcategory_filter ? "WHERE product_subcategory = '$subcategory_filter'" : "");

$result = mysqli_query($conn, $total_products_query);
$row = mysqli_fetch_assoc($result);
$total_products = $row['total_products'] ? $row['total_products'] : 0;


// Lấy danh sách các loại sản phẩm để hiển thị trong dropdown
$subcategory_query = "SELECT DISTINCT subcategory_name FROM inventory_entries";
$subcategories = mysqli_query($conn, $subcategory_query);

// Xử lý điều kiện lọc
$subcategory_filter = '';
if (isset($_GET['subcategory']) && !empty($_GET['subcategory'])) {
    $subcategory_filter = mysqli_real_escape_string($conn, $_GET['subcategory']);
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

/* Thiết lập chiều rộng tối thiểu cho các cột nếu cần */
th:nth-child(1),
td:nth-child(1) {
    width: 55px;
}

th:nth-child(2),
td:nth-child(2) {
    width: 350px;
}

th:nth-child(3),
td:nth-child(3) {
    width: 120px;
}

th:nth-child(4),
td:nth-child(4) {
    width: 150px;
}

th:nth-child(5),
td:nth-child(5) {
    width: 100px;
}

th:nth-child(6),
td:nth-child(6) {
    width: 100px;
}

th:nth-child(7),
td:nth-child(7) {
    width: 100px;
}

th:nth-child(8),
td:nth-child(8) {
    width: 120px;
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
    <?php include '../NVNK/NVNK_header.php'; ?>
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
    <div class="search-container">
        <form method="GET" action="">
            <div class="search-box">
                <span class="search-icon">
                    <i class="bi bi-search"></i> <!-- Sử dụng Font Awesome hoặc thay bằng icon khác -->
                </span>
                <input type="text" class="search" name="search" placeholder="Tìm kiếm sản phẩm..."
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">

            </div>
        </form>
    </div>

    <section class="shop">

        <div class="border-wrapper">
            <a style="font-size: 25px;" href="NVNK_pannel.php" class="back-arrow">&#8592;</a>
            <div class="total-quantity">
                <p style="margin-left: 2rem;"><?php echo number_format($total_products, 0, '.', '.'); ?> Sản Phẩm</p>
            </div>

            <!-- Thêm phần input lọc theo danh mục vào trong border-wrapper -->
            <form id="filterForm" method="GET" action="" style="display: flex; align-items: center;">
                <select name="category" id="subcategory" class="subcategory-select" style="margin-right: 1rem;"
                    onchange="applyFilter();">
                    <option value="">Tất cả</option>
                    <?php
                    // Truy vấn lấy danh sách product_subcategory từ bảng products
                    $subcategory_query = "SELECT DISTINCT product_subcategory FROM products WHERE product_subcategory IS NOT NULL AND product_subcategory != ''";
                    $subcategory_result = mysqli_query($conn, $subcategory_query);
                    while ($row = mysqli_fetch_assoc($subcategory_result)) {
                        // Kiểm tra product_subcategory để đặt trạng thái selected
                        echo '<option value="' . htmlspecialchars($row['product_subcategory']) . '"';
                        if (isset($_GET['subcategory']) && $_GET['subcategory'] == $row['product_subcategory']) {
                            echo ' selected';
                        }
                        echo '>' . htmlspecialchars($row['product_subcategory']) . '</option>';
                    }
                    ?>
                </select>
            </form>


        </div>
        <script>
        function applyFilter() {
            var subcategory = document.getElementById("subcategory").value;
            // Lấy URL hiện tại và cập nhật tham số URL với lựa chọn mới
            var currentUrl = window.location.href.split('?')[0]; // Lấy URL mà không có tham số query
            var newUrl = currentUrl + (subcategory ? "?subcategory=" + encodeURIComponent(subcategory) : "");
            window.location.href = newUrl; // Thực hiện chuyển hướng đến URL mới
        }
        </script>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên sản phẩm</th>
                        <th>Màu sắc</th>
                        <th>Giá sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Trạng thái</th>
                        <th>Thương hiệu</th>
                        <th>Loại sản phẩm</th>
                        <th>Hành động</th> <!-- Thêm cột Hành động -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Lấy giá trị ngày tháng từ form
                    $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
                    $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

                    // Khởi tạo phần điều kiện lọc
                    $filter_conditions = [];

                    // Kiểm tra và thêm điều kiện lọc theo loại sản phẩm
                    if ($subcategory_filter) {
                        $filter_conditions[] = "p.product_subcategory = '$subcategory_filter'";
                    }

                    // Kiểm tra và thêm điều kiện lọc theo ngày tháng
                    if ($date_from) {
                        $filter_conditions[] = "ie.date_received >= '$date_from'";
                    }
                    if ($date_to) {
                        $filter_conditions[] = "ie.date_received <= '$date_to'";
                    }

                    // Chuyển đổi mảng điều kiện thành chuỗi điều kiện SQL
                    $conditions = implode(' AND ', $filter_conditions);

                    // Lấy dữ liệu từ bảng products và bảng supplier với điều kiện lọc
                    $select_products = mysqli_query($conn, "
                SELECT p.product_name, p.product_price, p.color_name, p.quantity_in_stock, p.product_detail, p.status, p.brand_name, p.product_subcategory, p.product_id
                FROM products p
                JOIN products ie ON p.product_id = ie.product_id
                " . ($conditions ? "WHERE " . $conditions . " AND p.quantity_in_stock > 0" : "WHERE p.quantity_in_stock > 0") . "
            ") or die('Truy vấn thất bại: ' . mysqli_error($conn));

                    // Khởi tạo biến đếm cho số thứ tự
                    $count = 1;

                    if (mysqli_num_rows($select_products) > 0) {
                        while ($fetch_products = mysqli_fetch_assoc($select_products)) {
                    ?>
                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td><?php echo $fetch_products['product_name']; ?></td>
                        <td><?php echo $fetch_products['color_name']; ?></td>
                        <td><?php echo number_format($fetch_products['product_price'], 2) . ' VNĐ'; ?></td>

                        <td><?php echo $fetch_products['quantity_in_stock']; ?></td>
                        <td><?php echo $fetch_products['status']; ?></td>
                        <td><?php echo $fetch_products['brand_name']; ?></td>
                        <td><?php echo $fetch_products['product_subcategory']; ?></td>
                        <td>

                            <a href="edit_product.php?product_id=<?php echo $fetch_products['product_id']; ?>">Xem</a>

                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                        ?>
                    <tr>
                        <td colspan="9">
                            <div class="container">
                                <div class="no-products-message">
                                    Không có sản phẩm nào.
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




</html>