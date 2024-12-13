<?php
include '../connection/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Xử lý khi nhấn nút "Ok" để cập nhật số lượng qtyfor10ml
    if (isset($_POST['update_qty_10ml'])) {
        $update_qty_id_10ml = $_POST['update_qty_id_10ml'];
        $new_qty_10ml = $_POST['update_qty_10ml'];

        // Lấy giá hiện tại của chai 10ml và số lượng hiện có
        $fetch_price_query = mysqli_query($conn, "SELECT pricefor10ml, qtyfor10ml FROM cart WHERE pid='$update_qty_id_10ml'");
        $fetch_price_result = mysqli_fetch_assoc($fetch_price_query);
        $current_price_10ml = $fetch_price_result['pricefor10ml'];
        $current_qty_10ml = $fetch_price_result['qtyfor10ml'];

        // Tính toán giá mới dựa trên số lượng mới
        $new_price_10ml = ($current_qty_10ml - $new_qty_10ml) * $current_price_10ml;

        // Cập nhật số lượng và giá trong cơ sở dữ liệu
        $sql = "UPDATE cart SET qtyfor10ml = '$new_qty_10ml', pricefor10ml = '$new_price_10ml' WHERE pid = '$update_qty_id_10ml'";
        if ($conn->query($sql) === TRUE) {
            echo "Cập nhật số lượng qtyfor10ml và giá thành công.";
        } else {
            echo "Lỗi khi cập nhật số lượng qtyfor10ml và giá: " . $conn->error;
        }
    }

    // Xử lý khi nhấn nút "Ok" để cập nhật số lượng qtyfor50ml
    if (isset($_POST['update_qty_50ml'])) {
        $update_qty_id_50ml = $_POST['update_qty_id_50ml'];
        $new_qty_50ml = $_POST['update_qty_50ml'];

        // Lấy giá hiện tại của chai 50ml và số lượng hiện có
        $fetch_price_query = mysqli_query($conn, "SELECT pricefor50ml, qtyfor50ml FROM cart WHERE pid='$update_qty_id_50ml'");
        $fetch_price_result = mysqli_fetch_assoc($fetch_price_query);
        $current_price_50ml = $fetch_price_result['pricefor50ml'];
        $current_qty_50ml = $fetch_price_result['qtyfor50ml'];

        // Tính toán giá mới dựa trên số lượng mới
        $new_price_50ml = ($current_qty_50ml - $new_qty_50ml) * $current_price_50ml;

        // Cập nhật số lượng và giá trong cơ sở dữ liệu
        $sql = "UPDATE cart SET qtyfor50ml = '$new_qty_50ml', pricefor50ml = '$new_price_50ml' WHERE pid = '$update_qty_id_50ml'";
        if ($conn->query($sql) === TRUE) {
            echo "Cập nhật số lượng qtyfor50ml và giá thành công.";
        } else {
            echo "Lỗi khi cập nhật số lượng qtyfor50ml và giá: " . $conn->error;
        }
    }

    // Xử lý khi nhấn nút "Ok" để cập nhật số lượng qtyfor75ml
    if (isset($_POST['update_qty_75ml'])) {
        $update_qty_id_75ml = $_POST['update_qty_id_75ml'];
        $new_qty_75ml = $_POST['update_qty_75ml'];

        // Lấy giá hiện tại của chai 75ml và số lượng hiện có
        $fetch_price_query = mysqli_query($conn, "SELECT pricefor75ml, qtyfor75ml FROM cart WHERE pid='$update_qty_id_75ml'");
        $fetch_price_result = mysqli_fetch_assoc($fetch_price_query);
        $current_price_75ml = $fetch_price_result['pricefor75ml'];
        $current_qty_75ml = $fetch_price_result['qtyfor75ml'];

        // Tính toán giá mới dựa trên số lượng mới
        $new_price_75ml = ($current_qty_75ml - $new_qty_75ml) * $current_price_75ml;

        // Cập nhật số lượng và giá trong cơ sở dữ liệu
        $sql = "UPDATE cart SET qtyfor75ml = '$new_qty_75ml', pricefor75ml = '$new_price_75ml' WHERE pid = '$update_qty_id_75ml'";
        if ($conn->query($sql) === TRUE) {
            echo "Cập nhật số lượng qtyfor75ml và giá thành công.";
        } else {
            echo "Lỗi khi cập nhật số lượng qtyfor75ml và giá: " . $conn->error;
        }
    }

    // Xử lý khi nhấn nút "Ok" để cập nhật số lượng qtyfor100ml
    if (isset($_POST['update_qty_100ml'])) {
        $update_qty_id_100ml = $_POST['update_qty_id_100ml'];
        $new_qty_100ml = $_POST['update_qty_100ml'];

        // Lấy giá hiện tại của chai 100ml và số lượng hiện có
        $fetch_price_query = mysqli_query($conn, "SELECT pricefor100ml, qtyfor100ml FROM cart WHERE pid='$update_qty_id_100ml'");
        $fetch_price_result = mysqli_fetch_assoc($fetch_price_query);
        $current_price_100ml = $fetch_price_result['pricefor100ml'];
        $current_qty_100ml = $fetch_price_result['qtyfor100ml'];

        // Tính toán giá mới dựa trên số lượng mới
        $new_price_100ml = ($current_qty_100ml - $new_qty_100ml) * $current_price_100ml;

        // Cập nhật số lượng và giá trong cơ sở dữ liệu
        $sql = "UPDATE cart SET qtyfor100ml = '$new_qty_100ml', pricefor100ml = '$new_price_100ml' WHERE pid = '$update_qty_id_100ml'";
        if ($conn->query($sql) === TRUE) {
            echo "Cập nhật số lượng qtyfor100ml và giá thành công.";
        } else {
            echo "Lỗi khi cập nhật số lượng qtyfor100ml và giá: " . $conn->error;
        }
    }
}

// Đóng kết nối đến cơ sở dữ liệu
$conn->close();
?>
