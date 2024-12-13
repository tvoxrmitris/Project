<?php
include '../connection/connection.php';

if (isset($_POST['category_id'])) {
    $category_id = intval($_POST['category_id']); // Chuyển đổi sang số nguyên để tránh SQL Injection

    // Lấy danh sách subcategory dựa trên category_id
    $query = "SELECT subcategory_id, subcategory_name FROM subcategory WHERE category_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $subcategories = array();
    while ($row = $result->fetch_assoc()) {
        $subcategories[] = $row;
    }

    // Đóng kết nối
    $stmt->close();

    // Trả về kết quả dưới dạng JSON
    header('Content-Type: application/json');
    echo json_encode($subcategories);
}