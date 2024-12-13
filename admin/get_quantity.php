<?php
include '../connection/connection.php';

// Kiểm tra xem các tham số cần thiết có được gửi qua POST hay không
if (isset($_POST['product_name'], $_POST['code_color'], $_POST['capacity'])) {
    // Lấy dữ liệu từ yêu cầu POST và bảo vệ chống SQL Injection
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $code_color = mysqli_real_escape_string($conn, $_POST['code_color']);
    $capacity = mysqli_real_escape_string($conn, $_POST['capacity']);

    // Debug: Log các giá trị nhận được
    error_log("Received values - Product: $product_name, Color: $code_color, Capacity: $capacity");

    // Truy vấn cơ sở dữ liệu để lấy số lượng tồn kho
    $query = "
        SELECT quantity_stock 
        FROM inventory_entries 
        WHERE product_name = '$product_name' 
        AND code_color = '$code_color' 
        AND capacity = '$capacity'
    ";

    // Debug: Log câu truy vấn
    error_log("Query: $query");

    $result = mysqli_query($conn, $query);

    // Kiểm tra lỗi truy vấn
    if (!$result) {
        error_log("MySQL Error: " . mysqli_error($conn));
        echo json_encode(['error' => 'Lỗi truy vấn cơ sở dữ liệu.']);
        exit;
    }

    // Kiểm tra xem truy vấn có thành công và có dữ liệu trả về hay không
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $response = ['quantity_stock' => $row['quantity_stock']];

        // Debug: Log kết quả
        error_log("Found quantity_stock: " . $row['quantity_stock']);

        echo json_encode($response);
    } else {
        error_log("No results found for the given criteria");
        echo json_encode(['error' => 'Không tìm thấy sản phẩm.']);
    }
} else {
    // Debug: Log các tham số thiếu
    error_log("Missing parameters: " . print_r($_POST, true));
    echo json_encode(['error' => 'Thiếu dữ liệu.']);
}

// Đóng kết nối
mysqli_close($conn);
