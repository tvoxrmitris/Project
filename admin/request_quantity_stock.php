<?php
include '../connection/connection.php';

if (isset($_POST['inventory_id']) && isset($_POST['action'])) {
    $inventory_id = mysqli_real_escape_string($conn, $_POST['inventory_id']);
    $action = $_POST['action'];

    if ($action === 'request') {
        // Thêm vào bảng low_quantity_stock nếu chưa có
        $requested_at = date('Y-m-d H:i:s');
        $check_query = "SELECT * FROM low_quantity_stock WHERE inventory_id = '$inventory_id'";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) == 0) {
            $insert_query = "INSERT INTO low_quantity_stock (inventory_id, requested_at) VALUES ('$inventory_id', '$requested_at')";
            if (mysqli_query($conn, $insert_query)) {
                echo 'success';
            } else {
                echo 'Có lỗi xảy ra khi yêu cầu nhập hàng.';
            }
        } else {
            echo 'Sản phẩm đã được yêu cầu trước đó.';
        }
    } elseif ($action === 'delete') {
        // Xóa khỏi bảng low_quantity_stock
        $delete_query = "DELETE FROM low_quantity_stock WHERE inventory_id = '$inventory_id'";
        if (mysqli_query($conn, $delete_query)) {
            echo 'success';
        } else {
            echo 'Có lỗi xảy ra khi hủy yêu cầu.';
        }
    } else {
        echo 'Hành động không hợp lệ.';
    }
} else {
    echo 'Dữ liệu không hợp lệ.';
}