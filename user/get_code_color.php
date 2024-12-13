<?php
// Kết nối cơ sở dữ liệu
include '../connection/connection.php';

// Nhận dữ liệu từ yêu cầu AJAX
$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'];
$color_name = $data['color_name'];

// Truy vấn bảng code_color để lấy mã màu
$query = "SELECT color_code FROM code_color WHERE product_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$stmt->bind_result($color_code);
$stmt->fetch();
$stmt->close();

// Trả kết quả về dưới dạng JSON
if ($color_code) {
    echo json_encode(['success' => true, 'color_code' => $color_code]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy mã màu.']);
}
?>