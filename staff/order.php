<?php
include '../connection/connection.php';
session_start();
if (!isset($_SESSION['employee_id']) || $_SESSION['employee_type'] !== 'staff') {
    header('location:../components/admin_login.php');
    exit;
}



// Xử lý đăng xuất
if (isset($_POST['logout'])) {
    session_destroy();
    header('location:../components/admin_login.php');
    exit;
}



$select_orders = mysqli_query($conn, "SELECT * FROM orders" . $filter_query) or die('query failed');
if (isset($_POST['confirm_order'])) {
    // Lấy order_id từ form
    $order_id = $_POST['order_id'];

    // Cập nhật trạng thái đơn hàng trong cơ sở dữ liệu
    $update_status = mysqli_query($conn, "UPDATE orders SET status_order = 'Đang chuẩn bị' WHERE order_id = '$order_id'") or die('query failed');

    // Kiểm tra nếu việc cập nhật thành công
    if ($update_status) {
        // Lấy thông tin đơn hàng để gửi email
        $query = mysqli_query($conn, "SELECT * FROM orders WHERE order_id = '$order_id'") or die('query failed');
        if ($row = mysqli_fetch_assoc($query)) {
            $user_name = $row['user_name'];
            $email = $row['user_email'];
            $total_price = $row['total_price'];
            $shipping_fee = $row['shipping_fee'];
            $total_discount = $row['total_discount_price'];
            $grand_total_price = $row['total_price'];
            $user_number = $row['user_number'];
            $full_address = $row['address'];
            $payment_method = $row['method'];
            $placed_on = new DateTime($row['placed_on']);
            $delivery_from = clone $placed_on;
            $delivery_to = clone $placed_on;
            $shipping_time = clone $placed_on;
            $delivery_from->modify('+3 days');
            $delivery_to->modify('+7 days');
            $shipping_time->modify('+1 days');

            // Gửi email xác nhận
            require "../mail/PHPMailer/src/PHPMailer.php";
            require "../mail/PHPMailer/src/SMTP.php";
            require "../mail/PHPMailer/src/Exception.php";

            $mail = new PHPMailer\PHPMailer\PHPMailer();
            $mail->IsSMTP();
            $mail->SMTPDebug = 0;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl';
            $mail->Host = "smtp.gmail.com";
            $mail->Port = 465;
            $mail->IsHTML(true);
            $mail->Username = "seraphbeauty22@gmail.com";
            $mail->Password = "einsonpyjjyxepyr"; // Hãy sử dụng biến môi trường cho mật khẩu
            $mail->SetFrom("seraphbeauty22@gmail.com", "Seraph Beauty");
            $mail->Subject = mb_encode_mimeheader("Xác Nhận Đơn Hàng", 'UTF-8');

            // Tạo nội dung email
            $mail->Body = "Xin chào $user_name,<br><br>
            Chúng tôi xin thông báo rằng đơn hàng của bạn đã được xác nhận và đang được chuẩn bị để gửi đến đơn vị vận chuyển. Dưới đây là thông tin đơn hàng của bạn:<br><br>
            
            <strong>Tóm tắt đơn hàng:</strong><br>
            - Phí vận chuyển: $shipping_fee VND<br>
            - Giảm giá: $total_discount VND<br>
            - Tổng thanh toán sau giảm giá: $grand_total_price VND<br><br>

            <strong>Thông tin cá nhân:</strong><br>
            - Tên: $user_name<br>
            - Số điện thoại: $user_number<br>
            - Email: $email<br>
            - Địa chỉ: $full_address<br>
            - Phương thức thanh toán: $payment_method<br><br>
            

                        <strong>Thời gian dự kiến giao hàng cho đơn vị vận chuyển:</strong><br>
            - Trong vòng 24h kể từ ngày: " . $shipping_time->format('d-m-Y') . "<br><br>
            
            Cảm ơn bạn đã đặt hàng! Nếu có bất kỳ thắc mắc nào xin liên hệ chúng tôi qua email và số điện thoại hỗ trợ.<br>
            Chúng tôi sẽ theo dõi đơn hàng của bạn và thông báo khi nó được giao.<br>
            Trân trọng,<br>
            Seraph Beauty";

            $mail->AddAddress($email);

            // Gửi email
            if (!$mail->send()) {
                echo "Mailer error: " . $mail->ErrorInfo;
            } else {
                echo "Email xác nhận đã được gửi thành công!";
            }
        } else {
            echo "Không tìm thấy đơn hàng!";
        }
    } else {
        echo "Không thể cập nhật trạng thái đơn hàng!";
    }
}

