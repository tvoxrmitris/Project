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



// Kiểm tra nếu có product_id được gửi qua URL
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    // Kiểm tra xem session 'shopping_session' đã tồn tại hay chưa, nếu chưa thì khởi tạo
    if (!isset($_SESSION['shopping_session'])) {
        $_SESSION['shopping_session'] = [];
    }

    // Kiểm tra xem sản phẩm đã có trong session shopping_session chưa
    if (!in_array($product_id, array_column($_SESSION['shopping_session'], 'product_id'))) {
        // Thêm sản phẩm vào session shopping_session
        $_SESSION['shopping_session'][] = [
            'product_id' => $product_id,
            'quantity' => 1 // Số lượng mặc định là 1 khi mua ngay
        ];
    }

    // Redirect đến trang checkout hoặc một trang khác
    header("Location: checkout.php");
    exit();
}

?>



<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Barlow:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Lora:ital,wght@0,400..700;1,400..700&family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
    rel="stylesheet">



<style type="text/css">
<?php include '../CSS/main.css'
?>
</style>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=, initial-scale=1.0">
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
    <!-- <link rel="stylesheet" type="text/css" href="slick.css"> -->
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh@0.3"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs-core"></script>

    <link rel="stylesheet" type="text/css" href="../CSS/main.css?v=1.1 <?php echo time(); ?>">
    <title>Seraph Beauty - Shop</title>
</head>

<body>





    <!-- <div class="line3"></div> -->
    <?php include '../guest/header_guest.php' ?>



    <section class="shop">

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

        <style>
        .slider {
            position: relative;
            width: 100%;
            overflow: hidden;
        }

        .slideBox {
            display: none;
        }

        .slideBox.active {
            display: block;
        }

        .imgBox {
            text-align: center;
        }

        .imgBox img {
            object-fit: contain;
            /* Giữ nguyên cách hiển thị */
            bottom: 0;
            height: 70%;
            left: 0;
            right: 0;
            top: 0;
            width: 95%;
            padding-top: 1rem;

            /* Thêm bộ lọc để làm rõ nét */
            filter: brightness(1) contrast(1) saturate(1);

            /* Tăng độ sắc nét */
            image-rendering: -webkit-optimize-contrast;
            /* Cho Chrome */
            image-rendering: crisp-edges;
            /* Cho các trình duyệt khác */
            image-rendering: pixelated;
            /* Khi hình ảnh nhỏ */
        }

        /* Box thông báo tone trắng đen */
        .cart-notification {
            position: fixed;
            text-transform: none;
            /* Giữ nguyên kiểu chữ ban đầu */
            bottom: 20px;
            left: 20px;
            padding: 4px 10px;
            /* Giảm padding để hộp nhỏ hơn */
            background-color: #000;
            color: #fff;
            font-size: 14px;
            /* Giảm kích thước font */
            letter-spacing: 0.5px;
            /* Giảm khoảng cách chữ một chút */
            border-radius: 3px;
            /* Giảm bo góc */
            border: 1px solid #fff;
            /* Đường viền mỏng hơn */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            /* Giảm đổ bóng cho hiệu ứng nhẹ nhàng hơn */
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.4s ease, transform 0.4s ease;
            z-index: 1000;
        }

        /* Hiệu ứng xuất hiện */
        .cart-notification.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Hiệu ứng hover tinh tế */
        .cart-notification.show:hover {
            background-color: #fff;
            color: #000;
            border-color: #000;
        }
        </style>


        <script type="text/javascript">
        $('.img-about').slick({
            // dots: true,
            infinite: true,
            autoplay: true,
            autoplaySpeed: 2000,
            lazyLoad: 'ondemand',
            // speed: 300,
            slidesToShow: 1,
            adaptiveHeight: true
        });
        </script>
        <div class="banner-container">
            <div class="slider">
                <div class="slideBox active">
                    <div class="textBox">
                    </div>
                    <div class="imgBox">
                        <img src="../image/bannerfenty.png" alt="">
                    </div>
                </div>



            </div>

        </div>

        <?php
        // Trích xuất tham số category và sort từ URL
        $category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
        $sort_by = isset($_GET['sort']) ? mysqli_real_escape_string($conn, $_GET['sort']) : 'name';

        // Xác định thứ tự sắp xếp
        $sort_query = '';
        switch ($sort_by) {
            case 'price_asc':
                $sort_query = 'ORDER BY product_price ASC';
                break;
            case 'price_desc':
                $sort_query = 'ORDER BY product_price DESC';
                break;
            case 'name_asc':
                $sort_query = 'ORDER BY product_name ASC';
                break;
            case 'name_desc':
                $sort_query = 'ORDER BY product_name DESC';
                break;
            default:
                $sort_query = 'ORDER BY product_name ASC'; // Mặc định sắp xếp theo tên sản phẩm
        }

        $category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

        // Tính tổng số lượng sản phẩm trong subcategory đang được chọn
        $total_products_query = "
    SELECT COUNT(*) AS total_products
    FROM products
    WHERE product_subcategory = '$category'
