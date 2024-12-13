<?php
// Kết nối cơ sở dữ liệu
include '../connection/connection.php';

// Lấy các tham số từ AJAX
$productName = $_GET['product_name'];
$codeColor = $_GET['code_color'];
$capacity = $_GET['capacity'];

// Truy vấn inventory_entries để lấy inventory_id dựa trên product_name, code_color và capacity
if ($capacity === 'NULL' || $capacity === '') {
    // Nếu capacity là NULL hoặc rỗng, bỏ qua phần capacity trong truy vấn
    $query = "SELECT inventory_id FROM inventory_entries WHERE product_name = ? AND code_color = ? AND (capacity IS NULL OR capacity = ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $productName, $codeColor, $capacity);
} else {
    // Nếu capacity có giá trị, kiểm tra chính xác giá trị
    $query = "SELECT inventory_id FROM inventory_entries WHERE product_name = ? AND code_color = ? AND capacity = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $productName, $codeColor, $capacity);
}

$stmt->execute();
$result = $stmt->get_result();

// Chuẩn bị phản hồi mặc định
$response = ['match' => false];

// Nếu tìm thấy inventory_id, kiểm tra xem nó có trong bảng low_quantity_stock không
if ($row = $result->fetch_assoc()) {
    $inventory_id = $row['inventory_id'];

    // Kiểm tra low_quantity_stock
    $lowStockQuery = "SELECT * FROM low_quantity_stock WHERE inventory_id = ?";
    $lowStockStmt = $conn->prepare($lowStockQuery);
    $lowStockStmt->bind_param("i", $inventory_id);
    $lowStockStmt->execute();
    $lowStockResult = $lowStockStmt->get_result();

    // Nếu tìm thấy trong low_quantity_stock, trả về match cùng với inventory_id
    if ($lowStockResult->num_rows > 0) {
        $response = [
            'match' => true,
            'inventory_id' => $inventory_id
        ];
    }
}

// Trả về phản hồi dạng JSON
echo json_encode($response);