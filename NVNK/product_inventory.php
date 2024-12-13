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

// Tính tổng số lượng sản phẩm trong bảng inventory_entries dựa trên các điều kiện lọc
$total_products_query = "
    SELECT COUNT(*) AS total_products
    FROM inventory_entries ie
    JOIN suplier s ON ie.suplier_id = s.suplier_id
    " . ($conditions ? "WHERE " . $conditions : "") . "
";
$result = mysqli_query($conn, $total_products_query);
$row = mysqli_fetch_assoc($result);
$total_products = $row['total_products'] ? $row['total_products'] : 0;

// Lấy danh sách các loại sản phẩm để hiển thị trong dropdown
$subcategory_query = "SELECT DISTINCT subcategory_name FROM inventory_entries";
$subcategories = mysqli_query($conn, $subcategory_query);

$select_products = mysqli_query($conn, "
    SELECT 
        ie.*, 
        s.suplier_name, 
        sc.subcategory_name
    FROM 
        inventory_entries ie
    JOIN 
        suplier s ON ie.suplier_id = s.suplier_id
    JOIN 
        subcategory sc ON ie.subcategory_name = sc.subcategory_id
    " . ($conditions ? "WHERE " . $conditions : "") . "
    ORDER BY 
        ie.quantity_stock ASC
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
    width: 380px;
}

th:nth-child(3),
td:nth-child(3) {
    width: 220px;
}

th:nth-child(4),
td:nth-child(4) {
    width: 200px;
}

th:nth-child(5),
td:nth-child(5) {
    width: 150px;
}

th:nth-child(6),
td:nth-child(6) {
    width: 180px;
}

th:nth-child(7),
td:nth-child(7) {
    width: 150px;
}

th:nth-child(8),
td:nth-child(8) {
    width: 105px;
}

th:nth-child(9),
td:nth-child(9) {
    width: 300px;
}

th:nth-child(10),
td:nth-child(10) {
    width: 150px;
}

th:nth-child(11),
td:nth-child(11) {
    width: 150px;
}

.border-wrapper {
    display: flex;
    justify-content: space-between;
    /* Căn đều các phần tử bên trong */
    align-items: center;
    /* Căn chỉnh các phần tử theo chiều dọc */
    padding: 10px;
    background-color: #fff;
    /* Nền trắng */
}

.total-quantity {
    flex: 1;
    /* Lấy hết không gian còn lại để căn chỉnh */
    display: flex;
    justify-content: flex-start;
    /* Căn trái cho phần total-quantity */
    font-size: 1rem;
    color: #333;
    /* Màu chữ đen */
}

#filterForm {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    /* Căn phải cho form lọc */
}

