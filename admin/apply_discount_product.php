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
$subcategory_query = "SELECT DISTINCT product_subcategory FROM products";
$subcategories = mysqli_query($conn, $subcategory_query);

// Xử lý điều kiện lọc
$subcategory_filter = '';
if (isset($_GET['subcategory']) && !empty($_GET['subcategory'])) {
    $subcategory_filter = mysqli_real_escape_string($conn, $_GET['subcategory']);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_promotion' && isset($_POST['product_id']) && isset($_POST['code_discount'])) {
        $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
        $code_discount = mysqli_real_escape_string($conn, $_POST['code_discount']);

        // Lấy thông tin từ bảng `promotions` dựa trên `code_discount`
        $promotion_query = "
            SELECT discount_percent, start_date, end_date 
            FROM promotions 
            WHERE code_discount = '$code_discount'
        ";
        $promotion_result = mysqli_query($conn, $promotion_query);

        if (mysqli_num_rows($promotion_result) > 0) {
            $promotion = mysqli_fetch_assoc($promotion_result);
            $discount_percent = $promotion['discount_percent'];
            $start_date = $promotion['start_date'];
            $end_date = $promotion['end_date'];

            // Kiểm tra xem sản phẩm đã tồn tại trong bảng `product_promotion` chưa
            $promotion_check_query = "SELECT * FROM product_promotion WHERE product_id = '$product_id'";
            $promotion_check_result = mysqli_query($conn, $promotion_check_query);

            if (mysqli_num_rows($promotion_check_result) > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Sản phẩm đã được áp dụng giảm giá.'
                ]);
            } else {
                // Chèn sản phẩm vào bảng `product_promotion`
                $insert_promotion_query = "
                    INSERT INTO product_promotion (product_id, code_discount, discount_percent, start_date, end_date) 
                    VALUES ('$product_id', '$code_discount', $discount_percent, '$start_date', '$end_date')
                ";

                if (mysqli_query($conn, $insert_promotion_query)) {
                    echo json_encode([
                        'success' => true,
                        'message' => "Áp dụng giảm giá thành công cho sản phẩm"
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => "Không thể áp dụng giảm giá. Vui lòng thử lại."
                    ]);
                }
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => "Mã giảm giá $code_discount không tồn tại hoặc không hợp lệ."
            ]);
        }
    }

    // Xử lý "Xóa giảm giá" nếu cần
    if (isset($_POST['action']) && $_POST['action'] === 'remove_promotion' && isset($_POST['product_id'])) {
        $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);

        // Xóa giảm giá cho sản phẩm
        $remove_promotion_query = "DELETE FROM product_promotion WHERE product_id = '$product_id'";

        if (mysqli_query($conn, $remove_promotion_query)) {
            echo json_encode([
                'success' => true,
                'message' => "Đã xóa giảm giá cho sản phẩm"
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => "Không thể xóa giảm giá. Vui lòng thử lại."
            ]);
        }
    }

    exit;
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

.toast {
    position: fixed;
    bottom: 40px;
    left: 20px;
    min-width: 200px;
    padding: 10px 20px;
    border-radius: 5px;
    color: white;
    font-size: 1rem;
    background-color: #000;
    /* Màu nền đen */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    display: none;
    z-index: 1000;
    transition: transform 0.5s ease-out, opacity 0.5s ease-out;
    opacity: 0;
    /* Ẩn khi chưa hiển thị */
}

.toast.show {
    display: block;
    transform: translateY(20px);
    /* Đẩy toast lên từ dưới */
    opacity: 1;
}

.toast.success {
    background-color: #000;
    color: #fff;
    /* Màu xanh cho thành công */
}

.toast.error {
    background-color: #F44336;
    /* Màu đỏ cho lỗi */
}

/* Nút chung */
button {
    font-family: 'Arial', sans-serif;
    font-size: 16px;
    font-weight: bold;
    border: none;
    cursor: pointer;
    padding: 10px 20px;
    text-transform: uppercase;
    border-radius: 5px;
    transition: all 0.3s ease;
}

/* Nút 'Xóa giảm giá' */
button.remove-promotion {
    background-color: #ffffff;
    /* Màu nền trắng */
    color: #000000;
    /* Màu chữ đen */
    border: 2px solid #000000;
    /* Đường viền đen */
}

