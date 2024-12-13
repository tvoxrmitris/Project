<?php
include '../connection/connection.php';

if (isset($_POST['product_name']) && isset($_POST['color_name']) && isset($_POST['capacity'])) {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $color_name = mysqli_real_escape_string($conn, $_POST['color_name']);
    $capacity = mysqli_real_escape_string($conn, $_POST['capacity']);

    // Truy vấn lấy inventory_id từ bảng inventory_entries dựa trên product_name, color_name và capacity
    $query = "SELECT inventory_id FROM inventory_entries 
              WHERE product_name = '$product_name' 
              AND code_color = '$color_name' 
              AND capacity = '$capacity' 
              LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo $row['inventory_id']; // Trả về inventory_id
    } else {
        echo ''; // Trả về rỗng nếu không tìm thấy
    }
}
