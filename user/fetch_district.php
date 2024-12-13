<?php
include '../connection/connection.php';

if (isset($_POST['province_id'])) {
    $province_id = $_POST['province_id'];
    
    $query = "SELECT * FROM district WHERE province_id = '$province_id'";
    $result = mysqli_query($conn, $query);
    
    echo '<option value="">Chọn một quận/huyện</option>';
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<option value="' . $row['district_id'] . '">' . $row['name'] . '</option>';
    }
}
?>
