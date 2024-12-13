<?php
include '../connection/connection.php';
session_start();
$admin_id = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];

if (!isset($admin_id)) {
    header('location:../components/login.php');
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('location:../components/login.php');
}
$check_cart_query = "SELECT * FROM cart WHERE user_id = '$user_id'";
$result_cart = mysqli_query($conn, $check_cart_query);

if (mysqli_num_rows($result_cart) > 0) {
    // Xóa tất cả dữ liệu trong bảng cart của người dùng
    $delete_cart_query = "DELETE FROM cart WHERE user_id = '$user_id'";
    mysqli_query($conn, $delete_cart_query);

    echo "Dữ liệu trong giỏ hàng đã được xóa.";
} else {
    echo "Giỏ hàng của người dùng không tồn tại hoặc đã trống.";
}
if (isset($_GET['success'])) {
    // Lấy order_id từ URL
    $order_id = intval($_GET['order_id']);

    // Kiểm tra xem order_id có tồn tại trong bảng orders và order_items không
    $check_order_query = "SELECT * FROM orders WHERE order_id = '$order_id'";
    $result_order = mysqli_query($conn, $check_order_query);

    if (mysqli_num_rows($result_order) > 0) {
        $check_order_items_query = "SELECT * FROM order_items WHERE order_id = '$order_id'";
        $result_order_items = mysqli_query($conn, $check_order_items_query);

        if (mysqli_num_rows($result_order_items) > 0) {
            // Cập nhật bảng orders, set success = 1
            $update_orders_query = "UPDATE orders SET success = 1 WHERE order_id = '$order_id'";
            mysqli_query($conn, $update_orders_query);

            // Cập nhật bảng order_items, set success = 1
            $update_order_items_query = "UPDATE order_items SET success = 1 WHERE order_id = '$order_id'";
            mysqli_query($conn, $update_order_items_query);

            // Tạo mảng lưu trữ tổng số lượng cho mỗi product_id
            $product_quantities = [];

            // Bắt đầu xử lý cập nhật số lượng kho và lưu trữ số lượng sản phẩm cho mỗi product_id
            while ($row = mysqli_fetch_assoc($result_order_items)) {
                $product_id = $row['product_id']; // Lấy product_id từ order_items
                $quantity = $row['quantity']; // Lấy quantity từ order_items

                // Kiểm tra nếu product_id đã có trong mảng, nếu có thì cộng thêm quantity
                if (isset($product_quantities[$product_id])) {
                    $product_quantities[$product_id] += $quantity;
                } else {
                    // Nếu chưa có, thêm mới vào mảng
                    $product_quantities[$product_id] = $quantity;
                }

                // Truy vấn bảng products để lấy quantity_in_stock
                $check_product_query = "SELECT quantity_in_stock FROM products WHERE product_id = '$product_id'";
                $result_product = mysqli_query($conn, $check_product_query);

                if (mysqli_num_rows($result_product) > 0) {
                    $fetch_product = mysqli_fetch_assoc($result_product);
                    $quantity_in_stock = $fetch_product['quantity_in_stock'];

                    // Tính số lượng mới trong kho
                    $new_quantity_in_stock = $quantity_in_stock - $quantity;

                    // Cập nhật số lượng trong kho
                    $update_stock_query = "UPDATE products SET quantity_in_stock = '$new_quantity_in_stock' WHERE product_id = '$product_id'";
                    mysqli_query($conn, $update_stock_query);
                } else {
                    echo "Không tìm thấy sản phẩm với ID: $product_id";
                }
            }

            // Cập nhật hoặc chèn vào bảng purchases dựa trên tổng số lượng của từng sản phẩm
            foreach ($product_quantities as $product_id => $total_quantity) {
                // Kiểm tra bảng purchases xem product_id đã tồn tại chưa
                $check_purchase_query = "SELECT * FROM purchases WHERE product_id = '$product_id'";
                $result_purchase = mysqli_query($conn, $check_purchase_query);

                if (mysqli_num_rows($result_purchase) > 0) {
                    // Nếu đã tồn tại, cập nhật purchase_amout
                    $update_purchase_query = "UPDATE purchases SET purchase_amout = purchase_amout + '$total_quantity' WHERE product_id = '$product_id'";
                    mysqli_query($conn, $update_purchase_query);
                } else {
                    // Nếu chưa tồn tại, insert vào bảng purchases
                    $insert_purchase_query = "INSERT INTO purchases (product_id, purchase_amout, purchase_at) 
                                               VALUES ('$product_id', '$total_quantity', NOW())";
                    mysqli_query($conn, $insert_purchase_query);
                }
            }

            echo "Đơn hàng đã được xác nhận và số lượng kho đã được cập nhật!";
        } else {
            echo "Không tìm thấy thông tin sản phẩm cho đơn hàng.";
        }
    } else {
        echo "Không tìm thấy đơn hàng với ID này.";
    }
}


