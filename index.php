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

    <title>Home</title>
</head>
<body>
    <!-- <div class="line3"></div> -->
    <?php include 'header.php'?>
    <div class="slider-container">
        <div class="slider">
            <div class="slideBox active">
                <div class="textBox">
                <!-- <span>Kiểm tra chất lượng</span> -->
                    <h1>Mắt Kính<br>Panda</h1>
                    <p>Đôi mắt là ngôn từ của trái tim</p>
                    <a href="shop.php" class="btn">Mua sắm ngay</a>
                </div>
                <div class="imgBox">
                    <img src="./image/slider6.png" alt="">
                </div>
            </div>
            <div class="slideBox">
                <div class="textBox">
                    <!-- <span>Kiểm tra chất lượng</span> -->
                    <h1>Mắt Kính<br>Panda</h1>
                    <p>Đôi mắt là ngôn từ của trái tim</p>
                    <a style="cursor: pointer;" href="shop.php" class="btn" >Mua sắm ngay</a>
                </div>
                <div class="imgBox">
                    <img src="./image/slider8.png" alt="">
                </div>
            </div>

            <div class="slideBox">
                <div class="textBox">
                    <!-- <span>Kiểm tra chất lượng</span> -->
                    <h1>Mắt Kính<br>Panda</h1>
                    <p>Đôi mắt là ngôn từ của trái tim</p>
                    <a href="shop.php" class="btn">Mua sắm ngay</a>
                </div>
                <div class="imgBox">
                    <img src="./image/slider7.png" alt="">
                </div>
            </div>
            
        </div>
        <!-- <ul class="controls">
            <li onclick="nextSlide();" class="next"> 
                <i class="bi bi-chevron-right"></i>
            </li>
            <li onclick="prevSlide();" class="prev"> 
                <i class="bi bi-chevron-left"></i>
            </li>
        </ul> -->
    </div>


    
    <div class="services">
            <div class="row">
                <div class="box">
                    <img src="./image/shipping.png">
                    <div>
                        <h1>Miễn phí vận chuyển nhanh chóng</h1>
                    </div>
                </div>
                <div class="box">
                    <img src="./image/moneyback.png">
                    <div>
                        <h1>Hoàn lại tiền và đảm bảo an toàn </h1>
                    </div>
                </div>
                <div class="box">
                    <img src="./image/support.png">
                    <div>
                        <h1>Hỗ trợ trực tuyến 24/7</h1>
                    </div>
                </div>
            </div>
        </div>
    <div class="line"></div>

    <div class="categories" style="margin-top: -3rem;">
        <div class="title">
            <h1>Thương hiệu</h1>
        </div>
        <div class="box-container">
        <?php
            // Assuming you have a database connectio

            $select_brands = $conn->prepare("SELECT * FROM `brands`");
            $select_brands->execute();

            $result = $select_brands->get_result();

            // Fetch results and display
            while ($brands = $result->fetch_assoc()) {
        ?>
                <div class="box">
                    <img src="image/<?= $brands['image']; ?>">
                    <a href="brand_product.php?id=<?= $brands['id']; ?>" class="btn-brand" style="text-transform: uppercase;"><?= $brands['name']; ?></a>
                </div>
        <?php
            }
        ?>
        </div>
    </div>

    <div class="line"></div>

    <div class="categories" style="margin-top: -3rem;">
        <div class="title">
            <h1>Danh mục</h1>
        </div>
        <div class="box-container">
        <?php
            // Assuming you have a database connectio

            $select_categories = $conn->prepare("SELECT * FROM `categories`");
            $select_categories->execute();

            $result = $select_categories->get_result();

            // Fetch results and display
            while ($categories = $result->fetch_assoc()) {
        ?>
                <div class="box">
                    <img src="image/<?= $categories['image']; ?>">
                    <a href="categories.php?id=<?= $categories['id']; ?>" class="btn-brand" style="text-transform: uppercase;"><?= $categories['name']; ?></a>
                </div>
        <?php
            }
        ?>
        </div>
    </div>
        <div class="line3"></div>
    <?php include 'homeshop.php'?>
    <?php include 'footer.php'?>
    <script src="jquary.js"></script>
    <script src="slick.css"></script>
    


    <script type="text/javascript" src="script2.js"></script>
</body>
</html>