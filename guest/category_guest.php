<?php
include '../connection/connection.php';
session_start();

if(isset($_POST['logout'])){
    session_destroy();
    header('location:../components/login.php');
}

if(isset($_POST['add_to_wishlist'])){
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_image = $_POST['product_image'];

    $wishlist_number = mysqli_query($conn, "SELECT * FROM `wishlist` WHERE product_name='$product_name' AND user_id ='$user_id'") or die('query failed');

    $cart_num = mysqli_query($conn,"SELECT * FROM `cart` WHERE product_name='$product_name' AND user_id ='$user_id'") or die('query failed');

    if(mysqli_num_rows($wishlist_number) > 0){
        $message[]='Sản phẩm đã tồn tại trong danh sách yêu thích';
    }else if(mysqli_num_rows($cart_num)>0 && mysqli_num_rows($wishlist_number)>0){
        $message[]='Sản phẩm đã tồn tại trong giỏ hàng';
    }else{
        mysqli_query($conn, "INSERT INTO `wishlist`(`user_id`, `pid`, `product_name`, `product_price`, `product_image`) VALUES('$user_id', '$product_id', '$product_name', '$product_price', '$product_image')");
        $message[]='Sản phẩm đã được thêm thành công vào danh sách yêu thích';
    }
}

//adding product in cart
//adding product in cart
if(isset($_POST['add_to_cart'])){
$product_id = $_POST['product_id'];
$product_name = $_POST['product_name'];
$product_image = $_POST['product_image'];
$selectedCapacity = $_POST['selectedCapacity']; // Lấy dung tích đã chọn từ form

// Kiểm tra số lượng sản phẩm trong kho
$stock_query = mysqli_query($conn, "SELECT `qtyfor$selectedCapacity` AS quantity FROM products WHERE product_id='$product_id'");
$stock_row = mysqli_fetch_assoc($stock_query);
$stock_available = $stock_row['quantity'];

// Kiểm tra sản phẩm đã tồn tại trong giỏ hàng của người dùng chưa
$cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE product_name='$product_name' AND user_id ='$user_id'") or die('query failed');

// Lấy thông tin số lượng và giá sản phẩm từ cơ sở dữ liệu dựa trên dung tích đã chọn
$price_query = mysqli_query($conn, "SELECT `pricefor$selectedCapacity` AS price FROM products WHERE product_id='$product_id'");
$price_row = mysqli_fetch_assoc($price_query);
$product_price = $price_row['price'];

if(mysqli_num_rows($cart_query) > 0){
// Nếu sản phẩm đã tồn tại trong giỏ hàng, cập nhật thông tin sản phẩm
$cart_row = mysqli_fetch_assoc($cart_query);
$current_qty_column = 'qtyfor'.$selectedCapacity;
$current_price_column = 'pricefor'.$selectedCapacity;

// Tính toán số lượng mới và giá mới
$new_quantity = $cart_row[$current_qty_column] + 1;
$new_price = $cart_row[$current_price_column] + $product_price;

// Kiểm tra xem số lượng mới có vượt quá số lượng tồn kho không
if($new_quantity <= $stock_available) {
    // Cập nhật thông tin số lượng và giá của sản phẩm trong giỏ hàng
    mysqli_query($conn, "UPDATE `cart` SET `$current_qty_column`='$new_quantity', `$current_price_column`='$new_price' WHERE product_name='$product_name' AND user_id ='$user_id'");
    $message[] = 'Sản phẩm đã được cập nhật trong giỏ hàng';
} else {
    // Nếu số lượng mới vượt quá số lượng tồn kho, thông báo cho người dùng
    $message[] = 'Sản phẩm không đủ số lượng trong kho.';
}
} else {
// Nếu sản phẩm chưa tồn tại trong giỏ hàng, thêm sản phẩm mới
if($stock_available > 0) {
    // Kiểm tra xem còn hàng trong kho không
    mysqli_query($conn, "INSERT INTO `cart`(`user_id`, `pid`, `product_name`, `product_image`, `qtyfor$selectedCapacity`, `pricefor$selectedCapacity`) VALUES('$user_id', '$product_id', '$product_name', '$product_image', '1', '$product_price')");
    $message[] = 'Sản phẩm đã được thêm vào giỏ hàng';
} else {
    // Nếu hết hàng trong kho, thông báo cho người dùng
    $message[] = 'Sản phẩm không còn trong kho.';
}
}
}





// Kết nối cơ sở dữ liệu và các cài đặt khác

