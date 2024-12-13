<?php
include '../connection/connection.php';

if (isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $new_status = 'Đã giao';

    $update_order_status = mysqli_query($conn, "UPDATE `orders` SET `status_order`='$new_status' WHERE `order_id`='$order_id'");

    if ($update_order_status) {
        echo 'success';
    } else {
        echo 'error';
    }
}
