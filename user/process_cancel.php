<?php
// Bao gồm file kết nối CSDL
include '../connection/connection.php';

// Kiểm tra xem request method là POST và tồn tại dữ liệu gửi đi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reason']) && isset($_POST['order_id'])) {
    // Lấy dữ liệu từ Ajax gửi đi
    $reason = $_POST['reason'];
    $order_id = $_POST['order_id'];

    // Kiểm tra trạng thái thanh toán của đơn hàng trước khi hủy
    $payment_status_query = "SELECT payment_status FROM `orders` WHERE `order_id` = $order_id";
    $payment_status_result = mysqli_query($conn, $payment_status_query);

    if ($payment_status_result) {
        $row = mysqli_fetch_assoc($payment_status_result);
        $payment_status = $row['payment_status'];

        // Nếu đơn hàng đã thanh toán, đặt cancel_reason thành rỗng và không cho phép hủy
        if ($payment_status == 'Đã thanh toán') {
            // Thực hiện câu truy vấn để cập nhật trạng thái thanh toán và lý do hủy đơn hàng vào CSDL
            $update_query = "UPDATE `orders` SET `cancel_reason` = '', `payment_status` = 'Đã thanh toán' WHERE `order_id` = $order_id";

            // Thực thi câu truy vấn và kiểm tra kết quả
            if (mysqli_query($conn, $update_query)) {
                // Trả về thông báo không cho phép hủy nếu đã thanh toán
                echo "Đơn hàng đã thanh toán không thể hủy!";
            } else {
                // Trả về thông báo lỗi nếu cập nhật không thành công
                echo "Đã xảy ra lỗi, vui lòng thử lại sau!";
            }
        } else {
            // Thực hiện câu truy vấn để cập nhật lý do hủy đơn hàng vào CSDL
            $update_query = "UPDATE `orders` SET `cancel_reason` = '$reason', `payment_status` = 'Đã hủy' WHERE `order_id` = $order_id";

            // Thực thi câu truy vấn và kiểm tra kết quả
            if (mysqli_query($conn, $update_query)) {
                // Trả về phản hồi thành công nếu cập nhật thành công
                echo "Xác nhận hủy đơn hàng!";
            } else {
                // Trả về thông báo lỗi nếu cập nhật không thành công
                echo "Đã xảy ra lỗi, vui lòng thử lại sau!";
            }
        }
    } else {
        // Trả về thông báo lỗi nếu không thể truy vấn trạng thái thanh toán
        echo "Đã xảy ra lỗi khi kiểm tra trạng thái thanh toán!";
    }
} else {
    // Trả về thông báo lỗi nếu dữ liệu không hợp lệ hoặc không tồn tại
    echo "Dữ liệu không hợp lệ hoặc không tồn tại!";
}
?>