";
        $result = mysqli_query($conn, $total_products_query);
        $row = mysqli_fetch_assoc($result);
        $total_products = $row['total_products'] ? $row['total_products'] : 0;

        $category = mysqli_real_escape_string($conn, $category);

        // Query to fetch distinct subcategories based on the selected category
        $query = "SELECT DISTINCT product_subcategory FROM products WHERE product_subcategory = '$category'";
        $result = mysqli_query($conn, $query);

        ?>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../guest/guest.php">Trang chủ</a></li>
                <?php
                // Kiểm tra xem tham số 'category' có trong URL không
                if (isset($_GET['category'])) {
                    $category = htmlspecialchars($_GET['category']); // Lấy và làm sạch dữ liệu
                    $category = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : 'Default Category'; // Thay 'Default Category' bằng giá trị mặc định nếu không có category
                    $currentPage = htmlspecialchars($_SERVER['PHP_SELF']) . '?category=' . urlencode($category);

                    echo '<li class="breadcrumb-item active" aria-current="page"><a href="' . $currentPage . '">' . $category . '</a></li>';
                }
                ?>
            </ol>
        </nav>
        <div class="subcategory">
            <?php
            // Kiểm tra xem truy vấn có thành công không
            if ($result) {
                // Lấy kết quả dưới dạng mảng kết hợp
                $subcategories = mysqli_fetch_all($result, MYSQLI_ASSOC);

                // Kiểm tra nếu có danh mục phụ
                if (!empty($subcategories)) {
                    // Luôn luôn hiển thị trường product_subcategory
                    foreach ($subcategories as $row) {
                        // Giả sử 'product_subcategory' là tên trường
                        echo htmlspecialchars($row['product_subcategory']) . '<br>';
                    }
                } else {
                    // Nếu không có danh mục phụ, hiển thị $category
                    echo htmlspecialchars($category);
                }
            } else {
                echo 'Lỗi truy vấn cơ sở dữ liệu.';
            }

            // Giải phóng kết quả
            mysqli_free_result($result);
            ?>
        </div>





        <!-- Dropdown Sắp Xếp -->
        <div class="border-wrapper">
            <div class="total-quantity">
                <p><?php echo number_format($total_products, 0, '.', '.'); ?> Sản Phẩm</p>
            </div>
            <div class="filters">
                <span id="filter-toggle">Bộ lọc</span> <!-- Thêm ID để xử lý sự kiện click -->
                <img src="../image/icons/filter.png" id="filter-icon">
            </div>

            <div class="sort-by">
                <form method="get" action="">
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                    <select name="sort" id="sort" onchange="this.form.submit()">
                        <option value="name_asc" <?php echo ($sort_by == 'name_asc') ? 'selected' : ''; ?>>Tên (A-Z)
                        </option>
                        <option value="name_desc" <?php echo ($sort_by == 'name_desc') ? 'selected' : ''; ?>>Tên (Z-A)
                        </option>
                        <option value="price_asc" <?php echo ($sort_by == 'price_asc') ? 'selected' : ''; ?>>Giá (Thấp
                            đến Cao)</option>
                        <option value="price_desc" <?php echo ($sort_by == 'price_desc') ? 'selected' : ''; ?>>Giá (Cao
                            đến Thấp)</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Box bộ lọc -->


        <div class="line2"></div>





        <div class="box-container">
            <div id="filter-box" class="filter-box">
                <!-- Bộ lọc giá -->
                <article class="price-filter">
                    <summary onclick="toggleFilter(this)">Lọc theo giá </summary>
                    <form id="price-filter-form">
                        <div class="price-options">
                            <div class="price-option" data-price="300000" onclick="updatePriceFilter('300000')">
                                Dưới 300K
                            </div>
                            <div class="price-option" data-price="500000" onclick="updatePriceFilter('500000')">
                                Dưới 500K
                            </div>
                            <div class="price-option" data-price="1000000" onclick="updatePriceFilter('1000000')">
                                Dưới 1M
                            </div>
                            <div class="price-option" data-price="1500000" onclick="updatePriceFilter('1500000')">
                                Dưới 1.5M
                            </div>
                        </div>
                    </form>
                </article>

                <!-- Sản phẩm phổ biến -->
                <article class="price-filter">
                    <summary onclick="toggleFilter(this)">Sản phẩm phổ biến </summary>
                    <form id="popularity-filter-form">
                        <div class="price-options">
                            <div class="price-option" data-filter="bestseller"
                                onclick="updatePopularityFilter('bestseller')">
                                Sản phẩm bán chạy
                            </div>
                            <div class="price-option" data-filter="new" onclick="updatePopularityFilter('new')">
                                Sản phẩm mới
                            </div>
                            <div class="price-option" data-filter="favorite"
                                onclick="updatePopularityFilter('favorite')">
                                Sản phẩm được yêu thích nhất
                            </div>
                        </div>
                    </form>
                </article>
            </div>


            <script>
            // Hàm toggleFilter để bật/tắt hiển thị các tùy chọn trong form tương ứng
            function toggleFilter(summaryElement) {
                const article = summaryElement.closest('.price-filter');
                const formOptions = article.querySelector('.price-options');
                const summary = article.querySelector('summary');

                summary.classList.toggle('active');

                if (summary.classList.contains('active')) {
                    formOptions.style.display = 'flex';
                    formOptions.style.opacity = '0';
                    formOptions.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        formOptions.style.opacity = '1';
                        formOptions.style.transform = 'translateY(0)';
                        formOptions.style.transition = 'all 0.3s ease';
                    }, 10);
                } else {
                    formOptions.style.opacity = '0';
                    formOptions.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        formOptions.style.display = 'none';
                    }, 300);
                }
            }

            // Hàm xử lý khi người dùng chọn một mức giá
            function updatePriceFilter(price) {
                const urlParams = new URLSearchParams(window.location.search);
                const currentPrice = urlParams.get('price');
                const selectedOptions = document.querySelectorAll('.price-option');

                if (currentPrice === price) {
                    // Nếu giá trị đã được chọn, loại bỏ bộ lọc và highlight
                    urlParams.delete('price');
                    selectedOptions.forEach(option => {
                        option.classList.remove('active'); // Xóa highlight
                    });
                } else {
                    // Nếu chưa được chọn, thêm hoặc cập nhật giá trị
                    urlParams.set('price', price);
                    selectedOptions.forEach(option => {
                        const optionPrice = option.getAttribute('data-price');
                        if (optionPrice === price) {
                            option.classList.add('active'); // Highlight tùy chọn mới
                        } else {
                            option.classList.remove('active'); // Xóa highlight tùy chọn khác
                        }
                    });
                }

                // Lưu vị trí scroll hiện tại
                localStorage.setItem('scrollPosition', window.scrollY);

                // Reload trang với tham số URL đã cập nhật
                window.location.href = `${window.location.pathname}?${urlParams.toString()}`;
            }

            // Hàm xử lý khi người dùng chọn bộ lọc phổ biến
            function updatePopularityFilter(filterType) {
                const urlParams = new URLSearchParams(window.location.search);
                const currentFilter = urlParams.get('filter');
                const selectedOptions = document.querySelectorAll('.price-option[data-filter]');

                if (currentFilter === filterType) {
                    // Nếu giá trị đã được chọn, loại bỏ bộ lọc và highlight
                    urlParams.delete('filter');
                    selectedOptions.forEach(option => {
                        option.classList.remove('active'); // Xóa highlight
                    });
                } else {
                    // Nếu chưa được chọn, thêm hoặc cập nhật giá trị
                    urlParams.set('filter', filterType);
                    selectedOptions.forEach(option => {
                        const optionFilter = option.getAttribute('data-filter');
                        if (optionFilter === filterType) {
                            option.classList.add('active'); // Highlight tùy chọn mới
                        } else {
                            option.classList.remove('active'); // Xóa highlight tùy chọn khác
                        }
                    });
                }

                // Lưu vị trí scroll hiện tại
                localStorage.setItem('scrollPosition', window.scrollY);

                // Reload trang với tham số URL đã cập nhật
                window.location.href = `${window.location.pathname}?${urlParams.toString()}`;
            }

            // Khôi phục trạng thái sau khi reload
            window.addEventListener('load', () => {
                const urlParams = new URLSearchParams(window.location.search);

                // Lấy giá trị mức giá được chọn
                const selectedPrice = urlParams.get('price');
                const selectedPriceOptions = document.querySelectorAll('.price-option[data-price]');
                selectedPriceOptions.forEach(option => {
                    const price = option.getAttribute('data-price');
                    if (price === selectedPrice) {
                        option.classList.add('active');
                    } else {
                        option.classList.remove('active');
                    }
                });

                // Lấy giá trị bộ lọc phổ biến được chọn
                const selectedFilter = urlParams.get('filter');
                const selectedFilterOptions = document.querySelectorAll('.price-option[data-filter]');
                selectedFilterOptions.forEach(option => {
                    const filter = option.getAttribute('data-filter');
                    if (filter === selectedFilter) {
                        option.classList.add('active');
                    } else {
                        option.classList.remove('active');
                    }
                });

                // Lấy và khôi phục vị trí scroll
                const scrollPosition = localStorage.getItem('scrollPosition');
                if (scrollPosition) {
                    window.scrollTo(0, parseInt(scrollPosition));
                }
            });
            </script>



            <?php
            $category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
            $filter = isset($_GET['filter']) ? mysqli_real_escape_string($conn, $_GET['filter']) : '';
            $max_price = isset($_GET['price']) ? intval($_GET['price']) : 0;
            $current_date = date('Y-m-d');

            // Nếu chọn "Sản phẩm bán chạy"
            $bestseller_condition = "";
            if ($filter === 'bestseller') {
                // Lấy 10 product_id có purchase_amount lớn nhất
                $query_bestsellers = "
        SELECT product_id 
        FROM purchases 
        ORDER BY purchase_amout DESC 
        LIMIT 5
    ";
                $result_bestsellers = mysqli_query($conn, $query_bestsellers) or die('Truy vấn bestseller thất bại: ' . mysqli_error($conn));

                $bestseller_ids = [];
                while ($row = mysqli_fetch_assoc($result_bestsellers)) {
                    $bestseller_ids[] = $row['product_id'];
                }

                // Tạo điều kiện WHERE để lọc theo product_id của bảng bestseller
                if (!empty($bestseller_ids)) {
                    $bestseller_ids = implode(',', $bestseller_ids);
                    $bestseller_condition = "AND p.product_id IN ($bestseller_ids)";
                }
            }
            // Truy vấn sản phẩm với điều kiện lọc giá
            $query = "
