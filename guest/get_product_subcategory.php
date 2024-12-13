<?php
include '../connection/connection.php';

if (isset($_GET['product_name']) && isset($_GET['color_name'])) {
    $productName = $_GET['product_name'];
    $colorName = $_GET['color_name'];

    // Truy vấn lấy product_subcategory từ bảng products
    $stmt = $conn->prepare("SELECT product_subcategory FROM products WHERE product_name = ? AND color_name = ?");
    $stmt->bind_param("ss", $productName, $colorName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(['product_subcategory' => $row['product_subcategory']]);
    } else {
        echo json_encode(['error' => 'Product not found.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['error' => 'Invalid request.']);
}
?>