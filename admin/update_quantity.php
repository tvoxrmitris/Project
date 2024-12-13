<?php
include '../connection/connection.php';
// Kiểm tra xem có nhận được product_id, new_quantity, và color_name từ yêu cầu POST hay không
if (isset($_POST['product_id']) && isset($_POST['new_quantity']) && isset($_POST['color_name'])) {
    // Nhận product_id, new_quantity và color_name từ yêu cầu và bảo vệ chống SQL Injection
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $new_quantity = mysqli_real_escape_string($conn, $_POST['new_quantity']);
    $color_name = mysqli_real_escape_string($conn, $_POST['color_name']);

    // Kiểm tra nếu new_quantity là một số hợp lệ
    if (is_numeric($new_quantity) && $new_quantity >= 0) {
        // Truy vấn để lấy quantity_stock từ bảng inventory_entries
        $query = "SELECT ie.quantity_stock FROM inventory_entries ie
                  WHERE ie.product_id = '$product_id' AND ie.color_name = '$color_name'";
        $result = mysqli_query($conn, $query);

        // Nếu tìm thấy sản phẩm
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $inventory_quantity = $row['quantity_stock']; // Lấy số lượng từ bảng inventory_entries

            // Kiểm tra nếu số lượng nhập <= số lượng trong inventory_entries
            if ($new_quantity <= $inventory_quantity) {
                // Nếu số lượng nhập hợp lệ (<= quantity_stock), thực hiện cập nhật
                // Truy vấn cập nhật số lượng vào cơ sở dữ liệu
                $update_query = "UPDATE products SET quantity_in_stock = '$new_quantity' WHERE product_id = '$product_id' AND color_name = '$color_name'";
                $update_result = mysqli_query($conn, $update_query);

                // Kiểm tra xem cập nhật có thành công không
                if ($update_result) {
                    // Trả về thông báo thành công
                    echo json_encode(['success' => true]);
                } else {
                    // Trả về lỗi nếu truy vấn cập nhật không thành công
                    echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật cơ sở dữ liệu.']);
                }
            } else {
                // Trả về lỗi nếu số lượng nhập lớn hơn số lượng trong inventory_entries
                echo json_encode(['success' => false, 'message' => 'Số lượng nhập vượt quá số lượng tồn kho (inventory_entries).']);
            }
        } else {
            // Trả về lỗi nếu không tìm thấy sản phẩm
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm trong kho.']);
        }
    } else {
        // Trả về lỗi nếu new_quantity không hợp lệ
        echo json_encode(['success' => false, 'message' => 'Số lượng nhập không hợp lệ.']);
    }
} else {
    // Nếu thiếu product_id hoặc new_quantity hoặc color_name, trả về lỗi
    echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu.']);
}
?>