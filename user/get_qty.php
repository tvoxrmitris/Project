<?php
include '../connection/connection.php';

// Truy vấn cơ sở dữ liệu để lấy giá trị qtyfor10ml từ bảng products
$sql = "SELECT qtyfor10ml FROM products";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Chuyển kết quả thành mảng kết hợp và trả về dưới dạng JSON
    $row = $result->fetch_assoc();
    echo json_encode($row);
} else {
    // Trả về một đối tượng JSON trống nếu không có dữ liệu
    echo json_encode(array());
}
?>