if (isset($_GET['search'])) {
    $search = $_GET['search'];
    // Thực hiện truy vấn để lấy sản phẩm từ cơ sở dữ liệu dựa trên từ khóa tìm kiếm
    $search_query = mysqli_query($conn, "SELECT * FROM `products` WHERE `product_name` LIKE '%$search%' OR `product_price` LIKE '%$search%' OR `product_detail` LIKE '%$search%'") or die('query failed');

    if (mysqli_num_rows($search_query) > 0) {
        // Hiển thị sản phẩm tìm kiếm
        while ($fetch_products = mysqli_fetch_assoc($search_query)) {
            // Hiển thị thông tin sản phẩm, ví dụ:
            echo '<div>';
            echo '<img src="../image/' . $fetch_products['product_image'] . '">';
            echo '<div>' . $fetch_products['product_name'] . '</div>';
            echo '<div>' . number_format($fetch_products['product_price'], 0, '.', '.') . 'VNĐ</div>';
            echo '</div>';
        }
    } else {
        echo '<p class="empty">Không tìm thấy sản phẩm phù hợp.</p>';
    }
} else {
    // echo '<p class="empty">Vui lòng nhập từ khóa để tìm kiếm sản phẩm.</p>';
}

// $category_name = "";
// if (isset($_GET['category_id'])) {
//     $category_id = $_GET['category_id'];

//     // Truy vấn để lấy tên thương hiệu từ ID
//     $select_category_name = $conn->prepare("SELECT category_name FROM categories WHERE category_id = ?");
//     $select_category_name->bind_param("i", $category_id);
//     $select_category_name->execute();
//     $result_category_name = $select_category_name->get_result();

//     // Kiểm tra và gán tên thương hiệu nếu có kết quả trả về từ cơ sở dữ liệu
//     if ($result_category_name->num_rows > 0) {
//         $category_name = $result_category_name->fetch_assoc()['category_name'];
//     }
// }

$category_name = "";
if (isset($_GET['category_id'])) {
    $category_id = $_GET['category_id'];

    // Truy vấn để lấy tên thương hiệu từ ID
    $select_category_name = $conn->prepare("SELECT category_name FROM categories WHERE category_id = ?");
    $select_category_name->bind_param("i", $category_id);
    $select_category_name->execute();
    $result_category_name = $select_category_name->get_result();

    // Kiểm tra và gán tên thương hiệu nếu có kết quả trả về từ cơ sở dữ liệu
    if ($result_category_name->num_rows > 0) {
        $category_name = $result_category_name->fetch_assoc()['category_name'];
    }
}
?>

<style type="text/css">
    <?php include '../CSS/main.css' ?>
</style>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="../CSS/main.css?v=1.1 <?php echo time();?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <!-- <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon"> -->
    <!-- Các phần head của bạn -->
