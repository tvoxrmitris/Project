<?php
include '../connection/connection.php';

if (isset($_POST['keyword'])) {
    $keyword = trim($_POST['keyword']); // Lấy từ khóa từ POST và loại bỏ khoảng trắng

    if (!empty($keyword)) {
        // Kiểm tra nếu từ khóa đã tồn tại trong bảng search_keywords
        $sql = "SELECT search_count FROM search_keywords WHERE keyword = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $keyword);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Từ khóa đã tồn tại, cập nhật search_count
            $sql = "UPDATE search_keywords SET search_count = search_count + 1 WHERE keyword = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $keyword);
            $stmt->execute();
        } else {
            // Từ khóa chưa tồn tại, thêm mới với search_count = 1
            $sql = "INSERT INTO search_keywords (keyword, search_count) VALUES (?, 1)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $keyword);
            $stmt->execute();
        }

        $stmt->close();
    }
}
