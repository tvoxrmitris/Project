<?php
// Kết nối cơ sở dữ liệu và kiểm tra session wishlist
if (isset($_SESSION['wishlist']) && !empty($_SESSION['wishlist'])) {
    $product_ids = array_column($_SESSION['wishlist'], 'product_id');
    $product_ids_str = implode(',', $product_ids);

    // Truy vấn thông tin sản phẩm từ cơ sở dữ liệu
    $query = "SELECT product_id, product_name, product_price, product_image, color_name 
              FROM products 
              WHERE product_id IN ($product_ids_str)";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $images = explode(',', $row['product_image']);
            $first_image = $images[0];

            // Trả về HTML cho wishlist-container
            echo '<div class="product-item" data-id="' . $row['product_id'] . '">';
            echo '<img src="../image/product/' . $first_image . '" alt="' . $row['product_name'] . '">';
            echo '<div class="product-info">';
            echo '<h3>' . $row['product_name'] . ' - ' . $row['color_name'] . '</h3>';
            echo '<p>' . number_format($row['product_price'], 0, '.', '.') . ' VNĐ</p>';
            echo '<div class="product-actions">';
            echo '<a href="view_page_guest.php?product_id=' . $row['product_id'] . '" class="view-product-link">Xem sản phẩm</a>';
            echo '<i class="bi bi-heart" id="heart-btn"></i>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p>Không có sản phẩm nào trong wishlist.</p>';
    }
} else {
    echo '<p>Wishlist trống.</p>';
}
