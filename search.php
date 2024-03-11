<?php
include 'connection.php';

if(isset($_GET['search'])) {
    $search = $_GET['search'];
    $search_query = mysqli_query($conn, "SELECT * FROM `products` WHERE `name` LIKE '%$search%' OR `price` LIKE '%$search%' OR `product_detail` LIKE '%$search%'") or die('query failed');
    
    if(mysqli_num_rows($search_query) > 0) {
        while($fetch_products = mysqli_fetch_assoc($search_query)) {
            // Hiển thị kết quả tìm kiếm
            echo '<div class="search-item">' . $fetch_products['name'] . '</div>';
        }
    } else {
        echo '<p class="empty">Không tìm thấy sản phẩm phù hợp.</p>';
    }
}
?>
