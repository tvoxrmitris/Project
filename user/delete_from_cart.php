<?php
include '../connection/connection.php';
session_start();

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo 'not_logged_in'; // Trả về phản hồi cho JavaScript
    exit;
}

$user_id = $_SESSION['user_id']; // Lấy user_id từ session

if (isset($_POST['cart_id'])) {
    $cart_id = $_POST['cart_id'];

    // Xóa sản phẩm khỏi bảng `cart`
    $delete_cart_item = mysqli_query($conn, "DELETE FROM `cart` WHERE cart_id = '$cart_id'") or die('Lỗi truy vấn: ' . mysqli_error($conn));

    if ($delete_cart_item) {
        // Có thể thêm thông báo thành công nếu cần
        header('Location: ../user/cart.php'); // Chuyển hướng về trang giỏ hàng
    } else {
        echo 'Có lỗi xảy ra khi xóa sản phẩm!';
    }
} else {
    echo 'Không có ID sản phẩm!';
}