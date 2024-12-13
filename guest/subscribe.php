<?php
include '../connection/connection.php';

// Lấy dữ liệu từ AJAX
$email = $_POST['email'];
$name = $_POST['name'];

// Kiểm tra xem email đã tồn tại trong cơ sở dữ liệu chưa
$checkStmt = $conn->prepare("SELECT 1 FROM newsletter WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows === 0) {
    // Thêm email vào cơ sở dữ liệu nếu chưa tồn tại
    $stmt = $conn->prepare("INSERT INTO newsletter (email, name, subscribed_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $email, $name);

    if ($stmt->execute()) {
        echo "Bạn đã đăng ký thành công!";
    } else {
        echo "Có lỗi xảy ra: " . $stmt->error;
    }

    $stmt->close();
} else {
    // Bỏ qua nếu email đã tồn tại
    echo "Email này đã tồn tại. Không cần đăng ký lại.";
}

// Đóng kết nối
$checkStmt->close();
$conn->close();
