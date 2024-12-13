<?php
include '../connection/connection.php';

function getCoordinates($address)
{
    $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($address);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "YourAppName/1.0 (your_email@example.com)");
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    // Kiểm tra xem API có trả về kết quả hay không
    if (!empty($data) && isset($data[0])) {
        return ['lat' => $data[0]['lat'], 'lon' => $data[0]['lon']];
    }

    // Nếu không có kết quả, trả về null
    return null;
}

$province = $_POST['province'] ?? '';
$district = $_POST['district'] ?? '';
$ward = $_POST['ward'] ?? '';

$customerAddress = "$ward, $district, $province, Việt Nam";
$storeAddress = "Hồ Chí Minh, Việt Nam";

$customerCoordinates = getCoordinates($customerAddress);
$storeCoordinates = getCoordinates($storeAddress);

if ($customerCoordinates && $storeCoordinates) {
    // Tính khoảng cách giữa hai điểm (Haversine formula)
    $theta = $customerCoordinates['lon'] - $storeCoordinates['lon'];
    $dist = sin(deg2rad($customerCoordinates['lat'])) * sin(deg2rad($storeCoordinates['lat'])) +
        cos(deg2rad($customerCoordinates['lat'])) * cos(deg2rad($storeCoordinates['lat'])) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $distance = $dist * 60 * 1.1515 * 1.609344; // km

    // Tính phí ship dựa trên khoảng cách
    if ($province === 'Hồ Chí Minh') {
        // Phí nội tỉnh
        if ($distance <= 5) {
            $shippingFee = 10000; // Phí cố định cho 5km đầu tiên
        } elseif ($distance <= 10) {
            $shippingFee = 10000 + (($distance - 5) * 1500); // Phí cho mỗi km sau 5km
        } else {
            $shippingFee = 10000 + (5 * 1500) + (($distance - 10) * 2000); // Tăng phí cho mỗi km sau 10km
        }
    } else {
        // Phí ngoại tỉnh
        if ($distance <= 5) {
            $shippingFee = 500; // Phí cố định cho 5km đầu tiên
        } elseif ($distance <= 10) {
            $shippingFee = 500 + (($distance - 5) * 100); // Phí cho mỗi km sau 5km
        } else {
            $shippingFee = 500 + (5 * 100) + (($distance - 10) * 200); // Phí cho mỗi km sau 10km
        }
    }

    // Trả về kết quả khoảng cách và phí ship
    $response = [
        'distance' => round($distance, 2),
        'shipping_fee' => round($shippingFee)
    ];
    echo json_encode($response);
} else {
    // Trả về lỗi nếu không tính được tọa độ
    echo json_encode(['error' => 'Không thể tính phí giao hàng']);
}
