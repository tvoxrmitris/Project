<?php
include '../connection/connection.php';

if (isset($_POST['district_id'])) {
    $district_id = $_POST['district_id'];
    
    $query = "SELECT * FROM wards WHERE district_id = '$district_id'";
    $result = mysqli_query($conn, $query);
    
    echo '<option value="">Chọn một xã</option>';
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<option value="' . $row['wards_id'] . '">' . $row['name'] . '</option>';
    }
}
?>