// Xử lý khi đơn hàng chuyển sang trạng thái "Giao cho đơn vị vận chuyển"
if (isset($_POST['ship_order'])) {
    // Lấy order_id từ form
    $order_id = $_POST['order_id'];

    // Cập nhật trạng thái đơn hàng trong cơ sở dữ liệu
    $update_status = mysqli_query($conn, "UPDATE orders SET status_order = 'Đang giao' WHERE order_id = '$order_id'") or die('query failed');

    // Kiểm tra nếu việc cập nhật thành công
    if ($update_status) {
        // Lấy thông tin đơn hàng để gửi email
        $query = mysqli_query($conn, "SELECT * FROM orders WHERE order_id = '$order_id'") or die('query failed');
        if ($row = mysqli_fetch_assoc($query)) {
            $user_name = $row['user_name'];
            $email = $row['user_email'];
            $total_price = $row['total_price'];
            $shipping_fee = $row['shipping_fee'];
            $total_discount = $row['total_discount_price'];
            $grand_total_price = $row['total_price'];
            $user_number = $row['user_number'];
            $full_address = $row['address'];
            $payment_method = $row['method'];
            $placed_on = new DateTime($row['placed_on']);
            $delivery_from = clone $placed_on;
            $delivery_to = clone $placed_on;
            $shipping_time = clone $placed_on;
            $delivery_from->modify('+3 days');
            $delivery_to->modify('+7 days');
            $shipping_time->modify('+1 days');
            // Gửi email xác nhận
            require "../mail/PHPMailer/src/PHPMailer.php";
            require "../mail/PHPMailer/src/SMTP.php";
            require "../mail/PHPMailer/src/Exception.php";

            // Gửi email thông báo "Giao hàng"
            $mail = new PHPMailer\PHPMailer\PHPMailer();
            $mail->IsSMTP();
            $mail->SMTPDebug = 0;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl';
            $mail->Host = "smtp.gmail.com";
            $mail->Port = 465;
            $mail->IsHTML(true);
            $mail->Username = "seraphbeauty22@gmail.com";
            $mail->Password = "einsonpyjjyxepyr"; // Hãy sử dụng biến môi trường cho mật khẩu
            $mail->SetFrom("seraphbeauty22@gmail.com", "Seraph Beauty");
            $mail->Subject = mb_encode_mimeheader("Thông Báo Giao Hàng", 'UTF-8');

            // Nội dung email thông báo giao hàng
            $mail->Body = "Xin chào $user_name,<br><br>
            Chúng tôi xin thông báo rằng đơn hàng của bạn đã được giao cho đơn vị vận chuyển và giao đến bạn sớm nhật có thể.<br><br>
            
            <strong>Tóm tắt đơn hàng:</strong><br>
            - Phí vận chuyển: $shipping_fee VND<br>
            - Giảm giá: $total_discount VND<br>
            - Tổng thanh toán sau giảm giá: $grand_total_price VND<br><br>

            <strong>Thông tin cá nhân:</strong><br>
            - Tên: $user_name<br>
            - Số điện thoại: $user_number<br>
            - Email: $email<br>
            - Địa chỉ: $full_address<br>
            - Phương thức thanh toán: $payment_method<br><br>
                                    <strong>Thời gian dự kiến giao hàng:</strong><br>
            - Ngày dự kiến giao hàng: " . $delivery_from->format('d-m-Y') . " đến " . $delivery_to->format('d-m-Y') . "<br><br>
            
            Cảm ơn bạn đã đặt hàng! Nếu có bất kỳ thắc mắc nào xin liên hệ chúng tôi qua email và số điện thoại hỗ trợ.<br>
            Chúng tôi sẽ theo dõi đơn hàng của bạn và thông báo khi nó được giao.<br>
            Trân trọng,<br>
            Seraph Beauty";

            $mail->AddAddress($email);

            // Gửi email
            if (!$mail->send()) {
                echo "Mailer error: " . $mail->ErrorInfo;
            } else {
            }
        } else {
            echo "Không tìm thấy đơn hàng!";
        }
    } else {
        echo "Không thể cập nhật trạng thái đơn hàng!";
    }
}