.subcategory-select {
    font-family: 'Helvetica Neue', sans-serif;
    font-size: 1rem;
    padding: 10px;
    color: #333;
    background-color: #fff;
    border: 2px solid #000;
    border-radius: 5px;
    transition: all 0.3s ease-in-out;
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

.notification-box {
    background: #000;
    color: #fff;
    display: none;
    padding: 15px 30px;
    border: 2px solid #fff;
    border-radius: 8px;
    font-family: 'Helvetica Neue', sans-serif;
    text-transform: uppercase;
    position: fixed;
    bottom: 20px;
    left: 20px;
    z-index: 1000;
    opacity: 0;
    animation: slideUp 0.5s forwards;
    transition: opacity 1s ease-out;
}

@keyframes slideUp {
    0% {
        bottom: -100px;
        opacity: 0;
    }

    100% {
        bottom: 20px;
        opacity: 1;
    }
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
        <h2 style="font-size:50px;">Sản phẩm tồn kho</h2>
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
            <a href="NVNK_pannel.php" class="back-arrow"
                style="text-decoration: none; font-size: 1.5rem; margin-right: 1rem; ">&#8592;</a>
            <div class="total-quantity">
                <p style="margin-left: 2rem; ">
                    <?php echo number_format($total_products, 0, '.', '.'); ?> Sản Phẩm</p>
            </div>

            <!-- Thêm phần input lọc theo danh mục vào trong border-wrapper -->
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
        <div id="notification" class="notification-box"></div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên sản phẩm</th>
                        <th>Màu sắc và thể tích</th>
                        <th>Mô tả màu sắc</th>
                        <th>Giá nhập</th>
                        <th>Loại sản phẩm</th>
                        <th>Nhà cung cấp</th>
                        <th>Số lượng</th>

                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($select_products) > 0) {
                        while ($fetch_products = mysqli_fetch_assoc($select_products)) {
                            $lowStockStyle = ($fetch_products['quantity_stock'] <= 20) ? 'color: #bd0100; font-weight: bold;' : '';
                    ?>
                    <tr>
                        <td style="<?php echo $lowStockStyle; ?>"><?php echo $count++; ?></td>
                        <td style="<?php echo $lowStockStyle; ?>">
                            <?php echo htmlspecialchars($fetch_products['product_name']); ?></td>
                        <td style="<?php echo $lowStockStyle; ?>">
                            <?php echo htmlspecialchars($fetch_products['code_color']); ?>
                            <?php if (!empty($fetch_products['capacity'])): ?>
                            | <?php echo htmlspecialchars($fetch_products['capacity']); ?>
                            <?php endif; ?>
                        </td>
                        <td style="<?php echo $lowStockStyle; ?>">
                            <?php echo htmlspecialchars($fetch_products['color_name']); ?></td>
                        <td style="<?php echo $lowStockStyle; ?>">
                            <?php echo number_format(htmlspecialchars($fetch_products['import_price']), 0, '.', ',') . ' VND'; ?>
                        </td>
                        <td style="<?php echo $lowStockStyle; ?>">
                            <?php echo htmlspecialchars($fetch_products['subcategory_name']); ?></td>
                        <td style="<?php echo $lowStockStyle; ?>">
                            <?php echo htmlspecialchars($fetch_products['suplier_name']); ?></td>
                        <td style="<?php echo $lowStockStyle; ?>">
                            <?php echo htmlspecialchars($fetch_products['quantity_stock']); ?></td>


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

        <script>
        function toggleRequest(inventoryId, action) {
            // Lưu vị trí cuộn hiện tại trước khi reload
            localStorage.setItem('scrollPosition', window.scrollY);

            fetch('../admin/request_quantity_stock.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `inventory_id=${inventoryId}&action=${action}`,
                })
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === 'success') {
                        // Lưu thông báo vào localStorage để hiển thị sau reload
                        const message = action === 'request' ?
                            'Yêu cầu nhập hàng thành công!' :
                            'Đã hủy yêu cầu nhập hàng!';
                        localStorage.setItem('notificationMessage', message);

                        // Reload trang để cập nhật trạng thái
                        location.reload();
                    } else {
                        alert(data.trim()); // Hiển thị thông báo lỗi ngay lập tức nếu có
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Đặt lại vị trí cuộn và hiển thị thông báo sau khi reload
        window.onload = function() {
            // Lấy vị trí cuộn từ localStorage
            const scrollPosition = localStorage.getItem('scrollPosition');
            if (scrollPosition) {
                window.scrollTo(0, parseInt(scrollPosition, 10));
                localStorage.removeItem('scrollPosition'); // Xóa vị trí cuộn sau khi đặt lại
            }

            // Hiển thị thông báo từ localStorage
            const notificationBox = document.getElementById('notification');
            const message = localStorage.getItem('notificationMessage');
            if (message) {
                notificationBox.textContent = message;
                notificationBox.style.display = 'block';

                // Ẩn thông báo sau 3 giây
                setTimeout(() => {
                    notificationBox.style.display = 'none';
                    localStorage.removeItem('notificationMessage'); // Xóa thông báo sau khi hiển thị
                }, 3000);
            }
        };
        </script>


    </section>
</body>

</html>