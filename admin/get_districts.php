<?php
include '../connection/connection.php';

if (isset($_POST['province_id'])) {
    $province_id = $_POST['province_id'];

    // Debug print (chỉ để kiểm tra)
    // Sau khi kiểm tra xong, hãy xoá hoặc comment lại dòng dưới đây.
    // print("Province ID nhận được: $province_id");
    // exit; // Sau khi kiểm tra xong, xoá dòng này để không ảnh hưởng tới JSON output.

    $sql = "SELECT * FROM district WHERE province_id = '$province_id'";
    $result = mysqli_query($conn, $sql);

    $districts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $districts[] = $row;
    }

    echo json_encode($districts); // Trả về JSON hợp lệ
}