// Xử lý xóa đơn hàng
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    mysqli_query($conn, "DELETE FROM `orders` WHERE order_id = $delete_id") or die('query failed');
    $message[] = 'Đơn hàng đã được xóa thành công';
    header('location:../admin/admin_order.php');
}

// Cập nhật trạng thái thanh toán
if (isset($_POST['update_order'])) {
    $order_id = $_POST['order_id'];
    $update_payment = $_POST['update-payment'];

    mysqli_query($conn, "UPDATE `orders` SET payment_status = '$update_payment' WHERE order_id=$order_id") or die('query failed');
}
// Lấy giá trị lọc từ URL
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$filter_query = '';

// Áp dụng điều kiện lọc nếu có
if (!empty($status_filter)) {
    $filter_query = " WHERE status_order = '" . mysqli_real_escape_string($conn, $status_filter) . "'";
}

// Lấy danh sách đơn hàng
$select_orders = mysqli_query($conn, "SELECT * FROM orders" . $filter_query) or die('Query failed: ' . mysqli_error($conn));

// Lấy tổng số lượng đơn hàng phụ thuộc vào điều kiện lọc
$total_products_query = "SELECT COUNT(*) AS total_products FROM orders" . $filter_query;
$result = mysqli_query($conn, $total_products_query) or die('Query failed: ' . mysqli_error($conn));
$row = mysqli_fetch_assoc($result);
$total_products = $row['total_products'] ? $row['total_products'] : 0;


?>

<style>
/* Thiết lập chiều rộng tối thiểu cho các cột nếu cần */
th:nth-child(1),
td:nth-child(1) {
    width: 65px;
}

th:nth-child(2),
td:nth-child(2) {
    width: 160px;
}

th:nth-child(3),
td:nth-child(3) {
    width: 130px;
}

th:nth-child(4),
td:nth-child(4) {
    width: 130px;
}

th:nth-child(5),
td:nth-child(5) {
    width: 280px;
}

th:nth-child(6),
td:nth-child(6) {
    width: 150px;
}

th:nth-child(7),
td:nth-child(7) {
    width: 220px;
}

th:nth-child(8),
td:nth-child(8) {
    width: 400px;
}

th:nth-child(9),
td:nth-child(9) {
    width: 250px;
}

th:nth-child(10),
td:nth-child(10) {
    width: 200px;
}

th:nth-child(11),
td:nth-child(11) {
    width: 190px;
}

th:nth-child(12),
td:nth-child(12) {
    width: 200px;
}

th:nth-child(13),
td:nth-child(13) {
    width: 150px;
}

#filterForm {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    /* Căn phải cho form lọc */
}

.no-orders-message {
    text-align: center;
    font-size: 1.5rem;
    font-weight: bold;
    color: #000;
    /* Màu đen */
    background-color: #fff;
    /* Màu trắng */
    border: 2px solid #000;
    /* Viền đen */
    padding: 0.5rem 1rem;
    /* Giảm padding để chiều cao ngắn hơn */
    margin: 2rem auto;
    width: 50%;
    /* Đặt độ rộng phù hợp */
    border-radius: 10px;
    /* Góc bo tròn */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    /* Tạo bóng sang trọng */

    letter-spacing: 2px;
    /* Tăng khoảng cách giữa các chữ */
    max-height: 3rem;
    /* Giới hạn chiều cao tối đa */
    overflow: hidden;
    /* Đảm bảo nội dung không vượt quá chiều cao */
}

