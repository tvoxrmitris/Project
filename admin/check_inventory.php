<?php
// Kết nối tới cơ sở dữ liệu
include '../connection/connection.php';

// Nhận dữ liệu từ yêu cầu POST
$product_name = $_POST['productName'];
$code_color = $_POST['codeColor'];
$capacity = $_POST['capacity'];

// Truy vấn để lấy thông tin tồn kho
$query = "SELECT inventory_id, quantity_stock, capacity FROM inventory_entries WHERE product_name = ? AND code_color = ? AND capacity = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $product_name, $code_color, $capacity);
$stmt->execute();
$result = $stmt->get_result();

$response = ['quantity_stock' => 0, 'inventory_id' => '', 'capacity' => ''];

if ($row = $result->fetch_assoc()) {
    $response['quantity_stock'] = $row['quantity_stock'];
    $response['inventory_id'] = $row['inventory_id'];
    $response['capacity'] = $row['capacity']; // Thêm capacity vào phản hồi
}

// Đóng các statement và kết nối
$stmt->close();
$conn->close();

// Trả về dữ liệu JSON
echo json_encode($response);
