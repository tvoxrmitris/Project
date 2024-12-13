<?php
include '../connection/connection.php';

// Lấy từ khóa tìm kiếm từ URL (phương thức GET)
$query = isset($_GET['query']) ? $_GET['query'] : '';

if ($query) {
    // Truy vấn sản phẩm với tên chứa từ khóa tìm kiếm (không phân biệt hoa thường)
    $sql = "SELECT product_id, product_name, color_name, capacity FROM products WHERE product_name LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchTerm = '%' . $query . '%';
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();

    $result = $stmt->get_result();
    $products = [];

    // Lấy tất cả kết quả và chuyển đổi thành mảng JSON
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    // Trả về kết quả JSON
    echo json_encode($products);
} else {
    echo json_encode([]); // Trả về mảng trống nếu không có từ khóa
}

// Đóng kết nối
$conn->close();