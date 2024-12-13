<?php
include '../connection/connection.php';

// Thiết lập header cho JSON
header('Content-Type: application/json');

$popularKeywords = [];

try {
    // Lấy các từ khóa phổ biến nhất, sắp xếp theo số lần tìm kiếm (search_count)
    $sql = "SELECT keyword, search_count FROM search_keywords ORDER BY search_count DESC LIMIT 5";
    $result = $conn->query($sql);

    // Kiểm tra và thêm từ khóa vào mảng
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $popularKeywords[] = $row['keyword'];
        }
    }
} catch (Exception $e) {
    error_log("Error fetching popular keywords: " . $e->getMessage());
}

// Trả về JSON
echo json_encode($popularKeywords);
