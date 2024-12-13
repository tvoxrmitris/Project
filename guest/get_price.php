<?php
// Include file connection.php để kết nối cơ sở dữ liệu
include '../connection/connection.php';

// Kiểm tra xem có dữ liệu được gửi từ phía client không
if(isset($_POST['productId']) && isset($_POST['selectedCapacity'])) {
    // Lấy productId và selectedCapacity từ dữ liệu gửi đi
    $productId = $_POST['productId'];
    $selectedCapacity = $_POST['selectedCapacity'];

    // Khai báo biến giá để lưu giá sản phẩm
    $price = 0;

    // Xác định cột giá tương ứng với dung tích đã chọn
    switch($selectedCapacity) {
        case '10ml':
            $priceColumn = 'pricefor10ml';
            break;
        case '50ml':
            $priceColumn = 'pricefor50ml';
            break;
        case '75ml':
            $priceColumn = 'pricefor75ml';
            break;
        case '100ml':
            $priceColumn = 'pricefor100ml';
            break;
        default:
            // Nếu không có dung tích được chọn, không thực hiện truy vấn và gán giá mặc định là 0
            break;
    }

    // Nếu đã xác định được cột giá tương ứng
    if(isset($priceColumn)) {
        // Truy vấn cơ sở dữ liệu để lấy giá sản phẩm cho dung tích đã chọn
        $query = "SELECT `$priceColumn` AS price FROM products WHERE product_id = '$productId'";

        $result = mysqli_query($conn, $query);

        // Kiểm tra xem truy vấn có thành công không
        if($result) {
            // Lấy giá sản phẩm từ kết quả truy vấn
            $row = mysqli_fetch_assoc($result);
            $price = $row['price'];
        } else {
            // Nếu có lỗi trong quá trình truy vấn, gán giá mặc định là 0
            $price = 0;
        }
    }

    // Hiển thị giá sản phẩm với định dạng mong muốn
    echo number_format($price, 0, '.', '.') . 'VNĐ';
} else {
    // Nếu không có dữ liệu gửi đi từ phía client, hiển thị thông báo lỗi
    echo 'Dữ liệu không hợp lệ.';
}



?>