SELECT 
    p.product_id, 
    p.product_name, 
    p.product_price, 
    p.color_name, 
    pp.discount_percent, 
    -- Tính giá đã giảm nếu có khuyến mãi
    IFNULL((p.product_price * (1 - pp.discount_percent / 100)), p.product_price) AS final_price,
    p.product_image
FROM 
    products p
LEFT JOIN 
    product_promotion pp 
ON 
    p.product_id = pp.product_id  -- So sánh product_id giữa bảng products và bảng product_promotion
    AND NOW() BETWEEN pp.start_date AND pp.end_date  -- Chỉ áp dụng khuyến mãi còn hiệu lực
WHERE 
    p.status = 'Còn hàng'
    " . ($max_price > 0 ? "AND p.product_price <= $max_price" : "") . "
    AND p.product_subcategory = '$category'
    $bestseller_condition
GROUP BY 
    p.product_name, p.color_name
";

            $select_products = mysqli_query($conn, $query) or die(mysqli_error($conn));


            // Hiển thị sản phẩm
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
                <div class="img-container">
                    <?php
                            $is_in_wishlist = false;

                            // Kiểm tra nếu session wishlist tồn tại và chứa sản phẩm này
                            if (isset($_SESSION['wishlist'])) {
                                foreach ($_SESSION['wishlist'] as $item) {
                                    if ($item['product_id'] == $fetch_products['product_id']) {
                                        $is_in_wishlist = true;
                                        break;
                                    }
                                }
                            }
                            ?>

                    <div class="heart-icon <?php echo $is_in_wishlist ? 'active' : ''; ?>"
                        data-id="<?php echo $fetch_products['product_id']; ?>">
                        <!-- <i class="fas fa-heart"></i> -->
                    </div>
                    <!-- Thông báo yêu thích -->
                    <div class="wishlist-notification">
                        Sản phẩm đã được thêm vào yêu thích!
                    </div>
                    <style>
                    .wishlist-notification {
                        opacity: 0;
                        position: fixed;
                        bottom: 20px;
                        left: 20px;
                        background-color: #000;
                        color: #fff;
                        padding: 15px 30px;
                        border: 2px solid #000000;
                        border-radius: 8px;
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
                        font-size: 16px;
                        font-weight: bold;
                        z-index: 1000;
                        transform: translateY(30px);
                        transition: transform 0.3s ease, opacity 0.3s ease;
                    }

                    /* Hiệu ứng xuất hiện từ từ từ dưới lên */
                    .wishlist-notification.show {
                        opacity: 1;
                        transform: translateY(0);
                    }
                    </style>

                    <?php if (!empty($fetch_products['discount_percent']) && $fetch_products['discount_percent'] > 0) { ?>
                    <div class="discount-badge">
                        -<?php echo $fetch_products['discount_percent']; ?>%
                    </div>
                    <?php } ?>
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

                    <a href="#" class="add-to-cart" data-id="<?php echo $product_id; ?>">Thêm vào giỏ hàng</a>
                    <a href="../components/login.php?product_id=<?php echo $product_id; ?>&redirect=checkout"
                        class="buy_now" data-id="<?php echo $product_id; ?>">Mua ngay</a>
                </div>
                <div id="cart-notification" class="cart-notification">
                    Sản phẩm đã được thêm vào giỏ hàng!
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
            </form>

            <?php
                }
            } else {
                echo '<p class="empty">Chưa có sản phẩm nào được thêm.</p>';
            }
            ?>
        </div>


        <script>
        // Xử lý khi tải trang
        window.addEventListener('load', function() {
            const filterBox = document.getElementById('filter-box');
            const boxContainer = document.querySelector('.box-container');
            const isFilterBoxOpen = localStorage.getItem('filterBoxOpen') ===
                'true'; // Lấy trạng thái từ localStorage

            if (isFilterBoxOpen) {
                // Khôi phục trạng thái mở
                filterBox.style.display = 'block';
                filterBox.classList.add('open');
                boxContainer.classList.add('shift-right');
            } else {
                // Đảm bảo trạng thái ban đầu
                filterBox.style.display = 'none';
                filterBox.classList.remove('open');
                boxContainer.classList.remove('shift-right');
            }
        });

        // Xử lý toggle mở/đóng filter-box
        document.getElementById('filter-toggle').addEventListener('click', function() {
            const filterBox = document.getElementById('filter-box');
            const boxContainer = document.querySelector('.box-container');

            if (!filterBox.classList.contains('open')) {
                // Mở filter-box
                filterBox.style.display = 'block'; // Hiển thị trước để hiệu ứng hoạt động
                setTimeout(() => {
                    filterBox.classList.add('open'); // Thêm hiệu ứng trượt
                    boxContainer.classList.add('shift-right'); // Điều chỉnh bố cục
                }, 10);
                localStorage.setItem('filterBoxOpen', 'true'); // Lưu trạng thái mở
            } else {
                // Đóng filter-box
                filterBox.classList.remove('open');
                boxContainer.classList.remove('shift-right'); // Khôi phục bố cục ban đầu
                setTimeout(() => {
                    filterBox.style.display = 'none'; // Ẩn filter-box
                }, 300); // Trùng khớp thời gian với `transition` của CSS
                localStorage.setItem('filterBoxOpen', 'false'); // Lưu trạng thái đóng
            }
        });
        </script>





        <script>
        document.querySelectorAll('.heart-icon').forEach((icon) => {
            icon.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-id');
                const isActive = this.classList.contains('active');
                const action = isActive ? 'remove' : 'add';

                // Thêm hiệu ứng thu nhỏ
                this.style.transform = 'scale(0.9)';

                // Sau khi thu nhỏ, phóng to lại
                setTimeout(() => {
                    this.style.transform = 'scale(1.1)';
                }, 150);

                fetch('../guest/add_wishlist.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            product_id: productId,
                            action: action
                        })
                    })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            this.classList.toggle('active');

                            // Tạo thông báo mới
                            const notification = document.createElement('div');
                            notification.className = 'wishlist-notification';
                            notification.innerText = isActive ?
                                'Sản phẩm đã được xóa khỏi yêu thích!' :
                                'Sản phẩm đã được thêm vào yêu thích!';

                            // Thêm thông báo vào DOM
                            document.body.appendChild(notification);

                            // Sử dụng setTimeout để kích hoạt hiệu ứng
                            setTimeout(() => {
                                notification.classList.add('show');
                            }, 10);

                            // Tự động ẩn thông báo sau 3 giây và xóa khỏi DOM
                            setTimeout(() => {
                                notification.classList.remove('show');
                                setTimeout(() => {
                                    document.body.removeChild(notification);
                                }, 300);
                            }, 3000);
                        } else {
                            console.error(data.message);
                        }
                    })
                    .catch((error) => console.error('Lỗi:', error));
            });
        });
        </script>





    </section>









    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Kiểm tra xem có thông báo cần hiển thị sau khi reload trang hay không
        if (sessionStorage.getItem('showCartNotification') === 'true') {
            showCartNotification();
            sessionStorage.removeItem('showCartNotification'); // Xóa trạng thái sau khi hiển thị
        }

        // Thêm sự kiện cho nút "Thêm vào giỏ hàng"
        $('.add-to-cart').click(function(e) {
            e.preventDefault(); // Ngăn chặn hành vi mặc định của thẻ a

            var productId = $(this).data('id'); // Lấy product_id từ thuộc tính data-id

            $.ajax({
                url: '../guest/add_to_cart.php', // Đường dẫn đến tệp PHP xử lý thêm vào giỏ hàng
                method: 'GET',
                data: {
                    add: productId
                },
                success: function(response) {
                    // Đặt trạng thái hiển thị thông báo vào sessionStorage
                    sessionStorage.setItem('showCartNotification', 'true');
                    location.reload(); // Tự động tải lại trang khi thành công
                },
                error: function() {
                    console.error('Có lỗi xảy ra, vui lòng thử lại!');
                }
            });
        });

        // Hàm hiển thị box thông báo với hiệu ứng trượt từ dưới lên
        function showCartNotification() {
            var notification = $('#cart-notification');

            // Đặt display thành block trước khi thêm class để đảm bảo hiệu ứng hoạt động
            notification.css('display', 'block');

            // Thêm độ trễ nhỏ để đảm bảo hiệu ứng hoạt động đúng
            setTimeout(function() {
                notification.addClass('show');
            }, 10);

            // Ẩn box thông báo sau 3 giây
            setTimeout(function() {
                notification.removeClass('show');

                // Sau khi hiệu ứng kết thúc, đặt display lại thành none
                setTimeout(function() {
                    notification.css('display', 'none');
                }, 400); // 400ms trùng với thời gian chuyển động trong CSS
            }, 3000);
        }
    });
    </script>






    <?php include '../guest/footer.php' ?>

    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script> -->
    <script type="text/javascript" src="../js/script2.js"></script>
</body>

</html>