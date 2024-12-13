<?php
include '../connection/connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'] ?? '';
    $color_name = $_POST['color_name'] ?? '';
    $capacity = $_POST['capacity'] ?? '';

    // Kiểm tra dữ liệu đầu vào
    if (empty($product_name) || empty($color_name) || empty($capacity)) {
        echo "invalid input";
        exit;
    }

    // Truy vấn để kiểm tra sản phẩm với product_name, color_name và capacity đã cho
    $query = "SELECT product_id FROM products WHERE product_name = ? AND color_name = ? AND capacity = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $product_name, $color_name, $capacity);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Lấy product_id từ kết quả truy vấn
        $row = $result->fetch_assoc();
        echo $row['product_id'];  // Trả về product_id
    } else {
        echo "not found";  // Không tìm thấy sản phẩm
    }
}