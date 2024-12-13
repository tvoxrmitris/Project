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

// Tính tổng số lượng sản phẩm trong bảng inventory_entries
$total_products_query = "
    SELECT COUNT(*) AS total_products
    FROM products
";
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

?>th,
td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
    cursor: pointer;
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
    width: 170px;
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
    width: 150px;
}

th:nth-child(7),
td:nth-child(7) {
    width: 150px;
}

th:nth-child(8),
td:nth-child(8) {
    width: 250px;
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

    font-size: 1rem;
    padding: 10px;
    color: #333;
    background-color: #fff;
    border: 2px solid #000;
    border-radius: 5px;
    transition: all 0.3s ease-in-out;
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
        <h2 style="font-size:50px;">Sản phẩm đã thêm</h2>
    </div>


    <section class="shop">

        <div class="border-wrapper">
            <a href="manage_product.php" class="back-arrow"
                style="text-decoration: none; font-size: 1.5rem; margin-right: 1rem;">&#8592;</a>
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

        <div id="notification" class="notification-box"></div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên sản phẩm</th>
                        <th>Màu sắc và thể tích</th>
                        <th>Giá sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Thương hiệu</th>
                        <th>Loại sản phẩm</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
                    $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
                    $filter_conditions = [];

                    if ($subcategory_filter) {
                        $filter_conditions[] = "p.product_subcategory = '$subcategory_filter'";
                    }

                    if ($date_from) {
                        $filter_conditions[] = "ie.date_received >= '$date_from'";
                    }
                    if ($date_to) {
                        $filter_conditions[] = "ie.date_received <= '$date_to'";
                    }

                    $conditions = implode(' AND ', $filter_conditions);

                    $select_products = mysqli_query($conn, "
                    SELECT p.product_name, p.product_price, p.color_name, p.quantity_in_stock, p.product_detail, p.capacity, p.brand_name, p.product_subcategory, p.product_id
                    FROM products p
                    JOIN products ie ON p.product_id = ie.product_id
                    " . ($conditions ? "WHERE " . $conditions . " AND p.quantity_in_stock > 0" : "WHERE p.quantity_in_stock > 0") . "
                    ORDER BY p.quantity_in_stock ASC
                ") or die('Truy vấn thất bại: ' . mysqli_error($conn));

                    $count = 1;

                    if (mysqli_num_rows($select_products) > 0) {
                        while ($fetch_products = mysqli_fetch_assoc($select_products)) {
                            $lowStockStyle = ($fetch_products['quantity_in_stock'] <= 20) ? 'color: #bd0100; font-weight: bold;' : '';
                    ?>
                    <tr>
                        <td style="<?php echo $lowStockStyle; ?>"
                            onclick="window.location.href='edit_product.php?product_id=<?php echo $fetch_products['product_id']; ?>'">
                            <?php echo $count++; ?>
                        </td>
                        <td style="<?php echo $lowStockStyle; ?>"
                            onclick="window.location.href='edit_product.php?product_id=<?php echo $fetch_products['product_id']; ?>'">
                            <?php echo $fetch_products['product_name']; ?>
                        </td>
                        <td style="<?php echo $lowStockStyle; ?>"
                            onclick="window.location.href='edit_product.php?product_id=<?php echo $fetch_products['product_id']; ?>'">
                            <?php
                                    echo $fetch_products['color_name'];
                                    if (!empty($fetch_products['capacity'])) {
                                        echo ' | ' . $fetch_products['capacity'];
                                    }
                                    ?>
                        </td>

                        <td style="<?php echo $lowStockStyle; ?>"
                            onclick="window.location.href='edit_product.php?product_id=<?php echo $fetch_products['product_id']; ?>'">
                            <?php echo number_format($fetch_products['product_price']) . ' VNĐ'; ?>
                        </td>
                        <td style="<?php echo $lowStockStyle; ?>"
                            onclick="window.location.href='edit_product.php?product_id=<?php echo $fetch_products['product_id']; ?>'">
                            <?php echo $fetch_products['quantity_in_stock']; ?>
                        </td>
                        <td style="<?php echo $lowStockStyle; ?>"
                            onclick="window.location.href='edit_product.php?product_id=<?php echo $fetch_products['product_id']; ?>'">
                            <?php echo $fetch_products['brand_name']; ?>
                        </td>
                        <td style="<?php echo $lowStockStyle; ?>"
                            onclick="window.location.href='edit_product.php?product_id=<?php echo $fetch_products['product_id']; ?>'">
                            <?php echo $fetch_products['product_subcategory']; ?>
                        </td>
                        <td style="<?php echo $lowStockStyle; ?>">
                            <?php
                                    $product_id = $fetch_products['product_id'];

                                    // Kiểm tra sản phẩm trong bảng low_stock_requests
                                    $check_request_query = "SELECT * FROM low_stock_requests WHERE product_id = '$product_id'";
                                    $check_request_result = mysqli_query($conn, $check_request_query);

                                    if (mysqli_num_rows($check_request_result) > 0) {
                                        echo '<a style="' . $lowStockStyle . ' color: #000; font-weight: bold; " 
                                                href="view_product_added.php" onclick="deleteRequest(' . $product_id . '); return false;">Hủy yêu cầu</a>';
                                    } else {
                                        echo '<a style="color: #000; font-weight: bold; " href="view_product_added.php"
                                onclick="requestStock(' . $product_id . '); return false;">Yêu cầu nhập hàng</a>';
                                    }
                                    ?>
                            <a style="<?php echo $lowStockStyle; ?>"
                                href="edit_product.php?product_id=<?php echo $fetch_products['product_id']; ?>">Sửa</a>
                            <a style="<?php echo $lowStockStyle; ?>"
                                href="delete_product.php?product_id=<?php echo $fetch_products['product_id']; ?>"
                                onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?');">Xóa</a>
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


    <script>
    // Hàm yêu cầu nhập hàng
    function requestStock(productId) {
        if (!productId) {
            console.error('Product ID is undefined or null.');
            return;
        }

        fetch('request_stock.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=request&product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' || data.status === 'exists') {
                    // Lưu thông báo vào localStorage trước khi reload
                    localStorage.setItem('notificationMessage', data.message);
                    location.reload(); // Reload trang
                } else {
                    console.error('Error:', data.message); // Log lỗi nếu có
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });
    }

    // Hàm xóa yêu cầu nhập hàng
    function deleteRequest(productId) {
        if (!productId) {
            console.error('Product ID is undefined or null.');
            return;
        }

        fetch('request_stock.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete&product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Lưu thông báo vào localStorage trước khi reload
                    localStorage.setItem('notificationMessage', data.message);
                    location.reload(); // Reload trang
                } else {
                    console.error('Error:', data.message); // Log lỗi nếu có
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });
    }

    // Hiển thị thông báo sau khi reload
    window.addEventListener('load', () => {
        const notificationMessage = localStorage.getItem('notificationMessage');
        if (notificationMessage) {
            // Hiển thị thông báo
            showNotification(notificationMessage);
            // Xóa thông báo để không hiển thị lại
            localStorage.removeItem('notificationMessage');
        }
    });

    function showNotification(message) {
        const notification = document.getElementById('notification');
        notification.textContent = message;

        // Hiển thị hộp thông báo với hiệu ứng
        notification.style.display = 'block';

        // Ẩn thông báo sau 4 giây (tương ứng với thời gian hiệu ứng fadeOut)
        setTimeout(() => {
            notification.style.display = 'none';
        }, 4000); // Giữ thông báo trong 4 giây
    }
    </script>



</html>