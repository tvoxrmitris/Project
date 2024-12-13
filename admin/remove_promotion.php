<?php
session_start();
include '../connection/connection.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'] ?? null;

if ($product_id) {
    $product_id = mysqli_real_escape_string($conn, $product_id);

    // Kiểm tra xem product_id có tồn tại trong bảng product_promotion
    $promotion_check_query = "SELECT * FROM product_promotion WHERE product_id = '$product_id'";
    $promotion_check_result = mysqli_query($conn, $promotion_check_query);

    if (mysqli_num_rows($promotion_check_result) > 0) {
        // Nếu tồn tại, thực hiện xóa
        $delete_promotion_query = "DELETE FROM product_promotion WHERE product_id = '$product_id'";
        if (mysqli_query($conn, $delete_promotion_query)) {
            echo json_encode([
                'success' => true,
                'message' => "Đã xóa giảm giá cho sản phẩm ID $product_id."
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không thể xóa giảm giá. Vui lòng thử lại.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => "Sản phẩm ID $product_id không có giảm giá."
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Dữ liệu không hợp lệ.'
    ]);
}
?>