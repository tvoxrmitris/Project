<?php
include '../connection/connection.php';
session_start();
$admin_id = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];
header('Content-type: text/html; charset=utf-8');

include('helper_momo.php');

// Lấy order_id từ URL
$order_id = intval($_GET['order_id']);

// Truy vấn để lấy total_price từ bảng orders
$select_order = mysqli_query($conn, "SELECT total_price FROM `orders` WHERE order_id = '$order_id'") 
    or die('Lỗi truy vấn đơn hàng: ' . mysqli_error($conn));

if (mysqli_num_rows($select_order) > 0) {
    $fetch_order = mysqli_fetch_assoc($select_order);
    $grand_total = $fetch_order['total_price'];
} else {
    echo "Đơn hàng không tồn tại.";
    exit();
}

// MoMo API configuration
$endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
$partnerCode = 'MOMOBKUN20180529';
$accessKey = 'klm05TvNBzhg7h7j';
$secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';

$orderInfo = "Thanh toán qua MoMo ATM";
$orderId = time() . "";

// Cập nhật URL chuyển hướng để bao gồm order_id
$redirectUrl = "http://localhost/NLCSN/user/order.php?success&order_id=" . $order_id;

$ipnUrl = "http://localhost/NLCSN/user/order.php?order_id=" . $order_id; 

$extraData = "";
$requestId = time() . "";
$requestType = "payWithATM";

$extraData = isset($_POST["extraData"]) ? $_POST["extraData"] : "";

// Tạo chữ ký HMAC SHA256
$rawHash = "accessKey=" . $accessKey . "&amount=" . $grand_total . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
$signature = hash_hmac("sha256", $rawHash, $secretKey);

$data = array(
    'partnerCode' => $partnerCode,
    'partnerName' => "Test",
    "storeId" => "MomoTestStore",
    'requestId' => $requestId,
    'amount' => $grand_total,
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
$jsonResult = json_decode($result, true);  // Giải mã json

if (isset($jsonResult['payUrl'])) {
    $payUrl = $jsonResult['payUrl'];
    header('Location: ' . $payUrl);
    exit();
} else {
    echo "Không tìm thấy URL thanh toán.";
    exit();
}