?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css"
        integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css"
        integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js"
        integrity="sha512-HGOnQO9+SP1V92SrtZfjqxxtLmVzqZpjFFekvzZVWoiASSQgSr4cw9Kqd2+l8Llp4Gm0G8GIFJ4ddwZilcdb8A=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.js"
        integrity="sha512-eP8DK17a+MOcKHXC5Yrqzd8WI5WKh6F1TIk5QZ/8Lbv+8ssblcz7oGC8ZmQ/ZSAPa7ZmsCU4e/hcovqR8jfJqA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" type="text/css" href="../CSS/main.css?v=1.1 <?php echo time(); ?>">
    <title>Home</title>
</head>



<body>
    <?php include '../user/header.php' ?>




    <div class="order-section">
        <!-- Sidebar trạng thái đơn hàng -->
        <div class="status-list">
            <a href="#" class="status-item active">Tất cả</a>
            <a href="#" class="status-item">Chờ xác nhận</a>
            <a href="#" class="status-item">Đang chuẩn bị</a>
            <a href="#" class="status-item">Đang giao</a>
            <a href="#" class="status-item">Đã giao</a>
            <a href="#" class="status-item">Đã hủy</a>
        </div>

        <div class="box-container">
            <div class="title">
                <h1>Đơn hàng của tôi</h1>
            </div>
            <p class="no-orders-message" style="display: none;">Không có đơn hàng của trạng thái này.</p>
            <p class="no-orders-cancelled" style="display: none;">Hiện tại chưa có đơn hàng nào đã hủy.</p>
            <p class="no-orders-confirmation" style="display: none;">Hiện tại chưa có đơn hàng nào chờ xác nhận.</p>
            <p class="no-orders-preparing" style="display: none;">Hiện tại chưa có đơn hàng nào đang chuẩn bị.</p>
            <p class="no-orders-delivering" style="display: none;">Hiện tại chưa có đơn hàng nào đang giao.</p>
            <p class="no-orders-delivered" style="display: none;">Hiện tại chưa có đơn hàng nào đã giao.</p>
            <p class="no-orders-all" style="display: none;">Hiện tại chưa có đơn hàng nào.</p>

            <?php