.no-orders-message:hover {
    background-color: #000;
    /* Đổi nền sang đen khi hover */
    color: #fff;
    /* Chữ đổi thành trắng */
    transition: all 0.3s ease-in-out;
    /* Hiệu ứng chuyển đổi */
}


.status-select {

    font-size: 1rem;
    padding: 10px;
    color: #333;
    background-color: #fff;
    border: 2px solid #000;
    border-radius: 5px;
    transition: all 0.3s ease-in-out;
}

.table-responsive {
    overflow-x: auto;
    /* Thêm thanh cuộn ngang */
    white-space: nowrap;
    /* Giữ nội dung trên cùng một dòng */

    /* Thêm khoảng cách trên và dưới nếu cần */
    border: 1px solid #ddd;
    /* Thêm đường viền để dễ quan sát */
}

table {
    border-collapse: collapse;
    width: 100%;
    min-width: 1000px;
    /* Đặt chiều rộng tối thiểu cho bảng nếu muốn */
}

td {
    cursor: pointer;
}
</style>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" type="text/css" href="../CSS/style.css?v=1.1 <?php echo time(); ?>">
    <title>Seraph Beauty - Đơn Hàng</title>
</head>

<body>

    <?php include '../staff/staff_header.php'; ?>
    <?php
    if (isset($message)) {
        foreach ($message as $message) {
            echo '
                    <div class="message">
                        <span>' . $message . '</span>
                        <i class="bi bi-x-circle" onclick="this.parentElement.remove()"></i>
                    </div>
                ';
        }
    }
    ?>


    <section class="shop">
        <div class="border-wrapper">
            <a href="staff_pannel.php" class="back-arrow"
                style="text-decoration: none; font-size: 1.5rem; margin-right: 1rem;">&#8592;</a>
            <div class="total-quantity">
                <p style="margin-left: 2rem;"><?php echo number_format($total_products, 0, '.', '.'); ?> Đơn Hàng</p>
            </div>
            <!-- Thêm phần input lọc theo trạng thái đơn hàng -->
            <form id="filterForm" method="GET" action="" style="display: flex; align-items: center;">
                <select name="status" id="status" class="status-select" style="margin-right: 1rem;"
                    onchange="applyFilter();">
                    <option value="">Tất cả trạng thái</option>
                    <option value="Chờ xác nhận"
                        <?php echo (isset($_GET['status']) && $_GET['status'] == 'Chờ xác nhận') ? 'selected' : ''; ?>>
                        Chờ xác nhận</option>
                    <option value="Đang chuẩn bị"
                        <?php echo (isset($_GET['status']) && $_GET['status'] == 'Đang chuẩn bị') ? 'selected' : ''; ?>>
                        Đang chuẩn bị</option>
                    <option value="Đang giao"
                        <?php echo (isset($_GET['status']) && $_GET['status'] == 'Đang giao') ? 'selected' : ''; ?>>
                        Đang giao</option>
                    <option value="Đã giao"
                        <?php echo (isset($_GET['status']) && $_GET['status'] == 'Đã giao') ? 'selected' : ''; ?>>
                        Đã giao</option>
                    <option value="Đã hủy"
                        <?php echo (isset($_GET['status']) && $_GET['status'] == 'Đã hủy') ? 'selected' : ''; ?>>
                        Đã hủy</option>
                </select>
            </form>
        </div>
    </section>

    <script>
    function applyFilter() {
        var status = document.getElementById("status").value;
        // Lấy URL hiện tại và cập nhật tham số URL với lựa chọn mới
        var currentUrl = window.location.href.split('?')[0]; // Lấy URL mà không có tham số query
        var newUrl = currentUrl + (status ? "?status=" + encodeURIComponent(status) : "");
        window.location.href = newUrl; // Thực hiện chuyển hướng đến URL mới
    }
    </script>

    <section class="order-container">
        <div class="box-container">
            <?php
            $filter_query = "";
            if (isset($_GET['status']) && $_GET['status'] != "") {
                $status = mysqli_real_escape_string($conn, $_GET['status']);
                $filter_query = " WHERE status_order = '$status'";
            }

            $select_orders = mysqli_query($conn, "SELECT * FROM orders" . $filter_query) or die('query failed');

            if (mysqli_num_rows($select_orders) > 0) {
            ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Họ và tên</th>
                            <th>Ngày và giờ</th>
                            <th>Số điện thoại</th>
                            <th>Email</th>
                            <th>Tổng giá tiền</th>
                            <th>Phương thức thanh toán</th>
                            <th>Địa chỉ</th>
                            <th>Tổng số lượng sản phẩm</th>
                            <th>Trạng thái thanh toán</th>
                            <th>Trạng thái đơn hàng</th>
                            <th>Xác nhận đơn hàng</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $stt = 1;
                            while ($fetch_orders = mysqli_fetch_assoc($select_orders)) {
                            ?>
                        <tr class="order-row" data-order-id="<?php echo $fetch_orders['order_id']; ?>">
                            <td><?php echo $stt++; ?></td>
                            <td><?php echo $fetch_orders['user_name']; ?></td>
                            <td><?php echo $fetch_orders['placed_on']; ?></td>
                            <td><?php echo $fetch_orders['user_number']; ?></td>
                            <td><?php echo $fetch_orders['user_email']; ?></td>
                            <td><?php echo number_format($fetch_orders['total_price'], 0, ',', '.') . ' VND'; ?></td>
                            <td><?php echo $fetch_orders['method']; ?></td>
                            <td><?php echo $fetch_orders['address']; ?></td>
                            <td><?php echo $fetch_orders['total_products']; ?></td>
                            <td>
                                <?php
                                        if ($fetch_orders['method'] == 'Thanh toán khi nhận hàng') {
                                            echo 'Chưa thanh toán';
                                        } elseif ($fetch_orders['method'] == 'Thanh toán bằng Momo') {
                                            echo 'Đã thanh toán';
                                        } else {
                                            echo 'Trạng thái không xác định';
                                        }
                                        ?>
                            </td>
                            <td>
                                <span><?php echo $fetch_orders['status_order'] ? $fetch_orders['status_order'] : 'Chờ xác nhận'; ?></span>
                            </td>
                            <td>
                                <?php
                                        if ($fetch_orders['status_order'] === 'Chờ xác nhận') {
                                        ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="order_id"
                                        value="<?php echo $fetch_orders['order_id']; ?>">
                                    <input type="submit" name="confirm_order" class="btn btn-success" value="Xác nhận">
                                </form>
                                <?php
                                        } elseif ($fetch_orders['status_order'] === 'Đang chuẩn bị') {
                                        ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="order_id"
                                        value="<?php echo $fetch_orders['order_id']; ?>">
                                    <input type="submit" name="ship_order" class="btn btn-info"
                                        value="Giao cho vận chuyển">
                                </form>
                                <?php } ?>
                            </td>

                        </tr>
                        <?php } ?>
                    </tbody>

                </table>
            </div>
            <?php
            } else {
                // Hiển thị thông báo nếu không có đơn hàng
                echo "<p class='no-orders-message'>Không tìm thấy đơn hàng nào!</p>";
            }
            ?>
        </div>
        <div class="line"></div>
        <script src="../js/script.js"></script>
</body>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.order-row'); // Chọn tất cả các hàng có class "order-row"
    rows.forEach(row => {
        row.addEventListener('click', function() {
            const orderId = this.getAttribute(
                'data-order-id'); // Lấy order_id từ thuộc tính data-order-id
            if (orderId) {
                // Chuyển hướng sang view_order.php với order_id
                window.location.href = `view_order.php?order_id=${orderId}`;
            }
        });
    });
});
</script>

</html>