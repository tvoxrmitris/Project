<?php
include '../connection/connection.php';
session_start();

// Kiểm tra xem người dùng có đăng nhập hay không
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Nếu chưa đăng nhập, sử dụng session_id() làm định danh tạm thời
    $user_id = session_id();
}

// Xử lý thêm sản phẩm vào wishlist
if (isset($_POST['add_to_wishlist'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];

    // Kiểm tra xem sản phẩm đã tồn tại trong wishlist chưa
    $wishlist_number = mysqli_query($conn, "SELECT * FROM `wishlist` WHERE product_name='$product_name' AND user_id ='$user_id'") or die('query failed');

    $cart_num = mysqli_query($conn, "SELECT * FROM `cart` WHERE product_name='$product_name' AND user_id ='$user_id'") or die('query failed');

    if (mysqli_num_rows($wishlist_number) > 0) {
        $message[] = 'Sản phẩm đã tồn tại trong danh sách yêu thích';
    } else if (mysqli_num_rows($cart_num) > 0) {
        $message[] = 'Sản phẩm đã tồn tại trong giỏ hàng';
    } else {
        mysqli_query($conn, "INSERT INTO `wishlist`(`user_id`, `pid`, `product_name`, `product_price`, `product_image`) VALUES('$user_id', '$product_id', '$product_name', '$product_price', '$product_image')");
        $message[] = 'Sản phẩm đã được thêm vào danh sách yêu thích';
    }
}

// Xử lý thêm sản phẩm vào giỏ hàng
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];
    $product_quantity = $_POST['product_quantity'];

    // Kiểm tra số lượng sản phẩm trong kho
    $stock_query = mysqli_query($conn, "SELECT product_quantity FROM products WHERE product_id='$product_id'");
    $stock_row = mysqli_fetch_assoc($stock_query);
    $stock_available = $stock_row['product_quantity'];

    // Kiểm tra số lượng sản phẩm trong giỏ hàng
    $cart_num = mysqli_query($conn, "SELECT * FROM `cart` WHERE product_name='$product_name' AND user_id ='$user_id'") or die('query failed');

    if ($product_quantity > $stock_available) {
        $message[] = 'Không thể thêm sản phẩm vào giỏ hàng. Số lượng sản phẩm trong kho không đủ';
    } elseif (mysqli_num_rows($cart_num) > 0) {
        $message[] = 'Sản phẩm đã tồn tại trong giỏ hàng';
    } else {
        mysqli_query($conn, "INSERT INTO `cart`(`user_id`, `pid`, `product_name`, `product_price`, `quantity`, `product_image`) VALUES('$user_id', '$product_id', '$product_name', '$product_price', '$product_quantity', '$product_image')");
        $message[] = 'Sản phẩm đã được thêm vào giỏ hàng';
    }
}
?>


<style type="text/css">
<?php include '../CSS/main.css'
?>
</style>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css"
        integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css"
        integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js"
        integrity="sha512-HGOnQO9+SP1V92SrtZfjqxxtLmVzqZpjFFekvzZVWoiASSQgSr4cw9Kqd2+l8Llp4Gm0G8GIFJ4ddwZilcdb8A=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.js"
        integrity="sha512-eP8DK17a+MOcKHXC5Yrqzd8WI5WKh6F1TIk5QZ/8Lbv+8ssblcz7oGC8ZmQ/ZSAPa7ZmsCU4e/hcovqR8jfJqA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" type="text/css" href="../CSS/main.css?v=1.1 <?php echo time(); ?>">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />


    <!-- <link rel="shortcut icon" href="../image/logo.png" type="image/vnd.microsoft.icon"> -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Seraph Beauty - Trang Chủ</title>
</head>

