<?php
include '../connection/connection.php';
// session_start();
// Khởi tạo biến user_id
$user_id = null;

// Kiểm tra nếu người dùng đã đăng nhập
$is_logged_in = isset($_SESSION['user_id']);

// Kiểm tra nếu người dùng đã đăng nhập để cập nhật wishlist
if ($is_logged_in) {
    // Truy vấn số lượng sản phẩm trong wishlist của người dùng từ CSDL
    $wishlist_query = mysqli_query($conn, "SELECT * FROM `wishlist` WHERE user_id='{$_SESSION['user_id']}'");
    $wishlist_num_rows = mysqli_num_rows($wishlist_query);
} else {
    // Nếu người dùng chưa đăng nhập, gán giá trị mặc định là 0 cho wishlist
    $wishlist_num_rows = 0;
}

// Kiểm tra giỏ hàng từ session (không phụ thuộc vào đăng nhập)
if (isset($_SESSION['cart'])) {
    // Tính tổng số lượng sản phẩm trong giỏ hàng từ session
    $cart_num_rows = 0;
    foreach ($_SESSION['cart'] as $item) {
        $cart_num_rows += $item['quantity']; // Cộng dồn số lượng của từng sản phẩm
    }
} else {
    // Nếu giỏ hàng trong session chưa tồn tại, đặt số lượng sản phẩm là 0
    $cart_num_rows = 0;
}





$sql = "SELECT subcategory_name FROM Subcategory";
$result = $conn->query($sql);

$all_categories = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $all_categories[] = $row['subcategory_name'];
    }
}

$sql = "SELECT subcategory_name FROM Subcategory WHERE category_id = 2";
$result = $conn->query($sql);

$category_makeup = [];
if ($result->num_rows > 0) {
    // Lấy dữ liệu từ mỗi hàng
    while ($row = $result->fetch_assoc()) {
        $category_makeup[] = $row['subcategory_name'];
    }
}

$sql = "SELECT subcategory_name FROM Subcategory WHERE category_id = 3";
$result = $conn->query($sql);

$category_skincare = [];
if ($result->num_rows > 0) {
    // Lấy dữ liệu từ mỗi hàng
    while ($row = $result->fetch_assoc()) {
        $category_skincare[] = $row['subcategory_name'];
    }
}

$sql = "SELECT subcategory_name FROM Subcategory WHERE category_id = 4";
$result = $conn->query($sql);

$haircare = [];
if ($result->num_rows > 0) {
    // Lấy dữ liệu từ mỗi hàng
    while ($row = $result->fetch_assoc()) {
        $haircare[] = $row['subcategory_name'];
    }
}

$sql = "SELECT subcategory_name FROM Subcategory WHERE category_id = 5";
$result = $conn->query($sql);

$eyemakeup = [];
if ($result->num_rows > 0) {
    // Lấy dữ liệu từ mỗi hàng
    while ($row = $result->fetch_assoc()) {
        $eyemakeup[] = $row['subcategory_name'];
    }
}

$category_id = 2;

// Chuẩn bị và thực hiện truy vấn
$sql = "SELECT subcategory_name, subcategory_image FROM subcategory WHERE category_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id); // "i" cho integer
$stmt->execute();
$result = $stmt->get_result();

// Lấy dữ liệu từ truy vấn
$subcategories = $result->fetch_all(MYSQLI_ASSOC);





?>



<style type="text/css">
<?php include '../CSS/main.css'


?>.about .fsdetail span {
    background: #000;

    color: white;
    display: block;
    text-align: center;
    padding: 10px;
    /* Thêm bo tròn góc (tuỳ chọn) */
}
</style>
<!DOCTYPE HTML>
<html lang="vi">

<head>
    <title>Header</title>
    <link href="../CSS/main.css" rel="stylesheet" type="text/css" media="all" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- <div class="about">
        <div class="fsdetail">
            <span>Miễn phí ship cho hóa đơn trên 500.000VNĐ</span>
            <span>Luôn được trả hàng miễn phí</span>
        </div>
    </div> -->

</head>

