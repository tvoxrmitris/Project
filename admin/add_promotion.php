<?php
include '../connection/connection.php';

if (isset($_GET['product_id']) && isset($_GET['code_discount'])) {
    $product_id = intval($_GET['product_id']);
    $code_discount = htmlspecialchars($_GET['code_discount']);

    // Truy vấn bảng promotions để lấy discount_percent, start_date, end_date
    $promotion_query = "SELECT discount_percent, start_date, end_date FROM promotions WHERE code_discount = '$code_discount'";
    $promotion_result = mysqli_query($conn, $promotion_query);

    if ($promotion_result && mysqli_num_rows($promotion_result) > 0) {
        $promotion_data = mysqli_fetch_assoc($promotion_result);

        $discount_percent = $promotion_data['discount_percent'];
        $start_date = $promotion_data['start_date'];
        $end_date = $promotion_data['end_date'];

        // Kiểm tra xem sản phẩm đã có trong product_promotion chưa
        $check_query = "SELECT * FROM product_promotion WHERE product_id = $product_id AND code_discount = '$code_discount'";
        $check_result = mysqli_query($conn, $check_query);

        if ($check_result && mysqli_num_rows($check_result) == 0) {
            // Thực hiện thêm dữ liệu vào bảng product_promotion
            $insert_query = "
                INSERT INTO product_promotion (product_id, code_discount, discount_percent, start_date, end_date) 
                VALUES ($product_id, '$code_discount', $discount_percent, '$start_date', '$end_date')
            ";
            $insert_result = mysqli_query($conn, $insert_query);

            if ($insert_result) {
                echo "<script>alert('Thêm khuyến mãi thành công!'); window.location.href = 'admin_discount.php';</script>";
            } else {
                echo "<script>alert('Thêm khuyến mãi thất bại!'); window.location.href = 'admin_discount.php';</script>";
            }
        } else {
            echo "<script>alert('Sản phẩm đã có khuyến mãi này!'); window.location.href = 'admin_discount.php';</script>";
        }
    } else {
        echo "<script>alert('Mã giảm giá không hợp lệ!'); window.location.href = 'admin_discount.php';</script>";
    }
} else {
    echo "<script>alert('Thông tin không đầy đủ!'); window.location.href = 'admin_discount.php';</script>";
}
?>