<body>


    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>



    <?php include '../guest/header_guest.php' ?>





    <div class="banner-slider">
        <div class="slider-container">

            <div class="img-banner">
                <img src="../image/banner/bannerslider.jpg" alt="">
                <span>Seraph Beauty x Arcane</span>
                <a href="guest_collection.php?collection=Arcane%20Collection" style="text-decoration: none;">
                    <span style="background-color: white; color: black; padding: 5px; margin-top: 4rem;">Khám phá
                        ngay</span>
                </a>

            </div>
            <div class="img-banner">
                <img src="../image/banner/fentybannerrihanna.jpg" alt="">
                <div class="overlay-box">
                    <p style="font-size: 22px; font-family: 'Futura', sans-serif; margin-bottom: 3rem;">Lip Legends
                    </p>

                    <p style="font-size: 29px; text-transform: uppercase; margin-bottom: 1rem; width: 80%;">Son Bóng
                        Gloss Bomb +
                        Bút Kẻ Môi Trace'd Out
                    </p>

                    <p style="font-size: 20px; width: 90%; margin-bottom: 3rem; vertical-align: baseline;;">Nâng tầm đôi
                        môi của bạn lên một cấp
                        độ hoàn toàn mới về độ
                        bóng, sắc
                        thái + độ
                        chính xác.
                    </p>
                    <a href="lipandborder.php">
                        <p style="background-color: white; color: black; padding: 10px;">Khám phá ngay</p>
                    </a>



                </div> <!-- Hộp mới thêm vào -->
            </div>

        </div>
    </div>

    <div class="next-banner">
        <button class="slider-button prev" onclick="prevSlide()">❮</button>
        <div class="banner-details">
            <p>BST KEM NỀN</p>
            <p>Seraph Beauty x Arcane</p>
            <p>Lip Legends</p>
        </div>
        <button class="slider-button next" onclick="nextSlide()">❯</button>
    </div>


    <script>
    let currentIndex = 0;

    function showSlide(index) {
        const sliderContainer = document.querySelector('.slider-container');
        const totalSlides = document.querySelectorAll('.slider-container > *').length;

        // Cập nhật currentIndex dựa vào index đầu vào
        if (index >= totalSlides) {
            currentIndex = 0;
        } else if (index < 0) {
            currentIndex = totalSlides - 1;
        } else {
            currentIndex = index;
        }

        // Di chuyển slider
        const offset = -currentIndex * 100;
        sliderContainer.style.transform = `translateX(${offset}vw)`;

        // Thêm/lấy đi lớp active-slide cho thẻ p tương ứng
        const bannerDetails = document.querySelectorAll('.banner-details p');
        bannerDetails.forEach((p, i) => {
            if (i === currentIndex) {
                p.classList.add('active-slide');
            } else {
                p.classList.remove('active-slide');
            }
        });
    }

    function nextSlide() {
        showSlide(currentIndex + 1);
    }

    function prevSlide() {
        showSlide(currentIndex - 1);
    }

    // Gọi hàm showSlide(0) khi trang tải lần đầu để hiển thị slide đầu tiên và underline thẻ p đầu tiên
    showSlide(0);

    // Thêm sự kiện onclick cho mỗi thẻ p trong banner-details
    document.querySelectorAll('.banner-details p').forEach((p, index) => {
        p.onclick = () => showSlide(index);
    });
    </script>


    <style>
    .banner-gloss {
        position: relative;
    }

    .banner-gloss img {
        width: 100%;
        margin-bottom: 15px;
    }

    .gloss-box {
        position: absolute;
        top: 50%;
        left: 16%;
        width: 28%;
        height: 320px;
        transform: translate(-50%, -50%);
        background-color: #000;
        color: white;
        padding: 20px;
        text-align: left;
        margin-bottom: 5rem;
    }

    .gloss-box img {
        width: 99%;
        height: 90px;
        object-fit: cover;
        margin-bottom: 0;
        overflow: hidden;
    }

    .gloss-box h3 {
        font-size: 32px;
        text-align: left;
        line-height: 40px;
        text-transform: uppercase;
    }

    .gloss-box p {
        line-height: 1;
        margin-top: 0.5rem;
        letter-spacing: 0;
        font-family: Brown, sans-serif;
    }

    .explore-box {
        position: absolute;
        top: 50%;
        right: -100%;
        width: 650px;
        height: 80%;
        background-color: #fff;
        color: black;
        padding: 20px;
        transition: right 1s ease, opacity 1s ease;
        z-index: 1000;
        transform: translateY(-50%);
        display: none;
        opacity: 0;
        /* Bắt đầu với độ trong suốt 0 */
    }

    .explore-box.open {
        right: 0;
        opacity: 1;
        /* Đặt độ trong suốt về 1 khi mở */
    }


    .shop-now-button {
        position: absolute;
        top: 38%;
        right: 10px;
        transform: translateY(-50%) rotate(-90deg);
        background-color: #fff;
        color: black;
        border: none;
        height: 60px;
        width: 153px;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        transition: background-color 1.5s ease, right 0.5s ease;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        transform-origin: right center;
    }

    .shop-now-button.move-left {
        right: 670px;
        /* Đẩy nút qua trái khi hộp mở */

    }

    .shop-now-button:hover {
        background-color: #f0f0f0;
    }
    </style>

    <?php

