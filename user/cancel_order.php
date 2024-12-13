<?php
include '../connection/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = isset($_POST['order_id']) ? $_POST['order_id'] : null;
    $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
    $otherReason = isset($_POST['other_reason']) ? trim($_POST['other_reason']) : ''; // Lấy lý do khác từ input

    if ($orderId) {
        // Nếu lý do là "Lý do khác", sử dụng input được nhập
        if ($reason === "Lý do khác" && !empty($otherReason)) {
            $reason = $otherReason;
        }

        if (!empty($reason)) {
            // Truy vấn cập nhật trạng thái đơn hàng
            $queryUpdate = "UPDATE orders SET status_order = 'Đã hủy', cancel_reason = ? WHERE order_id = ?";
            $stmtUpdate = $conn->prepare($queryUpdate);
            $stmtUpdate->bind_param('si', $reason, $orderId);

            if ($stmtUpdate->execute()) {
                // Nếu cập nhật thành công, chèn dữ liệu vào bảng cancel_order
                $cancelAt = date('Y-m-d H:i:s'); // Lấy thời gian hiện tại
                $queryInsert = "INSERT INTO cancel_order (order_id, cancel_reason, cancel_at) VALUES (?, ?, ?)";
                $stmtInsert = $conn->prepare($queryInsert);
                $stmtInsert->bind_param('iss', $orderId, $reason, $cancelAt);

                if ($stmtInsert->execute()) {
                    // Cập nhật quantity_in_stock trong bảng products
                    $querySelectItems = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
                    $stmtSelectItems = $conn->prepare($querySelectItems);
                    $stmtSelectItems->bind_param('i', $orderId);

                    if ($stmtSelectItems->execute()) {
                        $resultItems = $stmtSelectItems->get_result();

                        while ($row = $resultItems->fetch_assoc()) {
                            $productId = $row['product_id'];
                            $quantity = $row['quantity'];

                            // Tăng số lượng tồn kho trong bảng products
                            $queryUpdateStock = "UPDATE products SET quantity_in_stock = quantity_in_stock + ? WHERE product_id = ?";
                            $stmtUpdateStock = $conn->prepare($queryUpdateStock);
                            $stmtUpdateStock->bind_param('ii', $quantity, $productId);

                            if (!$stmtUpdateStock->execute()) {
                                echo "Lỗi khi cập nhật số lượng tồn kho cho sản phẩm $productId: " . $stmtUpdateStock->error;
                            }
                            $stmtUpdateStock->close();
                        }
                        echo "Đơn hàng đã được hủy thành công và số lượng tồn kho đã được cập nhật!";
                    } else {
                        echo "Lỗi khi lấy thông tin sản phẩm từ order_items: " . $stmtSelectItems->error;
                    }
                    $stmtSelectItems->close();
                } else {
                    echo "Đơn hàng đã được hủy nhưng lỗi khi lưu vào bảng cancel_order: " . $stmtInsert->error;
                }
                $stmtInsert->close();
            } else {
                echo "Lỗi khi hủy đơn hàng: " . $stmtUpdate->error;
            }
            $stmtUpdate->close();
        } else {
            echo "Vui lòng cung cấp lý do hủy đơn hàng.";
        }
    } else {
        echo "Không tìm thấy thông tin đơn hàng.";
    }
} else {
    echo "Phương thức không hợp lệ.";
}