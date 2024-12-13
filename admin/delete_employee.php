<?php
// Kết nối cơ sở dữ liệu
include '../connection/connection.php';

// Kiểm tra nếu nhận được `employee_id` từ URL
if (isset($_GET['employee_id'])) {
    $employee_id = $_GET['employee_id'];

    // Prepared statement để xóa nhân viên
    $stmt = $conn->prepare("DELETE FROM `employees` WHERE `employee_id` = ?");
    $stmt->bind_param("s", $employee_id); // "s" là kiểu dữ liệu cho chuỗi

    if ($stmt->execute()) {
        echo "<script>alert('Đã xóa nhân viên thành công!');</script>";
    } else {
        echo "<script>alert('Xóa nhân viên thất bại!');</script>";
    }

    $stmt->close();
}

// Chuyển hướng quay lại trang danh sách nhân viên
header("Location: admin_staff.php");
exit;
