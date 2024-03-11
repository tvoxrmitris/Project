<?php
include 'connection.php';
session_start();
$admin_id = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];

if (!isset($admin_id)) {
    header('location: login.php');
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('location: login.php');
}

        //adding product in wishlist
        if(isset($_POST['add_to_wishlist'])){
            $product_id = $_POST['product_id'];
            $product_name = $_POST['product_name'];
            $product_price = $_POST['product_price'];
            $product_image = $_POST['product_image'];
    
            $wishlist_number = mysqli_query($conn, "SELECT * FROM `wishlist` WHERE name='$product_name' AND user_id ='$user_id'") or die('query failed');
    
            $cart_num = mysqli_query($conn,"SELECT * FROM `cart` WHERE name='$product_name' AND user_id ='$user_id'") or die('query failed');
    
            if(mysqli_num_rows($wishlist_number) > 0){
                $message[]='Sản phẩm đã tồn tại trong danh sách yêu thích';
            }else if(mysqli_num_rows($cart_num)>0 && mysqli_num_rows($wishlist_number)>0){
                $message[]='Sản phẩm đã tồn tại trong giỏ hàng';
            }else{
                mysqli_query($conn, "INSERT INTO `wishlist`(`user_id`, `pid`, `name`, `price`, `image`) VALUES('$user_id', '$product_id', '$product_name', '$product_price', '$product_image')");
                $message[]='Sản phẩm đã được thêm thành công vào danh sách yêu thích';
            }
        }
    
        //adding product in cart
        if(isset($_POST['add_to_cart'])){
            $product_id = $_POST['product_id'];
            $product_name = $_POST['product_name'];
            $product_price = $_POST['product_price'];
            $product_image = $_POST['product_image'];
            $product_quantity = $_POST['product_quantity'];
    
            $cart_num = mysqli_query($conn,"SELECT * FROM `cart` WHERE name='$product_name' AND user_id ='$user_id'") or die('query failed');
    
            if(mysqli_num_rows($cart_num)>0){
                $message[]='Sản phẩm đã tồn tại trong giỏ hàng';
            }else{
                mysqli_query($conn, "INSERT INTO `cart`(`user_id`, `pid`, `name`, `price`, `quantity`, `image`) VALUES('$user_id', '$product_id', '$product_name', '$product_price', '$product_quantity', '$product_image')");
                $message[]='Sản phẩm đã được thêm thành công vào giỏ hàng';
            }
        }

        if(isset($_GET['search'])) {
            $search = $_GET['search'];
            $search_query = mysqli_query($conn, "SELECT * FROM `products` WHERE `name` LIKE '%$search%' OR `price` LIKE '%$search%' OR `product_detail` LIKE '%$search%'") or die('query failed');
            
            if(mysqli_num_rows($search_query) > 0) {
                while($fetch_products = mysqli_fetch_assoc($search_query)) {
                    // Hiển thị kết quả tìm kiếm tại đây
                }
            } else {
                echo '<p class="empty">Không tìm thấy sản phẩm phù hợp.</p>';
            }
        } else {
            // Hiển thị toàn bộ sản phẩm nếu không có yêu cầu tìm kiếm
        }

$category_name = "";
if (isset($_GET['id'])) {
    $brand_id = $_GET['id'];

    // Truy vấn để lấy tên thương hiệu từ ID
    $select_category_name = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $select_category_name->bind_param("i", $category_id);
    $select_category_name->execute();
    $result_category_name = $select_category_name->get_result();

    // Kiểm tra và gán tên thương hiệu nếu có kết quả trả về từ cơ sở dữ liệu
    if ($result_category_name->num_rows > 0) {
        $category_name = $result_category_name->fetch_assoc()['name'];
    }
}
?>

<style type="text/css">
    <?php include 'main.css' ?>
</style>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="main.css?v=1.1 <?php echo time();?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">
    <!-- Các phần head của bạn -->
</head>
<body>
    <?php include 'header.php' ?>

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
        if (isset($_GET['id'])) {
            $category_id = $_GET['id'];

            // Lấy tên thương hiệu dựa trên ID thương hiệu đã chọn
            $select_categories = $conn->prepare("SELECT name FROM categories WHERE id = ?");
            $select_categories->bind_param("i", $category_id);
            $select_categories->execute();
            $result_categories = $select_categories->get_result();

            if ($result_categories->num_rows > 0) {
                $category_name = $result_categories->fetch_assoc()['name'];

                // Lấy sản phẩm liên quan đến tên thương hiệu đã chọn
                $status = 'Đang hoạt động';
                $select_products = $conn->prepare("SELECT * FROM `products` WHERE BINARY categories = ? AND status = ?");
                $select_products->bind_param("ss", $category_name, $status);
                $select_products->execute();

                $result_products = $select_products->get_result();

                if ($result_products->num_rows > 0) {
                    while ($fetch_products = $result_products->fetch_assoc()) {
        ?>
                        <!-- Hiển thị thông tin sản phẩm -->
                        <form method="post" class="box">
                            <img class="imgshop" src="image/<?php echo $fetch_products['image']; ?>">
                            <div class="priceshop"><?php echo number_format($fetch_products['price'], 0, '.', '.'); ?>VNĐ</div>
                            <div class="name"><?php echo $fetch_products['name']; ?></div>
                            <input type="hidden" name="product_id" value="<?php echo $fetch_products['id']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo $fetch_products['name']; ?>">
                            <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
                            <input type="hidden" name="product_quantity" value="1" min="0">
                            <input type="hidden" name="product_image" value="<?php echo $fetch_products['image']; ?>">
                            <div class="icon">
                                <a href="view_page.php?pid=<?php echo $fetch_products['id']; ?>" class="bi bi-eye-fill"></a>
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
        }
        ?>
    </div>

</div>
    </section>



    

    <script src="http://cdnjs.cloudflare.com/ajax.libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script type="text/javascript" src="script2.js"></script>
    <?php include 'footer.php'?>
</body>
</html>