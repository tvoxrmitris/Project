<?php
// Kết nối đến cơ sở dữ liệu
include '../connection/connection.php';

// Bắt đầu phiên làm việc để lưu thông tin giỏ hàng
session_start();

// Kiểm tra xem có nhận được product_id từ AJAX không
if (isset($_GET['add'])) {
    $product_id = intval($_GET['add']); // Lấy product_id từ chuỗi truy vấn

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

        // Kiểm tra xem giỏ hàng trong session đã tồn tại hay chưa
        if (isset($_SESSION['cart'])) {
            $product_exists = false;

            // Duyệt qua giỏ hàng để kiểm tra xem sản phẩm đã tồn tại hay chưa
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['product_id'] == $product_id) {
                    $item['quantity'] += 1; // Nếu tồn tại, tăng số lượng lên 1
                    $product_exists = true;
                    break;
                }
            }

            // Nếu sản phẩm chưa có trong giỏ hàng, thêm mới
            if (!$product_exists) {
                $_SESSION['cart'][] = [
                    'product_id' => $product_id,
                    'product_name' => $product_name,
                    'product_image' => $product_image,
                    'quantity' => $quantity
                ];
            }
        } else {
            // Nếu session giỏ hàng chưa tồn tại, tạo giỏ hàng mới và thêm sản phẩm
            $_SESSION['cart'] = [[
                'product_id' => $product_id,
                'product_name' => $product_name,
                'product_image' => $product_image,
                'quantity' => $quantity
            ]];
        }

        // Trả về phản hồi JSON để báo thành công
        echo json_encode(['status' => 'success', 'message' => 'Sản phẩm đã được thêm vào giỏ hàng']);
    } else {
        // Trả về phản hồi JSON nếu không tìm thấy sản phẩm
        echo json_encode(['status' => 'error', 'message' => 'Sản phẩm không tồn tại']);
    }
} else {
    // Trả về phản hồi JSON nếu không nhận được product_id
    echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ']);
}