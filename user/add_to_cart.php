<?php
// Kết nối đến cơ sở dữ liệu
include '../connection/connection.php';

// Bắt đầu phiên làm việc để lưu thông tin giỏ hàng
session_start();

// Kiểm tra xem có nhận được product_id từ AJAX không
if (isset($_GET['add'])) {
    $product_id = intval($_GET['add']); // Lấy product_id từ chuỗi truy vấn
    
    // Kiểm tra xem người dùng đã đăng nhập chưa
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Sử dụng câu lệnh chuẩn bị (prepared statement) để lấy thông tin sản phẩm từ bảng products
        $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $fetch_product = $result->fetch_assoc();

            $product_name = $fetch_product['product_name'];
            $product_image = explode(',', $fetch_product['product_image'])[0]; // Lấy hình ảnh đầu tiên
            $quantity = 1; // Số lượng mặc định là 1

            // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
            $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $cart_result = $stmt->get_result();

            if ($cart_result->num_rows > 0) {
                // Nếu sản phẩm đã có trong giỏ hàng, cập nhật số lượng
                $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
                $stmt->bind_param("ii", $user_id, $product_id);
                $stmt->execute();
                echo json_encode(['status' => 'updated', 'message' => 'Sản phẩm đã có trong giỏ hàng, số lượng đã được cập nhật!']);
            } else {
                // Nếu sản phẩm chưa có trong giỏ hàng, thêm mới
                $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, product_name, quantity, product_image) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisss", $user_id, $product_id, $product_name, $quantity, $product_image);
                $stmt->execute();
                echo json_encode(['status' => 'success', 'message' => 'Sản phẩm đã được thêm vào giỏ hàng!']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Sản phẩm không tồn tại!']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Không có sản phẩm nào được chọn!']);
}

?>