button.remove-promotion:hover {
    background-color: #000000;
    /* Màu nền đen khi hover */
    color: #ffffff;
    /* Màu chữ trắng khi hover */
    border-color: #ffffff;
    /* Đổi màu viền thành trắng */
}

/* Nút 'Áp dụng' */
button.apply-promotion {
    background-color: #000000;
    /* Màu nền đen */
    color: #ffffff;
    /* Màu chữ trắng */
    border: 2px solid #000000;
    /* Đường viền đen */
}

button.apply-promotion:hover {
    background-color: #ffffff;
    /* Màu nền trắng khi hover */
    color: #000000;
    /* Màu chữ đen khi hover */
    border-color: #000000;
    /* Đổi màu viền đen */
}

/* Định dạng cho thông báo */
.toast-message {
    position: fixed;
    bottom: 20px;
    /* Cách đáy 20px */
    left: 20px;
    /* Cách trái 20px */
    background-color: rgba(0, 0, 0, 0.7);
    /* Màu nền đen mờ */
    color: white;
    /* Màu chữ trắng */
    padding: 10px 20px;
    border-radius: 5px;
    font-size: 16px;
    font-weight: bold;
    z-index: 9999;
    opacity: 0;
    transform: translateX(-100%);
    transition: opacity 0.3s ease, transform 0.3s ease;
}

/* Hiển thị thông báo */
.toast-message.show {
    opacity: 1;
    transform: translateX(0);
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
    <!-- Phần thông báo -->
    <div id="toast-message" class="toast-message"></div>


    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Lưu vị trí cuộn trước khi tải lại trang
        const scrollPosition = sessionStorage.getItem("scrollPosition");
        if (scrollPosition) {
            window.scrollTo(0, scrollPosition);
        }

        // Lấy thông báo từ PHP nếu có
        <?php if (isset($_SESSION['toast_message']) && isset($_SESSION['toast_type'])): ?>
        const message = "<?php echo addslashes($_SESSION['toast_message']); ?>";
        const type = "<?php echo $_SESSION['toast_type']; ?>";

        // Hiển thị thông báo
        const toast = document.getElementById("toast");
        toast.textContent = message;
        toast.classList.add("show", type);

        // Ẩn thông báo sau 3 giây
        setTimeout(() => {
            toast.classList.remove("show");
        }, 3000);

        // Xóa thông báo khỏi session
        <?php unset($_SESSION['toast_message'], $_SESSION['toast_type']); ?>
        <?php endif; ?>

        // Lưu vị trí cuộn khi người dùng di chuyển trang
        window.addEventListener('beforeunload', function() {
            sessionStorage.setItem("scrollPosition", window.scrollY);
        });
    });
    </script>

    <div class="title">
        <h2 style="font-size:50px;">Sản phẩm đã áp dụng giảm giá</h2>
    </div>
    <!-- <div class="search-container">
        <form method="GET" action="">
            <div class="search-box">
                <span class="search-icon">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="search" name="search" placeholder="Tìm kiếm sản phẩm..."
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">

            </div>
        </form>
    </div> -->

    <section class="shop">

        <div class="border-wrapper">
            <a href="admin_discount.php" class="back-arrow"
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
            // Truy vấn sản phẩm và kiểm tra trong cùng một truy vấn để giảm số lượng truy vấn
            $select_products_query = "
    SELECT 
        p.product_name, 
        p.product_price, 
        p.color_name, 
        p.quantity_in_stock, 
        p.product_detail, 
        p.capacity, 
        p.brand_name, 
        p.product_subcategory, 
        p.product_id,
        (SELECT COUNT(*) FROM product_promotion pp WHERE pp.product_id = p.product_id) AS exists_in_promotion
    FROM products p
    WHERE p.quantity_in_stock > 0
";

// Kiểm tra nếu có giá trị lọc theo category (product_subcategory)
if (!empty($subcategory_filter)) {
    $select_products_query .= " AND p.product_subcategory = '$subcategory_filter'";
}

// Sắp xếp theo số lượng tồn kho
$select_products_query .= " ORDER BY p.quantity_in_stock ASC";

