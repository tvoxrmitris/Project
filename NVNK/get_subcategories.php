<?php
include '../connection/connection.php';

if (isset($_POST['category_id'])) {
    $category_id = $_POST['category_id'];

    // Lấy danh sách subcategory dựa trên category_id
    $query = "SELECT subcategory_id, subcategory_name FROM subcategory WHERE category_id = '$category_id'";
    $result = mysqli_query($conn, $query);

    $subcategories = array();
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $subcategories[] = $row;
        }
    }

    // Trả về kết quả dưới dạng JSON
    echo json_encode($subcategories);
}
