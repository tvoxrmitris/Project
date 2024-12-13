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
if(isset($_POST['add_to_wishlist'])){
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];

    // Kiểm tra xem sản phẩm đã tồn tại trong wishlist chưa
    $wishlist_number = mysqli_query($conn, "SELECT * FROM `wishlist` WHERE product_name='$product_name' AND user_id ='$user_id'") or die('query failed');

    $cart_num = mysqli_query($conn,"SELECT * FROM `cart` WHERE product_name='$product_name' AND user_id ='$user_id'") or die('query failed');

    if(mysqli_num_rows($wishlist_number) > 0){
        $message[]='Sản phẩm đã tồn tại trong danh sách yêu thích';
    } else if(mysqli_num_rows($cart_num) > 0){
        $message[]='Sản phẩm đã tồn tại trong giỏ hàng';
    } else {
        mysqli_query($conn, "INSERT INTO `wishlist`(`user_id`, `pid`, `product_name`, `product_price`, `product_image`) VALUES('$user_id', '$product_id', '$product_name', '$product_price', '$product_image')");
        $message[]='Sản phẩm đã được thêm vào danh sách yêu thích';
    }
}



//adding product in cart
if (isset($_GET['add'])) {
    // Lấy product_id từ chuỗi truy vấn
    $product_id = $_GET['add'];

    // Sử dụng câu lệnh chuẩn bị (prepared statement) để tránh lỗi SQL Injection
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $fetch_product = $result->fetch_assoc();

        $product_name = $fetch_product['product_name'];
        $product_image = explode(',', $fetch_product['product_image'])[0]; // Lấy hình ảnh đầu tiên
        $quantity = 1; // Số lượng mặc định thêm vào là 1

        // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
        $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $cart_result = $stmt->get_result();

        if ($cart_result->num_rows > 0) {
            // Nếu sản phẩm đã có trong giỏ hàng, cập nhật số lượng
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $message[] = 'Sản phẩm đã có trong giỏ hàng, số lượng đã được cập nhật!';
        } else {
            // Nếu sản phẩm chưa có trong giỏ hàng, thêm mới
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, product_name, quantity, product_image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisss", $user_id, $product_id, $product_name, $quantity, $product_image);
            $stmt->execute();
            $message[] = 'Sản phẩm đã được thêm vào giỏ hàng!';
        }
    } else {
        $message[] = 'Sản phẩm không tồn tại!';
    }
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
    <!-- Link Swiper's CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

    <link rel="stylesheet" type="text/css" href="../CSS/main.css?v=1.1 <?php echo time();?>">
    <title>Seraph Beauty - Cửa Hàng</title>
</head>

<body>





    <!-- <div class="line3"></div> -->
    <?php include '../guest/header_guest.php'?>



    <section class="shop">

        <?php
    if(isset($message)){
        foreach($message as $message){
            echo '
                <div class="message">
                    <span>' . $message . '</span>
                    <i class="bi bi-x-circle" onclick="this.parentElement.remove()"></i>
                </div>
            ';
        }
    }
    ?>


        <div class="line"></div>




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
                <li class="breadcrumb-item">
                    <a href="#" onclick="resetPage()">Trang điểm mắt</a>
                </li>
            </ol>
        </nav>

        <script>
        function resetPage() {
            // Tải lại trang hiện tại
            location.reload();
        }
        </script>


        <div class="subcategory">
            Son Môi và Kẻ Viền Môi<br></div>








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
    // Cập nhật truy vấn SQL để bao gồm điều kiện lọc và sắp xếp
    $category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : ''; // Di chuyển lên trước truy vấn
    $select_products = mysqli_query($conn, "SELECT * FROM products WHERE status = 'Còn hàng' AND (product_subcategory = 'Kẻ Viền Môi' OR product_subcategory = 'Son Môi') $sort_query") or die('Truy vấn thất bại: ' . mysqli_error($conn));


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
                    <!-- Thẻ a Thêm vào giỏ hàng -->
                    <a href="#" class="add-to-cart" data-id="<?php echo $product_id; ?>">Thêm vào giỏ hàng</a>
                    <a href="../components/login.php?product_id=<?php echo $product_id; ?>&redirect=checkout"
                        class="buy_now" data-id="<?php echo $product_id; ?>">Mua ngay</a>

                </div>

                <div class="priceshop">
                    <?php
                    // Hiển thị giá cho sản phẩm
                    echo number_format($fetch_products['product_price'], 0, '.', '.') . ' VNĐ';
                    ?>
                </div>
                <div class="detail">
                    <div class="name"><?php echo $fetch_products['product_name']; ?></div>
                    <div class="color">
                        <?php echo $fetch_products['color_name']; ?>

                        <?php
        // Truy vấn số lượng sản phẩm có tên giống nhau
        $product_name = $fetch_products['product_name'];
        $query_count = "SELECT COUNT(*) as total_same_name FROM products WHERE product_name = ?";
        $stmt_count = $conn->prepare($query_count);
        $stmt_count->bind_param("s", $product_name);
        $stmt_count->execute();
        $result_count = $stmt_count->get_result();
        $row_count = $result_count->fetch_assoc();
        $total_same_name = $row_count['total_same_name'];
        ?>

                        <!-- Hiển thị tổng số sản phẩm có tên giống nhau -->
                        <span class="same-name-count">(<?php echo $total_same_name; ?> màu)</span>
                    </div>
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

    <script>
    function selectCapacity(event, capacity) {
        var container = event.target.closest('.box');
        var capacityButtons = container.querySelectorAll('.capacity-buttons button');
        capacityButtons.forEach(function(button) {
            button.classList.remove('selected');
        });
        event.target.classList.add('selected');
        var selectedCapacity = capacity;
        container.querySelector('input[name="selectedCapacity"]').value =
            selectedCapacity; // Cập nhật giá trị dung tích cho trường input ẩn

        var productId = container.querySelector('input[name="product_id"]').value;
        // Gửi selectedCapacity đến server để lấy giá tương ứng từ cơ sở dữ liệu
        $.ajax({
            url: 'get_price.php',
            type: 'POST',
            data: {
                productId: productId,
                selectedCapacity: selectedCapacity
            },
            success: function(response) {
                container.querySelector('.priceshop').innerHTML = response;
            }
        });
    }
    </script>


    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Lấy tất cả các điểm đánh dấu
        var dots = document.querySelectorAll('.dot');

        // Lặp qua từng điểm đánh dấu và thêm sự kiện click
        dots.forEach(function(dot) {
            dot.addEventListener('click', function() {
                // Lấy chỉ số của điểm đánh dấu được nhấp
                var index = parseInt(dot.getAttribute('data-index'));

                // Lấy ID sản phẩm tương ứng
                var productId = dot.getAttribute('data-product-id');

                // Lấy container của sản phẩm
                var container = dot.closest('.box');

                // Lấy tất cả các ảnh của sản phẩm
                var images = container.querySelectorAll('.imgshop');

                // Ẩn tất cả các ảnh của sản phẩm và bỏ đi lớp 'active' khỏi các điểm đánh dấu
                images.forEach(function(image) {
                    image.classList.add('hidden');
                });
                dots.forEach(function(d) {
                    d.classList.remove('active');
                });

                // Hiển thị ảnh tương ứng với điểm đánh dấu được nhấp và thêm lớp 'active' cho điểm đánh dấu đó
                images[index].classList.remove('hidden');
                dot.classList.add('active');
            });
        });
    });
    </script>



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



    <?php include '../user/footer.php'?>

    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script> -->
    <script type="text/javascript" src="../js/script2.js"></script>
</body>

</html>