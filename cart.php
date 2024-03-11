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
    
    //updating qty
    if(isset($_POST['update_qty_btn'])){
        $update_qty_id = $_POST['update_qty_id'];
        $update_value = $_POST['update_qty'];

        $update_query = mysqli_query($conn, "UPDATE `cart` SET quantity = '$update_value' WHERE id='$update_qty_id'") or die('query failed');
        if($update_query){
            header('location:cart.php');
        }
    }

    //delete product from wishlist
    if(isset($_GET['delete'])){
        $delete_id = $_GET['delete'];
        mysqli_query($conn,"DELETE FROM `cart` WHERE id = '$delete_id'") or die('query failed');

        header('location:cart.php');
    }

    if(isset($_GET['delete_all'])){
        mysqli_query($conn,"DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');

        header('location:cart.php');
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
    <meta name="viewport" content="width=, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css" integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

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
            <h1>Giỏ hàng của tôi</h1>
            <p>Đôi mắt là ngôn từ của trái tim.</p>
            <a href="index.php">Trang chủ</a><span>/Danh sách yêu thích</span>
        </div>
    </div>
    <div class="line"></div>

    <section class="shop">
        <!-- <h1 class="title">Sản phẩm bán chạy nhất</h1> -->
        <h1 class="title">Những sản phẩm đã được thêm vào giỏ hàng</h1>
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
                $grand_total=0;
                $select_cart= mysqli_query($conn, "SELECT * FROM `cart`") or die('query failed');
                if(mysqli_num_rows($select_cart)>0){
                    while($fetch_cart = mysqli_fetch_assoc($select_cart)){

            ?>
            <div class="box">
                <!-- <div class="icon">
                    <a href="view_page.php?pid=<?php echo $fetch_cart['id'];?>" class="bi bi-eye-fill"></a>
                    <a href="cart.php?delete=<?php echo $fetch_cart['id'];?>" class="bi bi-x" onclick="return confirm('Bạn có muốn xóa sản phẩm khỏi giỏ hàng')"></a>
                    <button type="submit" name="add_to_cart" class="bi bi-cart"></button>
                </div> -->
                <img  class="imgcart" src="image/<?php echo $fetch_cart['image']; ?>">
                <div class="pricecart"><?php echo number_format($fetch_cart['price'], 0, '.', '.'); ?>VNĐ</div>
                <div class="name"><?php echo $fetch_cart['name']; ?></div>
                <form method="post">
                        <input type="hidden" name="update_qty_id" value="<?php echo $fetch_cart['id'];?>">
                        <div class="qty">
                            <input type="number" min="1" name="update_qty" value="<?php echo $fetch_cart['quantity'];?>">
                            <input type="submit" name="update_qty_btn" value="Cập nhật">
                            <!-- <input type="submit" name="delete_all" value="Xóa"> -->
                        </div>
                </form>
                <div class="total-amt">
                    Tổng Cộng: <span><?php echo number_format($total_amt = ($fetch_cart['price']*$fetch_cart['quantity']), 0, '.', '.'); ?></span>

                </div>
            </div>
            <?php
                    $grand_total += $total_amt;
                    }
                }else{
                    echo '<p class="empty">Chưa có sản phẩm nào được thêm!</p>';
                }
            ?>
        </div>
        <div class="dlt">
        <a href="cart.php?delete_all" class="btn2" onclick="return confirm('Bạn có muốn xóa tất cả sản phẩm trong giỏ hàng')">Xóa tất cả</a>
        </div>
        <div class="wishlist_total">
                <p>Tổng số tiền phải trả: <span><?php echo number_format($grand_total, 0, '.', '.');?></span>VND</p>
                <a href="shop.php" class="btn">Tiếp tục mua sắm</a>
                <a href="checkout.php?delete_all" class="btn <?php echo ($grand_total>1)?'':'disabled'?>)">Tiến hành kiểm tra</a>
        </div>
    </section>

    <?php include 'footer.php'?>
    
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script> -->
    <script type="text/javascript" src="script2.js"></script>
</body>
</html>