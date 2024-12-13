

<?php
include '../connection/connection.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $order_id = $_POST['order_id'];
    $quantity = $_POST['quantity'];

    // Cập nhật số lượng sản phẩm trong đơn hàng
    $stmt = $conn->prepare("UPDATE order_items SET quantity = ? WHERE product_id = ? AND order_id = ?");
    $stmt->bind_param('iii', $quantity, $product_id, $order_id);

    if ($stmt->execute()) {
        echo "Cập nhật thành công";
    } else {
        echo "Lỗi cập nhật: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