// Mảng các ID subcategory
$subcategory_ids = [9, 10, 12, 28, 31, 35, 37];

// Tạo một danh sách placeholder cho câu truy vấn
$placeholders = implode(',', array_fill(0, count($subcategory_ids), '?'));

// Truy vấn dữ liệu subcategory
$sql = "SELECT subcategory_id, subcategory_name, subcategory_image FROM subcategory WHERE subcategory_id IN ($placeholders)";
$stmt = $conn->prepare($sql);

// Kiểm tra nếu câu truy vấn được chuẩn bị thành công
if ($stmt === false) {
    die('Error in preparing the statement: ' . $conn->error);
}

// Gắn giá trị ID danh mục con vào truy vấn
$stmt->bind_param(str_repeat('i', count($subcategory_ids)), ...$subcategory_ids);

$stmt->execute();
$result = $stmt->get_result();

// Lấy tất cả dữ liệu subcategory
$subcategories = [];
while ($row = $result->fetch_assoc()) {
    $subcategories[] = $row;
}

?>

    <div class="tryon">
        <div class="subcategory-info">
            <div class="camera-detail-wrapper">
                <a href="try_on_makeup.php">
                    <img class="camera-detail-img" src="../image/icons/camera.png" alt="Camera Icon">
                </a>
            </div>

            <p id="subcategory-name">Trang điểm ảo</p>
        </div>


        <?php foreach ($subcategories as $subcategory): ?>
        <div class="subcategory-info">
            <img id="subcategory-image" class="subcategory-image"
                src="../image/subcategory/<?php echo htmlspecialchars($subcategory['subcategory_image']); ?>"
                alt="<?php echo htmlspecialchars($subcategory['subcategory_name']); ?> Image">
            <p id="subcategory-name">
                <?php echo htmlspecialchars($subcategory['subcategory_name']); ?>
            </p>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="beauty-tryon-section">
        <h1 class="beauty-title">SERAPH BEAUTY THỬ TRANG ĐIỂM ẢO</h1>
        <p class="beauty-description">
            Chọn phong cách phù hợp nhất với bạn và thử trang điểm ảo với các sản phẩm mỹ phẩm mới nhất
        </p>
        <a href="try_on_makeup.php" class="try-now-button">Thử Ngay</a>

    </div>

    <div class="line3"></div>

    <div class="banner-gloss">
        <img src="../image/banner/creatbomblip.jpg" alt="">
        <div class="gloss-box">
            <img src="../image/bannerlogo.jpg" alt="">
            <h3>Combo son môi ấn tượng</h3>
            <p>Kết hợp Gloss Bomb Stix và chì kẻ môi Trace’d Out để tạo ra vô vàn kiểu dáng môi.</p>
            <p style="background-color: white; color: black; padding: 15px; width: 40%; margin-top: 1rem; cursor: pointer;"
                onclick="toggleExploreBox()">Khám phá ngay</p>
        </div>

        <button class="shop-now-button" onclick="toggleExploreBox()">Khám phá ngay</button>

        <div class="explore-box" id="exploreBox">
            <section class="shop">




                <div class="box-container">

                    <?php

                    $current_date = date('Y-m-d');

                    $query = "
    SELECT 
        p.product_id, 
        p.product_name, 
        p.product_price, 
        p.color_name, 
        pp.discount_percent, 
        (p.product_price * (1 - IFNULL(pp.discount_percent, 0) / 100)) AS final_price,
        p.product_image
    FROM 
        products p
    LEFT JOIN 
        product_promotion pp 
    ON 
        p.category_name = pp.category_name 
        AND p.product_subcategory = pp.subcategory_name
        AND '$current_date' BETWEEN pp.start_date AND pp.end_date
    WHERE 
        p.status = 'Còn hàng' 
        AND p.product_id IN (9, 10)  -- Chỉ lấy sản phẩm có product_id là 1 và 20
