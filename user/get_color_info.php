<?php
include '../connection/connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'];
    $color_name = $_POST['color_name'];

    // Truy vấn để kiểm tra sản phẩm và màu sắc
    $query = "SELECT product_id FROM products WHERE product_name = ? AND color_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $product_name, $color_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "found"; // Trả về "found" nếu tìm thấy
    } else {
        echo "not found"; // Trả về "not found" nếu không tìm thấy
    }
}
