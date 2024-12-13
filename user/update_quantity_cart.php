<?php
session_start(); // Đảm bảo session được khởi tạo
include '../connection/connection.php';

if (isset($_POST['cart_id']) && isset($_POST['quantity'])) {
    $cart_id = $_POST['cart_id'];
    $new_quantity = $_POST['quantity'];

    // Lấy product_id từ bảng cart để kiểm tra số lượng có sẵn trong kho
    $get_product_query = "SELECT product_id FROM cart WHERE cart_id = '$cart_id'";
    $result_product = mysqli_query($conn, $get_product_query);

    if (mysqli_num_rows($result_product) > 0) {
        $fetch_product = mysqli_fetch_assoc($result_product);
        $product_id = $fetch_product['product_id'];

        // Lấy số lượng có sẵn trong kho của sản phẩm
        $get_stock_query = "SELECT quantity_in_stock FROM products WHERE product_id = '$product_id'";
        $result_stock = mysqli_query($conn, $get_stock_query);

        if (mysqli_num_rows($result_stock) > 0) {
            $fetch_stock = mysqli_fetch_assoc($result_stock);
            $quantity_in_stock = $fetch_stock['quantity_in_stock'];

            // Kiểm tra nếu số lượng muốn cập nhật lớn hơn số lượng có sẵn trong kho
            if ($new_quantity > $quantity_in_stock) {
                $_SESSION['error_message'] = "Số lượng sản phẩm vượt quá số lượng có sẵn trong kho!";
            } else {
                $update_quantity = mysqli_query($conn, "UPDATE `cart` SET `quantity` = '$new_quantity' WHERE `cart_id` = '$cart_id'")
                    or die('Lỗi truy vấn: ' . mysqli_error($conn));

                if ($update_quantity) {
                    $_SESSION['success_message'] = "Cập nhật số lượng thành công!";
                }
            }
        } else {
            $_SESSION['error_message'] = "Không tìm thấy sản phẩm trong kho.";
        }
    } else {
        $_SESSION['error_message'] = "Không tìm thấy sản phẩm trong giỏ hàng.";
    }

    // Redirect về trang cart.php sau khi xử lý xong
    header('Location: ../user/cart.php');
    exit();
}
?>