<?php
include '../connection/connection.php';

// Kiểm tra xem yêu cầu POST có chứa dữ liệu 'product_brand_name' không
if(isset($_POST['product_brand_name'])) {
    $product_brand_name = $_POST['product_brand_name'];
    // Truy vấn để lấy brand_id từ bảng brands dựa trên product_brand_name
    $select_brand_id_query = mysqli_query($conn, "SELECT brand_id FROM brands WHERE brand_name='$product_brand_name' LIMIT 1");
    if($select_brand_id_query) {
        $brand_id_row = mysqli_fetch_assoc($select_brand_id_query);
        $brand_id = $brand_id_row['brand_id'];
        echo $brand_id; // Trả về brand_id
    } else {
        echo "0"; // Trả về 0 nếu không tìm thấy brand_id
    }
} else {
    echo "0"; // Trả về 0 nếu không có dữ liệu 'product_brand_name'
}
?>
