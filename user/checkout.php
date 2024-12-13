<?php
include '../connection/connection.php';
include '../user/calculate_shipping.php'; // Đảm bảo đường dẫn đúng
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



// Truy vấn để lấy dữ liệu từ bảng province
$sql = "SELECT * FROM province";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die('Lỗi truy vấn: ' . mysqli_error($conn));
}

if (isset($_POST['add_sale'])) {
    echo "<pre>";
    print_r($_POST);
    die();
}







if (isset($_POST['order-btn'])) {

    // Lấy các dữ liệu từ form
    $user_name = mysqli_real_escape_string($conn, $_POST['name']);
    $user_number = mysqli_real_escape_string($conn, $_POST['number']);
    $user_email = mysqli_real_escape_string($conn, $_POST['email']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['method']);
    $province_id = $_POST['province'];
    $district_id = $_POST['district'];
    $wards_id = $_POST['wards'];
    $flat = mysqli_real_escape_string($conn, $_POST['flat']);
    $placed_on = date('Y-m-d H:i:s');
    $payment_status = "Chờ xác nhận";
    $track_order = isset($_POST['track_order']) ? 1 : 0;

    // Lấy tên địa phương từ ID
    $province_name = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT name FROM province WHERE province_id = '$province_id'")
    )['name'];
    $district_name = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT name FROM district WHERE district_id = '$district_id'")
    )['name'];
    $wards_name = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT name FROM wards WHERE wards_id = '$wards_id'")
    )['name'];

    // Địa chỉ đầy đủ
    $full_address = "$flat, $wards_name, $district_name, $province_name";
    $address_shipping = "$wards_name, $district_name, $province_name";

    // Lấy tọa độ của địa chỉ cửa hàng và khách hàng
    $customerCoordinates = getCoordinates($address_shipping);
    $storeCoordinates = getCoordinates("Hồ Chí Minh, Việt Nam");

    // Kiểm tra và tính khoảng cách, phí vận chuyển
    if ($customerCoordinates && $storeCoordinates) {
        $theta = $customerCoordinates['lon'] - $storeCoordinates['lon'];
        $dist = sin(deg2rad($customerCoordinates['lat'])) * sin(deg2rad($storeCoordinates['lat'])) +
            cos(deg2rad($customerCoordinates['lat'])) * cos(deg2rad($storeCoordinates['lat'])) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $distance = $dist * 60 * 1.1515 * 1.609344; // km

        // Tính phí ship dựa trên khoảng cách và tỉnh thành
        if ($province_name === 'Hồ Chí Minh') {
            if ($distance <= 5) {
                $shipping_fee = 10000;
            } elseif ($distance <= 10) {
                $shipping_fee = 10000 + (($distance - 5) * 1500);
            } else {
                $shipping_fee = 10000 + (5 * 1500) + (($distance - 10) * 2000);
            }
        } else {
            if ($distance <= 5) {
                $shipping_fee = 500;
            } elseif ($distance <= 10) {
                $shipping_fee = 500 + (($distance - 5) * 100);
            } else {
                $shipping_fee = 500 + (5 * 100) + (($distance - 10) * 200);
            }
        }
    } else {
        echo "<script>alert('Không thể tính phí vận chuyển!');</script>";
        exit;
    }

    $current_date = date('Y-m-d');
    $user_id = $_SESSION['user_id']; // hoặc lấy từ cơ sở dữ liệu nếu có
        $select_user_info = mysqli_query($conn, "SELECT point, user_name, user_email, user_number FROM users WHERE user_id = '$user_id'") or die('Lỗi truy vấn: ' . mysqli_error($conn));


            // Xác định hạng dựa trên điểm thưởng
            if ($user_point >= 5000) {
                $rank = 'Kim Cương';
                $next_rank = null; // Không có hạng tiếp theo
            } elseif ($user_point >= 2000) {
                $rank = 'Bạch Kim';
                $next_rank = 'Kim Cương';
                $next_point = 5000;
            } elseif ($user_point >= 1000) {
                $rank = 'Vàng';
                $next_rank = 'Bạch Kim';
                $next_point = 2000;
            } elseif ($user_point >= 500) {
                $rank = 'Bạc';
                $next_rank = 'Vàng';
                $next_point = 1000;
            } else {
                $rank = 'Thành viên thường';
                $next_rank = 'Bạc';
                $next_point = 500;
            }

            // Xác định mức giảm giá
            $discount = 0; // Mặc định là không giảm giá
            if ($rank === 'Bạc') {
                $discount = 2;
            } elseif ($rank === 'Vàng') {
                $discount = 5;
            } elseif ($rank === 'Bạch Kim') {
                $discount = 8;
            } elseif ($rank === 'Kim Cương') {
                $discount = 10;
            }

    // Kiểm tra nếu có product_id trên URL
    if (isset($_GET['product_id'])) {
        $product_id_from_url = intval($_GET['product_id']);
    
        // Truy vấn sản phẩm từ bảng `products`
        $select_product = mysqli_query($conn, "SELECT product_name, product_price, product_image, product_subcategory, quantity_in_stock FROM `products` WHERE product_id = '$product_id_from_url'")
            or die('Lỗi truy vấn: ' . mysqli_error($conn));
    
        if (mysqli_num_rows($select_product) > 0) {
            $fetch_product = mysqli_fetch_assoc($select_product);
            $price = $fetch_product['product_price'];
            $product_name = mysqli_real_escape_string($conn, $fetch_product['product_name']);
            $product_image = mysqli_real_escape_string($conn, $fetch_product['product_image']);
            $product_subcategory = $fetch_product['product_subcategory'];
            $quantity = 1; // Số lượng đặt hàng
            $discount_percent = 0; // Giá trị mặc định
            $current_date = date('Y-m-d'); // Ngày hiện tại
            $rankdiscount=0;
    
            // Truy vấn bảng `product_promotion` để lấy discount_percent theo product_id
            $select_promotion = mysqli_query($conn, "
                SELECT discount_percent 
                FROM `product_promotion` 
                WHERE product_id = '$product_id_from_url' 
                AND start_date <= '$current_date' 
                AND end_date >= '$current_date'
            ") or die('Lỗi truy vấn khuyến mãi: ' . mysqli_error($conn));
    
            if ($select_promotion && mysqli_num_rows($select_promotion) > 0) {
                $discount_percent = mysqli_fetch_assoc($select_promotion)['discount_percent'];
            }
    
            $discount_amount = $price * ($discount_percent / 100);
            $coupon_discount = 0; // Khởi tạo giá trị giảm giá mã coupon
    
            // Nếu có mã giảm giá được gửi từ client
            if (isset($_POST['coupon_code']) && !empty($_POST['coupon_code'])) {
                $coupon_code = mysqli_real_escape_string($conn, $_POST['coupon_code']);
    
                // Truy vấn kiểm tra mã giảm giá từ bảng promotions
                $coupon_query = mysqli_query($conn, "
                SELECT discount_percent 
                FROM promotions 
                WHERE code_discount = '$coupon_code' 
                AND start_date <= CURDATE() 
                AND end_date >= CURDATE()
                ") or die('Lỗi truy vấn mã giảm giá: ' . mysqli_error($conn));
    
                if (mysqli_num_rows($coupon_query) > 0) {
                    $coupon_data = mysqli_fetch_assoc($coupon_query);
                    $coupon_percent = $coupon_data['discount_percent'];
    
                    if ($coupon_percent > 0 && $coupon_percent <= 100) {
                        // Tính giảm giá từ mã coupon
                        $coupon_discount = ($coupon_percent / 100) * $price * $quantity;
                    }
                }
            }
    
            // Tổng giảm giá cuối cùng (bao gồm giảm giá mã coupon và giảm giá sản phẩm)
            $total_discount = ($discount_amount * $quantity) + $coupon_discount;
    
            $total_discounted_price = ($price - $discount_amount) * $quantity;
    
            $grand_total = $total_discounted_price + $shipping_fee;
            $grand_total_original = $price * $quantity;
    $rankdiscount=$grand_total*$discount;
            // Tính tổng giá (grand_total + shipping_fee)
            $total_price = $grand_total-$rankdiscount;
    

            

// Kiểm tra phương thức thanh toán
if ($payment_method === 'payatm') {
    $success_status = 0; // Đơn hàng chưa thành công
} else {
    $success_status = 1; // Đơn hàng thành công
}

// Câu lệnh INSERT vào bảng `orders`
$insert_order = "INSERT INTO `orders` (
    user_id, user_name, user_number, user_email, method, 
    address, total_products, total_discount_price, shipping_fee, total_price, placed_on, status_order, success
) VALUES (
    '$user_id', '$user_name', '$user_number', '$user_email', 
    '$payment_method', '$full_address', '$quantity', 
    '$total_discount', '$shipping_fee', '$total_price', NOW(), '$payment_status', '$success_status'
)";

// Thực hiện câu lệnh INSERT
$insert_order_result = mysqli_query($conn, $insert_order)
    or die('Lỗi khi thêm đơn hàng: ' . mysqli_error($conn));

// Kiểm tra nếu đơn hàng được thêm thành công
if ($insert_order_result) {
    $order_id = mysqli_insert_id($conn); // Lấy ID của đơn hàng vừa thêm

    // Chèn thông tin vào bảng order_items
    $insert_order_item = "INSERT INTO `order_items` (
        order_id, product_id, product_name, product_image, quantity, price, discount_fee, total_price, success
    ) VALUES (
        '$order_id', '$product_id_from_url', '$product_name', '$product_image', 
        '$quantity', '$price', '$total_discount', '$total_discounted_price', '$success_status'
    )";

    $insert_order_item_result = mysqli_query($conn, $insert_order_item)
        or die('Lỗi khi thêm sản phẩm vào đơn hàng: ' . mysqli_error($conn));

    // Kiểm tra nếu phương thức thanh toán là 'payatm', không cập nhật kho
    if ($payment_method !== 'payatm') {
        // Cập nhật số lượng trong kho
        $new_quantity_in_stock = $fetch_product['quantity_in_stock'] - $quantity; // Tính số lượng mới

        $update_stock = "
            UPDATE `products` 
            SET quantity_in_stock = '$new_quantity_in_stock', 
                purchase_amount = purchase_amount + '$quantity' 
            WHERE product_id = '$product_id_from_url'";

        mysqli_query($conn, $update_stock)
            or die('Lỗi khi cập nhật kho và purchase_amount: ' . mysqli_error($conn));
    }

    // Chuyển hướng nếu phương thức thanh toán là 'payatm'
    if ($payment_method === 'payatm') {
        // Lưu trữ thông tin vào session
        $_SESSION['product_id'] = $product_id_from_url;
        $_SESSION['shipping_fee'] = $shipping_fee;
        
        // Chuyển hướng đến trang atmpayment.php
        header("Location: ../payment/atmpayment.php?product_id=$product_id_from_url&shipping_fee=$shipping_fee&order_id=$order_id");
        exit();
    } else {
        $insert_purchase = "
        INSERT INTO `purchases` (product_id, purchase_amout, purchase_at) 
        VALUES ('$product_id_from_url', '$quantity', NOW())";

    $insert_purchase_result = mysqli_query($conn, $insert_purchase)
        or die('Lỗi khi thêm vào purchases: ' . mysqli_error($conn));

    if ($insert_purchase_result) {
        // Bạn có thể xử lý thêm nếu insert thành công, ví dụ thông báo hoặc ghi log
    } else {
        echo "Lỗi khi thêm vào bảng purchases!";
    }
    }



    

   
        // Nội dung email
        $today = new DateTime();
    
        // Ngày giao hàng từ hôm nay cộng 3 ngày
        $delivery_from = clone $today;
        $delivery_from->modify('+3 days');
    
        // Ngày giao hàng từ hôm nay cộng 7 ngày
        $delivery_to = clone $today;
        $delivery_to->modify('+7 days');
    
        $subject = "Xác nhận đặt hàng từ Seraph Beauty";
    
        // Định dạng các giá trị tiền tệ
        $shipping_fee = number_format(floor($shipping_fee), 0, '', '.');
        $discount_amount = number_format(floor($discount_amount), 0, '', '.');
        $total_discounted_price = number_format(floor($total_discounted_price), 0, '', '.');
        $grand_total = number_format(floor($grand_total), 0, '', '.');
    
        $body = "Xin chào $user_name,<br><br>
        <span style='font-family: 'Futura', sans-serif; font-size: 14px; line-height: 1.5; color: #000000;'>
        Chúng tôi đã nhận được đơn hàng của bạn với thông tin sau:<br><br>
        
        <strong style='color: #000000;'>Tóm tắt đơn hàng:</strong><br>
        <div style='padding-left: 20px; color: #000000;'>
        - Phí vận chuyển: $shipping_fee VND<br>
        - Giảm giá: $discount_amount VND<br>
        - Tổng tiền trước giảm giá: $total_discounted_price VND<br>
        - Tổng thanh toán sau giảm giá: $grand_total VND<br>
        </div><br>
        
        - Tên: $user_name<br>
        - Số điện thoại: $user_number<br>
        - Email: $user_email<br>
        - Địa chỉ: $full_address<br>
        - Phương thức thanh toán: $payment_method<br>
        - Ngày dự kiến giao hàng: " . $delivery_from->format('d-m-Y') . " đến " . $delivery_to->format('d-m-Y') . "<br><br>
        
        Cảm ơn bạn đã đặt hàng! Nếu có bất kỳ thắc mắc nào xin liên hệ chúng tôi qua email và số điện thoại hỗ trợ.<br>
        Chúng tôi sẽ xử lý đơn hàng của bạn sớm nhất có thể.<br>
        Trân trọng,<br>
        </span>
        <h2 style='font-weight: bold;'>Seraph Beauty</h2>
        <span>Số điện thoại: 0922 222 2222 | Email: seraphbeauty22@gmail.com</span>
        ";
    
        // Gửi email bằng PHPMailer
        require "../mail/PHPMailer/src/PHPMailer.php";
        require "../mail/PHPMailer/src/SMTP.php";
        require "../mail/PHPMailer/src/Exception.php";
    
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = "smtp.gmail.com";
        $mail->Port = 465;
        $mail->IsHTML(true);
        $mail->CharSet = 'UTF-8'; // Đặt mã ký tự UTF-8
        $mail->Username = "email@gmail.com"; // Đổi thành email của bạn
        $mail->Password = "yourpassword"; // Đổi thành mật khẩu email
        $mail->SetFrom("email@gmail.com", "Seraph Beauty");
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AddAddress($user_email);
    
        if ($mail->Send()) {
            echo "Email đã được gửi!";
        } else {
            echo "Lỗi khi gửi email: " . $mail->ErrorInfo;
        }
    
    
                


                    // Hiển thị thông báo toàn màn hình và chuyển hướng sau 3 giây
                    echo "<script>
   // Tạo box thông báo
let successOverlay = document.createElement('div');
successOverlay.style.position = 'fixed';
successOverlay.style.top = '20%';
successOverlay.style.left = '0';
successOverlay.style.width = '100%';
successOverlay.style.height = '80%';
successOverlay.style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
successOverlay.style.color = '#000';
successOverlay.style.display = 'flex';
successOverlay.style.flexDirection = 'column';
successOverlay.style.alignItems = 'center';
successOverlay.style.justifyContent = 'center';
successOverlay.style.fontSize = '24px';
successOverlay.style.zIndex = '9999';
successOverlay.style.fontFamily = 'Futura, sans-serif';
successOverlay.style.opacity = '0'; // Khởi tạo với opacity 0 để ẩn
successOverlay.style.transition = 'opacity 1s'; // Thêm hiệu ứng fade-in

// Tạo nội dung thông báo
let successText = document.createElement('p');
successText.innerText = 'Đặt hàng thành công!';

successOverlay.appendChild(successText);

// Tạo phần tử xoay tròn (spinner)
let spinner = document.createElement('div');
spinner.style.border = '8px solid #000';
spinner.style.borderTop = '8px solid #fff';
spinner.style.borderRadius = '50%';
spinner.style.width = '60px';
spinner.style.height = '60px';
spinner.style.animation = 'spin 1s linear infinite';
successOverlay.appendChild(spinner);

// Thêm keyframes cho hiệu ứng xoay tròn
let style = document.createElement('style');
style.innerHTML = `
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);

// Thêm thanh thông báo vào body
document.body.appendChild(successOverlay);

// Hiển thị thanh thông báo với hiệu ứng fade-in
setTimeout(function() {
    successOverlay.style.opacity = '1'; // Sau 100ms sẽ hiện ra
}, 100);

// Sau khi thanh toán thành công, thực hiện chuyển hướng sau 3 giây
setTimeout(function() {
    // Chuyển đến trang đơn hàng
    window.location.href = '../user/order.php'; // Địa chỉ trang đơn hàng của bạn
}, 3000);


</script>";
                } else {
                    echo "<script>alert('Đặt hàng thất bại!');</script>";
                }
            } else {
                echo "<script>alert('Sản phẩm không tồn tại!');</script>";
            }
        
    } elseif (isset($_GET['sessioncart'])) {
        // Kiểm tra nếu có session cart trong link
        if (!empty($_SESSION['cart'])) {
            $cart_items = $_SESSION['cart'];
            $total_quantity = 0;
            $total_discount_price = 0;
            $grand_total_price = 0;
    
            // Kiểm tra phương thức thanh toán
            if ($payment_method === 'payatm') {
                $success_status = 0; // Đơn hàng chưa thành công
            } else {
                $success_status = 1; // Đơn hàng thành công
            }
    
            // Duyệt qua từng sản phẩm trong giỏ hàng
            $total_discount_price = 0; // Khởi tạo tổng giá trị giảm giá

foreach ($cart_items as $item) {
    $product_id = $item['product_id'];
    $quantity = $item['quantity'];

    // Truy vấn thông tin khuyến mãi của sản phẩm từ bảng product_promotion
    $select_promotion = mysqli_query($conn, "SELECT discount_percent FROM `product_promotion` WHERE product_id = '$product_id' AND start_date <= '$current_date' AND end_date >= '$current_date'")
        or die('Lỗi truy vấn: ' . mysqli_error($conn));
    
    if (mysqli_num_rows($select_promotion) > 0) {
        // Nếu có khuyến mãi, lấy discount_percent
        $promotion = mysqli_fetch_assoc($select_promotion);
        $discount_percent = $promotion['discount_percent'];

        // Truy vấn thông tin giá sản phẩm từ bảng products
        $select_product = mysqli_query($conn, "SELECT product_price FROM `products` WHERE product_id = '$product_id'")
            or die('Lỗi truy vấn: ' . mysqli_error($conn));

        if (mysqli_num_rows($select_product) > 0) {
            $product = mysqli_fetch_assoc($select_product);
            $price = $product['product_price'];

            // Tính toán số tiền giảm giá cho sản phẩm
            $discount_amount = $price * $quantity * ($discount_percent / 100);

            // Cộng dồn vào tổng giá trị giảm giá
            $total_discount_price += $discount_amount;
        }
    }
}

// $total_discount_price bây giờ chứa tổng số tiền giảm giá của tất cả các sản phẩm trong giỏ hàng

    
            // Nếu giỏ hàng có sản phẩm, ghi vào bảng `orders`
            if ($total_quantity > 0) {
                $insert_order = "INSERT INTO `orders` (
                    user_id, user_name, user_number, user_email, method, 
                    address, total_products, total_price, total_discount_price, shipping_fee, placed_on, status_order, success
                ) VALUES (
                    '$user_id', '$user_name', '$user_number', '$user_email',
                    '$payment_method', '$full_address', '$total_quantity', '$grand_total', 
                    '$total_discountpricep', '$shipping_fee', NOW(), '$payment_status', '$success_status'
                )";
    
                $insert_order_result = mysqli_query($conn, $insert_order) or die('Lỗi khi thêm đơn hàng: ' . mysqli_error($conn));
    
                if ($insert_order_result) {
                    $order_id = mysqli_insert_id($conn); // Lấy ID của đơn hàng vừa thêm
    
                    foreach ($cart_items as $item) {
                        $product_id = $item['product_id'];
                        $quantity = $item['quantity'];
                    
                        // Truy vấn lại thông tin sản phẩm
                        $select_product = mysqli_query($conn, "SELECT product_name, product_image, product_price, product_subcategory FROM `products` WHERE product_id = '$product_id'")
                            or die('Lỗi truy vấn: ' . mysqli_error($conn));
                        $fetch_product = mysqli_fetch_assoc($select_product);
                        $product_name = mysqli_real_escape_string($conn, $fetch_product['product_name']);
                        $product_image = mysqli_real_escape_string($conn, $fetch_product['product_image']);
                        $price = $fetch_product['product_price'];
                        $product_subcategory = $fetch_product['product_subcategory'];
                    
                        // Tính toán giảm giá
                        $select_promotion = mysqli_query($conn, "SELECT discount_percent FROM `product_promotion` WHERE subcategory_name = '$product_subcategory' AND start_date <= '$current_date' AND end_date >= '$current_date'")
                            or die('Lỗi truy vấn: ' . mysqli_error($conn));
                        $discount_percent = (mysqli_num_rows($select_promotion) > 0) ? mysqli_fetch_assoc($select_promotion)['discount_percent'] : 0;
  
                    
                        // Thêm vào bảng order_items với success = 0 nếu phương thức thanh toán là payatm
                        $insert_order_item = "INSERT INTO `order_items` (
                            order_id, product_id, product_name, product_image, quantity, price, discount_fee, total_price, success
                        ) VALUES (
                            '$order_id', '$product_id', '$product_name', '$product_image', 
                            '$quantity', '$price', '$discount_amount', '$total_discounted_price', '$success_status'
                        )";
                    
                        mysqli_query($conn, $insert_order_item) or die('Lỗi khi thêm sản phẩm vào đơn hàng: ' . mysqli_error($conn));
                    
                        // Kiểm tra phương thức thanh toán trước khi cập nhật bảng purchases
                        if ($payment_method !== 'payatm') {
                            // Cập nhật số lượng trong kho
                            $new_quantity_in_stock = $fetch_product['quantity_in_stock'] - $quantity; // Tính số lượng mới
                        
                            $update_stock = "
                                UPDATE `products` 
                                SET quantity_in_stock = '$new_quantity_in_stock', 
                                    purchase_amount = purchase_amount + '$quantity' 
                                WHERE product_id = '$product_id'";
                            
                            mysqli_query($conn, $update_stock)
                                or die('Lỗi khi cập nhật kho và purchase_amount: ' . mysqli_error($conn));
                        
                            // Kiểm tra nếu phương thức thanh toán là 'Thanh toán khi nhận hàng'
                            if ($payment_method === 'Thanh toán khi nhận hàng') {
                                // Kiểm tra xem sản phẩm đã tồn tại trong bảng purchases chưa
                                $check_purchase = mysqli_query($conn, "SELECT * FROM `purchases` WHERE product_id = '$product_id'");
                        
                                if (mysqli_num_rows($check_purchase) > 0) {
                                    // Nếu sản phẩm đã tồn tại, cập nhật số lượng
                                    $update_purchase = "
                                        UPDATE `purchases` 
                                        SET purchase_amout = purchase_amout + '$quantity', purchase_at = NOW() 
                                        WHERE product_id = '$product_id'";
                                    
                                    mysqli_query($conn, $update_purchase)
                                        or die('Lỗi khi cập nhật số lượng vào purchases: ' . mysqli_error($conn));
                                } else {
                                    // Nếu sản phẩm chưa tồn tại, insert mới vào bảng purchases
                                    $insert_purchase = "
                                        INSERT INTO `purchases` (product_id, purchase_amout, purchase_at)
                                        VALUES ('$product_id', '$quantity', NOW())";
                                    
                                    mysqli_query($conn, $insert_purchase)
                                        or die('Lỗi khi thêm vào purchases: ' . mysqli_error($conn));
                                }
                            } else {
                                // Nếu không phải phương thức thanh toán 'payatm' hoặc 'payonreceive', chèn vào purchases
                                $insert_purchase = "
                                    INSERT IGNORE INTO `purchases` (product_id, purchase_amout, purchase_at)
                                    VALUES ('$product_id', '$quantity', NOW())";
                                
                                mysqli_query($conn, $insert_purchase)
                                    or die('Lỗi khi thêm vào purchases: ' . mysqli_error($conn));
                            }
                        }
                        
                    }
                    
                    // Chuyển hướng nếu phương thức thanh toán là 'payatm'
                    if ($payment_method === 'payatm') {
                        // Lưu trữ thông tin vào session
                        $_SESSION['shipping_fee'] = $shipping_fee;
                        
                        // Chuyển hướng đến trang atmpayment.php
                        header("Location: ../payment/atmpayment.php?shipping_fee=$shipping_fee&order_id=$order_id");
                        exit();
                    } else {
                        
                    }
                    

                    

                    echo "<script>
                    // Tạo box thông báo
                 let successOverlay = document.createElement('div');
                 successOverlay.style.position = 'fixed';
                 successOverlay.style.top = '20%';
                 successOverlay.style.left = '0';
                 successOverlay.style.width = '100%';
                 successOverlay.style.height = '80%';
                 successOverlay.style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
                 successOverlay.style.color = '#000';
                 successOverlay.style.display = 'flex';
                 successOverlay.style.flexDirection = 'column';
                 successOverlay.style.alignItems = 'center';
                 successOverlay.style.justifyContent = 'center';
                 successOverlay.style.fontSize = '24px';
                 successOverlay.style.zIndex = '9999';
                 successOverlay.style.fontFamily = 'Futura, sans-serif';
                 successOverlay.style.opacity = '0'; // Khởi tạo với opacity 0 để ẩn
                 successOverlay.style.transition = 'opacity 1s'; // Thêm hiệu ứng fade-in
                 
                 // Tạo nội dung thông báo
                 let successText = document.createElement('p');
                 successText.innerText = 'Đặt hàng thành công!';
                 
                 successOverlay.appendChild(successText);
                 
                 // Tạo phần tử xoay tròn (spinner)
                 let spinner = document.createElement('div');
                 spinner.style.border = '8px solid #000';
                 spinner.style.borderTop = '8px solid #fff';
                 spinner.style.borderRadius = '50%';
                 spinner.style.width = '60px';
                 spinner.style.height = '60px';
                 spinner.style.animation = 'spin 1s linear infinite';
                 successOverlay.appendChild(spinner);
                 
                 // Thêm keyframes cho hiệu ứng xoay tròn
                 let style = document.createElement('style');
                 style.innerHTML = `
                     @keyframes spin {
                         0% { transform: rotate(0deg); }
                         100% { transform: rotate(360deg); }
                     }
                 `;
                 document.head.appendChild(style);
                 
                 // Thêm thanh thông báo vào body
                 document.body.appendChild(successOverlay);
                 
                 // Hiển thị thanh thông báo với hiệu ứng fade-in
                 setTimeout(function() {
                     successOverlay.style.opacity = '1'; // Sau 100ms sẽ hiện ra
                 }, 100);
                 
                 // Sau khi thanh toán thành công, thực hiện chuyển hướng sau 3 giây
                 setTimeout(function() {
                     // Chuyển đến trang đơn hàng
                     window.location.href = '../user/order.php'; // Địa chỉ trang đơn hàng của bạn
                 }, 3000);
                 
                 
                 </script>";

                    unset($_SESSION['cart']); // Xóa giỏ hàng sau khi đặt hàng thành công
                    // Nội dung email
                    $today = new DateTime();

                    // Ngày giao hàng từ hôm nay cộng 3 ngày
                    $delivery_from = clone $today; // Tạo bản sao của ngày hôm nay
                    $delivery_from->modify('+3 days');

                    // Ngày giao hàng từ hôm nay cộng 7 ngày
                    $delivery_to = clone $today; // Tạo bản sao khác
                    $delivery_to->modify('+7 days');
                    $subject = "Xác nhận đặt hàng từ Seraph Beauty";

                    // Định dạng các giá trị tiền tệ
                    $shipping_fee = number_format(floor($shipping_fee), 0, '', '.'); // Làm tròn xuống và định dạng
                    $total_discount = number_format(floor($total_discount), 0, '', '.');
                    $total_discount_price = number_format(floor($total_discount_price), 0, '', '.');
                    $grand_total_price = number_format(floor($grand_total_price), 0, '', '.');
                    $price_before_discount = ($grand_total_price + $total_discount_price) - $shipping_fee;

                    $price_before_discount = number_format(floor($price_before_discount), 0, '', '.');
                    $body = "Xin chào $user_name,<br><br>
<span style='font-family: 'Futura', sans-serif; font-size: 14px; line-height: 1.5; color: #000000;'>
Chúng tôi đã nhận được đơn hàng của bạn với thông tin sau:<br><br>

<strong style='color: #000000;'>Tóm tắt đơn hàng:</strong><br>
<div style='padding-left: 20px; color: #000000;'>
- Phí vận chuyển: $shipping_fee VND<br>
- Giảm giá: $total_discount_price VND<br>

- Tổng thanh toán sau giảm giá: $grand_total_price VND<br>
</div><br>

- Tên: $user_name<br>
- Số điện thoại: $user_number<br>
- Email: $user_email<br>
- Địa chỉ: $full_address<br>
- Phương thức thanh toán: $payment_method<br>
- Ngày dự kiến giao hàng: " . $delivery_from->format('d-m-Y') . " đến " . $delivery_to->format('d-m-Y') . "<br><br>

Cảm ơn bạn đã đặt hàng! Nếu có bất kỳ thắc mắc nào xin liên hệ chúng tôi qua email và số điện thoại hỗ trợ.<br>
Chúng tôi sẽ xử lý đơn hàng của bạn sớm nhất có thể.<br>
Trân trọng,<br>
</span>
<h2 style='font-weight: bold;'>Seraph Beauty</h2>
<span>Số điện thoại: 0922 222 2222 | Email: seraphbeauty22@gmail.com</span>
";

                    // Gửi email bằng PHPMailer
                    require "../mail/PHPMailer/src/PHPMailer.php";
                    require "../mail/PHPMailer/src/SMTP.php";
                    require "../mail/PHPMailer/src/Exception.php";

                    $mail = new PHPMailer\PHPMailer\PHPMailer();
                    $mail->IsSMTP();
                    $mail->SMTPAuth = true;
                    $mail->SMTPSecure = 'ssl';
                    $mail->Host = "smtp.gmail.com";
                    $mail->Port = 465;
                    $mail->IsHTML(true);
                    $mail->CharSet = 'UTF-8'; // Đặt mã ký tự cho email
                    $mail->Username = "seraphbeauty22@gmail.com";
                    $mail->Password = "einsonpyjjyxepyr"; // Hãy sử dụng biến môi trường cho mật khẩu
                    $mail->SetFrom("seraphbeauty22@gmail.com", "Seraph Beauty");
                    $mail->Subject = $subject;
                    $mail->Body = $body;
                    $mail->AddAddress($user_email); // Sử dụng $user_email

                    if ($mail->send()) {
                        
                    } else {
                        echo "Không thể gửi email: " . $mail->ErrorInfo;
                    }
                } else {
                    echo "<script>alert('Đặt hàng thất bại!');</script>";
                }
            } else {
                echo "<script>alert('Giỏ hàng trống!');</script>";
            }
        } else {
            echo "<script>alert('Giỏ hàng trống!');</script>";
        }
    } else {
        // Trường hợp còn lại, sử dụng dữ liệu từ bảng cart của người dùng
        $user_id = $_SESSION['user_id']; // Lấy user_id từ session
        $select_cart_db = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('Lỗi truy vấn: ' . mysqli_error($conn));

        if (mysqli_num_rows($select_cart_db) > 0) {
            $total_quantity = 0;
            $total_discount_price = 0;
            $grand_total_price = 0;

            // Duyệt qua từng sản phẩm trong bảng `cart`
            while ($cart_item = mysqli_fetch_assoc($select_cart_db)) {
                $product_id = $cart_item['product_id'];
                $quantity = $cart_item['quantity'];

                // Truy vấn thông tin sản phẩm từ bảng products
                $select_product = mysqli_query($conn, "SELECT product_name, product_price, product_image, product_subcategory, quantity_in_stock FROM `products` WHERE product_id = '$product_id'") or die('Lỗi truy vấn: ' . mysqli_error($conn));

                if (mysqli_num_rows($select_product) > 0) {
                    $fetch_product = mysqli_fetch_assoc($select_product);
                    $price = $fetch_product['product_price'];
                    $product_name = mysqli_real_escape_string($conn, $fetch_product['product_name']);
                    $product_image = mysqli_real_escape_string($conn, $fetch_product['product_image']);
                    $product_subcategory = $fetch_product['product_subcategory'];
                    $quantity_in_stock = $fetch_product['quantity_in_stock'];

                    // Kiểm tra nếu số lượng đủ để đặt hàng
                    if ($quantity_in_stock < $quantity) {
                        echo "<script>alert('Số lượng yêu cầu vượt quá số lượng trong kho cho sản phẩm: $product_name');</script>";
                        continue;
                    }

                    // Kiểm tra khuyến mãi
                    $select_promotion = mysqli_query($conn, "SELECT discount_percent FROM `product_promotion` WHERE subcategory_name = '$product_subcategory' AND start_date <= '$current_date' AND end_date >= '$current_date'") or die('Lỗi truy vấn: ' . mysqli_error($conn));
                    $discount_percent = (mysqli_num_rows($select_promotion) > 0) ? mysqli_fetch_assoc($select_promotion)['discount_percent'] : 0;

                    // Tính toán giảm giá
                    $discount_amount = $price * ($discount_percent / 100);
                    $coupon_discount = 0; // Khởi tạo giá trị giảm giá mã coupon

                    // Nếu có mã giảm giá được gửi từ client
                    if (isset($_POST['coupon_code']) && !empty($_POST['coupon_code'])) {
                        $coupon_code = mysqli_real_escape_string($conn, $_POST['coupon_code']);

                        // Truy vấn kiểm tra mã giảm giá từ bảng promotions
                        $coupon_query = mysqli_query($conn, "
        SELECT discount_percent 
        FROM promotions 
        WHERE code_discount = '$coupon_code' 
        AND start_date <= CURDATE() 
        AND end_date >= CURDATE()
    ") or die('Lỗi truy vấn mã giảm giá: ' . mysqli_error($conn));

                        if (mysqli_num_rows($coupon_query) > 0) {
                            $coupon_data = mysqli_fetch_assoc($coupon_query);
                            $coupon_percent = $coupon_data['discount_percent'];

                            if ($coupon_percent > 0 && $coupon_percent <= 100) {
                                // Tính giảm giá từ mã coupon
                                $coupon_discount = ($coupon_percent / 100) * $price * $quantity;
                            }
                        }
                    }

                    // Tổng giảm giá cuối cùng (bao gồm giảm giá mã coupon và giảm giá sản phẩm)
                    $total_discount = ($discount_amount * $quantity) + $coupon_discount;
                    $total_discounted_price = ($price - $discount_amount) * $quantity;

                    // Cập nhật tổng giá trị đơn hàng
                    $total_quantity += $quantity;
                    $total_discount_price += $total_discount;
                    $grand_total_price += $total_discounted_price;
                    $grand_total = $grand_total_price +$shipping_fee;
                } else {
                    echo "<script>alert('Sản phẩm có ID $product_id không tồn tại trong cơ sở dữ liệu!');</script>";
                }
            }

            // Xử lý phương thức thanh toán
$success_value = ($payment_method === 'payatm') ? 0 : 1;

// Tạo đơn hàng mới trong bảng `orders`
if ($total_quantity > 0) {
    $insert_order = "
        INSERT INTO `orders` (
            user_id, user_name, user_number, user_email, method, 
            address, total_products, total_price, total_discount_price, shipping_fee, placed_on, status_order, success
        ) VALUES (
            '$user_id', '$user_name', '$user_number', '$user_email',
            '$payment_method', '$full_address', '$total_quantity', '$grand_total', 
            '$total_discount_price', '$shipping_fee', NOW(), '$payment_status', '$success_value'
        )";

    $insert_order_result = mysqli_query($conn, $insert_order) or die('Lỗi khi thêm đơn hàng: ' . mysqli_error($conn));

    if ($insert_order_result) {
        $order_id = mysqli_insert_id($conn); // Lấy ID của đơn hàng vừa tạo

        // Thêm từng sản phẩm vào bảng `order_items`
        mysqli_data_seek($select_cart_db, 0); // Reset con trỏ truy vấn bảng `cart`
        while ($cart_item = mysqli_fetch_assoc($select_cart_db)) {
            $product_id = $cart_item['product_id'];
            $quantity = $cart_item['quantity'];

            // Truy vấn lại thông tin sản phẩm
            $select_product = mysqli_query($conn, "SELECT quantity_in_stock, product_name, product_image, product_price, product_subcategory FROM `products` WHERE product_id = '$product_id'") or die('Lỗi truy vấn: ' . mysqli_error($conn));
            $fetch_product = mysqli_fetch_assoc($select_product);
            $product_name = mysqli_real_escape_string($conn, $fetch_product['product_name']);
            $product_image = explode(',', $fetch_product['product_image'])[0]; // Hình ảnh đầu tiên
            $price = $fetch_product['product_price'];
            $product_subcategory = $fetch_product['product_subcategory'];

            // Kiểm tra khuyến mãi
            $select_promotion = mysqli_query($conn, "SELECT discount_percent FROM `product_promotion` WHERE subcategory_name = '$product_subcategory' AND start_date <= '$current_date' AND end_date >= '$current_date'") or die('Lỗi truy vấn: ' . mysqli_error($conn));
            $discount_percent = (mysqli_num_rows($select_promotion) > 0) ? mysqli_fetch_assoc($select_promotion)['discount_percent'] : 0;

            $discount_amount = $price * ($discount_percent / 100);
            $total_discount = $discount_amount * $quantity;
            $total_discounted_price = ($price - $discount_amount) * $quantity;

            // Thêm sản phẩm vào `order_items`
            $insert_order_item = "
                INSERT INTO `order_items` (
                    order_id, product_id, product_name, product_image, quantity, price, discount_fee, total_price, success
                ) VALUES (
                    '$order_id', '$product_id', '$product_name', '$product_image', 
                    '$quantity', '$price', '$total_discount', '$total_discounted_price', '$success_value'
                )";

            mysqli_query($conn, $insert_order_item) or die('Lỗi khi thêm sản phẩm vào đơn hàng: ' . mysqli_error($conn));

            if ($payment_method !== 'payatm') {
                // Cập nhật số lượng trong kho
                $new_quantity_in_stock = $fetch_product['quantity_in_stock'] - $quantity; // Tính số lượng mới
            
                $update_stock = "
                    UPDATE `products` 
                    SET quantity_in_stock = '$new_quantity_in_stock', 
                        purchase_amount = purchase_amount + '$quantity' 
                    WHERE product_id = '$product_id'";
                
                mysqli_query($conn, $update_stock)
                    or die('Lỗi khi cập nhật kho và purchase_amount: ' . mysqli_error($conn));
            
                // Kiểm tra nếu phương thức thanh toán là 'Thanh toán khi nhận hàng'
                if ($payment_method === 'Thanh toán khi nhận hàng') {
                    // Kiểm tra xem sản phẩm đã tồn tại trong bảng purchases chưa
                    $check_purchase = mysqli_query($conn, "SELECT * FROM `purchases` WHERE product_id = '$product_id'");
            
                    if (mysqli_num_rows($check_purchase) > 0) {
                        // Nếu sản phẩm đã tồn tại, cập nhật số lượng
                        $update_purchase = "
                            UPDATE `purchases` 
                            SET purchase_amout = purchase_amout + '$quantity', purchase_at = NOW() 
                            WHERE product_id = '$product_id'";
                        
                        mysqli_query($conn, $update_purchase)
                            or die('Lỗi khi cập nhật số lượng vào purchases: ' . mysqli_error($conn));
                    } else {
                        // Nếu sản phẩm chưa tồn tại, insert mới vào bảng purchases
                        $insert_purchase = "
                            INSERT INTO `purchases` (product_id, purchase_amout, purchase_at)
                            VALUES ('$product_id', '$quantity', NOW())";
                        
                        mysqli_query($conn, $insert_purchase)
                            or die('Lỗi khi thêm vào purchases: ' . mysqli_error($conn));
                    }
                } else {
                    // Nếu không phải phương thức thanh toán 'payatm' hoặc 'payonreceive', chèn vào purchases
                    $insert_purchase = "
                        INSERT IGNORE INTO `purchases` (product_id, purchase_amout, purchase_at)
                        VALUES ('$product_id', '$quantity', NOW())";
                    
                    mysqli_query($conn, $insert_purchase)
                        or die('Lỗi khi thêm vào purchases: ' . mysqli_error($conn));
                }
            }
        }
        if ($payment_method === 'payatm') {
            // Lưu trữ thông tin vào session
            $_SESSION['shipping_fee'] = $shipping_fee;
            
            // Chuyển hướng đến trang atmpayment.php
            header("Location: ../payment/atmpayment.php?shipping_fee=$shipping_fee&order_id=$order_id");
            exit();
        } else {
            
        }

                    echo "<script>
                    // Tạo box thông báo
                 let successOverlay = document.createElement('div');
                 successOverlay.style.position = 'fixed';
                 successOverlay.style.top = '20%';
                 successOverlay.style.left = '0';
                 successOverlay.style.width = '100%';
                 successOverlay.style.height = '80%';
                 successOverlay.style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
                 successOverlay.style.color = '#000';
                 successOverlay.style.display = 'flex';
                 successOverlay.style.flexDirection = 'column';
                 successOverlay.style.alignItems = 'center';
                 successOverlay.style.justifyContent = 'center';
                 successOverlay.style.fontSize = '24px';
                 successOverlay.style.zIndex = '9999';
                 successOverlay.style.fontFamily = 'Futura, sans-serif';
                 successOverlay.style.opacity = '0'; // Khởi tạo với opacity 0 để ẩn
                 successOverlay.style.transition = 'opacity 1s'; // Thêm hiệu ứng fade-in
                 
                 // Tạo nội dung thông báo
                 let successText = document.createElement('p');
                 successText.innerText = 'Đặt hàng thành công!';
                 
                 successOverlay.appendChild(successText);
                 
                 // Tạo phần tử xoay tròn (spinner)
                 let spinner = document.createElement('div');
                 spinner.style.border = '8px solid #000';
                 spinner.style.borderTop = '8px solid #fff';
                 spinner.style.borderRadius = '50%';
                 spinner.style.width = '60px';
                 spinner.style.height = '60px';
                 spinner.style.animation = 'spin 1s linear infinite';
                 successOverlay.appendChild(spinner);
                 
                 // Thêm keyframes cho hiệu ứng xoay tròn
                 let style = document.createElement('style');
                 style.innerHTML = `
                     @keyframes spin {
                         0% { transform: rotate(0deg); }
                         100% { transform: rotate(360deg); }
                     }
                 `;
                 document.head.appendChild(style);
                 
                 // Thêm thanh thông báo vào body
                 document.body.appendChild(successOverlay);
                 
                 // Hiển thị thanh thông báo với hiệu ứng fade-in
                 setTimeout(function() {
                     successOverlay.style.opacity = '1'; // Sau 100ms sẽ hiện ra
                 }, 100);
                 
                 // Sau khi thanh toán thành công, thực hiện chuyển hướng sau 3 giây
                 setTimeout(function() {
                     // Chuyển đến trang đơn hàng
                     window.location.href = '../user/order.php'; // Địa chỉ trang đơn hàng của bạn
                 }, 3000);
                 
                 
                 </script>";

                    $today = new DateTime();

                    // Ngày giao hàng từ hôm nay cộng 3 ngày
                    $delivery_from = clone $today; // Tạo bản sao của ngày hôm nay
                    $delivery_from->modify('+3 days');

                    // Ngày giao hàng từ hôm nay cộng 7 ngày
                    $delivery_to = clone $today; // Tạo bản sao khác
                    $delivery_to->modify('+7 days');
                    $subject = "Xác nhận đặt hàng từ Seraph Beauty";

                    // Giữ nguyên giá trị số trước khi tính toán
                    $shipping_fee = floor($shipping_fee);
                    $total_discount_price = floor($total_discount_price);
                    $grand_total_price = floor($grand_total_price);

                    // Tính toán `price_before_discount`
                    $price_before_discount = ($grand_total_price + $total_discount_price) - $shipping_fee;

                    // Định dạng các giá trị sau khi tính toán xong
                    $shipping_fee = number_format($shipping_fee, 0, '', '.');
                    $total_discount_price = number_format($total_discount_price, 0, '', '.');
                    $grand_total_price = number_format($grand_total_price, 0, '', '.');
                    $price_before_discount = number_format($price_before_discount, 0, '', '.');

                    $body = "Xin chào $user_name,<br><br>
<span style='font-family: 'Futura', sans-serif; font-size: 14px; line-height: 1.5; color: #000000;'>
Chúng tôi đã nhận được đơn hàng của bạn với thông tin sau:<br><br>

<strong style='color: #000000;'>Tóm tắt đơn hàng:</strong><br>
<div style='padding-left: 20px; color: #000000;'>
- Phí vận chuyển: $shipping_fee VND<br>
- Giảm giá: $total_discount VND<br>

- Tổng thanh toán sau giảm giá: $grand_total_price VND<br>
</div><br>

- Tên: $user_name<br>
- Số điện thoại: $user_number<br>
- Email: $user_email<br>
- Địa chỉ: $full_address<br>
- Phương thức thanh toán: $payment_method<br>
- Ngày dự kiến giao hàng: " . $delivery_from->format('d-m-Y') . " đến " . $delivery_to->format('d-m-Y') . "<br><br>

Cảm ơn bạn đã đặt hàng! Nếu có bất kỳ thắc mắc nào xin liên hệ chúng tôi qua email và số điện thoại hỗ trợ.<br>
Chúng tôi sẽ xử lý đơn hàng của bạn sớm nhất có thể.<br>
Trân trọng,<br>
</span>
<h2 style='font-weight: bold;'>Seraph Beauty</h2>
<span>Số điện thoại: 0922 222 2222 | Email: seraphbeauty22@gmail.com</span>
";

                    // Gửi email bằng PHPMailer
                    require "../mail/PHPMailer/src/PHPMailer.php";
                    require "../mail/PHPMailer/src/SMTP.php";
                    require "../mail/PHPMailer/src/Exception.php";

                    $mail = new PHPMailer\PHPMailer\PHPMailer();
                    $mail->IsSMTP();
                    $mail->SMTPAuth = true;
                    $mail->SMTPSecure = 'ssl';
                    $mail->Host = "smtp.gmail.com";
                    $mail->Port = 465;
                    $mail->IsHTML(true);
                    $mail->CharSet = 'UTF-8'; // Đặt mã ký tự cho email
                    $mail->Username = "seraphbeauty22@gmail.com";
                    $mail->Password = "einsonpyjjyxepyr"; // Hãy sử dụng biến môi trường cho mật khẩu
                    $mail->SetFrom("seraphbeauty22@gmail.com", "Seraph Beauty");
                    $mail->Subject = $subject;
                    $mail->Body = $body;
                    $mail->AddAddress($user_email); // Sử dụng $user_email

                    if ($mail->send()) {
                        echo "Đơn hàng của bạn đã được xác nhận qua email!";
                    } else {
                        echo "Không thể gửi email: " . $mail->ErrorInfo;
                    }

                    // Xóa giỏ hàng trong bảng `cart` của người dùng
                    mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('Lỗi khi xóa giỏ hàng: ' . mysqli_error($conn));
                } else {
                    echo "<script>alert('Đặt hàng thất bại!');</script>";
                }
            } else {
                echo "<script>alert('Giỏ hàng trong bảng cart trống!');</script>";
            }
        } else {
            echo "<script>alert('Không có sản phẩm trong bảng cart!');</script>";
        }
    }
}
if (isset($coupon_code) && !empty($coupon_code)) {
    // Kiểm tra xem mã giảm giá đã tồn tại trong bảng coupon_user hay chưa
    $check_coupon = mysqli_query($conn, "SELECT * FROM `coupon_used` WHERE user_id = '$user_id' AND coupon_name = '$coupon_code'")
        or die('Lỗi khi kiểm tra coupon: ' . mysqli_error($conn));

    if (mysqli_num_rows($check_coupon) == 0) {
        // Nếu chưa tồn tại, chèn coupon_code vào bảng coupon_user
        $insert_coupon_user = "INSERT INTO `coupon_used` (user_id, coupon_name, applied_at) 
                               VALUES ('$user_id', '$coupon_code', NOW())";

        $insert_coupon_result = mysqli_query($conn, $insert_coupon_user)
            or die('Lỗi khi thêm mã giảm giá vào coupon_user: ' . mysqli_error($conn));

        if ($insert_coupon_result) {
            echo "Mã giảm giá đã được lưu thành công!";
        }
    } else {
        echo "Mã giảm giá này đã được sử dụng trước đó!";
    }
}


?>





<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css"
        integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.6.4.js"></script>
    <!-- <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css"
        integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js"
        integrity="sha512-HGOnQO9+SP1V92SrtZfjqxxtLmVzqZpjFFekvzZVWoiASSQgSr4cw9Kqd2+l8Llp4Gm0G8GIFJ4ddwZilcdb8A=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.js"
        integrity="sha512-eP8DK17a+MOcKHXC5Yrqzd8WI5WKh6F1TIk5QZ/8Lbv+8ssblcz7oGC8ZmQ/ZSAPa7ZmsCU4e/hcovqR8jfJqA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- <link rel="stylesheet" type="text/css" href="slick.css"> -->
    <link rel="stylesheet" type="text/css" href="../CSS/main.css?v=1.1 <?php echo time(); ?>">
    <<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDiFwNohKTqVhK-rJo0Fz7ZWebOncZilV8&libraries=places">
        </script>



        <script src="../js/app.js"></script>
</head>

<body>

    <?php include '../user/header.php' ?>

    <div class="line"></div>
    <section class="checkout">
        <div class="checkout-container">
            <div class="box-container">
                <?php
                $grand_total = 0;
                $total_quantity = 0;

                $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('Lỗi truy vấn: ' . mysqli_error($conn));

                if (mysqli_num_rows($select_cart) > 0) {
                    while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                        $product_name = $fetch_cart['product_name'];
                        $quantity = $fetch_cart['quantity'];
                        $product_id = $fetch_cart['product_id'];
                        $image_names = explode(',', $fetch_cart['product_image']);

                        $select_product = mysqli_query($conn, "SELECT product_price FROM `products` WHERE product_id = '$product_id'") or die('Lỗi truy vấn: ' . mysqli_error($conn));
                        $fetch_product = mysqli_fetch_assoc($select_product);

                        if ($fetch_product) {
                            $price = $fetch_product['product_price'];
                        } else {
                            $price = 0;
                        }

                        $total_price = $price * $quantity;
                        $grand_total += $total_price;
                        $total_quantity += $quantity;

                        // Display product info here
                    }
                } else {
                }
                ?>
            </div>



            <!-- Form only appears once -->
            <div class="checkout-form">

                <?php

$user_id = $_SESSION['user_id'];  // Nếu user_id lưu trong session

// Truy vấn thông tin người dùng từ bảng users
$query = "SELECT user_name, user_number, user_email FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    // Lấy thông tin người dùng từ kết quả truy vấn
    $user_info = mysqli_fetch_assoc($result);
    $user_name = $user_info['user_name'];
    $user_number = $user_info['user_number'];
    $user_email = $user_info['user_email'];
} else {
    // Nếu không tìm thấy người dùng, có thể xử lý ở đây
    $user_name = '';
    $user_number = '';
    $user_email = '';
}
?>

                <!-- Form hiển thị thông tin người dùng -->
                <form method="post" id="couponForm">
                    <div class="input-field">
                        <label>Tên của bạn<span>*</span></label>
                        <input type="text" name="name" placeholder="Hãy nhập tên của bạn"
                            value="<?php echo $user_name; ?>">
                    </div>
                    <div class="input-field">
                        <label>Số điện thoại của bạn<span>*</span></label>
                        <input type="number" name="number" placeholder="Hãy nhập số điện thoại của bạn"
                            value="<?php echo $user_number; ?>">
                    </div>
                    <div class="input-field">
                        <label>Email của bạn<span>*</span></label>
                        <input type="text" name="email" placeholder="Hãy nhập email của bạn"
                            value="<?php echo $user_email; ?>">
                    </div>
                    <div class="input-field">
                        <label>Vui lòng chọn phương thức thanh toán<span>*</span></label>
                        <select name="method">
                            <option selected disabled>Chọn phương thức thanh toán</option>
                            <option value="Thanh toán khi nhận hàng">Thanh toán khi nhận hàng</option>

                            <option value="payatm">Thanh toán bằng tài momo</option>
                        </select>
                    </div>

                    <div class="input-field" id="coupon-field">
                        <label for="coupon_code">Nhập mã giảm giá:</label>
                        <div class="input-container">
                            <input type="text" id="coupon_code" name="coupon_code" placeholder="Nhập mã giảm giá">
                            <button type="button" id="applyCoupon">Áp dụng</button>
                        </div>
                    </div>

                    <script>
                    let couponApplied = false; // Biến trạng thái mã giảm giá

                    document.getElementById('applyCoupon').addEventListener('click', function() {
                        if (couponApplied) {
                            alert("Mã giảm giá đã được áp dụng!");
                            return;
                        }

                        const couponCode = document.getElementById('coupon_code').value;
                        const originalPrice = parseFloat(document.getElementById(
                                'original_price_display')
                            .innerText.replace(/[^0-9]/g, ''));
                        const currentDiscount = parseFloat(document.getElementById('discount_display')
                            .innerText
                            .replace(/[^0-9]/g, '')) || 0;

                        if (!couponCode) {
                            alert("Vui lòng nhập mã giảm giá!");
                            return;
                        }

                        // Gửi dữ liệu bằng AJAX
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', 'check_coupon.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.onreadystatechange = function() {
                            if (xhr.readyState === 4 && xhr.status === 200) {
                                try {
                                    const response = JSON.parse(xhr.responseText);

                                    if (response.status === "success") {
                                        // Cập nhật UI với thông tin giảm giá
                                        document.getElementById('discount_display').innerText =
                                            `-${response.total_discount} VNĐ`;
                                        document.getElementById('grand_total_display').innerText =
                                            `${response.final_price} VNĐ`;

                                        couponApplied = true; // Đánh dấu mã giảm giá đã áp dụng
                                    } else {
                                        alert(response.message);
                                    }
                                } catch (e) {
                                    console.error("Lỗi phản hồi từ server:", e);
                                }
                            }
                        };

                        // Gửi dữ liệu đến server
                        const params =
                            `coupon_code=${encodeURIComponent(couponCode)}&total_price=${originalPrice}&current_discount=${currentDiscount}`;
                        xhr.send(params);
                    });
                    </script>

                    <div class="input-field">
                        <label for="province">Tỉnh/Thành phố<span>*</span></label>
                        <select id="province" name="province" class="form-control" required>
                            <option value="">Chọn một tỉnh</option>
                            <?php
                            $sql = "SELECT * FROM province";
                            $result = mysqli_query($conn, $sql);
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='{$row['province_id']}'>{$row['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="input-field">
                        <label for="district">Quận/Huyện<span>*</span></label>
                        <select id="district" name="district" class="form-control" required>
                            <option value="">Chọn một quận/huyện</option>
                        </select>
                    </div>

                    <div class="input-field">
                        <label for="wards">Phường/Xã<span>*</span></label>
                        <select id="wards" name="wards" class="form-control" required>
                            <option value="">Chọn một xã</option>
                        </select>
                    </div>







                    <div class="input-field">
                        <label>Tên đường, tòa nhà, số nhà<span>*</span></label>
                        <input type="text" id="flat" name="flat" placeholder="Nhập tên đường, tòa nhà, số nhà">
                    </div>




                    <!-- <div class="input-field">
                        <label>Bạn có muốn theo dõi đơn hàng qua email không?<span>*</span></label>
                        <select name="track_order" required>
                            <option value="1">Có</option>
                            <option value="0">Không</option>
                        </select>
                    </div> -->


                    <input type="submit" name="order-btn" class="order-btn" value="Đặt ngay">
                </form>
            </div>
        </div>



        <div class="order-detail">
            <div class="user-info">
                <h2>Thông tin người dùng</h2>
                <ul class="user-detail">
                    <?php
        // Giả sử bạn đã có thông tin người dùng trong session hoặc cơ sở dữ liệu.
        // Ví dụ, lấy thông tin từ session hoặc cơ sở dữ liệu:
        $user_id = $_SESSION['user_id']; // hoặc lấy từ cơ sở dữ liệu nếu có
        $select_user_info = mysqli_query($conn, "SELECT point, user_name, user_email, user_number FROM users WHERE user_id = '$user_id'") or die('Lỗi truy vấn: ' . mysqli_error($conn));

        if (mysqli_num_rows($select_user_info) > 0) {
            $user_info = mysqli_fetch_assoc($select_user_info);
            $user_name = $user_info['user_name'];
            $user_email = $user_info['user_email'];
            $user_number = $user_info['user_number'];
            $user_point = $user_info['point'];

            // Xác định hạng dựa trên điểm thưởng
            if ($user_point >= 5000) {
                $rank = 'Kim Cương';
                $next_rank = null; // Không có hạng tiếp theo
            } elseif ($user_point >= 2000) {
                $rank = 'Bạch Kim';
                $next_rank = 'Kim Cương';
                $next_point = 5000;
            } elseif ($user_point >= 1000) {
                $rank = 'Vàng';
                $next_rank = 'Bạch Kim';
                $next_point = 2000;
            } elseif ($user_point >= 500) {
                $rank = 'Bạc';
                $next_rank = 'Vàng';
                $next_point = 1000;
            } else {
                $rank = 'Thành viên thường';
                $next_rank = 'Bạc';
                $next_point = 500;
            }

            // Xác định mức giảm giá
            $discount = 0; // Mặc định là không giảm giá
            if ($rank === 'Bạc') {
                $discount = 2;
            } elseif ($rank === 'Vàng') {
                $discount = 5;
            } elseif ($rank === 'Bạch Kim') {
                $discount = 8;
            } elseif ($rank === 'Kim Cương') {
                $discount = 10;
            }

            echo '
            <li><strong>Tên người dùng:</strong> ' . $user_name . '</li>
            <li><strong>Email:</strong> ' . $user_email . '</li>
            <li><strong>Số điện thoại:</strong> ' . $user_number . '</li>
            <li><strong>Số điểm thưởng hiện có:</strong> ' . $user_point . '</li>
            <li><strong>Hạng hiện tại:</strong> ' . $rank . '</li>';
            
            if ($next_rank) {
                echo '<li><strong>Hạng tiếp theo:</strong> ' . $next_rank . ' (Cần ' . ($next_point - $user_point) . ' điểm)</li>';
            }

            echo '<p>Bạn đang là thành viên <strong>' . $rank . '</strong>, và bạn được giảm <strong>' . $discount . '%</strong> trên mỗi đơn hàng.</p>';

        } else {
            echo '<p class="empty">Thông tin người dùng không tồn tại!</p>';
        }

        ?>
                </ul>
            </div>
            <div class="order-summary">
                <h2>Tóm tắt đơn hàng</h2>
                <ul class="order-detail">
                    <?php
$grand_total = 0; // Tổng giá tiền sau giảm
$total_discount = 0; // Tổng số tiền đã giảm
$grand_total_original = 0; // Tổng giá tiền trước giảm

// Lấy user_id từ session
$user_id = $_SESSION['user_id'];

// Truy vấn thông tin điểm thưởng của người dùng
$select_user_info = mysqli_query($conn, "SELECT point FROM users WHERE user_id = '$user_id'") or die('Lỗi truy vấn: ' . mysqli_error($conn));

if (mysqli_num_rows($select_user_info) > 0) {
    $user_info = mysqli_fetch_assoc($select_user_info);
    $user_point = $user_info['point'];

    // Xác định hạng dựa trên điểm thưởng
    if ($user_point >= 5000) {
        $rank = 'Kim Cương';
        $discount = 10;
    } elseif ($user_point >= 2000) {
        $rank = 'Bạch Kim';
        $discount = 8;
    } elseif ($user_point >= 1000) {
        $rank = 'Vàng';
        $discount = 5;
    } elseif ($user_point >= 500) {
        $rank = 'Bạc';
        $discount = 2;
    } else {
        $rank = 'Thành viên thường';
        $discount = 0;
    }
} else {
    echo '<p class="empty">Thông tin người dùng không tồn tại!</p>';
    $rank = 'Thành viên thường';
    $discount = 0;
}

// Kiểm tra nếu có product_id trên URL
if (isset($_GET['product_id'])) {
    $product_id_from_url = intval($_GET['product_id']);
    
    // Truy vấn sản phẩm từ bảng `products`
    $select_product = mysqli_query($conn, "SELECT product_name, product_image, product_price, color_name, capacity FROM `products` WHERE product_id = '$product_id_from_url'") or die('Lỗi truy vấn: ' . mysqli_error($conn));
    
    if (mysqli_num_rows($select_product) > 0) {
        $fetch_product = mysqli_fetch_assoc($select_product);
        $product_name = $fetch_product['product_name'];
        $color_name = $fetch_product['color_name'];
        $capacity = $fetch_product['capacity'];
        $price = $fetch_product['product_price'];
        $image_names = explode(',', $fetch_product['product_image']);
        $quantity = 1; // Mặc định số lượng là 1

        // Kiểm tra khuyến mãi theo `product_id`
        $current_date = date('Y-m-d');
        $select_promotion = mysqli_query($conn, "SELECT discount_percent FROM `product_promotion` WHERE product_id = '$product_id_from_url' AND start_date <= '$current_date' AND end_date >= '$current_date'") or die('Lỗi truy vấn: ' . mysqli_error($conn));
        
        $discount_percent = (mysqli_num_rows($select_promotion) > 0) ? mysqli_fetch_assoc($select_promotion)['discount_percent'] : 0;

        // Tính toán giảm giá
        $discount_amount = $price * ($discount_percent / 100);
        $discounted_price_per_unit = $price - $discount_amount;

        // Áp dụng giảm giá thêm theo hạng thành viên
        $member_discount_amount = $discounted_price_per_unit * ($discount / 100);
        $final_price_per_unit = $discounted_price_per_unit - $member_discount_amount;

        // Tính tổng tiền
        $total_final_price = $final_price_per_unit * $quantity;
        $grand_total += $total_final_price;
        $total_discount += ($discount_amount) * $quantity;
        $grand_total_original += $price * $quantity;
        $rankdiscount = $grand_total * ($discount / 100);
        // Tạo tên sản phẩm hiển thị
        $display_name = $product_name;
        if (!empty($color_name)) $display_name .= ' - ' . $color_name;
        if (!empty($capacity)) $display_name .= ' - ' . $capacity;

        // Hiển thị sản phẩm
        echo '
        <li>
            <div class="box">
                <div class="box-content">
                    <div class="img-container">
                        <img class="imgshop" src="../image/product/' . $image_names[0] . '" alt="' . $product_name . '">
                    </div>
                    <div class="product-details">
                        <h3>' . $display_name . '</h3>
                        <p>SL: ' . $quantity . '</p>';
        


        echo '<p>Tổng cộng: ' . number_format($price, 0, ',', '.') . ' VNĐ</p>';
        echo '
                    </div>                    
                </div>
            </div>
        </li>';
    } else {
        echo '<p class="empty">Sản phẩm không tồn tại!</p>';
    }
}
                elseif (isset($_GET['sessioncart'])) {
                    // Hiển thị sản phẩm từ session cart
                    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $cart_item) {
                            $product_name = $cart_item['product_name'];
                            $quantity = $cart_item['quantity'];
                            $product_id = $cart_item['product_id'];
                            $image_names = explode(',', $cart_item['product_image']);
                
                            // Truy vấn thông tin sản phẩm từ bảng `products`
                            $select_product = mysqli_query($conn, "SELECT product_price, color_name, capacity FROM `products` WHERE product_id = '$product_id'") or die('Lỗi truy vấn: ' . mysqli_error($conn));
                            $fetch_product = mysqli_fetch_assoc($select_product);
                            $price = $fetch_product['product_price'];
                            $color_name = $fetch_product['color_name'];
                            $capacity = $fetch_product['capacity'];
                
                            // Kiểm tra khuyến mãi theo `product_id`
                            $current_date = date('Y-m-d');
                            $select_promotion = mysqli_query($conn, "SELECT discount_percent FROM `product_promotion` WHERE product_id = '$product_id' AND start_date <= '$current_date' AND end_date >= '$current_date'") or die('Lỗi truy vấn: ' . mysqli_error($conn));
                            $discount_percent = (mysqli_num_rows($select_promotion) > 0) ? mysqli_fetch_assoc($select_promotion)['discount_percent'] : 0;
                
                            // Tính giá sau khi giảm
                            $discount_amount = $price * ($discount_percent / 100);
                            $discounted_price_per_unit = $price - $discount_amount;
                
                            // Tính tổng tiền
                            $total_discounted_price = $discounted_price_per_unit * $quantity;
                            $grand_total += $total_discounted_price;
                            $total_discount += $discount_amount * $quantity;
                            $grand_total_original += $price * $quantity;
                
                            // Tạo tên sản phẩm hiển thị
                            $display_name = $product_name;
                            if (!empty($color_name)) $display_name .= ' - ' . $color_name;
                            if (!empty($capacity)) $display_name .= ' - ' . $capacity;
                
                            // Hiển thị sản phẩm
                            echo '
                            <li>
                                <div class="box">
                                    <div class="box-content">
                                        <div class="img-container">
                                            <img class="imgshop" src="../image/product/' . $image_names[0] . '" alt="' . $product_name . '">
                                        </div>
                                        <div class="product-details">
                                            <h3>' . $display_name . '</h3>
                                            <p>SL: ' . $quantity . '</p>';
                
                            // Hiển thị giá gốc và giá đã giảm
                            if ($discount_percent > 0) {
                                echo '<p>Giá: <span style="text-decoration: line-through; color: #666;">' . number_format($price, 0, ',', '.') . ' VNĐ</span> <span style="color: #bd0100;">' . number_format($discounted_price_per_unit, 0, ',', '.') . ' VNĐ</span></p>';
                            } else {
                                echo '<p>Giá: <span>' . number_format($price, 0, ',', '.') . ' VNĐ</span></p>';
                            }
                            echo '<p>Tổng cộng: ' . number_format($grand_total, 0, ',', '.') . ' VNĐ</p>';
                            echo '
                                        </div>                    
                                    </div>
                                </div>
                            </li>';
                        }
                    } else {
                        echo '<p class="empty">Giỏ hàng trống!</p>';
                    }
                }
                else {
                    // Nếu không có "sessioncart" trên URL, hiển thị sản phẩm từ bảng cart trong cơ sở dữ liệu
                    $select_cart_db = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('Lỗi truy vấn: ' . mysqli_error($conn));
                
                    if (mysqli_num_rows($select_cart_db) > 0) {
                        while ($fetch_cart_db = mysqli_fetch_assoc($select_cart_db)) {
                            $product_name = $fetch_cart_db['product_name'];
                            $quantity = $fetch_cart_db['quantity'];
                            $product_id = $fetch_cart_db['product_id'];
                            $image_names = explode(',', $fetch_cart_db['product_image']);
                
                            // Lấy giá và các thuộc tính khác từ bảng `products`
                            $select_product = mysqli_query($conn, "SELECT product_price, color_name, capacity FROM `products` WHERE product_id = '$product_id'") or die('Lỗi truy vấn: ' . mysqli_error($conn));
                            $fetch_product = mysqli_fetch_assoc($select_product);
                            $price = $fetch_product['product_price'];
                            $color_name = $fetch_product['color_name'];
                            $capacity = $fetch_product['capacity'];
                
                            // Kiểm tra nếu có khuyến mãi cho sản phẩm dựa trên product_id
                            $current_date = date('Y-m-d');
                            $select_promotion = mysqli_query($conn, "SELECT discount_percent FROM `product_promotion` WHERE product_id = '$product_id' AND start_date <= '$current_date' AND end_date >= '$current_date'") or die('Lỗi truy vấn: ' . mysqli_error($conn));
                            $discount_percent = (mysqli_num_rows($select_promotion) > 0) ? mysqli_fetch_assoc($select_promotion)['discount_percent'] : 0;
                
                            // Tính giá sau khi giảm
                            $discount_amount = $price * ($discount_percent / 100);
                            $discounted_price_per_unit = $price - $discount_amount;
                
                            // Tính tổng tiền
                            $total_discounted_price = $discounted_price_per_unit * $quantity;
                            $grand_total += $total_discounted_price;
                            $total_discount += $discount_amount * $quantity;
                            $grand_total_original += $price * $quantity;
                            $rankdiscount = $grand_total * ($discount / 100);
                            // Tính giảm giá từ hạng thành viên



                
                            // Tạo chuỗi hiển thị tên sản phẩm với color_name và capacity nếu có
                            $display_name = $product_name;
                            if (!empty($color_name)) {
                                $display_name .= ' - ' . $color_name;
                            }
                            if (!empty($capacity)) {
                                $display_name .= ' - ' . $capacity;
                            }
                
                            // Hiển thị sản phẩm trong cơ sở dữ liệu
                            echo '
                            <li>
                                <div class="box">
                                    <div class="box-content">
                                        <div class="img-container">
                                            <img class="imgshop" src="../image/product/' . $image_names[0] . '" alt="' . $product_name . '">
                                        </div>
                                        <div class="product-details">
                                            <h3>' . $display_name . '</h3>
                                            <p>SL: ' . $quantity . '</p>';
                
                            // Hiển thị giá gốc và giá đã giảm
                            if ($discount_percent > 0) {
                                echo '<p>Giá: <span style="text-decoration: line-through; color: #666;">' . number_format($price, 0, ',', '.') . ' VNĐ</span> <span style="color: #bd0100;">' . number_format($discounted_price_per_unit, 0, ',', '.') . ' VNĐ</span></p>';
                            } else {
                                echo '<p>Giá: <span>' . number_format($price, 0, ',', '.') . ' VNĐ</span></p>';
                            }
                            echo '<p>Tổng cộng: ' . number_format($total_discounted_price, 0, ',', '.') . ' VNĐ</p>';
                            echo '
                                        </div>                    
                                    </div>
                                </div>
                            </li>';
                        }
                    } else {
                        echo '<p class="empty">Giỏ hàng trống!</p>';
                    }
                }
                
                ?>

                    <style>
                    .total-price {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 0.2rem 0;
                        gap: 0.1rem;
                    }
                    </style>



                    <li class="total-price">
                        <strong>Tổng giá tiền chưa giảm:</strong>
                        <span
                            id="original_price_display"><?php echo number_format($grand_total_original, 0, ',', '.'); ?>
                            VNĐ</span>
                    </li>
                    <li class="total-price">
                        <strong>Tổng số tiền được giảm:</strong>
                        <span id="discount_display">-<?php echo number_format($total_discount, 0, ',', '.'); ?>
                            VNĐ</span>
                    </li>
                    <li class="total-price">
                        <strong>Số tiền được giảm từ hạng thành viên:</strong>
                        <span id="rank_discount_display">-<?php echo number_format($rankdiscount, 0, ',', '.'); ?>
                            VNĐ</span>
                    </li>


                    <li class="total-price">
                        <strong>Phí vận chuyển cho: <span id="distance-result"></span></strong>
                        <input type="hidden" id="shipping_fee" name="shipping_fee" value="0">
                        <span id="shipping_result"></span>
                    </li>
                    <li class="total-price">
                        <strong>Thành tiền:</strong>
                        <span
                            id="grand_total_display"><?php echo number_format($grand_total_original-$rankdiscount-$total_discount, 0, ',', '.'); ?>
                            VNĐ</span>
                    </li>


                </ul>
            </div>


        </div>







        <script type="text/javascript" src="../js/script2.js"></script>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</body>


<script>
$(document).ready(function() {
    var shipping_fee = 0; // Biến lưu trữ phí vận chuyển

    // Khi thay đổi tỉnh
    $('#province').change(function() {
        var province_id = $(this).val();
        if (province_id != '') {
            $.ajax({
                url: "fetch_district.php",
                method: "POST",
                data: {
                    province_id: province_id
                },
                success: function(data) {
                    $('#district').html(data);
                    $('#wards').html('<option value="">Chọn một xã</option>');
                }
            });
        }
    });

    // Khi thay đổi quận
    $('#district').change(function() {
        var district_id = $(this).val();
        if (district_id != '') {
            $.ajax({
                url: "fetch_wards.php",
                method: "POST",
                data: {
                    district_id: district_id
                },
                success: function(data) {
                    $('#wards').html(data);
                }
            });
        }
    });

    // Khi thay đổi phường/xã
    $('#wards').change(function() {
        calculateShipping(); // Tính phí vận chuyển khi chọn đủ địa chỉ
    });

    function calculateShipping() {
        var province = $('#province option:selected').text();
        var district = $('#district option:selected').text();
        var ward = $('#wards option:selected').text();

        if (province && district && ward) {
            $.ajax({
                url: "calculate_shipping.php",
                method: "POST",
                dataType: "json",
                data: {
                    province: province,
                    district: district,
                    ward: ward
                },
                success: function(response) {
                    if (response.error) {
                        $('#distance-result').html(response.error);
                        $('#shipping_result').html('');
                        shipping_fee = 0;
                    } else {
                        $('#distance-result').html(+response.distance + " km");
                        $('#shipping_result').html(+response.shipping_fee +
                            " VNĐ");
                        shipping_fee = parseInt(response.shipping_fee);
                        $('#shipping_fee').val(shipping_fee);
                        updateGrandTotal();
                    }
                },
                error: function() {
                    $('#distance-result').html('Không thể tính khoảng cách.');
                    $('#shipping_result').html('Không thể tính phí ship.');
                    shipping_fee = 0;
                    updateGrandTotal();
                }
            });
        }
    }

    function updateGrandTotal() {
        var grand_total = <?php echo $grand_total; ?>;
        var final_total = grand_total + shipping_fee;
        $('#grand_total').val(final_total);
        $('#grand_total_display').html(new Intl.NumberFormat('vi-VN').format(final_total) + " VNĐ");
    }
});
</script>



</html>