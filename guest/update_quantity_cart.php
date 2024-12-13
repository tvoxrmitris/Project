<?php
// Bắt đầu session
session_start();
include '../connection/connection.php';
// Kiểm tra xem dữ liệu có được gửi qua POST không
if (isset($_POST['cart_id']) && isset($_POST['quantity'])) {
    $cart_id = $_POST['cart_id'];
    $new_quantity = $_POST['quantity'];

    // Kiểm tra nếu session 'cart' đã tồn tại và có sản phẩm trong giỏ hàng
    if (isset($_SESSION['cart'][$cart_id])) {
        // Lấy ID sản phẩm từ giỏ hàng
        $product_id = $_SESSION['cart'][$cart_id]['product_id'];

        // Truy vấn cơ sở dữ liệu để lấy số lượng có sẵn trong kho
        $select_product = mysqli_query($conn, "SELECT quantity_in_stock FROM `products` WHERE product_id = '$product_id'") or die('Lỗi truy vấn: ' . mysqli_error($conn));
        $fetch_product = mysqli_fetch_assoc($select_product);

        // Nếu tìm thấy thông tin sản phẩm
        if ($fetch_product) {
            $quantity_in_stock = $fetch_product['quantity_in_stock'];

            // Kiểm tra nếu số lượng yêu cầu lớn hơn số lượng trong kho
            if ($new_quantity > $quantity_in_stock) {
                // Nếu vượt quá số lượng trong kho, không cho phép cập nhật
                echo '<script>alert("Số lượng yêu cầu vượt quá số lượng trong kho!");</script>';
                echo '<script>window.location.href = "../guest/cart_guest.php";</script>';
                exit();
            } else {
                // Cập nhật số lượng trong giỏ hàng
                $_SESSION['cart'][$cart_id]['quantity'] = $new_quantity;
            }
        }
    }

    // Sau khi cập nhật, chuyển hướng người dùng về trang giỏ hàng của khách
    header('Location: ../guest/cart_guest.php');
    exit(); // Đảm bảo không tiếp tục xử lý sau khi chuyển hướng
}
?>