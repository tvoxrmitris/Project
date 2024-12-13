<?php
include '../connection/connection.php';
session_start();
$admin_id = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];
header('Content-type: text/html; charset=utf-8');

include('helper_momo.php');
$shipping_fee = $_SESSION['order_data']['shipping_fee'] ?? 0; // Nếu không có, mặc định là 0
// Lấy product_id từ URL
$product_id_from_url = intval($_GET['product_id']);

// Truy vấn để lấy product_price và product_subcategory từ bảng products
$select_product = mysqli_query($conn, "SELECT product_price, product_subcategory FROM `products` WHERE product_id = '$product_id_from_url'") or die('Lỗi truy vấn sản phẩm: ' . mysqli_error($conn));

if (mysqli_num_rows($select_product) > 0) {
    $fetch_product = mysqli_fetch_assoc($select_product);
    $product_price = $fetch_product['product_price'];
    $product_subcategory = $fetch_product['product_subcategory'];

    // Truy vấn để kiểm tra giảm giá từ bảng product_promotion với điều kiện ngày
    $current_date = date('Y-m-d'); // Ngày hiện tại
    $select_promotion = mysqli_query($conn, "SELECT discount_percent FROM `product_promotion` WHERE subcategory_name = '$product_subcategory' AND start_date <= '$current_date' AND end_date >= '$current_date'") or die('Lỗi truy vấn khuyến mãi: ' . mysqli_error($conn));

    if (mysqli_num_rows($select_promotion) > 0) {
        $fetch_promotion = mysqli_fetch_assoc($select_promotion);
        $discount_percent = $fetch_promotion['discount_percent'];
        $discount_amount = $product_price * ($discount_percent / 100); // Tính số tiền giảm giá
        $amount = $product_price - $discount_amount + $shipping_fee; // Giá sau khi áp dụng giảm giá
    } else {
        // Không có giảm giá hợp lệ, sử dụng giá gốc
        $amount = $product_price + $shipping_fee;
    }
} else {
    echo "Sản phẩm không tồn tại.";
    exit();
}

// Các thông tin MoMo API
$endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
$partnerCode = 'MOMOBKUN20180529';
$accessKey = 'klm05TvNBzhg7h7j';
$secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';

$orderInfo = "Thanh toán qua mã QR MoMo";
$orderId = time() . "";
$redirectUrl = "http://localhost/NLCSN/user/checkout.php";
$ipnUrl = "http://localhost/NLCSN/user/checkout.php";
$extraData = "";
$requestId = time() . "";
$requestType = "captureWallet";

// Tạo chữ ký
$rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
$signature = hash_hmac("sha256", $rawHash, $secretKey);

$data = array(
    'partnerCode' => $partnerCode,
    'partnerName' => "Test",
    "storeId" => "MomoTestStore",
    'requestId' => $requestId,
    'amount' => $amount,
    'orderId' => $orderId,
    'orderInfo' => $orderInfo,
    'redirectUrl' => $redirectUrl,
    'ipnUrl' => $ipnUrl,
    'lang' => 'vi',
    'extraData' => $extraData,
    'requestType' => $requestType,
    'signature' => $signature
);

$result = execPostRequest($endpoint, json_encode($data));
$jsonResult = json_decode($result, true);

// Kiểm tra nếu có URL thanh toán
if (isset($jsonResult['payUrl'])) {
    $payUrl = $jsonResult['payUrl'];
    header('Location: ' . $payUrl);
    exit(); // Dừng mã sau khi chuyển hướng
} else {
    echo "Không tìm thấy URL thanh toán.";
}