// Lấy đơn hàng của người dùng với trạng thái 'success' và sắp xếp theo thời gian đặt gần nhất
$select_orders = mysqli_query($conn, "
    SELECT *, DATE_FORMAT(placed_on, '%d-%m-%Y %H:%i:%s') as placed_on_formatted 
    FROM `orders` 
    WHERE user_id='$user_id' AND success = '1' 
    ORDER BY placed_on DESC
") or die('query failed');

// Kiểm tra xem có đơn hàng nào không
if (mysqli_num_rows($select_orders) > 0) {
    while ($fetch_orders = mysqli_fetch_assoc($select_orders)) {
        $order_id = $fetch_orders['order_id'];
        $status_order = $fetch_orders['status_order'];

        // Tính tổng số tiền chưa giảm từ order_items
        $select_total_before_discount = mysqli_query(
            $conn,
            "SELECT SUM(quantity * price) as total_before_discount 
            FROM `order_items` 
            WHERE order_id='$order_id'"
        ) or die('query failed');
        $total_before_discount = mysqli_fetch_assoc($select_total_before_discount)['total_before_discount'];
?>

            <!-- Hiển thị thông tin đơn hàng -->
            <div class="box order-item" data-status="<?php echo $status_order; ?>">
                <div class="order-info" style="border-bottom: 1px solid #888;">
                    <p class="order-date">Ngày đặt hàng: <?php echo $fetch_orders['placed_on_formatted']; ?></p>
                    <p class="status-order"><?php echo $fetch_orders['status_order']; ?></p>
                </div>

                <?php
    // Hiển thị các sản phẩm trong đơn hàng
    $select_order_items = mysqli_query(
        $conn,
        "SELECT oi.product_id, oi.discount_fee, oi.price, oi.product_image, oi.product_name, oi.quantity, oi.total_price, 
        p.color_name, p.capacity, p.detail_color 
        FROM `order_items` oi 
        JOIN `products` p ON oi.product_id = p.product_id 
        WHERE oi.order_id='$order_id'"
    ) or die('query failed');

    while ($fetch_order_items = mysqli_fetch_assoc($select_order_items)) {
        $image_name = explode(',', $fetch_order_items['product_image'])[0];
        $product_name = $fetch_order_items['product_name'];
        $quantity = $fetch_order_items['quantity'];
        $product_price = $fetch_order_items['price'];
        $total_prodcut_price = $product_price;
        $discount_fee = $fetch_order_items['discount_fee'];
        $total_price = $product_price - ($discount_fee / $quantity);
        $color_name = $fetch_order_items['color_name'];
        $capacity = $fetch_order_items['capacity'];
    ?>
                <div class="product-item">
                    <div class="img-order">
                        <img class="img_order" src="../image/product/<?php echo $image_name; ?>" alt="Product Image">
                    </div>
                    <div class="order-summary">
                        <p><?php echo $product_name; ?></p>
                        <p>Màu sắc: <?php echo $color_name; ?>
                            <?php if (!empty($fetch_order_items['detail_color'])) {
                    echo "- " . $fetch_order_items['detail_color'];
                } ?>
                        </p>
                        <?php if (!empty($capacity)) { ?>
                        <p>Dung tích: <?php echo $capacity; ?></p>
                        <?php } ?>
                        <div class="order-quantity-price">
                            <p class="quantity">Số lượng: <?php echo $quantity; ?></p>
                            <div class="price-order">
                                <?php if (empty($discount_fee)) { ?>
                                <span class="product-price">
                                    <?php echo number_format($product_price, 0, '.', '.') . ' VNĐ'; ?>
                                </span>
                                <?php } else { ?>
                                <span class="original-price">
                                    <?php echo number_format($total_prodcut_price, 0, '.', '.') . ' VNĐ'; ?>
                                </span>
                                <span>
                                    <?php echo number_format($total_price, 0, '.', '.') . ' VNĐ'; ?>
                                </span>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
                <!-- Thông tin người dùng và các chi tiết khác -->
                <div class="user-info" style="display: none;">
                    <h4>Thông tin người dùng</h4>
                    <p>Họ tên: <span class="data-value"><?php echo $fetch_orders['user_name']; ?></span></p>
                    <p>Email: <span class="data-value"><?php echo $fetch_orders['user_email']; ?></span></p>
                    <p>Số điện thoại: <span class="data-value"><?php echo $fetch_orders['user_number']; ?></span></p>
                    <p>Địa chỉ giao hàng: <span class="data-value"><?php echo $fetch_orders['address']; ?></span></p>
                    <p>Phương thức thanh toán: <span class="data-value"><?php echo $fetch_orders['method']; ?></span>
                    </p>
                </div>

                <!-- Chi tiết lý do hủy đơn hàng -->
                <?php
    // Lấy thông tin hủy đơn hàng nếu có
    $current_order_id = $fetch_orders['order_id'];
    $query = "SELECT cancel_reason, cancel_at FROM cancel_order WHERE order_id = '$current_order_id'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $cancel_reason = $row['cancel_reason'];
        $cancel_at = $row['cancel_at'];
    } else {
        $cancel_reason = "Chưa hủy";
        $cancel_at = null;
    }
    ?>
                <div class="order-actions">
                    <div class="order-container">
                        <div class="reason-detail">
                            <?php if ($cancel_reason !== "Chưa hủy") { ?>
                            <li class="reason">
                                <span class="text-left">Thời gian hủy:</span>
                                <span class="text-right"><strong
                                        style="color: #bd0100;"><?php echo date("d/m/Y H:i", strtotime($cancel_at)); ?></strong></span>
                            </li>
                            <li class="reason">
                                <span class="text-left">Lý do hủy:</span>
                                <span class="text-right"><strong
                                        style="color: #bd0100;"><?php echo $cancel_reason; ?></strong></span>
                            </li>
                            <?php } ?>
                        </div>
                        <ul class="order-detail">
                            <!-- Các chi tiết đơn hàng -->
                            <li class="order-total">
                                <span class="text-left">Tổng tiền chưa giảm:</span>
                                <span
                                    class="text-right"><strong><?php echo number_format($total_before_discount, 0, '.', '.') . ' VNĐ'; ?></strong></span>
                            </li>
                            <li class="order-total">
                                <span class="text-left">Số tiền được giảm:</span>
                                <span
                                    class="text-right"><strong>-<?php echo number_format($fetch_orders['total_discount_price'], 0, '.', '.') . ' VNĐ'; ?></strong></span>
                            </li>
                            <li class="order-total">
                                <span class="text-left">Phí vận chuyển:</span>
                                <span
                                    class="text-right"><strong><?php echo number_format($fetch_orders['shipping_fee'], 0, '.', '.') . ' VNĐ'; ?></strong></span>
                            </li>
                            <li class="order-total">
                                <span class="text-left">Thành tiền:</span>
                                <span
                                    class="text-right"><strong><?php echo number_format($fetch_orders['total_price'], 0, '.', '.') . ' VNĐ'; ?></strong></span>
                            </li>
                        </ul>
                    </div>


                    <div class="order-buttons">
                        <div class="status-notify">
                            <?php if ($status_order === 'Chờ xác nhận') { ?>
                            <p class="seller-message">Người bán sẽ sớm chuẩn bị đơn hàng cho bạn.</p>
                            <?php } elseif ($status_order === 'Đang chuẩn bị') { ?>
                            <p class="seller-message">Người bán sẽ sớm giao đơn hàng cho đơn vị vận chuyển.</p>
                            <?php } elseif ($status_order === 'Đang giao') { ?>
                            <p class="seller-message">Thời gian giao hàng dự kiến từ 3-5 ngày.</p>
                            <input type="submit" name="received-btn" class="received-order" value="Đã nhận được hàng"
                                data-order-id="<?php echo $fetch_orders['order_id']; ?>">
                            <?php } elseif ($status_order === 'Đã giao') {
                                        $placed_on = $fetch_orders['placed_on'];
                                        $date_placed = new DateTime($placed_on);
                                        $date_now = new DateTime();
                                        $interval = $date_now->diff($date_placed);

                                        $deadline_date = clone $date_placed;
                                        $deadline_date->modify('+30 days');

                                        // Tính tổng quantity từ bảng order_items
                                        $order_id = $fetch_orders['order_id'];
                                        $get_total_quantity = mysqli_query($conn, "SELECT SUM(quantity) AS total_quantity FROM order_items WHERE order_id = '$order_id'");
                                        $quantity_result = mysqli_fetch_assoc($get_total_quantity);
                                        $total_quantity = $quantity_result['total_quantity'] ?? 0; // Trường hợp không có dữ liệu trả về, mặc định là 0

                                        // Tính số điểm được cộng
                                        $bonus_points = $total_quantity * 5;

                                        if ($interval->m >= 1 || ($interval->m == 0 && $interval->d >= 0)) {
                                            echo '<p class="seller-message">Đánh giá sản phẩm trước ngày ' . $deadline_date->format('d-m-Y') . ' để nhận ' . $bonus_points . ' điểm cộng!</p>';
                                        } else {
                                            $remaining_days = $deadline_date->diff($date_now)->days;
                                            echo '<p class="seller-message">Bạn còn ' . $remaining_days . ' ngày để đánh giá sản phẩm và nhận ' . $bonus_points . ' điểm cộng!</p>';
                                        }

                                        // Kiểm tra xem order_id đã tồn tại trong bảng evaluate hay chưa
                                        $check_evaluate = mysqli_query($conn, "SELECT * FROM evaluate WHERE order_id = '$order_id'");
                                        if (mysqli_num_rows($check_evaluate) > 0) {
                                            // Đã đánh giá, hiển thị "Đã đánh giá" và làm nút không thể click
                                            echo '<input type="button" class="submit-review" value="Đã đánh giá" disabled>';
                                        } else {
                                            // Chưa đánh giá, hiển thị nút "Gửi đánh giá"
                                            echo '<input type="button" class="submit-review" value="Gửi đánh giá" onclick="window.location.href=\'evaluate.php?order_id=' . $order_id . '\';">';
                                        }
                                    ?>
                            <?php } ?>
                        </div>

                        <!-- Nút "Mua lại" -->
                        <!-- <input type="submit" name="buy-again-btn" class="buyagain" value="Mua Lại"
                            data-order-id="<?php echo $fetch_orders['order_id']; ?>"> -->
                        <?php if ($status_order !== 'Đang giao' && $status_order !== 'Đã giao') { // Nếu không phải "Đang giao" hoặc "Đã giao"
                                    if ($status_order !== 'Đã hủy') { // Nếu không phải "Đã hủy"
                                ?>
                        <input name="cancel-order-btn" type="submit" class="cancel-order" value="Hủy hàng"
                            data-order-id="<?php echo $fetch_orders['order_id']; ?>">
                        <?php } else { ?>
                        <input type="button" class="cancel-order" value="Đã hủy" disabled>
                        <?php } ?>
                        <?php } ?>



                    </div>
                </div>


            </div>
            <?php
                }
            } else {
                echo '<p class="empty">Bạn chưa có đơn hàng nào!</p>';
            }
            ?>
        </div>

        <style>
        .cancel-box {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .cancel-content {
            background-color: #fff;
            border: 2px solid #000;
            border-radius: 8px;
            padding: 20px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        /* Tiêu đề */
        .cancel-content p {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        /* Form */
        label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
            color: #000;
        }

        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #000;
            border-radius: 4px;
            background-color: #f9f9f9;
            color: #000;
            font-size: 14px;
            margin-bottom: 15px;
        }

        /* Nút bấm */
        .button-group button {
            display: inline-block;
            font-size: 14px;
            font-weight: bold;
            padding: 10px 20px;
            border: 2px solid #000;
            border-radius: 4px;
            background-color: transparent;
            color: #000;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            margin: 5px;
        }

        .button-group button:hover {
            background-color: #000;
            color: #fff;
        }

        #close-cancel-btn {
            background-color: #f9f9f9;
        }

        #close-cancel-btn:hover {
            background-color: #000;
            color: #fff;
        }

        /* Textarea nếu chọn "Lý do khác" */
        #other-reason-container textarea {
            resize: none;
            height: 80px;
        }

        /* Hiệu ứng hiển thị/ẩn */
        .hidden {
            display: none;
        }

        .notification {
            position: fixed;
            bottom: -100px;
            /* Bắt đầu ở ngoài màn hình */
            left: 20px;
            background-color: #000;
            /* Màu nền trắng */
            color: #fff;
            /* Màu chữ đen */
            border: 2px solid black;
            /* Viền đen */
            padding: 15px 20px;
            border-radius: 8px;
            font-size: 16px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            opacity: 0;
            /* Ẩn lúc đầu */
            visibility: hidden;
            transition: transform 0.5s ease-out, opacity 0.5s ease-out, visibility 0.5s;
        }

        /* Hiển thị box */
        .notification.visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(-120px);
            /* Hiển thị cách đáy màn hình 20px */
        }
        </style>

        <div id="cancel-box" class="cancel-box hidden">
            <div class="cancel-content">
                <p>Bạn có chắc chắn muốn hủy đơn hàng này không?</p>
                <form id="cancel-form">
                    <label for="cancel-reason">Chọn lý do hủy đơn hàng:</label>
                    <select id="cancel-reason" name="cancel_reason" required>
                        <option value="" disabled selected>Chọn lý do</option>
                        <option value="Thay đổi ý định">Thay đổi địa chỉ giao hàng</option>
                        <option value="Đặt nhầm đơn">Đặt nhầm đơn</option>
                        <option value="Tìm thấy giá rẻ hơn">Tìm thấy giá rẻ hơn</option>
                        <option value="Thời gian giao hàng quá lâu">Thay đổi mã giảm giá</option>
                        <option value="Lý do khác">Lý do khác</option>
                    </select>
                    <!-- Container cho lý do khác -->
                    <div id="other-reason-container" class="hidden">
                        <label for="other-reason">Nhập lý do khác:</label>
                        <input type="text" id="other-reason" name="other_reason" placeholder="Nhập lý do của bạn" />
                    </div>
                    <div class="button-group">
                        <button type="submit" id="confirm-cancel-btn">Xác nhận</button>
                        <button type="button" id="close-cancel-btn">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="success-notification" class="notification hidden">
            Đơn hàng đã được hủy thành công.
        </div>


        <script>
        document.addEventListener("DOMContentLoaded", () => {
            const cancelButtons = document.querySelectorAll(".cancel-order");
            const cancelBox = document.getElementById("cancel-box");
            const confirmCancelBtn = document.getElementById("confirm-cancel-btn");
            const closeCancelBtn = document.getElementById("close-cancel-btn");
            const cancelReason = document.getElementById("cancel-reason");
            const otherReasonInput = document.getElementById("other-reason");
            const otherReasonContainer = document.getElementById("other-reason-container");
            const successNotification = document.getElementById("success-notification");
            let currentOrderId = null;

            // Hiển thị thông báo từ localStorage
            const notificationMessage = localStorage.getItem("cancelNotificationMessage");
            if (notificationMessage) {
                showNotification(notificationMessage);
                localStorage.removeItem("cancelNotificationMessage");
            }

            // Sự kiện click nút hủy
            cancelButtons.forEach(button => {
                button.addEventListener("click", event => {
                    event.preventDefault();
                    currentOrderId = button.getAttribute("data-order-id");
                    cancelBox.classList.remove("hidden");
                });
            });

            // Xác nhận hủy
            confirmCancelBtn.addEventListener("click", (event) => {
                event.preventDefault();
                if (currentOrderId) {
                    let reason = cancelReason.value;
                    if (reason === "Lý do khác") {
                        reason = otherReasonInput.value.trim();
                    }

                    if (!reason) {
                        showNotification("Vui lòng chọn hoặc nhập lý do hủy đơn hàng.");
                        return;
                    }

                    fetch('cancel_order.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `order_id=${encodeURIComponent(currentOrderId)}&reason=${encodeURIComponent(reason)}`,
                        })
                        .then(response => response.text())
                        .then(data => {
                            showNotification(data);
                            cancelBox.classList.add("hidden");
                            location.reload();
                        })
                        .catch(error => console.error("Error:", error));
                }
            });

            // Đóng hộp hủy
            closeCancelBtn.addEventListener("click", () => {
                cancelBox.classList.add("hidden");
            });

            // Lý do khác
            cancelReason.addEventListener("change", () => {
                if (cancelReason.value === "Lý do khác") {
                    otherReasonContainer.classList.remove("hidden");
                } else {
                    otherReasonContainer.classList.add("hidden");
                }
            });

            // Hiển thị thông báo
            function showNotification(message) {
                successNotification.textContent = message;
                successNotification.classList.remove("hidden");
                successNotification.classList.add("visible");

                setTimeout(() => {
                    successNotification.classList.remove("visible");
                    successNotification.classList.add("hidden");
                }, 3000);
            }
        });
        </script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const receivedButtons = document.querySelectorAll('.received-order');

            receivedButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-order-id');

                    // Gửi yêu cầu AJAX để cập nhật trạng thái đơn hàng
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'update_order_status.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (xhr.status === 200 && xhr.responseText === 'success') {
                            // Cập nhật thành công, lưu trạng thái "Đã giao" vào localStorage để sử dụng khi tải lại trang
                            localStorage.setItem('selectedOrderStatus', 'Đã giao');
                            alert('Đơn hàng đã được cập nhật thành "Đã giao".');

                            // Chuyển hướng đến trang hiển thị đơn hàng với trạng thái "Đã giao"
                            location.reload(); // Tải lại trang để áp dụng trạng thái
                        } else {
                            alert('Có lỗi xảy ra. Vui lòng thử lại.');
                        }
                    };
                    xhr.send('order_id=' + orderId);
                });
            });
        });

        document.querySelectorAll('.order-total.hidden').forEach(item => {
            item.style.display = 'flex'; // Hiển thị lại các mục bị ẩn
        });
        </script>


        <style>
        .hidden {
            display: none;
        }
        </style>
    </div>

    <script>
    $(document).ready(function() {
        // Sự kiện khi nhấn vào order-item để hiện/ẩn thông tin người dùng
        $(".order-item").click(function() {
            var productItem = $(this).find(".product-item");
            var userInfo = $(this).find(".user-info");

            // Sử dụng toggle hiệu ứng slide cho mượt mà hơn
            if (userInfo.is(":visible")) {
                userInfo.stop(true, true).slideUp(600); // Đảm bảo không có hiệu ứng nào đang chờ
                productItem.stop(true, true).slideDown(600);
            } else {
                productItem.stop(true, true).slideUp(600);
                userInfo.stop(true, true).slideDown(600);
            }
        });

        // Hiệu ứng trượt lên/xuống cho các mục Tổng tiền, Phí vận chuyển, và Số tiền được giảm
        $(".order-item").click(function() {
            $(this).find(".order-total.hidden").stop(true, true).slideToggle(600, "swing");
        });

        // Sự kiện cho nút "Mua Lại"
        $(".buyagain").click(function(e) {
            e.stopPropagation(); // Ngăn chặn sự kiện click lan ra ngoài
            var orderId = $(this).data("order-id");
            console.log("Mua lại đơn hàng với ID: " + orderId);
        });

        // Sự kiện cho nút "Hủy hàng"
        $(".cancel-order").click(function(e) {
            e.stopPropagation(); // Ngăn chặn sự kiện click lan ra ngoài
            var orderId = $(this).data("order-id");
            console.log("Hủy đơn hàng với ID: " + orderId);
        });

        var savedStatus = localStorage.getItem('selectedOrderStatus');

        // Nếu không có trạng thái đã lưu, mặc định chọn "Tất cả" và thêm lớp active
        if (!savedStatus) {
            savedStatus = 'Tất cả';
            localStorage.setItem('selectedOrderStatus', savedStatus);
        }

        var allOrders = document.querySelectorAll('.order-item');
        var statusItems = document.querySelectorAll('.status-item');

        // Đặt lại lớp active cho trạng thái đã lưu trong localStorage
        statusItems.forEach(function(status) {
            if (status.textContent.trim() === savedStatus) {
                status.classList.add('active');
            } else {
                status.classList.remove('active');
            }
        });

        // Hiển thị các đơn hàng theo trạng thái đã lưu
        var hasVisibleOrders = false;
        allOrders.forEach(function(order) {
            if (savedStatus === 'Tất cả' || order.getAttribute('data-status') === savedStatus) {
                order.style.display = 'block';
                hasVisibleOrders = true;
            } else {
                order.style.display = 'none';
            }
        });

        // Hiển thị hoặc ẩn thông báo không có đơn hàng tương ứng
        var noOrdersMessage = document.querySelector('.no-orders-message');
        var noOrdersCancelled = document.querySelector('.no-orders-cancelled');
        var noOrdersConfirmation = document.querySelector('.no-orders-confirmation');
        var noOrdersPreparing = document.querySelector('.no-orders-preparing');
        var noOrdersDelivering = document.querySelector('.no-orders-delivering');
        var noOrdersDelivered = document.querySelector('.no-orders-delivered');
        var noOrdersAll = document.querySelector('.no-orders-all');

        // Ẩn tất cả các thông báo trước khi hiển thị thông báo mới
        noOrdersMessage.style.display = 'none';
        noOrdersCancelled.style.display = 'none';
        noOrdersConfirmation.style.display = 'none';
        noOrdersPreparing.style.display = 'none';
        noOrdersDelivering.style.display = 'none';
        noOrdersDelivered.style.display = 'none';
        noOrdersAll.style.display = 'none';

        // Kiểm tra và hiển thị thông báo cho trạng thái hiện tại
        if (hasVisibleOrders) {
            // Nếu có đơn hàng, ẩn tất cả thông báo lỗi
            noOrdersMessage.style.display = 'none';
            noOrdersCancelled.style.display = 'none';
            noOrdersConfirmation.style.display = 'none';
            noOrdersPreparing.style.display = 'none';
            noOrdersDelivering.style.display = 'none';
            noOrdersDelivered.style.display = 'none';
            noOrdersAll.style.display = 'none';
        } else {
            // Nếu không có đơn hàng, hiển thị thông báo phù hợp
            if (savedStatus === 'Đã hủy') {
                noOrdersCancelled.style.display = 'block';
            } else if (savedStatus === 'Chờ xác nhận') {
                noOrdersConfirmation.style.display = 'block';
            } else if (savedStatus === 'Đang chuẩn bị') {
                noOrdersPreparing.style.display = 'block';
            } else if (savedStatus === 'Đang giao') {
                noOrdersDelivering.style.display = 'block';
            } else if (savedStatus === 'Đã giao') {
                noOrdersDelivered.style.display = 'block';
            } else if (savedStatus === 'Tất cả') {
                noOrdersAll.style.display = 'block';
            }
        }


        // Sự kiện khi nhấn vào các trạng thái
        statusItems.forEach(function(status) {
            status.addEventListener('click', function() {
                var statusText = status.textContent.trim();

                // Lưu trạng thái đã chọn vào localStorage
                localStorage.setItem('selectedOrderStatus', statusText);

                // Cập nhật trạng thái đã chọn
                statusItems.forEach(function(item) {
                    item.classList.remove('active');
                });
                status.classList.add('active');

                // Hiển thị đơn hàng theo trạng thái
                hasVisibleOrders = false;

                if (statusText === 'Tất cả') {
                    allOrders.forEach(function(order) {
                        order.style.display = 'block';
                        hasVisibleOrders = true;
                    });
                } else {
                    allOrders.forEach(function(order) {
                        if (order.getAttribute('data-status') === statusText) {
                            order.style.display = 'block';
                            hasVisibleOrders = true;
                        } else {
                            order.style.display = 'none';
                        }
                    });
                }

                // Hiển thị hoặc ẩn thông báo không có đơn hàng
                if (hasVisibleOrders) {
                    noOrdersMessage.style.display = 'none';
                    noOrdersCancelled.style.display = 'none';
                    noOrdersConfirmation.style.display = 'none';
                    noOrdersPreparing.style.display = 'none';
                    noOrdersDelivering.style.display = 'none';
                    noOrdersDelivered.style.display = 'none';
                    noOrdersAll.style.display = 'none';
                } else {
                    noOrdersMessage.style.display = 'none';
                    if (statusText === 'Đã hủy') {
                        noOrdersCancelled.style.display = 'block';
                    } else if (statusText === 'Chờ xác nhận') {
                        noOrdersConfirmation.style.display = 'block';
                    } else if (statusText === 'Đang chuẩn bị') {
                        noOrdersPreparing.style.display = 'block';
                    } else if (statusText === 'Đang giao') {
                        noOrdersDelivering.style.display = 'block';
                    } else if (statusText === 'Đã giao') {
                        noOrdersDelivered.style.display = 'block';
                    } else if (statusText === 'Tất cả') {
                        noOrdersAll.style.display = 'block';
                    }
                }
            });
        });
    });
    </script>


    <script>
    // Lắng nghe sự kiện click vào nút "Mua Lại"
    document.querySelectorAll("[name='buy-again-btn']").forEach(function(btn) {
        btn.addEventListener("click", function() {
            // Lấy order_id từ thuộc tính data
            var orderId = this.getAttribute("data-order-id");
            // Chuyển hướng đến trang checkout_order.php với order_id tương ứng
            window.location.href = "checkout_order.php?order_id=" + orderId;
        });
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var evaluateBtns = document.querySelectorAll("[name='evaluate-btn']");

        evaluateBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var orderId = this.getAttribute("data-order-id");
                window.location.href = 'evaluate.php?order_id=' + orderId;
            });
        });

        var paymentStatuses = document.querySelectorAll('.payment-status');

        paymentStatuses.forEach(function(paymentStatus) {
            var evaluateBtn = paymentStatus.parentElement.querySelector('.evaluate-btn');
            if (paymentStatus.textContent.trim() === 'Hoàn thành') {
                evaluateBtn.style.display = 'block'; // Hiển thị nút Đánh giá
            } else {
                evaluateBtn.style.display = 'none'; // Ẩn nút Đánh giá
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        var evaluateBtns = document.querySelectorAll('.evaluate-btn');

        evaluateBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var orderId = this.getAttribute("data-order-id");
                window.location.href = 'evaluate.php?order_id=' + orderId;
            });
        });
    });
    </script>
</body>
<?php include '../user/footer.php' ?>

</html>