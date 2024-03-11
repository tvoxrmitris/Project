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

// ... Phần logic thêm sản phẩm vào danh sách yêu thích và giỏ hàng ...
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
        }else if(mysqli_num_rows($cart_num)>0){
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


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css" integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link class="logoo" rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css" integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js" integrity="sha512-HGOnQO9+SP1V92SrtZfjqxxtLmVzqZpjFFekvzZVWoiASSQgSr4cw9Kqd2+l8Llp4Gm0G8GIFJ4ddwZilcdb8A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.js" integrity="sha512-eP8DK17a+MOcKHXC5Yrqzd8WI5WKh6F1TIk5QZ/8Lbv+8ssblcz7oGC8ZmQ/ZSAPa7ZmsCU4e/hcovqR8jfJqA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" type="text/css" href="main.css?v=1.1 <?php echo time();?>">
    <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Các phần head của bạn -->
</head>

<body>
    <?php include 'header.php' ?>
    <div class="line"></div>

<section class="shop">
    <div class="box-container">
        <?php
        if (isset($_GET['search'])) {
            $search = $_GET['search'];
            echo '<h2 style="margin-bottom: 20px;">Kết quả tìm kiếm cho: <em>' . htmlspecialchars($search) . '</em></h2>';
            
            $search_query = mysqli_query($conn, "SELECT * FROM `products` WHERE `name` LIKE '%$search%' OR `price` LIKE '%$search%' OR `product_detail` LIKE '%$search%'") or die('query failed');

            if (mysqli_num_rows($search_query) > 0) {
                while ($fetch_products = mysqli_fetch_assoc($search_query)) {
                    // Hiển thị kết quả tìm kiếm
        ?>
                    <div class="box">
                        <img class="imgshop" src="image/<?php echo $fetch_products['image']; ?>">
                        <p class="priceshop">Giá: <?php echo number_format($fetch_products['price'], 0, '.', '.'); ?> VND</p>
                        <h4><?php echo $fetch_products['name']; ?></h4>
                        <div class="icon">
                            <a href="view_page.php?pid=<?php echo $fetch_products['id'];?>" class="bi bi-eye-fill"></a>
                            <button type="submit" name="add_to_wishlist" class="bi bi-heart"></button>
                            <button type="submit" name="add_to_cart" class="bi bi-cart"></button>
                        </div>
                    </div>
                    
        <?php
                }
            } else {
                echo '<p class="empty">Không tìm thấy sản phẩm phù hợp.</p>';
            }
        }
        ?>
    </div>
</section>


    <div class="line"></div>




    <script type="text/javascript" src="script2.js"></script>
    <?php include 'footer.php'?>
</body>

</html>