</head>
<body>
    <?php include 'header_guest.php' ?>

    <div class="banner">
        <div class="detail">
            <h1 style="padding: 80px;">Danh mục <?php echo $category_name; ?></h1>
        </div>
    </div>

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
    <!-- Hiển thị sản phẩm theo thương hiệu -->
    <div class="box-container">
        <?php
        if (isset($_GET['category_id'])) {
            $category_id = $_GET['category_id'];

            // Lấy tên thương hiệu dựa trên ID thương hiệu đã chọn
            $select_categories = $conn->prepare("SELECT category_name FROM categories WHERE category_id = ?");
            $select_categories->bind_param("i", $category_id);
            $select_categories->execute();
            $result_categories = $select_categories->get_result();

            if ($result_categories->num_rows > 0) {
                $category_name = $result_categories->fetch_assoc()['category_name'];
                

                // Lấy sản phẩm liên quan đến tên thương hiệu đã chọn
                $status = 'Còn hàng';
                $select_products = $conn->prepare("SELECT * FROM `products` WHERE BINARY `category_name` = ?");
                $select_products->bind_param("s", $category_name);
                $select_products->execute();
                
                $result_products = $select_products->get_result();

                if ($result_products->num_rows > 0) {
                    while ($fetch_products = $result_products->fetch_assoc()) {
                        $image_names = explode(',', $fetch_products['product_image']);
                        $selectedCapacity = '10ml';                      
        ?>
                        <!-- Hiển thị thông tin sản phẩm -->
                        <form method="post" class="box">
                        <div class="img-container">
                <?php foreach ($image_names as $index => $image_name) { ?>
                <img class="imgshop <?php if ($index !== 0) echo 'hidden'; ?>" src="../image/<?php echo $image_name; ?>" data-index="<?php echo $index; ?> ">
            <?php } ?>
            <div class="status" style="z-index:1";><?php echo $status; ?></div>
                </div>

                <div class="dot-container" style="z-index:1";>
                    <?php for ($i = 0; $i < count($image_names); $i++) { ?>
                        <span class="dot <?php if ($i === 0) echo 'active'; ?>" data-index="<?php echo $i; ?>" data-product-id="<?php echo $fetch_products['product_id']; ?>"></span>
                    <?php } ?>
                </div>
                <div class="priceshop">
                    <?php
                        // Hiển thị giá cho dung tích 10ml
                        echo number_format($fetch_products['pricefor10ml'], 0, '.', '.') . 'VNĐ';
                    ?>
                </div>
                <div class="name" style="z-index:1";><?php echo $fetch_products['product_name']; ?></div>
                <div class="capacity-buttons" style="z-index:1";>
                    <button type="button" onclick="selectCapacity(event, '10ml')" data-capacity="10ml" <?php if($selectedCapacity == '10ml') echo 'class="selected"'; ?>>10ml</button>
                    <button type="button" onclick="selectCapacity(event, '50ml')" data-capacity="50ml" <?php if($selectedCapacity == '50ml') echo 'class="selected"'; ?>>50ml</button>
                    <button type="button" onclick="selectCapacity(event, '75ml')" data-capacity="75ml" <?php if($selectedCapacity == '75ml') echo 'class="selected"'; ?>>75ml</button>
                    <button type="button" onclick="selectCapacity(event, '100ml')" data-capacity="100ml" <?php if($selectedCapacity == '100ml') echo 'class="selected"'; ?>>100ml</button>
                    <input type="hidden" name="selectedCapacity" value="<?php echo $selectedCapacity; ?>">

                </div>

                <input type="hidden" name="product_id" value="<?php echo $fetch_products['product_id']; ?>">
                <input type="hidden" name="product_name" value="<?php echo $fetch_products['product_name']; ?>">
                <input type="hidden" name="product_image" value="<?php echo $fetch_products['product_image']; ?>">
                <div class="icon">
                    <a href="view_page_guest.php?pid=<?php echo $fetch_products['product_id'];?>" class="bi bi-eye-fill"></a>
                    <button type="submit" name="add_to_wishlist" class="bi bi-heart"></button>
                    <button type="submit" name="add_to_cart" class="bi bi-cart"></button>
                </div>
            </form>
        <?php
                    }
                } else {
                    echo "Không tìm thấy sản phẩm cho danh mục này.";
                }
            } else {
                echo "Không tìm thấy danh mục";
            }
        } else {
            echo "Không có ID danh mục được cung cấp.";
            var_dump($category_name);
        }
        ?>
    </div>

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
    container.querySelector('input[name="selectedCapacity"]').value = selectedCapacity; // Cập nhật giá trị dung tích cho trường input ẩn

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
    var dots = document.querySelectorAll('.dot');

// Lặp qua từng dấu chấm và gắn sự kiện click
dots.forEach(function(dot) {
    dot.addEventListener('click', function() {
        // Lấy chỉ số của dấu chấm
        var index = parseInt(dot.getAttribute('data-index'));

        // Lấy ID sản phẩm tương ứng
        var productId = dot.getAttribute('data-product-id');

        // Lấy tất cả các ảnh của sản phẩm
        var container = dot.closest('.box');
        var images = container.querySelectorAll('.imgshop');

        // Ẩn tất cả các ảnh của sản phẩm
        images.forEach(function(img) {
            img.classList.add('hidden');
        });

        // Hiển thị ảnh tương ứng
        images[index].classList.remove('hidden');

        // Bỏ chọn tất cả các dấu chấm của sản phẩm
        var productDots = container.querySelectorAll('.dot');
        productDots.forEach(function(d) {
            d.classList.remove('active');
        });

        // Chọn dấu chấm đã nhấp
        dot.classList.add('active');
    });
});
</script>
<script src="https://www.gstatic.com/dialogflow-console/fast/messenger/bootstrap.js?v=1"></script>
<df-messenger
  chat-title="Xin chào!"
  agent-id="9b3c9d53-e2a3-42da-a61c-e036c32c8aa2"
  language-code="en"
></df-messenger>


    <script src="http://cdnjs.cloudflare.com/ajax.libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script type="text/javascript" src="../js/script2.js"></script>
    <?php include '../user/footer.php'?>
</body>
</html>