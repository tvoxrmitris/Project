<?php
include '../connection/connection.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Lấy số lượng sản phẩm yêu thích của người dùng
$wishlist_query = mysqli_query($conn, "SELECT * FROM `wishlist` WHERE user_id='{$_SESSION['user_id']}'");
$wishlist_num_rows = mysqli_num_rows($wishlist_query);

// Lấy số lượng sản phẩm trong giỏ hàng của người dùng
$cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id='{$_SESSION['user_id']}'");
$cart_num_rows = mysqli_num_rows($cart_query);

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

// Đóng kết nối
$stmt->close();



?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Barlow:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Lora:ital,wght@0,400..700;1,400..700&family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap');
</style>

<style type="text/css">
<?php include '../CSS/main.css'
?>
</style>

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
            onclick="window.location.href='../user/index.php'">
    </div>
    <div class="navbar-wrapper">
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
        echo '<a href="view_page.php?product_id=' . $productId . '">';
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
                            <a href="../user/subcategory.php?category=<?php echo urlencode($categories); ?>">
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
                                        <a href="view_page.php?product_id=<?php echo $product_id; ?>">
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
                            <a href="../user/subcategory.php?category=<?php echo urlencode($category); ?>">
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
                                        <a href="view_page.php?product_id=<?php echo $product_id; ?>">
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
                <a href="bodycare.php" class="dropdown-toggle">Chăm Sóc Da</a>
                <div class="dropdown-menu">
                    <?php foreach ($category_skincare as $categories): ?>
                    <a
                        href="subcategory.php?category=<?php echo urlencode($categories); ?>"><?php echo htmlspecialchars($categories); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="dropdown">
                <a href="haircare.php" class="dropdown-toggle">Chăm Sóc Tóc</a>
                <div class="dropdown-menu">
                    <?php foreach ($haircare as $categories): ?>
                    <a
                        href="subcategory.php?category=<?php echo urlencode($categories); ?>"><?php echo htmlspecialchars($categories); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>


            <div class="dropdown">
                <a href="../user/order.php" class="dropdown-toggle">Đơn hàng</a>
                <div class="dropdown-menu">
                    <!-- <a href="#">Đơn hàng của tôi</a>
                    <a href="#">Theo dõi đơn hàng</a> -->
                </div>
            </div>
            <!-- <div class="dropdown">
                <a href="../user/contact.php" class="dropdown-toggle">Liên hệ</a>
                <div class="dropdown-menu">
                    <a href="#">Hỗ trợ</a>
                    <a href="#">Gửi ý kiến</a>
                </div>
            </div> -->
        </nav>
    </div>
    <div class="vietnam">
        <img src="../image/vietnam.png" width="25">
        <p>Việt Nam</p>
    </div>
    <div class="icons">
        <i class="bi bi-search" id="search-btn"></i>
        <i class="bi bi-person" id="user-btn"></i>
        <!-- <a href="../user/wishlist.php"><i class="bi bi-heart"></i><sup><?php echo $wishlist_num_rows; ?></sup></a> -->
        <a href="../user/cart.php"><i class="bi bi-cart"></i><sup><?php echo $cart_num_rows; ?></sup></a>
        <!-- <i class="bi bi-list" id="menu-btn"></i> -->
        <div class="user-box">
            <p>Tên tài khoản: <span><?php echo $_SESSION['user_name']; ?></span></p>
            <p>Email: <span><?php echo $_SESSION['user_email']; ?></span></p>
            <form method="post" action="profile_user.php" class="btn-container">
                <button type="submit" name="profile" class="profile-btn">Hồ sơ</button>
                <button type="submit" name="logout" class="logout-btn">Đăng xuất</button>
            </form>
        </div>
    </div>
</header>






<div id="search-box-container" class="hidden">
    <div class="search-box-content">
        <input type="text" class="search-input" placeholder="Nhập truy vấn tìm kiếm..." id="search-input">

        <img style="cursor: pointer;" src="../image/seraph.png" class="search-img"
            onclick="window.location.href='../guest/guest.php'">
        <span class="cancel-btn" id="cancel-search-btn">Hủy</span>
    </div>

    <!-- <div id="popular-search-box" class="suggestion-box">
                <h4>Từ khóa tìm kiếm phổ biến</h4>
                <ul id="popular-keywords-list"></ul>div           </div> -->
</div>

<div id="search-results" style="display:none;"></div>

<script>
document.getElementById('search-input').addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault(); // Ngăn chặn hành động mặc định của phím Enter
        const query = this.value; // Lấy giá trị từ ô input
        if (query) {
            // Điều hướng đến guest_subcategory.php với tham số tìm kiếm
            window.location.href = `search.php?search=${encodeURIComponent(query)}`;
        }
    }
});
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

<script type="text/javascript">
// Lắng nghe sự kiện khi trình duyệt đã tải xong
document.addEventListener("DOMContentLoaded", function() {
    // Lấy ra nút "Đăng xuất"
    var logoutButton = document.querySelector(".logout-btn");

    // Gán sự kiện click cho nút "Đăng xuất"
    logoutButton.addEventListener("click", function() {
        // Chuyển hướng đến trang đăng nhập chung khi nhấn vào nút "Đăng xuất"
        window.location.href = "../guest/guest.php";
    });
});
</script>