<body>

    <link rel="stylesheet" type="text/css" href="../CSS/main.css?v=1.1 <?php echo time(); ?>">

    <style>
    .btn-container {
        display: flex;
        justify-content: space-between;
    }

    .btn-container button[type="submit"] {
        width: 48%;
        /* Điều chỉnh kích thước của các nút để chúng vừa với phần tử cha */
    }
    </style>




    <header class="header">
        <div class="flex">
            <img style="cursor: pointer;" src="../image/seraph.png" width="200"
                onclick="window.location.href='../guest/guest.php'">
        </div>
        <div class="navbar-wrapper ">
            <nav class="navbar">
                <div class="dropdown promotion-dropdown">
                    <a href="#" class="dropdown-toggle">
                        <span class="sparkle sparkle-left">
                            <img src="../image/icons/sparkle.svg" alt="Red Sparkle">
                        </span>
                        Khuyến mãi
                        <span class="sparkle sparkle-right">
                            <img src="../image/icons/sparkle.svg" alt="Red Sparkle">
                        </span>
                    </a>

                    <div class="dropdown-menu">
                        <div class="menu-wrapper">
                            <div class="menu-content">
                                <div class="menu-links">
                                    <!-- <a href="#">Sản phẩm đang giảm giá</a> -->

                                </div>
                            </div>
                            <div class="subcategory_sale">
                                <?php
                                // Lấy ngày hiện tại
                                $current_date = date('Y-m-d');

                                // Truy vấn SQL để lấy subcategory_name và subcategory_image từ bảng subcategory
                                $query = "
                                SELECT p.product_id, p.product_name, p.product_image, p.color_name
                                FROM product_promotion pp
                                JOIN products p ON pp.product_id = p.product_id
                                WHERE pp.start_date <= '$current_date' AND pp.end_date >= '$current_date'
                                GROUP BY p.product_id, p.product_name, p.product_image, p.color_name
                            ";
                            
                            $result = mysqli_query($conn, $query);
                            
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo '<div class="subcategory-item">';
                                    
                                    // Lấy ảnh đầu tiên từ chuỗi product_image
                                    $images = explode(',', $row['product_image']);
                                    $firstImage = htmlspecialchars($images[0]);
                            
                                    // Lấy product_id
                                    $productId = htmlspecialchars($row['product_id']);
                                    
                                    // Gắn liên kết để chuyển đến view_page.php với product_id
                                    echo '<a href="view_page_guest.php?product_id=' . $productId . '">';
                                    echo '<img src="../image/product/' . $firstImage . '" alt="' . htmlspecialchars($row['product_name']) . '" title="' . htmlspecialchars($row['color_name']) . '">';
                                    echo '</a>';
                                    
                                    echo '<p>' . htmlspecialchars($row['product_name']) . '</p>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p>No promotions available.</p>';
                            }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>





                <!-- <div class="dropdown">
                    <a href="#" class="dropdown-toggle">Sản Phẩm Bán Chạy</a>
                    <div class="dropdown-menu">
                        <div class="menu-content">
                            <div class="menu-links">
                                <a href="#">Hàng Mới Về</a>
                                <a href="#">Bán Chạy</a>
                                <a href="#">Son Bóng</a>
                                <a href="#">Kem Nền</a>
                                <a href="#">Toner</a>
                                <a href="#">Tất Cả</a>
                            </div>
                            <div class="menu-image1">
                                <img src="../image/banner/bannerbesstseller.jpg" class="dropdown-image" alt="Banner">
                            </div>
                            <div class="menu-videobestseller">
                                <video autoplay loop muted class="dropdown-video"
                                    data-video-src="../image/video/eyemakeup.mp4">
                                    <source src="../image/video/bannerindex.mp4" type="video/mp4">
                                    Trình duyệt của bạn không hỗ trợ video.
                                </video>
                            </div>


                        </div>
                    </div>
                </div> -->



                <div class="dropdown">
                    <a href="view_makeupface.php" class="dropdown-toggle">Trang Điểm Mặt</a>
                    <div class="dropdown-menu">
                        <div class="menu-content">
                            <div class="menu-links">
                                <?php foreach ($category_makeup as $categories): ?>
                                <a href="../guest/guest_subcategory.php?category=<?php echo urlencode($categories); ?>">
                                    <?php echo htmlspecialchars($categories); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <div class="menu-video1">
                                <video autoplay muted loop>
                                    <source src="../image/video/glosslip.mp4" type="video/mp4">
                                    Trình duyệt của bạn không hỗ trợ video.
                                </video>
                            </div>

                            <section class="shop">
                                <div class="box-container">

                                    <?php
                                    // Cập nhật truy vấn SQL để bao gồm điều kiện lọc và sắp xếp
                                    $category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : ''; // Di chuyển lên trước truy vấn
                                    $select_products = mysqli_query($conn, "SELECT * FROM products WHERE status = 'Còn hàng' AND category_name = '2' LIMIT 3") or die('Truy vấn thất bại: ' . mysqli_error($conn));

                                    if (mysqli_num_rows($select_products) > 0) {
                                        while ($fetch_products = mysqli_fetch_assoc($select_products)) {

                                            $image_names = explode(',', $fetch_products['product_image']);
                                            $product_id = $fetch_products['product_id'];

                                            // Truy vấn để lấy số sao đánh giá cho sản phẩm này
                                            $select_star = mysqli_query($conn, "SELECT AVG(star) AS avg_star FROM evaluate WHERE product_id = '$product_id'") or die('Truy vấn đánh giá thất bại: ' . mysqli_error($conn));
                                            $fetch_star = mysqli_fetch_assoc($select_star);
                                            $average_star = round($fetch_star['avg_star'], 1); // Làm tròn số sao đánh giá
                                    ?>

                                    <form method="post" class="box">
                                        <div class="img-container">
                                            <?php
                                                    // Loop through image names and display only the first and second images
                                                    for ($i = 0; $i < min(2, count($image_names)); $i++) {
                                                        $class = $i === 0 ? 'main-img' : 'second-img';
                                                        // Mã hóa URL để xử lý các ký tự đặc biệt trong tên ảnh (như ký tự #)
                                                        $image_url = '../image/product/' . urlencode(trim($image_names[$i]));
                                                    ?>
                                            <a href="view_page_guest.php?product_id=<?php echo $product_id; ?>">
                                                <img class="<?php echo $class; ?>" src="<?php echo $image_url; ?>"
                                                    data-index="<?php echo $i; ?>">
                                            </a>
                                            <?php } ?>


                                        </div>

                                        <div class="priceshop">
                                            <?php
                                                    // Hiển thị giá cho sản phẩm
                                                    echo number_format($fetch_products['product_price'], 0, '.', '.') . ' VNĐ';
                                                    ?>
                                        </div>
                                        <div class="detail">
                                            <div class="name"><?php echo $fetch_products['product_name']; ?></div>
                                            <div class="star">
                                                <?php
                                                        // Hiển thị số sao đánh giá
                                                        for ($i = 1; $i <= 5; $i++) {
                                                            if ($i <= $average_star) {
                                                                echo '<span class="star-filled">★</span>'; // Sao đầy
                                                            } else {
                                                                echo '<span class="star-empty">☆</span>'; // Sao rỗng
                                                            }
                                                        }
                                                        $product_id = $fetch_products['product_id']; // ID của sản phẩm hiện tại

                                                        // Truy vấn số lượt đánh giá
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

                                        <input type="hidden" name="product_id"
                                            value="<?php echo $fetch_products['product_id']; ?>">
                                        <input type="hidden" name="product_name"
                                            value="<?php echo $fetch_products['product_name']; ?>">
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
                </div>








                <div class="dropdown">
                    <a href="view_eyemakeup.php" class="dropdown-toggle">Trang Điểm Mắt</a>
                    <div class="dropdown-menu">
                        <div class="menu-content">
                            <div class="menu-links">
                                <?php foreach ($eyemakeup as $category): ?>
                                <a href="../guest/guest_subcategory.php?category=<?php echo urlencode($category); ?>">
                                    <?php echo htmlspecialchars($category); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <div class="menu-video">
                                <video muted autoplay loop class="dropdown-video"
                                    data-video-src="../image/video/eyemakeup.mp4">
                                    <source src="../image/video/eyemakeup.mp4" type="video/mp4">
                                    Trình duyệt của bạn không hỗ trợ video.
                                </video>
                            </div>

                            <section class="shop">
                                <div class="box-container">

                                    <?php
                                    // Cập nhật truy vấn SQL để bao gồm điều kiện lọc và sắp xếp
                                    $category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : ''; // Di chuyển lên trước truy vấn
                                    $select_products = mysqli_query($conn, "SELECT * FROM products WHERE status = 'Còn hàng' AND category_name = '5' LIMIT 3") or die('Truy vấn thất bại: ' . mysqli_error($conn));

                                    if (mysqli_num_rows($select_products) > 0) {
                                        while ($fetch_products = mysqli_fetch_assoc($select_products)) {

                                            $image_names = explode(',', $fetch_products['product_image']);
                                            $product_id = $fetch_products['product_id'];

                                            // Truy vấn để lấy số sao đánh giá cho sản phẩm này
                                            $select_star = mysqli_query($conn, "SELECT AVG(star) AS avg_star FROM evaluate WHERE product_id = '$product_id'") or die('Truy vấn đánh giá thất bại: ' . mysqli_error($conn));
                                            $fetch_star = mysqli_fetch_assoc($select_star);
                                            $average_star = round($fetch_star['avg_star'], 1); // Làm tròn số sao đánh giá
                                    ?>

                                    <form method="post" class="box">
                                        <div class="img-container">
                                            <?php
                                                    // Loop through image names and display only the first and second images
                                                    for ($i = 0; $i < min(2, count($image_names)); $i++) {
                                                        $class = $i === 0 ? 'main-img' : 'second-img';
                                                        // Mã hóa URL để xử lý các ký tự đặc biệt trong tên ảnh (như ký tự #)
                                                        $image_url = '../image/product/' . urlencode(trim($image_names[$i]));
                                                    ?>
                                            <a href="view_page_guest.php?product_id=<?php echo $product_id; ?>">
                                                <img class="<?php echo $class; ?>" src="<?php echo $image_url; ?>"
                                                    data-index="<?php echo $i; ?>">
                                            </a>
                                            <?php } ?>


                                        </div>

                                        <div class="priceshop">
                                            <?php
                                                    // Hiển thị giá cho sản phẩm
                                                    echo number_format($fetch_products['product_price'], 0, '.', '.') . ' VNĐ';
                                                    ?>
                                        </div>
                                        <div class="detail">
                                            <div class="name"><?php echo $fetch_products['product_name']; ?></div>
                                            <div class="star">
                                                <?php
                                                        // Hiển thị số sao đánh giá
                                                        for ($i = 1; $i <= 5; $i++) {
                                                            if ($i <= $average_star) {
                                                                echo '<span class="star-filled">★</span>'; // Sao đầy
                                                            } else {
                                                                echo '<span class="star-empty">☆</span>'; // Sao rỗng
                                                            }
                                                        }
                                                        $product_id = $fetch_products['product_id']; // ID của sản phẩm hiện tại

                                                        // Truy vấn số lượt đánh giá
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

                                        <input type="hidden" name="product_id"
                                            value="<?php echo $fetch_products['product_id']; ?>">
                                        <input type="hidden" name="product_name"
                                            value="<?php echo $fetch_products['product_name']; ?>">
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
                </div>


                <script>
                // Lấy tất cả các video trong menu video
                const videos = document.querySelectorAll('.dropdown-video');

                videos.forEach(video => {
                    // Bắt sự kiện khi chuột di chuyển vào video
                    video.addEventListener('mouseenter', () => {
                        video.play(); // Phát video
                    });

                    // Bắt sự kiện khi chuột rời khỏi video
                    video.addEventListener('mouseleave', () => {
                        video.pause(); // Dừng video
                        video.currentTime = 0; // Reset video về đầu
                    });
                });
                </script>





                <div class="dropdown">
                    <a href="bodycare.php" class="dropdown-toggle">Chăm Sóc Da</a>
                    <div class="dropdown-menu">
                        <div class="menu-content">
                            <div class="menu-links">
                                <?php foreach ($category_skincare as $categories): ?>
                                <a
                                    href="../guest/guest_subcategory.php?category=<?php echo urlencode($categories); ?>"><?php echo htmlspecialchars($categories); ?></a>
                                <?php endforeach; ?>

                            </div>
                            <div class="menu-image">
                                <video autoplay muted loop class="dropdown-video">
                                    <source src="" type="video/mp4">
                                    Trình duyệt của bạn không hỗ trợ video.
                                </video>
                            </div>


                        </div>

                    </div>
                </div>
                <div class="dropdown">
                    <a href="haircare.php" class="dropdown-toggle">Chăm Sóc Tóc</a>
                    <div class="dropdown-menu">
                        <?php foreach ($haircare as $categories): ?>
                        <a
                            href="../guest/guest_subcategory.php?category=<?php echo urlencode($categories); ?>"><?php echo htmlspecialchars($categories); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- <div class="dropdown">
                    <a href="../user/about.php" class="dropdown-toggle">Về chúng tôi</a>
                    <div class="dropdown-menu">
                        <a href="#">Giới thiệu</a>
                        <a href="#">Đội ngũ</a>
                        <a href="#">Lịch sử</a>
                    </div>
                </div> -->


            </nav>
        </div>
        <div class="vietnam">
            <img src="../image/vietnam.png" width="25">
            <p>Việt Nam</p>
            <!-- <i class="bi bi-list" id="menu-btn"></i>  -->
        </div>

        <?php
        // Giả sử giỏ hàng của bạn là một mảng session
        $isCartEmpty = empty($_SESSION['cart']);
        ?>

        <div class="icons">
            <i class="bi bi-search" id="search-btn"></i>
            <i class="bi bi-person" id="user-btn"></i>
            <!-- <i class="bi bi-heart" id="heart-btn"></i> -->
            <a href="cart_guest.php" id="cart-link">
                <i class="bi bi-cart"></i>
                <sup><?php echo $cart_num_rows; ?></sup>
            </a>

            <div class="user-box">
                <p>Bạn chưa có tài khoản?</p>
                <form method="post">
                    <button type="button" name="login" class="login-btn"
                        onclick="window.location.href='../components/login.php'">Đăng nhập</button>
                    <button type="button" name="register" class="register-btn"
                        onclick="window.location.href='../components/register.php'">Đăng ký</button>
                </form>
            </div>
        </div>

        <div id="empty-cart-overlay" style="display:none;">
            <div id="empty-cart-box">
                <p>Giỏ hàng của bạn đang trống!</p>
            </div>
        </div>

        <script>
        var isCartEmpty = <?php echo json_encode($isCartEmpty); ?>;
        var notificationQueue = []; // Hàng đợi thông báo
        var isDisplaying = false; // Biến kiểm tra xem có thông báo nào đang hiển thị không

        // Bắt sự kiện click vào biểu tượng giỏ hàng
        document.getElementById('cart-link').addEventListener('click', function(event) {
            if (isCartEmpty) {
                event.preventDefault(); // Ngăn không cho chuyển trang
                // Thêm thông báo vào hàng đợi
                notificationQueue.push('Giỏ hàng của bạn đang trống!');

                // Nếu không có thông báo nào đang hiển thị, hiển thị thông báo đầu tiên trong hàng đợi
                if (!isDisplaying) {
                    displayNextNotification();
                }
            }
        });

        // Hàm hiển thị thông báo
        function displayNextNotification() {
            if (notificationQueue.length === 0) return; // Nếu không có thông báo, thoát

            isDisplaying = true; // Đánh dấu là đang hiển thị thông báo
            var message = notificationQueue.shift(); // Lấy thông báo đầu tiên trong hàng đợi

            // Hiển thị overlay và box thông báo
            document.getElementById('empty-cart-overlay').style.display = 'block';
            document.getElementById('empty-cart-overlay').querySelector('p').innerText = message;

            // Tự động ẩn thông báo sau 6 giây
            setTimeout(function() {
                document.getElementById('empty-cart-overlay').classList.add('hidden');

                // Khi thông báo ẩn xong, kiểm tra xem còn thông báo nào không
                setTimeout(function() {
                    document.getElementById('empty-cart-overlay').style.display = 'none';
                    isDisplaying = false; // Đánh dấu lại cho phép hiển thị thông báo lần sau

                    // Gọi hàm hiển thị thông báo tiếp theo nếu còn trong hàng đợi
                    displayNextNotification();
                }, 1000); // Thời gian ẩn (1 giây) để hiệu ứng mờ dần
            }, 5000); // Thời gian hiển thị (6 giây)
        }

        // Lắng nghe sự kiện transitionend để đặt lại biến khi hiệu ứng biến mất hoàn tất
        document.getElementById('empty-cart-overlay').addEventListener('transitionend', function() {
            if (this.classList.contains('hidden')) {
                isDisplaying = false; // Đánh dấu lại cho phép hiển thị thông báo lần sau
            }
        });

        // Hàm đóng box thông báo giỏ hàng rỗng (nếu cần)
        function closeEmptyCartBox() {
            document.getElementById('empty-cart-overlay').style.display = 'none';
            isDisplaying = false; // Đánh dấu lại cho phép hiển thị thông báo lần sau
        }
        </script>




        <div class="heart-box" id="heart-box">
            <div class="favorite">
                <i onclick="closeHeartBox()" class="bi bi-x close-icon"></i> <!-- Dấu X ở góc trái trên -->
                <i class="bi bi-arrow-left"></i>
                <h2>Yêu thích</h2>
            </div>

            <div class="detail-favorite">
                <h3>Bạn chưa thêm sản phẩm yêu thích nào.</h3>
                <p>Nhấn vào trái tim trên mỗi hình ảnh để thêm sản phẩm vào yêu thích.</p>
            </div>

            <div class="wishlist-container">
                <h3>Những sản phẩm yêu thích</h3>
                <?php
                // Biến để xác định trạng thái wishlist
                $wishlist_has_items = false;

                // Kiểm tra nếu session wishlist tồn tại và không rỗng
                if (isset($_SESSION['wishlist']) && !empty($_SESSION['wishlist'])) {
                    $wishlist_has_items = true; // Có sản phẩm trong wishlist

                    // Lấy danh sách product_id từ session wishlist
                    $product_ids = array_column($_SESSION['wishlist'], 'product_id');

                    // Tạo chuỗi các product_id để dùng trong câu truy vấn SQL
                    $product_ids_str = implode(',', $product_ids);

                    // Truy vấn thông tin từ bảng products dựa trên product_id trong session wishlist
                    $query = "SELECT product_id, product_name, product_price, product_image, color_name 
                      FROM products 
                      WHERE product_id IN ($product_ids_str)";
                    $result = $conn->query($query);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // Tách product_image và lấy ảnh đầu tiên
                            $images = explode(',', $row['product_image']); // Tách chuỗi ảnh
                            $first_image = $images[0]; // Lấy ảnh đầu tiên

                            // Hiển thị thông tin sản phẩm lấy từ bảng products
                            echo '<div class="product-item" data-id="' . $row['product_id'] . '">';
                            echo '<img src="../image/product/' . $first_image . '" alt="' . $row['product_name'] . '">';
                            echo '<div class="product-info">';
                            echo '<h3>' . $row['product_name'] . ' - ' . $row['color_name'] . '</h3>';
                            echo '<p>' . number_format($row['product_price'], 0, '.', '.') . ' VNĐ</p>';
                            echo '<div class="product-actions">'; // Thêm div container cho các hành động
                            echo '<a href="view_page_guest.php?product_id=' . $row['product_id'] . '" class="view-product-link">Xem sản phẩm</a>';
                            echo ' <i class="fas fa-heart" style="color: black;" 
        data-product-id="' . $row['product_id'] . '" 
        onclick="removeFromWishlist(this)"></i>';
                            // Biểu tượng trái tim màu đen
                            echo '</div>'; // Đóng div container
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>Không có sản phẩm nào trong wishlist.</p>';
                    }
                } else {
                    echo '<p>Wishlist trống.</p>';
                }
                ?>
            </div>

            <script>
            // Truyền biến PHP sang JavaScript
            const wishlistHasItems = <?php echo json_encode($wishlist_has_items); ?>;
            </script>
        </div>



        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const detailFavorite = document.querySelector('.detail-favorite');
            const wishlistContainer = document.querySelector('.wishlist-container');

            // Kiểm tra nếu có sản phẩm trong wishlist
            if (wishlistHasItems) {
                // Nếu có sản phẩm, ẩn phần detail-favorite và hiện wishlist-container
                detailFavorite.style.display = 'none';
                wishlistContainer.style.display = 'block';
            } else {
                // Nếu không có sản phẩm, ẩn wishlist-container và hiện detail-favorite
                wishlistContainer.style.display = 'none';
                detailFavorite.style.display = 'block';
            }

        });

        function removeFromWishlist(element) {
            const productId = element.getAttribute('data-product-id'); // Lấy product_id từ data attribute

            // Thêm lớp hiệu ứng
            element.classList.add('heart-effect');

            // Gửi yêu cầu AJAX để xóa sản phẩm khỏi wishlist
            fetch('remove_from_wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: productId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Thêm hiệu ứng fade out trước khi xóa sản phẩm
                        const productItem = element.closest('.product-item');
                        productItem.style.transition = 'opacity 1s'; // Thêm hiệu ứng chuyển tiếp
                        productItem.style.opacity = '0'; // Thay đổi độ mờ thành 0

                        // Sau khi hiệu ứng hoàn tất, xóa sản phẩm khỏi giao diện
                        setTimeout(() => {
                            productItem.remove();
                            // Cập nhật lại trạng thái wishlist nếu cần
                            if (document.querySelectorAll('.product-item').length === 0) {
                                document.querySelector('.detail-favorite').style.display = 'block';
                                document.querySelector('.wishlist-container').style.display = 'none';
                            }
                        }, 300); // Thời gian trễ tương ứng với thời gian hiệu ứng
                    } else {
                        alert('Có lỗi xảy ra, không thể xóa sản phẩm.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
        </script>

        <style>
        .heart-effect {
            animation: heartScale 1s ease forwards;
            /* Thời gian hiệu ứng 0.5 giây */
        }

        @keyframes heartScale {
            0% {
                transform: scale(1);
                /* Bắt đầu từ kích thước gốc */
            }

            50% {
                transform: scale(0.5);
                /* Thu nhỏ còn 50% */
                color: white;
                /* Chuyển sang màu trắng */
            }

            100% {
                transform: scale(1);
                /* Phóng to lại kích thước gốc */
            }
        }
        </style>



        <script>
        document.getElementById('heart-btn').onclick = function() {
            const heartBox = document.getElementById('heart-box');

            // Lưu trạng thái vào localStorage
            localStorage.setItem('heartBoxOpen', 'true');

            // Thay đổi URL mà không reload trang
            history.replaceState(null, null, window.location.href);

            // Reload trang
            window.location.reload();
        };

        // Kiểm tra trạng thái trong localStorage khi trang được tải
        document.addEventListener('DOMContentLoaded', function() {
            const heartBoxOpen = localStorage.getItem('heartBoxOpen');

            if (heartBoxOpen === 'true') {
                const heartBox = document.getElementById('heart-box');
                heartBox.style.display = 'block'; // Hiển thị box
                setTimeout(() => {
                    heartBox.classList.add('show'); // Thêm class 'show'
                }, 10); // Thêm một độ trễ nhỏ để đảm bảo CSS transition hoạt động

                // Xóa trạng thái sau khi đã mở box
                localStorage.removeItem('heartBoxOpen');
            }
        });

        function closeHeartBox() {
            const heartBox = document.getElementById('heart-box');
            heartBox.classList.remove('show'); // Xóa class 'show'

            // Thêm hiệu ứng ẩn box
            setTimeout(() => {
                heartBox.style.display = 'none'; // Ẩn box sau khi hiệu ứng hoàn tất
                window.location.reload(); // Reload trang
            }, 300); // Thời gian tương ứng với thời gian chuyển tiếp
        }
        </script>




        <!-- HTML -->
        <div id="search-box-container" class="hidden">
            <div class="search-box-content">
                <input type="text" class="search-input" placeholder="Nhập truy vấn tìm kiếm..." id="search-input">

                <img style="cursor: pointer;" src="../image/seraph.png" class="search-img"
                    onclick="window.location.href='../guest/guest.php'">
                <span class="cancel-btn" id="cancel-search-btn">Hủy</span>
            </div>

            <!-- <div id="popular-search-box" class="suggestion-box">
                <h4>Từ khóa tìm kiếm phổ biến</h4>
                <ul id="popular-keywords-list"></ul>
            </div> -->
        </div>

        <div id="search-results" style="display:none;"></div>

        <script>
        // Xử lý sự kiện Enter trong ô tìm kiếm
        document.getElementById('search-input').addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Ngăn chặn hành động mặc định của phím Enter
                const query = this.value.trim(); // Lấy và loại bỏ khoảng trắng của từ khóa
                if (query) {
                    // Gửi từ khóa đến record_search.php để lưu vào cơ sở dữ liệu
                    fetch('../guest/search_suggestions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `keyword=${encodeURIComponent(query)}`
                    }).catch(error => console.error('Error recording search keyword:', error));

                    // Điều hướng đến trang tìm kiếm
                    window.location.href = `search.php?search=${encodeURIComponent(query)}`;
                }
            }
        });

        // Hiển thị các từ khóa phổ biến khi tải trang
        window.onload = function() {
            fetch('../guest/popular_keywords.php')
                .then(response => response.json())
                .then(data => {
                    const listContainer = document.getElementById('popular-keywords-list');
                    if (data.length > 0) {
                        data.forEach(item => {
                            const listItem = document.createElement('li');
                            listItem.classList.add('suggestion-item');
                            listItem.textContent = item;

                            // Tạo hành động khi nhấp vào từ khóa
                            listItem.onclick = function() {
                                document.getElementById('search-input').value = item;
                                document.getElementById('search-results').style.display =
                                    'none'; // Ẩn kết quả gợi ý
                            };
                            listContainer.appendChild(listItem);
                        });
                    } else {
                        listContainer.innerHTML = '<li>Không có từ khóa phổ biến.</li>';
                    }
                })
                .catch(error => console.error('Error fetching popular keywords:', error));
        };
        </script>






    </header>





    <script>
    const menuBtn = document.getElementById('menu-btn'); // Lấy phần tử menu-btn
    const navbarWrapper = document.querySelector('.navbar-wrapper'); // Lấy navbar-wrapper

    // Bắt sự kiện click vào menu-btn
    menuBtn.addEventListener('click', () => {
        // Kiểm tra xem navbar có đang ẩn không
        if (navbarWrapper.classList.contains('hidden-navbar')) {
            navbarWrapper.classList.remove('hidden-navbar'); // Hiện navbar
        } else {
            navbarWrapper.classList.add('hidden-navbar'); // Ẩn navbar
        }
    });

    window.addEventListener('scroll', function() {
        const scrollPosition = window.scrollY;
        const header = document.getElementById('header');

        // Thêm lớp 'scrolled' cho header khi cuộn xuống
        if (scrollPosition > 10) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
    </script>




    <script type="text/javascript">
    // Lấy biểu tượng tìm kiếm và ô tìm kiếm
    const searchBtn = document.getElementById('search-btn');
    const searchBoxContainer = document.getElementById('search-box-container');
    const cancelSearchBtn = document.getElementById(
        'cancel-search-btn'); // Đổi tên biến để trỏ đến nút cancel trong hộp tìm kiếm

    // Thêm sự kiện click vào biểu tượng tìm kiếm
    searchBtn.addEventListener('click', function() {
        // Hiển thị hộp tìm kiếm khi nhấn vào biểu tượng tìm kiếm
        searchBoxContainer.classList.remove('hidden');
        searchBoxContainer.classList.add('active'); // Thêm lớp active để hiển thị
        cancelSearchBtn.style.display = 'block'; // Hiển thị nút cancel
    });

    // Thêm sự kiện click vào nút Cancel bên trong hộp tìm kiếm
    cancelSearchBtn.addEventListener('click', function() {
        // Thêm lớp hide để bắt đầu hiệu ứng giảm độ trong suốt
        searchBoxContainer.classList.remove('active'); // Loại bỏ lớp active
        searchBoxContainer.classList.add('hide'); // Thêm lớp hide để bắt đầu hiệu ứng

        // Đặt thời gian chờ để loại bỏ lớp hide sau khi hiệu ứng hoàn tất
        setTimeout(() => {
            searchBoxContainer.classList.remove('hide'); // Loại bỏ lớp hide
            searchBoxContainer.classList.add('hidden'); // Thêm lại lớp hidden
            cancelSearchBtn.style.display = 'none'; // Ẩn nút cancel
        }, 500); // Thời gian trùng khớp với thời gian chuyển tiếp trong CSS
    });
    </script>

    <script type="text/javascript">
    // Lắng nghe sự kiện khi trình duyệt đã tải xong
    document.addEventListener("DOMContentLoaded", function() {
        // Lấy ra nút "Đăng xuất"
        var logoutButton = document.querySelector(".logout-btn");

        // Gán sự kiện click cho nút "Đăng xuất"
        logoutButton.addEventListener("click", function() {
            // Chuyển hướng đến trang đăng nhập chung khi nhấn vào nút "Đăng xuất"
            window.location.href = "../components/login.php";
        });
    });

    const userBtn = document.getElementById('user-btn');
    const userBox = document.querySelector('.user-box');

    userBtn.addEventListener('click', function() {
        userBox.classList.toggle('active'); // Hiển thị hoặc ẩn user-box khi nhấn vào biểu tượng
    });
    </script>

    <script>
    window.onscroll = function() {
        var header = document.querySelector(".header");
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 10) {
            header.classList.add("scrolled");
        } else {
            header.classList.remove("scrolled");
        }
    };
    </script>
    <script>
    let lastScroll = 0;
    const header = document.querySelector('.header');
    const headerHeight = header.offsetHeight; // Chiều cao của header
    let transformY = 0; // Vị trí hiện tại của header trên trục Y

    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        const scrollDiff = currentScroll - lastScroll; // Tính khoảng cách cuộn

        // Điều chỉnh vị trí của header dựa trên khoảng cách cuộn
        if (scrollDiff > 0) {
            // Cuộn xuống -> đẩy header lên
            transformY = Math.max(transformY - scrollDiff, -headerHeight);
        } else {
            // Cuộn lên -> đẩy header xuống
            transformY = Math.min(transformY - scrollDiff, 0);
        }

        // Áp dụng vị trí mới cho header
        header.style.transform = `translateY(${transformY}px)`;

        // Cập nhật vị trí cuộn cuối cùng
        lastScroll = currentScroll;
    });
    </script>



</body>

</html>