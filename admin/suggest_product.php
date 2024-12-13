<?php
include '../connection/connection.php';

if (isset($_GET['query'])) {
    $query = $conn->real_escape_string($_GET['query']);

    // Truy vấn lấy thông tin từ bảng inventory_entries và kiểm tra tồn tại trong bảng low_quantity_stock
    $sql = "SELECT ie.*, 
                   CASE WHEN lqs.inventory_id IS NOT NULL THEN 1 ELSE 0 END AS in_low_stock 
            FROM inventory_entries ie
            LEFT JOIN low_quantity_stock lqs ON ie.inventory_id = lqs.inventory_id
            WHERE ie.product_name LIKE '%$query%'";
    $result = $conn->query($sql);

    $suggestions = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $suggestions[] = $row;
        }
    }

    echo json_encode($suggestions);
}

$conn->close();
