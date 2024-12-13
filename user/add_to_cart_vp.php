<?php
// Kết nối đến cơ sở dữ liệu
include '../connection/connection.php';

// Bắt đầu session (để lấy user_id nếu cần)
session_start();


// Nhận dữ liệu từ Ajax
$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'];
$quantity = $data['quantity'];

// Lấy user_id từ session (giả sử user đã đăng nhập)
$user_id = $_SESSION['user_id'];

// Kiểm tra product_id trong bảng products
$sql = "SELECT product_name, product_image FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Lấy thông tin sản phẩm
    $row = $result->fetch_assoc();
    $product_name = $row['product_name'];
    $product_image = $row['product_image'];

    // Kiểm tra sản phẩm đã tồn tại trong giỏ hàng hay chưa
    $check_cart_sql = "SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?";
    $check_cart_stmt = $conn->prepare($check_cart_sql);
    $check_cart_stmt->bind_param("ii", $user_id, $product_id);
    $check_cart_stmt->execute();
    $cart_result = $check_cart_stmt->get_result();

    if ($cart_result->num_rows > 0) {
        // Sản phẩm đã có trong giỏ hàng, cập nhật quantity
        $cart_row = $cart_result->fetch_assoc();
        $new_quantity = $cart_row['quantity'] + $quantity;

        $update_cart_sql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
        $update_cart_stmt = $conn->prepare($update_cart_sql);
        $update_cart_stmt->bind_param("iii", $new_quantity, $user_id, $product_id);
        $update_cart_stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Updated quantity in cart']);
    } else {
        // Sản phẩm chưa có trong giỏ hàng, thêm mới vào giỏ hàng
        $insert_sql = "INSERT INTO cart (user_id, product_id, product_name, quantity, product_image)
                       VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iisds", $user_id, $product_id, $product_name, $quantity, $product_image);
        $insert_stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Added new product to cart']);
    }

    $check_cart_stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
}

$stmt->close();
$conn->close();
