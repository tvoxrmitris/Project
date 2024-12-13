<?php
include '../connection/connection.php';
session_start();

if (isset($_POST['employee_id']) && isset($_POST['employee_type'])) {
    $employee_id = $_POST['employee_id'];
    $employee_type = $_POST['employee_type'];

    // Kiểm tra xem người dùng hiện tại có quyền "super admin"
    if ($_SESSION['employee_type'] == 'super admin') {
        // Cập nhật employee_type trong bảng employees
        $update_query = "UPDATE employees SET employee_type = '$employee_type' WHERE employee_id = '$employee_id'";
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['message'] = "Đã cập nhật chức vụ thành công";
        } else {
            $_SESSION['message'] = "Cập nhật chức vụ thất bại";
        }
    } else {
        $_SESSION['message'] = "Bạn không có quyền thay đổi chức vụ";
    }

    header('Location: ../admin/admin_staff.php');
    exit();
} else {
    header('Location: ../admin/admin_staff.php');
    exit();
}
