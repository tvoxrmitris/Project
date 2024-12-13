<?php
// Kết nối cơ sở dữ liệu và bắt đầu phiên
include '../connection/connection.php';
session_start();
if (!isset($_SESSION['employee_email']) || $_SESSION['employee_type'] !== 'super admin') {
    header('location:../components/admin_login.php');
    exit;
}



// Xử lý đăng xuất
if (isset($_POST['logout'])) {
    session_destroy();
    header('location:../components/admin_login.php');
    exit;
}





?>

<style type="text/css">
<?php include '../CSS/style.css';

?>.table-container {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

img {
    width: 100px;
    /* Kích thước hình ảnh */
    height: auto;
}

.star-filled {
    color: gold;
    /* Màu sao đầy */
}

.star-empty {
    color: lightgray;
    /* Màu sao rỗng */
}
</style>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" type="text/css" href="../CSS/style.css?v=1.1 <?php echo time(); ?>">
    <title>Admin Product</title>
</head>

<body>
    <?php include '../admin/admin_header.php'; ?>
    <?php
    if (isset($message)) {
        foreach ($message as $message) {
            echo '
                    <div class="message">
                        <span>' . $message . '</span>
                        <i class="bi bi-x-circle" onclick="this.parentElement.remove()"></i>
                    </div>
                ';
        }
    }
    ?>
    <div class="title">
        <h2 style="font-size:50px;">Chi tiết sản phẩm</h2>
    </div>


    <section class="shop">

        <div class="box-container">
            <?php

            $subcategory_name = isset($_GET['subcategory_name']) ? mysqli_real_escape_string($conn, urldecode($_GET['subcategory_name'])) : '';

            $select_products = mysqli_query($conn, "SELECT * FROM products WHERE status = 'Còn hàng' AND product_subcategory = '$subcategory_name' ") or die('Truy vấn thất bại: ' . mysqli_error($conn));

            if (mysqli_num_rows($select_products) > 0) {
                while ($fetch_products = mysqli_fetch_assoc($select_products)) {

                    $image_names = explode(',', $fetch_products['product_image']);
                    $product_id = $fetch_products['product_id'];


                    $select_star = mysqli_query($conn, "SELECT AVG(star) AS avg_star FROM evaluate WHERE product_id = '$product_id'") or die('Truy vấn đánh giá thất bại: ' . mysqli_error($conn));
                    $fetch_star = mysqli_fetch_assoc($select_star);
                    $average_star = round($fetch_star['avg_star'], 1);
            ?>

            <form method="post" class="box">
                <div class="img-container">
                    <?php

                            for ($i = 0; $i < min(2, count($image_names)); $i++) {
                                $class = $i === 0 ? 'main-img' : 'second-img';

                                $image_url = '../image/' . urlencode(trim($image_names[$i]));
                            ?>
                    <a href="view_page_guest.php?product_id=<?php echo $product_id; ?>">
                        <img class="<?php echo $class; ?>" src="<?php echo $image_url; ?>"
                            data-index="<?php echo $i; ?>">
                    </a>
                    <?php } ?>

                </div>

                <div class="priceshop">
                    <?php

                            echo number_format($fetch_products['product_price'], 0, '.', '.') . ' VNĐ';
                            ?>
                </div>
                <div class="detail">
                    <div class="name"><?php echo $fetch_products['product_name']; ?></div>
                    <div class="star">
                        <?php

                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $average_star) {
                                        echo '<span class="star-filled">★</span>';
                                    } else {
                                        echo '<span class="star-empty">☆</span>';
                                    }
                                }
                                $product_id = $fetch_products['product_id'];


                                $query = "SELECT COUNT(*) as total_reviews FROM evaluate WHERE product_id = ?";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("i", $product_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $row = $result->fetch_assoc();
                                $total_reviews = $row['total_reviews'];
                                ?>
                        <div class="reviews"><?php echo $total_reviews; ?> lượt đánh giá</div>
                    </div>
                </div>

                <input type="hidden" name="product_id" value="<?php echo $fetch_products['product_id']; ?>">
                <input type="hidden" name="product_name" value="<?php echo $fetch_products['product_name']; ?>">
                <input type="hidden" name="product_image" value="<?php echo $fetch_products['product_image']; ?>">

            </form>
            <?php
                }
            } else {
                echo '<p class="empty">Chưa có sản phẩm nào được thêm</p>';
            }
            ?>
        </div>


    </section>

    <section class="view_page">
        <?php
        if (isset($message)) {
            foreach ($message as $msg) {
                echo '
                <div class="message">
                    <span>' . $msg . '</span>
                    <i class="bi bi-x-circle" onclick="this.parentElement.remove()"></i>
                </div>
            ';
            }
        }
        ?>
        <div class="box-container">
            <?php
            // Lấy product_id từ URL
            $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

            // Truy vấn để lấy thông tin sản phẩm
            $product_query = "SELECT * FROM products WHERE product_id = ?";
            $stmt = $conn->prepare($product_query);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            if ($product) {
                $image_names = explode(',', $product['product_image']);
            ?>
            <form method="post" class="box">
                <div class="img-container">
                    <div class="small-imgs" id="small-imgs-container">
                        <?php
                            $first_image_url = '../image/' . urlencode(trim($image_names[0]));
                            $select_star = mysqli_query($conn, "SELECT AVG(star) AS avg_star FROM evaluate WHERE product_id = '$product_id'") or die('Truy vấn đánh giá thất bại: ' . mysqli_error($conn));
                            $fetch_star = mysqli_fetch_assoc($select_star);
                            $average_star = round($fetch_star['avg_star'], 1);
                            ?>
                        <img class="small-img" src="<?php echo $first_image_url; ?>" data-index="0" alt="Product Image"
                            onclick="updateMainImage(this)">
                        <?php
                            for ($index = 1; $index < count($image_names); $index++) {
                                $image_url = '../image/' . urlencode(trim($image_names[$index]));
                            ?>
                        <img class="small-img" src="<?php echo $image_url; ?>" data-index="<?php echo $index; ?>"
                            alt="Product Image" onclick="updateMainImage(this)">
                        <?php } ?>
                    </div>
                    <img class="main-img" src="<?php echo $first_image_url; ?>" alt="Main Product Image"
                        id="main-image">
                </div>

                <script>
                function updateMainImage(imgElement) {
                    const newImageSrc = imgElement.src;
                    const mainImage = document.getElementById('main-image');
                    mainImage.src = newImageSrc;

                    const smallImages = document.querySelectorAll('.small-img');
                    smallImages.forEach(smallImg => {
                        smallImg.classList.remove('active');
                    });

                    imgElement.classList.add('active');
                }
                </script>
                <div class="product-details">
                    <div class="name"><?php echo $product['product_name']; ?></div>
                    <div class="price_viewpage">
                        <?php echo number_format($product['product_price'], 0, '.', '.') . ' VNĐ'; ?>
                    </div>
                    <div class="detail_viewpage"><?php echo $product['product_detail']; ?></div>
                    <div class="star">
                        <?php
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $average_star) {
                                    echo '<span class="star-filled">★</span>';
                                } else {
                                    echo '<span class="star-empty">☆</span>';
                                }
                            }
                            $query = "SELECT COUNT(*) as total_reviews FROM evaluate WHERE product_id = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("i", $product_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $row = $result->fetch_assoc();
                            $total_reviews = $row['total_reviews'];
                            ?>
                        <div class="reviews"><?php echo $total_reviews; ?> lượt đánh giá</div>
                    </div>

                    <div class="color-swatches">
                        <?php
                            $color_query = "SELECT color_name, color_image, product_id FROM products WHERE product_name = ?";
                            $stmt = $conn->prepare($color_query);
                            $stmt->bind_param("s", $product['product_name']);
                            $stmt->execute();
                            $color_result = $stmt->get_result();

                            if ($color_result->num_rows > 0) {
                                while ($color = $color_result->fetch_assoc()) {
                                    $color_name = $color['color_name'];
                                    $color_image = !empty($color['color_image']) ? '../image/colorimage/' . rawurlencode(trim($color['color_image'])) : null;
                                    $color_product_id = $color['product_id'];

                                    if (!is_null($color_name) && $color_name !== '') {
                                        $border_style = ($color_product_id == $product_id) ? 'border: 1px solid #000;' : '';
                            ?>
                        <div class="swatch"
                            style="background-color: <?php echo htmlspecialchars($color_name); ?>; width: 45px; height: 45px; display: inline-block; position: relative; border-radius: 0;"
                            title="<?php echo htmlspecialchars($color_name); ?>">
                            <div class="image-container"
                                style="width: 100%; height: 100%; overflow: hidden; border-radius: 0;">
                                <?php if ($color_image): ?>
                                <img src="<?php echo $color_image; ?>"
                                    alt="<?php echo htmlspecialchars($color_name); ?>" class="color-image"
                                    style="width: 100%; height: 100%; object-fit: cover; <?php echo $border_style; ?>"
                                    onclick="getProductInfo('<?php echo addslashes($product['product_name']); ?>', '<?php echo addslashes($color_name); ?>', <?php echo $color_product_id; ?>)">
                                <?php else: ?>
                                <span
                                    style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; font-size: 12px; color: #000; background-color: #fff; <?php echo $border_style; ?>"
                                    onclick="getProductInfo('<?php echo addslashes($product['product_name']); ?>', '<?php echo addslashes($color_name); ?>', <?php echo $color_product_id; ?>)">
                                    <?php echo htmlspecialchars($color_name); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                                    }
                                }
                            } else {
                                echo '<p class="empty">No colors available.</p>';
                            }
                            ?>
                    </div>

                    <script>
                    function getProductInfo(productName, colorName, productId) {
                        var xhr = new XMLHttpRequest();
                        xhr.open("POST", "get_color_info.php", true);
                        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                        xhr.onreadystatechange = function() {
                            if (xhr.readyState === 4 && xhr.status === 200) {
                                if (xhr.responseText === "found") {
                                    window.location.href = "view_page_guest.php?product_id=" + productId;
                                } else {
                                    alert("Product not found.");
                                }
                            }
                        };

                        xhr.send("product_name=" + encodeURIComponent(productName) + "&color_name=" +
                            encodeURIComponent(colorName));
                    }
                    </script>

                    <div class="product-hero__add-to-cart">
                        <div class="quantity-selector">
                            <button type="button"
                                class="quantity-selector__action quantity-selector__action--decrement">−</button>
                            <p class="quantity-selector__field p1 bold">1</p>
                            <button type="button"
                                class="quantity-selector__action quantity-selector__action--increment">+</button>
                        </div>

                        <input type="hidden" name="quantity" id="quantity-field" value="1">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <button type="submit" name="add_to_cart"
                            class="btn btn--full uppercase product-hero__add-cta btn--primary">Add to Cart</button>
                    </div>
                </div>
            </form>
            <?php
            } else {
                echo '<div class="message">Sản phẩm không tồn tại!</div>';
            }
            ?>
        </div>
    </section>



</html>