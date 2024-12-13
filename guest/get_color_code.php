<?php
header('Content-Type: application/json');
include '../connection/connection.php';

if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);

    // Truy vấn lấy `color_code`
    $query = "SELECT c.color_code 
              FROM products p
              JOIN code_color c ON p.color_name = c.color_name
              WHERE p.product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['color_code' => $row['color_code']]);
    } else {
        echo json_encode(['error' => 'No color found for this product']);
    }
} else {
    echo json_encode(['error' => 'Missing product_id']);
}
