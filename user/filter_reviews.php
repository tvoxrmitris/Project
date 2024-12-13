<?php
include '../connection/connection.php';

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$rating_filter = isset($_GET['rating']) ? intval($_GET['rating']) : 0;

if ($product_id > 0) {
    // Xây dựng truy vấn để lấy đánh giá theo bộ lọc
    $query = "SELECT * FROM `evaluate` WHERE product_id = '$product_id'";
    if ($rating_filter > 0) {
        $query .= " AND star = '$rating_filter'";
    }
    $select_evaluates = mysqli_query($conn, $query) or die('Query failed: ' . mysqli_error($conn));

    if (mysqli_num_rows($select_evaluates) > 0) {
        while ($fetch_evaluate = mysqli_fetch_assoc($select_evaluates)) {
            ?>
            <div class="box" data-star="<?php echo $fetch_evaluate['star']; ?>">
                <div class="user"><?php echo $fetch_evaluate['user_name']; ?></div>
                <div class="star">
                    <?php
                    $yellow_stars = $fetch_evaluate['star'];
                    $white_stars = 5 - $yellow_stars;

                    for ($i = 0; $i < $yellow_stars; $i++) {
                        echo '<i class="bi bi-star-fill"></i>';
                    }
                    for ($i = 0; $i < $white_stars; $i++) {
                        echo '<i class="bi bi-star"></i>';
                    }
                    ?>
                </div>
                <div class="comment"><?php echo $fetch_evaluate['evaluate_detail']; ?></div>
                <div class="date"><?php echo date("m/d/y", strtotime($fetch_evaluate['date'])); ?></div>
            </div>
            <?php
        }
    } else {
        echo '<p class="empty">No reviews found.</p>';
    }
}
?>
