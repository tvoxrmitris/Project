<?php
include '../connection/connection.php';
session_start();

if (isset($_POST['coupon_code'], $_POST['total_price'], $_POST['current_discount'])) {
    $coupon_code = trim($_POST['coupon_code']);
    $total_price = floatval($_POST['total_price']);
    $current_discount = floatval($_POST['current_discount']);
    $user_id = $_SESSION['user_id']; // Lấy ID người dùng từ session

    if ($total_price <= 0) {
        echo json_encode(["status" => "error", "message" => "Tổng giá trị không hợp lệ."]);
        exit;
    }

    // Kiểm tra xem mã giảm giá đã được sử dụng chưa
    $check_used_coupon_stmt = $conn->prepare("SELECT * FROM coupon_used WHERE user_id = ? AND coupon_name = ?");
    if (!$check_used_coupon_stmt) {
        echo json_encode(["status" => "error", "message" => "Lỗi truy vấn cơ sở dữ liệu."]);
        exit;
    }

    $check_used_coupon_stmt->bind_param("is", $user_id, $coupon_code);
    $check_used_coupon_stmt->execute();
    $used_result = $check_used_coupon_stmt->get_result();

    if ($used_result->num_rows > 0) {
        // Nếu mã đã được sử dụng
        echo json_encode(["status" => "error", "message" => "Mã giảm giá đã được sử dụng."]);
        exit;
    }

    // Truy vấn kiểm tra mã giảm giá hợp lệ
    $stmt = $conn->prepare("SELECT discount_percent FROM promotions WHERE code_discount = ? AND start_date <= CURDATE() AND end_date >= CURDATE()");
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Lỗi truy vấn cơ sở dữ liệu."]);
        exit;
    }

    $stmt->bind_param("s", $coupon_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $discount_percent = $row['discount_percent'];

        if ($discount_percent > 0 && $discount_percent <= 100) {
            $new_discount_amount = ($discount_percent / 100) * $total_price;
            $total_discount = $current_discount + $new_discount_amount;
            $final_price = max(0, $total_price - $total_discount);

            echo json_encode([
                "status" => "success",
                "new_discount_amount" => $new_discount_amount,
                "total_discount" => $total_discount,
                "final_price" => $final_price,
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Phần trăm giảm giá không hợp lệ."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Mã giảm giá không hợp lệ hoặc đã hết hạn."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Dữ liệu không hợp lệ."]);
}
