<?php
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

// Lấy order_id từ URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

$products = [];
$user_info = [];
if ($order_id > 0) {
    // Truy vấn lấy thông tin sản phẩm
    $query_products = "SELECT product_name, product_image, quantity, price, discount_fee, product_id FROM order_items WHERE order_id = ?";
    $stmt_products = $conn->prepare($query_products);
    $stmt_products->bind_param('i', $order_id);
    $stmt_products->execute();
    $result_products = $stmt_products->get_result();

    while ($row = $result_products->fetch_assoc()) {
        $products[] = $row;  // Lưu tất cả thông tin sản phẩm
    }
    $stmt_products->close();

    // Truy vấn lấy thông tin người dùng từ bảng orders
    $query_user = "SELECT user_name, user_number, user_email, address, method, placed_on, status_order, total_discount_price, shipping_fee, total_price FROM orders WHERE order_id = ?";
    $stmt_user = $conn->prepare($query_user);
    $stmt_user->bind_param('i', $order_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($row = $result_user->fetch_assoc()) {
        $user_info = $row;  // Lưu thông tin người dùng
    }
    $stmt_user->close();
}

$total_before_discount = 0;

if ($order_id > 0) {
    // Truy vấn tính tổng tiền chưa giảm
    $query_total = "SELECT SUM(price * quantity) AS total FROM order_items WHERE order_id = ?";
    $stmt_total = $conn->prepare($query_total);
    $stmt_total->bind_param('i', $order_id);
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();

    if ($row_total = $result_total->fetch_assoc()) {
        $total_before_discount = $row_total['total'] ?? 0; // Lấy giá trị tổng tiền
    }
    $stmt_total->close();
}

// Kiểm tra xem order_id có tồn tại trong bảng cancel_order
$cancel_info = [];
$query = "SELECT cancel_reason, cancel_at FROM cancel_order WHERE order_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $cancel_info = $result->fetch_assoc();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" type="text/css" href="../CSS/style.css?v=1.1 <?php echo time(); ?>">
    <title>Seraph Beauty - Đơn Hàng</title>
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
    <style>
    .layout-container {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        margin: 20px 0;
    }

    .user-info {
        background-color: #fff;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        flex: 2;
        margin-bottom: 40px;
    }

    .user-info h2 {
        font-family: 'Georgia', serif;
        font-size: 24px;
        color: #000;
        margin-bottom: 20px;
        border-bottom: 2px solid #ddd;
        padding-bottom: 10px;
    }

    .user-details {
        border-bottom: 1px solid #666;
    }

    .user-details p {
        font-size: 16px;
        color: #333;
        margin: 8px 0;
    }

    .user-details strong {
        font-weight: bold;
    }

    .product-details {
        display: flex;
        flex-direction: column;
        gap: 40px;
        flex: 1;
    }

    .product-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
        /* Khoảng cách giữa các sản phẩm */
    }

    .product-item {
        display: flex;
        flex-direction: row;
        /* Đặt hình ảnh và thông tin theo hàng ngang */
        align-items: center;
        gap: 20px;
        /* Khoảng cách giữa hình ảnh và thông tin */
        background-color: #fff;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease-in-out;
    }

    .product-item:hover {
        transform: translateY(-5px);
        /* Hiệu ứng hover nhẹ nhàng */
    }

    .product-image img {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 5px;
    }

    .product-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .product-info p {
        font-size: 16px;
        margin: 8px 0;
    }

    .product-info strong {
        font-weight: bold;
        font-size: 16px;
        color: #333;
    }

    .status-detail {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #666;
    }

    .detail h3 {
        margin: 0;
        font-size: 16px;
        color: #333;
    }

    .detail strong {
        font-weight: bold;
        color: #000;
    }

    .order-actions {
        float: right;
        width: 450px;
        background-color: #fff;
        /* Nền trắng */
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
        text-align: right;
        font-size: 16px;
        border: 2px solid #000;
        /* Viền đen nổi bật */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        /* Tạo hiệu ứng đổ bóng nhẹ */
    }

    .order-actions ul {
        list-style-type: none;
        /* Xóa kiểu danh sách */
        padding: 0;
        margin: 0;
    }

    .order-actions li {
        display: flex;
        justify-content: space-between;
        /* Căn hai đầu */
        padding: 10px 0;
        font-size: 16px;
        border-bottom: 1px dashed #ccc;
        /* Đường kẻ nhẹ ngăn cách */
    }

    .order-actions li:last-child {
        border-bottom: none;
        /* Xóa đường kẻ cuối cùng */
    }

    .order-actions span {
        color: #000;
        /* Màu chữ đen */
    }

    .order-actions .text-left {
        font-weight: bold;
    }

    .order-actions .text-right strong {
        font-size: 18px;
        color: #333;
        /* Tạo sự tương phản nhẹ */
    }



    /* Hiệu ứng hover */
    .order-actions li:hover {
        background-color: #f9f9f9;
        /* Làm sáng nền khi hover */
        transition: background-color 0.3s ease;
        /* Hiệu ứng mượt */
    }

    /* Tăng cường phong cách */
    .order-actions::before {

        display: block;
        font-family: 'Georgia', serif;
        font-size: 24px;
        color: #000;
        text-align: center;
        margin-bottom: 10px;
        letter-spacing: 2px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .time-process {
        color: #333;
        /* Màu chữ xám đậm */
        background-color: #fff;
        /* Nền trắng */
        padding: 20px;
        border: 1px solid #ccc;
        /* Đường viền xám nhạt */
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        /* Bóng đổ nhẹ */
        margin-top: 10px;
    }

    .time-process strong {
        color: #000;
        /* Màu đen nổi bật cho tiêu đề */
        text-transform: uppercase;
        /* Viết hoa tiêu đề */
    }

    .cancel-info {
        margin-top: 15px;
        background-color: #f9f9f9;
        /* Nền xám nhạt */
        border-left: 4px solid #333;
        /* Đường kẻ xám đậm */
        padding: 10px;
        color: #555;

    }

    .status-detail.cancelled .cancel {
        color: #bd0100;
        /* Chữ trắng */


    }
    </style>



    <div class="title">
        <h2 style="font-size:50px;">Chi tiết đơn hàng</h2>
    </div>

    <div class="layout-container">

        <div class="product-details">

            <div class="product-details">
                <div class="product-list">
                    <?php if (!empty($products)) { ?>
                    <?php foreach ($products as $product) {
                            // Lấy product_id từ order_items
                            $product_id = $product['product_id'];

                            // Truy vấn bảng products để lấy code_color và capacity
                            $query = "SELECT color_name, capacity FROM products WHERE product_id = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("i", $product_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $product_details = $result->fetch_assoc();

                            // Lấy thông tin chi tiết
                            $code_color = isset($product_details['color_name']) ? $product_details['color_name'] : 'N/A';
                            $capacity = isset($product_details['capacity']) ? $product_details['capacity'] : 'N/A';

                            // Tính toán giá cuối cùng
                            $final_price = isset($product['discount_fee']) && $product['discount_fee'] > 0
                                ? $product['price'] - $product['discount_fee']
                                : $product['price'];
                                $total_price = $total_before_discount - $user_info['total_discount_price'] + $user_info['shipping_fee'];

                        ?>
                    <div class="product-item">
                        <div class="product-image">
                            <img src="../image/product/<?php echo htmlspecialchars($product['product_image']); ?>"
                                alt="Product Image">
                        </div>
                        <div class="product-info">
                            <p><strong>
                                    <?php
                                            echo htmlspecialchars($product['product_name']);
                                            if (!empty($code_color)) {
                                                echo ' - ' . htmlspecialchars($code_color);
                                            }
                                            if (!empty($capacity) && $capacity !== 'N/A') {
                                                echo ' - ' . htmlspecialchars($capacity);
                                            }
                                            ?>
                                </strong></p>

                            <p><strong>Số lượng:</strong> <?php echo htmlspecialchars($product['quantity']); ?></p>
                            <p><strong>Giá:</strong>
                                <?php if (!empty($product['discount_fee']) && $product['discount_fee'] > 0) { ?>
                                <span class="original-price" style="text-decoration: line-through; color: #999;">
                                    <?php echo number_format($product['price'], 0, ',', '.') . ' VND'; ?>
                                </span>
                                <span class="final-price" style="color: #333; font-weight: bold;">
                                    <?php echo number_format($final_price, 0, ',', '.') . ' VND'; ?>
                                </span>
                                <?php } else { ?>
                                <span class="final-price" style="color: #333; font-weight: bold;">
                                    <?php echo number_format($final_price, 0, ',', '.') . ' VND'; ?>
                                </span>
                                <?php } ?>
                            </p>
                        </div>
                    </div>
                    <?php } ?>
                    <?php } else { ?>
                    <p>Không có sản phẩm trong đơn hàng này.</p>
                    <?php } ?>
                </div>

            </div>




        </div>


        <!-- Thông tin người dùng -->
        <div class="user-info">
            <div
                class="status-detail <?php echo htmlspecialchars($user_info['status_order']) === 'Đã hủy' ? 'cancelled' : ''; ?>">
                <h3>
                    Mã đơn hàng: <strong>#SBO00<?php echo htmlspecialchars($order_id); ?></strong>
                </h3>

                <h3 class="cancel"><strong><?php echo htmlspecialchars($user_info['status_order']); ?></strong></h3>
            </div>


            <?php if (!empty($user_info)) { ?>
            <div class="user-details">
                <p>Tên người dùng: <strong><?php echo htmlspecialchars($user_info['user_name']); ?></strong></p>
                <p>Số điện thoại: <strong><?php echo htmlspecialchars($user_info['user_number']); ?></strong></p>
                <p>Email: <strong><?php echo htmlspecialchars($user_info['user_email']); ?></strong></p>
                <p>Địa chỉ: <strong><?php echo htmlspecialchars($user_info['address']); ?></strong></p>
                <p>Phương thức thanh toán: <strong><?php echo htmlspecialchars($user_info['method']); ?></strong></p>
            </div>

            <div class="total-summary">
                <div class="time-process">
                    <span><strong>Thời gian đặt hàng:</strong>
                        <?php echo htmlspecialchars($user_info['placed_on']); ?></span>

                    <?php if (!empty($cancel_info)) { ?>
                    <div class="cancel-info" style="margin-top: 10px; color: #bd0100;">
                        <p><strong>Lý do hủy:</strong> <?php echo htmlspecialchars($cancel_info['cancel_reason']); ?>
                        </p>
                        <p><strong>Thời gian hủy:</strong> <?php echo htmlspecialchars($cancel_info['cancel_at']); ?>
                        </p>
                    </div>
                    <?php } ?>
                </div>

                <div class="order-actions">
                    <ul class="order-detail">
                        <!-- Các chi tiết đơn hàng -->
                        <li class="order-total hidden">
                            <span class="text-left">Tổng tiền chưa giảm:</span>
                            <span class="text-right">
                                <strong><?php echo number_format($total_before_discount, 0, '.', '.') . ' VNĐ'; ?></strong>
                            </span>
                        </li>
                        <li class="order-total hidden">
                            <span class="text-left">Số tiền được giảm:</span>
                            <span class="text-right">
                                <strong>-<?php echo number_format($user_info['total_discount_price'], 0, '.', '.') . ' VNĐ'; ?></strong>
                            </span>
                        </li>
                        <li class="order-total hidden">
                            <span class="text-left">Phí vận chuyển:</span>
                            <span class="text-right">
                                <strong>+<?php echo number_format($user_info['shipping_fee'], 0, '.', '.') . ' VNĐ'; ?></strong>
                            </span>
                        </li>
                        <li class="order-total">
                            <span class="text-left">Thành tiền:</span>
                            <span class="text-right">
                                <strong><?php echo number_format($total_price, 0, '.', '.') . ' VNĐ'; ?></strong>
                            </span>
                        </li>
                    </ul>
                </div>

            </div>


            <?php } else { ?>
            <p>Không tìm thấy thông tin người dùng cho đơn hàng này.</p>
            <?php } ?>
        </div>
    </div>


    <script src="../js/script.js"></script>
</body>

</html>