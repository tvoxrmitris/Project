<?php
include '../connection/connection.php';

header('Content-Type: application/json');

if (isset($_POST['action']) && isset($_POST['product_id'])) {
    $action = $_POST['action'];
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);

    if ($action === 'request') {
        // Kiểm tra sản phẩm đã tồn tại trong bảng low_stock_requests
        $check_request_query = "SELECT * FROM low_stock_requests WHERE product_id = '$product_id'";
        $check_request_result = mysqli_query($conn, $check_request_query);

        if (mysqli_num_rows($check_request_result) > 0) {
            // Đã có yêu cầu trước đó
            $message = 'Sản phẩm đã được yêu cầu nhập hàng trước đó.';
            echo json_encode(['status' => 'exists', 'message' => $message]);
        } else {
            // Chèn yêu cầu mới
            $requested_at = date('Y-m-d H:i:s');
            $insert_request_query = "INSERT INTO low_stock_requests (product_id, requested_at) VALUES ('$product_id', '$requested_at')";

            if (mysqli_query($conn, $insert_request_query)) {
                $message = 'Yêu cầu nhập hàng thành công!';
                echo json_encode(['status' => 'success', 'message' => $message]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Lỗi khi chèn dữ liệu: ' . mysqli_error($conn)]);
            }
        }
    } elseif ($action === 'delete') {
        // Xóa sản phẩm khỏi bảng low_stock_requests
        $delete_request_query = "DELETE FROM low_stock_requests WHERE product_id = '$product_id'";
        if (mysqli_query($conn, $delete_request_query)) {
            $message = 'Yêu cầu nhập hàng đã được xóa thành công.';
            echo json_encode(['status' => 'success', 'message' => $message]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi khi xóa dữ liệu: ' . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Hành động không hợp lệ.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Thiếu tham số action hoặc product_id']);
}