<?php
include '../connection/connection.php';

// Kiểm tra nếu 'inventory_id' được truyền qua URL
if (isset($_GET['inventory_id'])) {
    // Lấy giá trị 'inventory_id'
    $inventory_id = intval($_GET['inventory_id']);

    // Truy vấn để xóa sản phẩm từ bảng 'inventory_entries'
    $delete_query = "DELETE FROM inventory_entries WHERE inventory_id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);

    // Kiểm tra nếu câu truy vấn chuẩn bị thành công
    if ($stmt) {
        // Gán giá trị vào dấu '?' trong câu truy vấn
        mysqli_stmt_bind_param($stmt, 'i', $inventory_id);

        // Thực thi câu truy vấn
        if (mysqli_stmt_execute($stmt)) {
            // Nếu xóa thành công, chuyển hướng về trang quản lý sản phẩm với thông báo thành công
            header('Location: product_inventory.php?message=deleted');
        } else {
            // Nếu xóa thất bại, hiển thị thông báo lỗi
            echo "Có lỗi xảy ra khi xóa sản phẩm.";
        }

        // Đóng câu truy vấn chuẩn bị
        mysqli_stmt_close($stmt);
    } else {
        echo "Có lỗi trong truy vấn SQL.";
    }

    // Đóng kết nối cơ sở dữ liệu
    mysqli_close($conn);
} else {
    // Nếu không có 'inventory_id' trong URL, chuyển hướng về trang quản lý sản phẩm
    header('Location: product_inventory.php');
}
