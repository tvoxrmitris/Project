<?php
include '../connection/connection.php';

if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);

    // Truy vấn lấy thông tin sản phẩm cùng với danh mục chính
    $query_product = "
        SELECT 
            p.*, 
            c.category_name, 
            c.category_id,
            p.product_subcategory,
            p.brand_name
        FROM products p
        LEFT JOIN categories c ON p.category_name = c.category_id
        WHERE p.product_id = $product_id";

    $result_product = mysqli_query($conn, $query_product);

    if ($result_product && mysqli_num_rows($result_product) > 0) {
        $product = mysqli_fetch_assoc($result_product);

        // Lấy danh sách các tag liên quan đến sản phẩm
        $query_tags = "SELECT tag_id, tag_name 
                       FROM product_tags 
                       WHERE product_id = $product_id";
        $result_tags = mysqli_query($conn, $query_tags);
        $tags = array();

        while ($row = mysqli_fetch_assoc($result_tags)) {
            $tags[] = $row;
        }

        // Lấy tất cả danh mục phụ
        $query_subcategories = "SELECT * FROM subcategory";
        $result_subcategories = mysqli_query($conn, $query_subcategories);
        $subcategories = array();

        while ($row = mysqli_fetch_assoc($result_subcategories)) {
            $subcategories[] = $row;
        }

        // Kết hợp thông tin sản phẩm, tags, danh mục phụ
        $product['tags'] = $tags;
        $product['subcategories'] = $subcategories;

        // Trả về dữ liệu sản phẩm, danh mục chính và phụ dưới dạng JSON
        echo json_encode($product);
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode(["error" => "product_id is required"]);
}
