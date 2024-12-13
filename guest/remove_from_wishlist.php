<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nhận dữ liệu từ yêu cầu AJAX
    $data = json_decode(file_get_contents('php://input'), true);
    $product_id = $data['product_id'];

    // Kiểm tra xem wishlist đã tồn tại trong session chưa
    if (isset($_SESSION['wishlist'])) {
        // Lọc ra sản phẩm cần xóa
        $_SESSION['wishlist'] = array_filter($_SESSION['wishlist'], function ($item) use ($product_id) {
            return $item['product_id'] !== $product_id;
        });

        // Trả về kết quả thành công
        echo json_encode(['success' => true]);
    } else {
        // Trả về kết quả thất bại nếu không có wishlist
        echo json_encode(['success' => false]);
    }
}
