<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['product_id']) && isset($data['action'])) {
        $product_id = $data['product_id'];
        $action = $data['action'];

        // Nếu session wishlist chưa tồn tại, khởi tạo mảng trống
        if (!isset($_SESSION['wishlist'])) {
            $_SESSION['wishlist'] = [];
        }

        // Xử lý hành động thêm hoặc xóa sản phẩm khỏi wishlist
        if ($action === 'add') {
            // Kiểm tra nếu sản phẩm đã tồn tại trong wishlist bằng array_search
            $product_ids = array_column($_SESSION['wishlist'], 'product_id');
            $is_in_wishlist = array_search($product_id, $product_ids);

            // Nếu chưa có trong wishlist, thêm sản phẩm
            if ($is_in_wishlist === false) {
                $product_name = isset($data['product_name']) ? $data['product_name'] : '';
                $product_image = isset($data['product_image']) ? $data['product_image'] : '';
                $color_name = isset($data['color_name']) ? $data['color_name'] : '';
                $product_price = isset($data['product_price']) ? $data['product_price'] : 0;
                $capacity = isset($data['capacity']) ? $data['capacity'] : ''; // Thêm capacity

                $_SESSION['wishlist'][] = [
                    'product_id' => $product_id,
                    'product_name' => $product_name,
                    'product_image' => $product_image,
                    'color_name' => $color_name,
                    'product_price' => $product_price,
                    'capacity' => $capacity, // Thêm capacity vào session
                ];

                echo json_encode(['success' => true, 'message' => 'Sản phẩm đã được thêm vào wishlist.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Sản phẩm đã có trong wishlist.']);
            }
        } elseif ($action === 'remove') {
            // Tìm sản phẩm theo product_id bằng array_search
            $product_ids = array_column($_SESSION['wishlist'], 'product_id');
            $index = array_search($product_id, $product_ids);

            if ($index !== false) {
                // Xóa sản phẩm khỏi wishlist nếu tồn tại
                unset($_SESSION['wishlist'][$index]);
                // Sắp xếp lại chỉ số của mảng sau khi xóa
                $_SESSION['wishlist'] = array_values($_SESSION['wishlist']);
                echo json_encode(['success' => true, 'message' => 'Sản phẩm đã bị xóa khỏi wishlist.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong wishlist.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Phương thức yêu cầu không hợp lệ.']);
}