";


                    $select_products = mysqli_query($conn, $query) or die('Truy vấn thất bại: ' . mysqli_error($conn));

                    if (mysqli_num_rows($select_products) > 0) {
                        while ($fetch_products = mysqli_fetch_assoc($select_products)) {
                            $image_names = explode(',', $fetch_products['product_image']);
                            $product_id = $fetch_products['product_id'];

                            // Lấy số sao đánh giá
                            $select_star = mysqli_query($conn, "SELECT AVG(star) AS avg_star FROM evaluate WHERE product_id = '$product_id'") or die('Truy vấn đánh giá thất bại: ' . mysqli_error($conn));
                            $fetch_star = mysqli_fetch_assoc($select_star);
                            $average_star = round($fetch_star['avg_star'], 1);

                            // Lấy giá đã giảm (nếu có khuyến mãi hợp lệ)
                            $final_price = $fetch_products['final_price'];

                            // Thoát ký tự đặc biệt trong product_name
                            $product_name = mysqli_real_escape_string($conn, $fetch_products['product_name']);

                            // Truy vấn đếm số lượng màu sắc khác nhau của các sản phẩm có cùng product_name
                            $query_colors = "SELECT COUNT(DISTINCT color_name) AS color_count FROM products WHERE product_name = '$product_name'";
                            $select_colors = mysqli_query($conn, $query_colors) or die('Truy vấn số lượng màu sắc thất bại: ' . mysqli_error($conn));
                            $fetch_colors = mysqli_fetch_assoc($select_colors);
                            $color_count = $fetch_colors['color_count'];
                    ?>

                    <form method="post" class="box">
                        <div class="img-container" style="width: 300px; margin-bottom: 1rem;">

                            <?php
                                    for ($i = 0; $i < min(2, count($image_names)); $i++) {
                                        $class = $i === 0 ? 'main-img' : 'second-img';
                                        $image_url = '../image/product/' . urlencode(trim($image_names[$i]));
                                    ?>
                            <a href="view_page_guest.php?product_id=<?php echo $product_id; ?>">
                                <img class="<?php echo $class; ?>" src="<?php echo $image_url; ?>"
                                    data-index="<?php echo $i; ?>">
                            </a>
                            <?php } ?>


                            <a href="../components/login.php?product_id=<?php echo $product_id; ?>&redirect=checkout"
                                class="buy_now" data-id="<?php echo $product_id; ?>">Khám phá ngay</a>
                        </div>

                        <div class="priceshop">
                            <?php
                                    $product_price = $fetch_products['product_price']; // Lấy giá gốc từ CSDL

                                    if ($final_price < $product_price) {
                                        // Hiển thị cả giá gốc và giá đã giảm
                                        echo '<span class="original-price">' . number_format($product_price, 0, '.', '.') . 'VNĐ</span>';
                                        echo '<span class="discounted-price">' . number_format($final_price, 0, '.', '.') . 'VNĐ</span>';
                                    } elseif ($final_price == $product_price) {
                                        // Khi giá đã giảm bằng giá gốc
                                        echo '<span class="discounted-price no-discount">' . number_format($final_price, 0, '.', '.') . 'VNĐ</span>';
                                    }
                                    ?>
                        </div>

                        <div class="detail">
                            <div class="name"><?php echo $fetch_products['product_name']; ?></div>
                            <div class="color">
                                <?php echo $fetch_products['color_name']; ?>
                                <span class="shade" style="cursor: pointer;"
                                    onclick="window.location.href='view_page_guest.php?product_id=<?php echo $product_id; ?>';">
                                    <?php echo $color_count; ?> màu
                                </span>
                                <!-- Hiển thị số lượng màu sắc -->
                            </div>
                            <div class="star">
                                <?php
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= $average_star ? '<span class="star-filled">★</span>' : '<span class="star-empty">☆</span>';
                                        }

                                        // Truy vấn số lượt đánh giá
                                        $query_reviews = "SELECT COUNT(*) as total_reviews FROM evaluate WHERE product_id = ?";
                                        $stmt = $conn->prepare($query_reviews);
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
                        <input type="hidden" name="product_image"
                            value="<?php echo $fetch_products['product_image']; ?>">
                    </form>
                    <?php
                        }
                    } else {
                        echo '<p class="empty">Chưa có sản phẩm nào được thêm</p>';
                    }
                    ?>
                </div>













            </section>
        </div>
    </div>

    <script>
    function toggleExploreBox() {
        const exploreBox = document.getElementById('exploreBox');
        const shopNowButton = document.querySelector('.shop-now-button');

        if (exploreBox.classList.contains('open')) {
            // Khi đóng hộp, thêm độ trễ để chờ hiệu ứng hoàn tất rồi mới ẩn
            exploreBox.classList.remove('open');
            setTimeout(() => {
                exploreBox.style.display = 'none';
            }, 500); // Khớp với thời gian chuyển đổi opacity
        } else {
            // Khi mở hộp, hiển thị block trước và sau đó thêm lớp open để kích hoạt hiệu ứng
            exploreBox.style.display = 'block';
            setTimeout(() => {
                exploreBox.classList.add('open');
            }, 10); // Thời gian ngắn để kích hoạt hiệu ứng
        }

        shopNowButton.classList.toggle('move-left');
    }
    </script>



    <div class="line"></div>
    <div class="content">

        <div class="content-container">



        </div>

        <!-- Thêm link Swiper CSS và JS nếu chưa có -->
        <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
        <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

        <!-- <div class="swiper-container1 total-video">
                <div class="swiper-wrapper">
                    <div class="swiper-slide menu-video1">
                        <video muted loop class="dropdown-video thumbnail" autoplay>
                            <source src="../image/video/fentybanner1.mp4" type="video/mp4">
                            Trình duyệt của bạn không hỗ trợ video.
                        </video>
                    </div>
                    <div class="swiper-slide menu-video1">
                        <video muted loop class="dropdown-video thumbnail" autoplay>
                            <source src="../image/video/fentymakeupbanner.mp4" type="video/mp4">
                            Trình duyệt của bạn không hỗ trợ video.
                        </video>
                    </div>
                    <div class="swiper-slide menu-video1">
                        <video muted loop class="dropdown-video thumbnail" autoplay>
                            <source src="../image/video/fentybanner4.mp4" type="video/mp4">
                            Trình duyệt của bạn không hỗ trợ video.
                        </video>
                    </div>
                    <div class="swiper-slide menu-video1">
                        <video muted loop class="dropdown-video thumbnail" autoplay>
                            <source src="../image/video/fentybanner3.mp4" type="video/mp4">
                            Trình duyệt của bạn không hỗ trợ video.
                        </video>
                    </div>
                    <div class="swiper-slide menu-video1">
                        <video muted loop class="dropdown-video thumbnail" autoplay>
                            <source src="../image/video/fentybanner2.mp4" type="video/mp4">
                            Trình duyệt của bạn không hỗ trợ video.
                        </video>
                    </div>
                    <div class="swiper-slide menu-video1">
                        <video muted loop class="dropdown-video thumbnail" autoplay>
                            <source src="../image/video/fentybanner5.mp4" type="video/mp4">
                            Trình duyệt của bạn không hỗ trợ video.
                        </video>
                    </div>
                    <div class="swiper-slide menu-video1">
                        <video muted loop class="dropdown-video thumbnail" autoplay>
                            <source src="../image/video/fentybanner6.mp4" type="video/mp4">
                            Trình duyệt của bạn không hỗ trợ video.
                        </video>
                    </div>
                    <div class="swiper-slide menu-video1">
                        <video muted loop class="dropdown-video thumbnail" autoplay>
                            <source src="../image/video/fentybanner7.mp4" type="video/mp4">
                            Trình duyệt của bạn không hỗ trợ video.
                        </video>
                    </div>
                </div> -->

        <!-- Nút điều hướng -->
        <!-- <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div> -->
    </div>

    <!-- Thêm CSS -->
    <!-- <style>
    .swiper-container1 {
        width: 100%;
        height: auto;
    }

    .swiper-wrapper {
        display: flex;
    }

    .swiper-slide {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 20%;
    }

    video {
        width: 100%;
        height: auto;
    }

    .hidden {
        display: none;
    }
    </style> -->


    <script>
    // Khởi tạo Swiper
    const swiper = new Swiper('.swiper-container1', {
        slidesPerView: 5, // Hiển thị 5 video mỗi hàng
        spaceBetween: 10, // Khoảng cách giữa các video
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        on: {
            slideChange: function() {
                updateVideoVisibility();
            },
        },
    });

    // Cập nhật tính năng ẩn video và phát video thứ 3
    function updateVideoVisibility() {
        const slides = document.querySelectorAll('.swiper-slide');
        slides.forEach((slide, index) => {
            if (index < swiper.activeIndex || index >= swiper.activeIndex + 5) {
                slide.classList.add('hidden'); // Ẩn các slide không cần thiết
                const video = slide.querySelector('video');
                if (video) video.pause(); // Dừng video khi ẩn
            } else {
                slide.classList.remove('hidden'); // Hiện slide
                const video = slide.querySelector('video');
                if (video) video.pause(); // Dừng video trước khi phát lại
            }
        });

        // Phát video thứ 3 trong các slide đang hiển thị
        const thirdSlideIndex = swiper.activeIndex + 2; // Chỉ số video thứ 3
        if (thirdSlideIndex < slides.length) {
            const thirdSlide = slides[thirdSlideIndex];
            const videoToPlay = thirdSlide.querySelector('video');
            if (videoToPlay) videoToPlay.play(); // Phát video thứ 3
        }
    }

    // Khởi động với trạng thái ban đầu
    updateVideoVisibility();
    </script>





    <div class="container" id="video-container">
        <video class="video" autoplay muted loop id="video">
            <source src="../image/video/fentycampagn.mp4" type="video/mp4">
            Trình duyệt của bạn không hỗ trợ video.
        </video>
        <div class="image-container">
            <div class="left-image-container">
                <img src="../image/categories/haircare.jpg" class="dropdown-image" id="left-image" alt="Banner">
                <p class="image-caption1">Seraph Chăm Sóc Tóc</p>
                <a class="image-a1" href="haircare.php">Cửa hàng</a>
            </div>

            <div class="right-image-container">
                <img src="../image/banner/arcane.jpg" class="dropdown-image" id="right-image" alt="Banner">
                <p class="image-caption2">Seraph Beauty x Arcane</p>
                <a class="image-a2" href="guest_collection.php?collection=Arcane%20Collection">Cửa hàng</a>
            </div>
        </div>
    </div>



    <style>
    .container {
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .video {
        width: 80%;
        height: 100%;
        transition: all 0.6s ease;
    }

    .container.active .video {
        width: 100%;
        transform: translateX(-55%);
    }

    .image-container {
        display: flex;
        flex-direction: row;
        gap: 10px;
        position: absolute;
        right: -30%;
        bottom: -50px;
        opacity: 0;
        transition: all 0.6s ease;
    }

    .container.active .image-container {
        right: 10%;
        bottom: 20%;
        opacity: 1;
    }

    .dropdown-image {
        width: 300px;
        height: 45vh !important;
        object-fit: cover;
        transition: opacity 0.6s ease, transform 0.6s ease;
    }

    /* CSS for image-caption1 */
    .image-caption1 {
        position: absolute;
        bottom: 10px;
        left: 25%;
        transform: translate(-50%, 0);
        /* Vị trí ban đầu */
        color: white;
        font-size: 15px;
        font-weight: bold;
        padding: 5px 10px;
        border-radius: 5px;
        text-align: center;
        top: 85%;
        transition: transform 0.5s ease;
        /* Thêm hiệu ứng di chuyển */
    }

    /* Khi thẻ <p> được đẩy lên */
    .image-caption1.move-up {
        transform: translate(-50%, -40px);
        /* Đẩy lên trên 10px */
    }

    /* CSS for image-caption2 */
    .image-caption2 {
        position: absolute;
        bottom: 10px;
        left: 25%;
        transform: translate(110%, 0);
        /* Vị trí ban đầu */
        color: white;
        font-size: 15px;
        font-weight: bold;
        padding: 5px 10px;
        border-radius: 5px;
        top: 85%;
        transition: transform 0.5s ease;
        /* Thêm hiệu ứng di chuyển */
        text-align: center;
    }

    /* Khi thẻ <p> được đẩy lên */
    .image-caption2.move-up {
        transform: translate(110%, -40px);
        /* Đẩy lên trên 10px */
    }

    /* CSS for image-a1 */
    .image-a1 {
        position: absolute;
        bottom: 10px;
        left: 25%;
        transform: translate(-50%, 20px);
        /* Bắt đầu thấp hơn */
        color: white;
        font-size: 15px;
        font-weight: bold;
        padding: 5px 10px;
        border-radius: 5px;
        text-align: center;
        opacity: 0;
        transition: opacity 0.4s ease, transform 0.4s ease;
        top: 90%;
        text-decoration: underline;
        /* Thêm gạch chân khi hover */

    }

    .image-a1.show {
        opacity: 1;
        transform: translate(-50%, -20px);
        /* Trở về vị trí ban đầu */
    }

    /* CSS for image-a2 */
    .image-a2 {
        position: absolute;
        bottom: 10px;
        left: 25%;
        transform: translate(285%, 20px);
        /* Bắt đầu thấp hơn */
        color: white;
        font-size: 15px;
        font-weight: bold;
        padding: 5px 10px;
        border-radius: 5px;
        text-align: center;
        opacity: 0;
        transition: opacity 0.4s ease, transform 0.4s ease;
        top: 90%;
        text-decoration: underline;
        /* Thêm gạch chân khi hover */
    }

    .image-a2.show {
        opacity: 1;
        transform: translate(285%, -20px);
        /* Trở về vị trí ban đầu */
    }
    </style>

    <script>
    const videoContainer = document.getElementById('video-container');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                videoContainer.classList.add('active');
            } else {
                videoContainer.classList.remove('active');
            }
        });
    }, {
        threshold: 0.5
    });

    observer.observe(videoContainer);

    const leftImage = document.getElementById('left-image');
    const rightImage = document.getElementById('right-image');
    const leftLink = document.querySelector('.image-a1'); // Chọn thẻ a của left-image
    const rightLink = document.querySelector('.image-a2'); // Chọn thẻ a của right-image
    const leftCaption = document.querySelector('.image-caption1'); // Chọn thẻ p của left-image
    const rightCaption = document.querySelector('.image-caption2'); // Chọn thẻ p của right-image

    // Hàm hiển thị liên kết và chú thích
    function showElements(link, caption) {
        link.classList.add('show');
        caption.classList.add('move-up');
    }

    // Hàm ẩn liên kết và chú thích
    function hideElements(link, caption) {
        link.classList.remove('show');
        caption.classList.remove('move-up');
    }

    // Sự kiện mouseenter cho ảnh bên trái và phải
    leftImage.addEventListener('mouseenter', () => showElements(leftLink, leftCaption));
    rightImage.addEventListener('mouseenter', () => showElements(rightLink, rightCaption));

    // Sự kiện mouseleave cho cả ảnh và liên kết của ảnh bên trái
    leftImage.addEventListener('mouseleave', (event) => {
        if (!leftLink.contains(event.relatedTarget)) {
            hideElements(leftLink, leftCaption);
        }
    });
    leftLink.addEventListener('mouseleave', (event) => {
        if (!leftImage.contains(event.relatedTarget)) {
            hideElements(leftLink, leftCaption);
        }
    });

    // Sự kiện mouseleave cho cả ảnh và liên kết của ảnh bên phải
    rightImage.addEventListener('mouseleave', (event) => {
        if (!rightLink.contains(event.relatedTarget)) {
            hideElements(rightLink, rightCaption);
        }
    });
    rightLink.addEventListener('mouseleave', (event) => {
        if (!rightImage.contains(event.relatedTarget)) {
            hideElements(rightLink, rightCaption);
        }
    });
    </script>





    <div class="line3"></div>




    <style>
    .seraph-brands {
        background-color: #eecdb7;
        height: 600px;
    }

    .seraph-brands .seraph-details {

        padding-top: 3rem;

    }


    .seraph-brands .seraph-details h2 {
        text-align: center;
        font-size: 32px;

        /* Di chuyển tiêu đề xuống dưới */
        margin-bottom: 0.8rem;
    }

    .seraph-brands .seraph-details p {
        text-align: center;

        /* Di chuyển đoạn văn xuống dưới */
    }




    .seraph-img {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 2rem;
        /* Khoảng cách giữa .seraph-details và hình ảnh */
    }

    .dropdown-image {
        max-width: 100%;
        height: auto;
        flex: 0 1 200px;
    }

    .caption {
        text-align: center;
        width: 100%;
        margin-top: 20px;
        font-size: 17px;
        color: #000;
        font-weight: bold;
    }
    </style>


    <div class="seraph-brands">
        <div class="seraph-details">
            <h2>THƯƠNG HIỆU LÀM ĐẸP SERAPH</h2>
            <p>Chăm sóc tóc, trang điểm, chăm sóc da, nước hoa cho tất cả mọi người.</p>
        </div>

        <div class="seraph-img">
            <div>
                <a href="haircare.php">
                    <!-- Thay đổi #link-to-fentyhair thành liên kết mong muốn -->
                    <img src="../image/banner/fentyhair.jpg" class="dropdown-image" alt="Banner">
                </a>
                <p class="caption">SERAPH CHĂM SÓC TÓC</p>
            </div>
            <div>
                <a href="view_makeupface.php">
                    <img src="../image/banner/fentybeauty.jpg" class="dropdown-image" alt="Banner">
                </a>
                <p class="caption">SERAPH TRANG ĐIỂM</p>
            </div>
            <div>
                <a href="bodycare.php">
                    <img src="../image/banner/fentyskin.jpg" class="dropdown-image" alt="Banner">
                </a>
                <p class="caption">SERAPH CHĂM SÓC DA</p>
            </div>
            <!-- <div>
                <a href="perfume.php">
                    <img src="../image/banner/fentyperfume.jpg" class="dropdown-image" alt="Banner">
                </a>
                <p class="caption">SERAPH NƯỚC HOA</p>
            </div> -->
        </div>
    </div>




    <?php include '../guest/footer.php' ?>
    <script src="jquary.js"></script>
    <script src="slick.css"></script>




    <script type="text/javascript" src="../js/script2.js"></script>
</body>

</html>