<?php
include '../connection/connection.php';
session_start();

// Lấy dữ liệu từ MoMo
$data = json_decode(file_get_contents('php://input'), true);

// Kiểm tra tính hợp lệ của dữ liệu từ MoMo
if ($data['status'] == 'success') {
    $user_id = $_SESSION['user_id']; // Lấy ID người dùng từ session
    $orderId = $data['orderId']; // ID đơn hàng từ MoMo
    $amount = $data['amount']; // Số tiền thanh toán
    $product_id = $data['product_id']; // product_id (nếu bạn truyền trong extraData)

    // Lấy thông tin sản phẩm
    $select_product = mysqli_query($conn, "SELECT product_name, product_price FROM `products` WHERE product_id = '$product_id'") or die('Lỗi truy vấn sản phẩm: ' . mysqli_error($conn));
    if (mysqli_num_rows($select_product) > 0) {
        $fetch_product = mysqli_fetch_assoc($select_product);
        $product_name = $fetch_product['product_name'];
        $product_price = $fetch_product['product_price'];

        // Chèn dữ liệu vào bảng `orders`
        $payment_status = "Đã thanh toán";
        $insert_order = "INSERT INTO `orders` (user_id, method, total_price, placed_on, status_order) VALUES ('$user_id', 'Momo', '$amount', NOW(), '$payment_status')";
        $insert_order_result = mysqli_query($conn, $insert_order) or die('Lỗi khi thêm đơn hàng: ' . mysqli_error($conn));

        if ($insert_order_result) {
            $order_id = mysqli_insert_id($conn); // Lấy ID đơn hàng vừa thêm

            // Chèn thông tin vào bảng `order_items`
            $quantity = 1; // Hoặc lấy từ dữ liệu nếu cần
            $total_discount = 0; // Nếu có giảm giá thì tính thêm
            $total_discounted_price = $product_price; // Giá sản phẩm sau khi áp dụng giảm giá

            $insert_order_item = "INSERT INTO `order_items` (order_id, product_id, product_name, quantity, price, total_price) VALUES ('$order_id', '$product_id', '$product_name', '$quantity', '$product_price', '$total_discounted_price')";
            $insert_order_item_result = mysqli_query($conn, $insert_order_item) or die('Lỗi khi thêm sản phẩm vào đơn hàng: ' . mysqli_error($conn));

            if ($insert_order_item_result) {
                echo "Đơn hàng đã được thêm thành công!";
            } else {
                echo "Có lỗi khi thêm sản phẩm vào đơn hàng.";
            }
        } else {
            echo "Có lỗi khi thêm đơn hàng.";
        }
    } else {
        echo "Sản phẩm không tồn tại.";
    }
} else {
    echo "Thanh toán không thành công.";
}
