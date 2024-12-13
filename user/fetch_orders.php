<?php
include '../connection/connection.php';

$user_id = $_SESSION['user_id']; // Giả sử đã đăng nhập và có `user_id`

// Lấy trạng thái từ tham số GET
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Tạo câu truy vấn SQL dựa trên trạng thái
if ($status === 'pending') {
    $query = "SELECT *, DATE_FORMAT(placed_on, '%d-%m-%Y %H:%i:%s') as placed_on_formatted FROM `orders` WHERE user_id='$user_id' AND status_order='đang chờ xử lí'";
} elseif ($status === 'preparing') {
    $query = "SELECT *, DATE_FORMAT(placed_on, '%d-%m-%Y %H:%i:%s') as placed_on_formatted FROM `orders` WHERE user_id='$user_id' AND status_order='đang chuẩn bị'";
} elseif ($status === 'shipping') {
    $query = "SELECT *, DATE_FORMAT(placed_on, '%d-%m-%Y %H:%i:%s') as placed_on_formatted FROM `orders` WHERE user_id='$user_id' AND status_order='đang giao'";
} elseif ($status === 'delivered') {
    $query = "SELECT *, DATE_FORMAT(placed_on, '%d-%m-%Y %H:%i:%s') as placed_on_formatted FROM `orders` WHERE user_id='$user_id' AND status_order='đã giao'";
} else {
    $query = "SELECT *, DATE_FORMAT(placed_on, '%d-%m-%Y %H:%i:%s') as placed_on_formatted FROM `orders` WHERE user_id='$user_id'";
}

$select_orders = mysqli_query($conn, $query) or die('query failed');

if (mysqli_num_rows($select_orders) > 0) {
    while ($fetch_orders = mysqli_fetch_assoc($select_orders)) {
        $order_id = $fetch_orders['order_id'];
        // Kiểm tra đánh giá
        $check_evaluation = mysqli_query($conn, "SELECT * FROM `evaluate` WHERE order_id='$order_id'") or die('query failed');
        $is_evaluated = mysqli_num_rows($check_evaluation) > 0;

        // Truy vấn sản phẩm của đơn hàng
        $select_order_items = mysqli_query(
            $conn,
            "SELECT oi.product_id, oi.discount_fee, oi.price, oi.product_image, oi.product_name, oi.quantity, oi.total_price, 
                p.color_name, p.capacity, p.detail_color 
             FROM `order_items` oi 
             JOIN `products` p ON oi.product_id = p.product_id 
             WHERE oi.order_id='$order_id'"
        ) or die('query failed');

        // HTML hiển thị chi tiết đơn hàng giống trang hiện tại
        echo '<div class="box">';
        echo '<p class="order-date">Ngày đặt hàng: ' . $fetch_orders['placed_on_formatted'] . '</p>';

        while ($fetch_order_items = mysqli_fetch_assoc($select_order_items)) {
            $image_name = $fetch_order_items['product_image'];
            $product_name = $fetch_order_items['product_name'];
            $quantity = $fetch_order_items['quantity'];
            $product_price = $fetch_order_items['price'];
            $discount_fee = $fetch_order_items['discount_fee'];
            $total_price = $product_price - ($discount_fee / $quantity);

            $color_name = $fetch_order_items['color_name'];
            $capacity = $fetch_order_items['capacity'];

            echo '<div class="product-item">';
            echo '<div class="img-order"><img class="img_order" src="../image/product/' . $image_name . '" alt="Product Image"></div>';
            echo '<div class="order-summary">';
            echo '<p>' . $product_name . '</p>';
            echo '<p>Màu sắc: ' . $color_name;
            if (!empty($fetch_order_items['detail_color'])) {
                echo ' - ' . $fetch_order_items['detail_color'];
            }
            echo '</p>';

            if (!empty($capacity)) {
                echo '<p>Dung tích: ' . $capacity . '</p>';
            }

            echo '<div class="order-quantity-price">';
            echo '<p class="quantity">Số lượng: ' . $quantity . '</p>';
            echo '<div class="price-order">';
            if (empty($discount_fee)) {
                echo '<span class="product-price">' . number_format($product_price, 0, '.', '.') . ' VNĐ</span>';
            } else {
                echo '<span class="original-price">' . number_format($product_price, 0, '.', '.') . ' VNĐ</span>';
                echo '<span>' . number_format($total_price, 0, '.', '.') . ' VNĐ</span>';
            }
            echo '</div></div></div></div>';
        }

        echo '<div class="order-actions">';
        echo '<p class="order-total">Thành tiền: ' . number_format($fetch_orders['total_price'], 0, '.', '.') . ' VNĐ</p>';
        echo '</div></div>';
    }
} else {
    echo '<div class="empty"><p>Chưa có đơn hàng nào được đặt!</p></div>';
}
