<?php
include '../connection/connection.php';
// session_start();
$user_id = null; // Khởi tạo biến user_id
// Kiểm tra nếu người dùng đã đăng nhập
$is_logged_in = isset($_SESSION['user_id']);

if ($is_logged_in) {
    $wishlist_query = mysqli_query($conn, "SELECT * FROM `wishlist` WHERE user_id='{$_SESSION['user_id']}'");
    $wishlist_num_rows = mysqli_num_rows($wishlist_query);

    $cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id='{$_SESSION['user_id']}'");
    $cart_num_rows = mysqli_num_rows($cart_query);

    // Truy vấn để đếm số lượng sản phẩm trong giỏ hàng của người dùng
    $cart_query = mysqli_query($conn, "SELECT COUNT(*) AS cart_count FROM cart WHERE user_id = '{$_SESSION['user_id']}'") or die('Lỗi truy vấn: ' . mysqli_error($conn));
    $cart_result = mysqli_fetch_assoc($cart_query);
    $cart_num_rows = $cart_result['cart_count']; // Lấy giá trị số lượng sản phẩm
} else {
    $wishlist_num_rows = 0;
    $cart_num_rows = 0;
}

// Truy vấn để đếm số lượng sản phẩm trong giỏ hàng của người dùng
$cart_query = mysqli_query($conn, "SELECT COUNT(*) AS cart_count FROM cart WHERE user_id = '$user_id'") or die('Lỗi truy vấn: ' . mysqli_error($conn));
$cart_result = mysqli_fetch_assoc($cart_query);
$cart_num_rows = $cart_result['cart_count']; // Lấy giá trị số lượng sản phẩm

// Lấy thương hiệu từ cơ sở dữ liệu
$sql = "SELECT brand_name FROM brands";
$result = $conn->query($sql);

$brands = [];
if ($result->num_rows > 0) {
    // Lấy dữ liệu từ mỗi hàng
    while ($row = $result->fetch_assoc()) {
        $brands[] = $row['brand_name'];
    }
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
        <div class="navbar-wrapper hidden-navbar">
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
                                    <a href="#">Sản phẩm đang giảm giá</a>
                                    <a href="#">Chi tiết ưu đãi</a>
                                </div>
                            </div>
                            <div class="subcategory_sale">
                                <?php
                                // Lấy ngày hiện tại
                                $current_date = date('Y-m-d');

                                // Truy vấn SQL để lấy subcategory_name và subcategory_image từ bảng subcategory
                                $query = "
                    SELECT s.subcategory_name, s.subcategory_image 
                    FROM product_promotion pp
                    JOIN subcategory s ON pp.subcategory_name = s.subcategory_name
                    WHERE pp.start_date <= '$current_date' AND pp.end_date >= '$current_date'
                    GROUP BY s.subcategory_name, s.subcategory_image
                ";

                                $result = mysqli_query($conn, $query);

                                if (mysqli_num_rows($result) > 0) {
                                    // Hiển thị subcategory_name và subcategory_image
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo '<div class="subcategory-item">';

                                        echo '<img src="../image/subcategory/' . htmlspecialchars($row['subcategory_image']) . '" alt="' . htmlspecialchars($row['subcategory_name']) . '">';
                                        echo '<p>' . htmlspecialchars($row['subcategory_name']) . '</p>';
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





                <div class="dropdown">
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
                </div>



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

                                <video muted loop class="dropdown-video" data-video-src="../image/video/eyemakeup.mp4">
                                    <source src="../image/video/eyemakeup.mp4" type="video/mp4">
                                    Trình duyệt của bạn không hỗ trợ video.
                                </video>
                                <video muted loop class="dropdown-video" data-video-src="../image/video/eyemakeup1.mp4">
                                    <source src="../image/video/eyemakeup1.mp4" type="video/mp4">
                                    Trình duyệt của bạn không hỗ trợ video.
                                </video>

                            </div>
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
                    <a class="dropdown-toggle">Chăm Sóc Tóc</a>
                    <div class="dropdown-menu">
                        <?php foreach ($haircare as $categories): ?>
                        <a
                            href="../guest/guest_subcategory.php?category=<?php echo urlencode($categories); ?>"><?php echo htmlspecialchars($categories); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="dropdown">
                    <a href="../user/about.php" class="dropdown-toggle">Về chúng tôi</a>
                    <div class="dropdown-menu">
                        <a href="#">Giới thiệu</a>
                        <a href="#">Đội ngũ</a>
                        <a href="#">Lịch sử</a>
                    </div>
                </div>


            </nav>
        </div>
        <div class="vietnam">
            <img src="../image/vietnam.png" width="25">
            <p>Việt Nam</p>
        </div>

        <?php
        // Giả sử giỏ hàng của bạn là một mảng session
        $isCartEmpty = empty($_SESSION['cart']);
        ?>

        <div class="icons">

            <i class="bi bi-search" id="search-btn"></i>
            <i class="bi bi-person" id="user-btn"></i>
            <a href="cart_guest.php" id="cart-link">
                <i class="bi bi-cart"></i>
                <sup><?php echo $cart_num_rows; ?></sup>
            </a>

            <!-- Thêm box thông báo giỏ hàng trống -->
            <div id="empty-cart-overlay" style="display:none;">
                <div id="empty-cart-box">
                    <p>Giỏ hàng của bạn đang trống!</p>
                    <button type="button" onclick="closeEmptyCartBox()">Đóng</button>
                </div>
            </div>
            <script>
            // Chuyển giá trị PHP của trạng thái giỏ hàng trống sang JavaScript
            var isCartEmpty = <?php echo json_encode($isCartEmpty); ?>;

            // Bắt sự kiện click vào biểu tượng giỏ hàng
            document.getElementById('cart-link').addEventListener('click', function(event) {
                if (isCartEmpty) {
                    event.preventDefault(); // Ngăn không cho chuyển trang
                    document.getElementById('empty-cart-overlay').style.display =
                    'block'; // Hiển thị overlay và box thông báo
                }
            });

            // Hàm đóng box thông báo giỏ hàng rỗng
            function closeEmptyCartBox() {
                document.getElementById('empty-cart-overlay').style.display = 'none';
            }
            </script>

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

        <div id="search-box-container" class="hidden">
            <input type="text" class="search-input" placeholder="Nhập truy vấn tìm kiếm...">
            <i class="bi bi-search" id="searchh-btn"></i> <!-- Thêm biểu tượng tìm kiếm ở đây -->
            <img style="cursor: pointer;" src="../image/seraph.png" class="searhimg"
                onclick="window.location.href='../guest/guest.php'">
            <span class="cancel-btn" id="cancel-search-btn">Hủy</span>
        </div>





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

</body>

</html>