// Thực hiện truy vấn
$select_products = mysqli_query($conn, $select_products_query) or die('Truy vấn thất bại: ' . mysqli_error($conn));


   

            $count = 1;

            if (mysqli_num_rows($select_products) > 0) {
                while ($product = mysqli_fetch_assoc($select_products)) {
                    $lowStockStyle = ($product['quantity_in_stock'] <= 20) ? 'color: #bd0100; font-weight: bold;' : '';
                    $is_in_promotion = $product['exists_in_promotion'] > 0;
            ?>
                    <tr>
                        <td style="<?php echo $lowStockStyle; ?>">
                            <?php echo $count++; ?>
                        </td>
                        <td style="<?php echo $lowStockStyle; ?>">
                            <?php echo htmlspecialchars($product['product_name']); ?>
                        </td>
                        <td style="<?php echo $lowStockStyle; ?>">
                            <?php
                        echo htmlspecialchars($product['color_name']);
                        if (!empty($product['capacity'])) {
                            echo ' | ' . htmlspecialchars($product['capacity']);
                        }
                        ?>
                        </td>
                        <td style="<?php echo $lowStockStyle; ?>">
                            <?php echo number_format($product['product_price']) . ' VNĐ'; ?>
                        </td>
                        <td style="<?php echo $lowStockStyle; ?>">
                            <?php echo $product['quantity_in_stock']; ?>
                        </td>
                        <td style="<?php echo $lowStockStyle; ?>">
                            <?php echo htmlspecialchars($product['brand_name']); ?>
                        </td>
                        <td style="<?php echo $lowStockStyle; ?>">
                            <?php echo htmlspecialchars($product['product_subcategory']); ?>
                        </td>
                        <td>
                            <?php if ($is_in_promotion): ?>
                            <button class="remove-promotion" data-product-id="<?php echo $product['product_id']; ?>">
                                Xóa giảm giá
                            </button>
                            <?php else: ?>
                            <button class="apply-promotion" data-product-id="<?php echo $product['product_id']; ?>"
                                data-code-discount="<?php echo urlencode($_GET['code_discount']); ?>">
                                Áp dụng
                            </button>
                            <?php endif; ?>
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
    function applyFilter() {
        var subcategory = document.getElementById("subcategory").value;
        var currentUrl = new URL(window.location.href); // Lấy URL hiện tại
        var params = new URLSearchParams(currentUrl.search); // Lấy các tham số query của URL

        // Nếu có giá trị mới cho subcategory, thêm hoặc cập nhật tham số trong URL
        if (subcategory) {
            params.set('subcategory', subcategory); // Cập nhật hoặc thêm tham số subcategory
        } else {
            params.delete('subcategory'); // Nếu không có giá trị, xóa tham số subcategory
        }

        // Cập nhật URL mà không thay đổi phần còn lại của URL
        currentUrl.search = params.toString();
        window.history.pushState({}, '', currentUrl); // Cập nhật URL mà không tải lại trang
    }
    </script>


    <script>
    // Hàm hiển thị thông báo
    function showToastMessage(message) {
        const toastMessage = document.getElementById('toast-message');
        toastMessage.textContent = message;
        toastMessage.classList.add('show');

        // Tự động ẩn sau 3 giây
        setTimeout(() => {
            toastMessage.classList.remove('show');
        }, 3000); // 3000ms = 3 giây
    }

    // Sử dụng hàm này để hiển thị thông báo
    showToastMessage('Áp dụng giảm giá thành công!');

    document.addEventListener("DOMContentLoaded", function() {
        // Xử lý nhấn vào "Áp dụng" giảm giá
        document.querySelectorAll('.apply-promotion').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const codeDiscount = this.getAttribute('data-code-discount');

                const data = new FormData();
                data.append('action', 'add_promotion');
                data.append('product_id', productId);
                data.append('code_discount', codeDiscount);

                fetch('apply_discount_product.php', {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(result => {
                        alert(result.message); // Hiển thị thông báo kết quả
                        if (result.success) {
                            location.reload(); // Làm mới trang nếu thành công
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });

        // Xử lý nhấn vào "Xóa giảm giá"
        document.querySelectorAll('.remove-promotion').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');

                const data = new FormData();
                data.append('action', 'remove_promotion');
                data.append('product_id', productId);

                fetch('apply_discount_product.php', {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(result => {
                        alert(result.message); // Hiển thị thông báo kết quả
                        if (result.success) {
                            location.reload(); // Làm mới trang nếu thành công
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    });
    </script>

</html>