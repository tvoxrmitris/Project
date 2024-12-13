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


                <!-- <div class="slideBox">
                <div class="textBox">
                </div>
                <div class="imgBox">
                    <img src="../image/diorbanner.png" alt="">
                </div>
            </div> -->

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
        <div class="line2"></div>

        <div class="box-container">
            <?php
            $category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

            // Lấy ngày hiện tại
            $current_date = date('Y-m-d');

            $query = "
    SELECT 
        p.product_id, 
        p.product_name, 
        MIN(p.product_price) AS product_price, 
        p.color_name, 
        pp.discount_percent, 
        (MIN(p.product_price) * (1 - IFNULL(pp.discount_percent, 0) / 100)) AS final_price,
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
        AND p.product_subcategory = '$category'
    GROUP BY 
        p.product_name, p.color_name
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
                                <i class="fas fa-heart"></i>
                            </div>


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
                        <input type="hidden" name="product_image" value="<?php echo $fetch_products['product_image']; ?>">
                    </form>
            <?php
                }
            } else {
                echo '<p class="empty">Chưa có sản phẩm nào được thêm</p>';
            }
            ?>
        </div>




        <script>
            document.querySelectorAll('.heart-icon').forEach((icon) => {
                icon.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productId = this.closest('.img-container').querySelector('.add-to-cart')
                        .getAttribute('data-id');
                    const isActive = this.classList.contains('active');
                    const action = isActive ? 'remove' : 'add';

                    // Thêm hiệu ứng thu nhỏ
                    this.style.transform = 'scale(0.9)';

                    // Sau khi thu nhỏ, phóng to lại
                    setTimeout(() => {
                        this.style.transform = 'scale(1.1)';
                    }, 150); // 150ms để tạo cảm giác thu nhỏ trước khi phóng to

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
                                this.classList.toggle(
                                    'active'); // Chuyển đổi màu khi trạng thái thay đổi
                            } else {
                                console.error(data.message);
                            }
                        })
                        .catch((error) => console.error('Lỗi:', error));
                });
            });
        </script>





    </section>








    <script src="https://www.gstatic.com/dialogflow-console/fast/messenger/bootstrap.js?v=1"></script>
    <df-messenger chat-title="Xin chào!" agent-id="9b3c9d53-e2a3-42da-a61c-e036c32c8aa2" language-code="en">
    </df-messenger>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
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
                        location.reload(); // Tự động tải lại trang khi thành công
                    },
                    error: function() {
                        console.error(
                            'Có lỗi xảy ra, vui lòng thử lại!'
                        ); // Hiển thị lỗi trong console nếu xảy ra
                    }
                });
            });
        });
    </script>



    <?php include '../user/footer.php' ?>

    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script> -->
    <script type="text/javascript" src="../js/script2.js"></script>
</body>

</html>