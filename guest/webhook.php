<?php
include '../connection/connection.php';
// Xử lý yêu cầu từ Dialogflow
$request = file_get_contents('php://input');
$requestJson = json_decode($request, true);

// Lấy câu hỏi của khách hàng
$queryText = $requestJson['queryResult']['queryText'];

// Truy vấn dữ liệu sản phẩm
$sql = "SELECT * FROM products WHERE product_name LIKE ?";
$stmt = $conn->prepare($sql);
$search = "%" . $queryText . "%";
$stmt->bind_param("s", $search);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    $response = [
        "fulfillmentText" => "Sản phẩm " . $product['product_name'] . " có giá " . $product['product_price'] . " VND."
    ];
} else {
    $response = [
        "fulfillmentText" => "Rất tiếc, tôi không tìm thấy sản phẩm bạn yêu cầu."
    ];
}



// Trả dữ liệu về Dialogflow
header('Content-Type: application/json');
echo json_encode($response);
?>