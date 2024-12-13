<?php
session_start();

if (isset($_POST['cart_id'])) {
    $cart_id = intval($_POST['cart_id']); // Lấy cart_id từ POST

    // Kiểm tra nếu session giỏ hàng tồn tại và có sản phẩm với cart_id đó
    if (isset($_SESSION['cart'][$cart_id])) {
        // Xóa sản phẩm khỏi giỏ hàng trong session
        unset($_SESSION['cart'][$cart_id]);

        // Trả về kết quả thành công
        echo 'success';
    } else {
        // Nếu không tìm thấy sản phẩm trong giỏ hàng
        echo 'error: Product not found in cart';
    }
} else {
    // Nếu không nhận được cart_id từ POST
    echo 'invalid';
}
