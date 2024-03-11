<?php
    include 'connection.php';
    session_start();
    $admin_id = $_SESSION['user_name'];
    $user_id = $_SESSION['user_id'];

    if(!isset($admin_id)){
        header('location:login.php');
    }

    if(isset($_POST['logout'])){
        session_destroy();
        header('location:login.php');
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

        // Kết nối cơ sở dữ liệu và các cài đặt khác
        
        if (isset($_GET['search'])) {
            $search = $_GET['search'];
            // Thực hiện truy vấn để lấy sản phẩm từ cơ sở dữ liệu dựa trên từ khóa tìm kiếm
            $search_query = mysqli_query($conn, "SELECT * FROM `products` WHERE `name` LIKE '%$search%' OR `price` LIKE '%$search%' OR `product_detail` LIKE '%$search%'") or die('query failed');
        
            if (mysqli_num_rows($search_query) > 0) {
                // Hiển thị sản phẩm tìm kiếm
                while ($fetch_products = mysqli_fetch_assoc($search_query)) {
                    // Hiển thị thông tin sản phẩm, ví dụ:
                    echo '<div>';
                    echo '<img src="image/' . $fetch_products['image'] . '">';
                    echo '<div>' . $fetch_products['name'] . '</div>';
                    echo '<div>' . number_format($fetch_products['price'], 0, '.', '.') . 'VNĐ</div>';
                    echo '</div>';
                }
            } else {
                echo '<p class="empty">Không tìm thấy sản phẩm phù hợp.</p>';
            }
        } else {
            // echo '<p class="empty">Vui lòng nhập từ khóa để tìm kiếm sản phẩm.</p>';
        }
        ?>
        
        
        
   
?>

<style type="text/css">
    <?php
        include 'main.css'
    ?>
</style>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css" integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css" integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js" integrity="sha512-HGOnQO9+SP1V92SrtZfjqxxtLmVzqZpjFFekvzZVWoiASSQgSr4cw9Kqd2+l8Llp4Gm0G8GIFJ4ddwZilcdb8A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.js" integrity="sha512-eP8DK17a+MOcKHXC5Yrqzd8WI5WKh6F1TIk5QZ/8Lbv+8ssblcz7oGC8ZmQ/ZSAPa7ZmsCU4e/hcovqR8jfJqA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- <link rel="stylesheet" type="text/css" href="slick.css"> -->
    <link rel="stylesheet" type="text/css" href="main.css?v=1.1 <?php echo time();?>">
    <title>Home</title>
</head> 
<body>
    <!-- <div class="line3"></div> -->
    <?php include 'header.php'?>
    <div class="banner">
        <div class="detail">
            <h1>Cửa hàng của chúng tôi</h1>
            <p>Đôi mắt là ngôn từ của trái tim.</p>
            <a href="index.php">Trang chủ</a><span>/về chúng tôi</span>
        </div>
    </div>
    <div class="line"></div>

    <section class="shop">
        <h1 class="title">Sản phẩm bán chạy nhất</h1>

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

        <div class="box-container">
            <?php
                $select_products = mysqli_query($conn, "SELECT * FROM `products`") or die('query failed');
                if(mysqli_num_rows($select_products)>0){
                    while($fetch_products = mysqli_fetch_assoc($select_products)){

            ?>
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
                    <a href="view_page.php?pid=<?php echo $fetch_products['id'];?>" class="bi bi-eye-fill"></a>
                    <button type="submit" name="add_to_wishlist" class="bi bi-heart"></button>
                    <button type="submit" name="add_to_cart" class="bi bi-cart"></button>
                </div>
            </form>
            <?php
                    }
                }else{
                    echo '<p class="empty">Chưa có sản phẩm nào được thêm</p>';
                }
            ?>
        </div>
    </section>

    <?php include 'footer.php'?>
    
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script> -->
    <script type="text/javascript" src="script2.js"></script>
</body>
</html>