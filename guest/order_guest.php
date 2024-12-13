<?php
    include '../connection/connection.php';
    session_start();
    // $admin_id = $_SESSION['user_name'];
    // $user_id = $_SESSION['user_id'];

    // if(!isset($admin_id)){
    //     header('location:../components/login.php');
    // }

    if(isset($_POST['logout'])){
        session_destroy();
        header('location:../components/login.php');
    }

    if(isset($_POST['submit-btn'])){
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $number = mysqli_real_escape_string($conn, $_POST['number']);
        $message = mysqli_real_escape_string($conn, $_POST['message']);

        $select_message = mysqli_query($conn, "SELECT * FROM `message` WHERE name='$name' AND email='$email' AND number='$number' AND message='$message'") or die('query failed');
        if(mysqli_num_rows($select_message)>0){
            echo 'Tin nhắn đã được gửi';
        }else{
            mysqli_query($conn, "INSERT INTO `message`(`user_id`, `name`, `email`, `number`, `message`) VALUES ('$user_id', '$name', '$email', '$number', '$message')") or die('query failed');
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
    <meta name="viewport" content="width=, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css" integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css" integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js" integrity="sha512-HGOnQO9+SP1V92SrtZfjqxxtLmVzqZpjFFekvzZVWoiASSQgSr4cw9Kqd2+l8Llp4Gm0G8GIFJ4ddwZilcdb8A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.js" integrity="sha512-eP8DK17a+MOcKHXC5Yrqzd8WI5WKh6F1TIk5QZ/8Lbv+8ssblcz7oGC8ZmQ/ZSAPa7ZmsCU4e/hcovqR8jfJqA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- <link rel="stylesheet" type="text/css" href="slick.css"> -->
    <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" type="text/css" href="main.css?v=1.1 <?php echo time();?>">
    <title>Seraph Beauty - Đơn Hàng</title>
</head> 
<body>
    <!-- <div class="line3"></div> -->
    <?php include 'header_guest.php'?>

    <div class="line3"></div>
    <div class="line3"></div>

    <div class="order-section">
        <div class="box-container">
            <?php
                $select_orders = mysqli_query($conn, "SELECT * FROM `orders` ") or die('query failed');
                if(mysqli_num_rows($select_orders)>0){
                    while($fetch_orders = mysqli_fetch_assoc($select_orders)){
                        $image_names = explode(',', $fetch_orders['product_image']);
            ?>
            <div class="box">

            <div class="img-order">
                <?php foreach ($image_names as $index => $image_name) { ?>
                    <img class="img_order <?php if ($index !== 0) echo 'hidden'; ?>" src="../image/<?php echo $image_name; ?>" data-order-id="<?php echo $fetch_orders['order_id']; ?>">
                <?php } ?>
                <div class="payment-status"><?php echo $fetch_orders['payment_status']; ?></div> 
                <input type="submit" name="buy-again-btn" class="btn" value="Mua Lại" data-order-id="<?php echo $fetch_orders['order_id']; ?>">


<script>
    // Lắng nghe sự kiện click vào nút "Mua Lại"
    document.getElementById("buy-again-btn").addEventListener("click", function() {
        // Chuyển hướng đến trang checkout_order.php
        window.location.href = "checkout_order.php";
    });
</script>

                <div class="price-order"><?php echo number_format($fetch_orders['total_price'], 0, '.', '.') . ' VNĐ'; ?></div>
                <div class="dot-container">
                    <?php for ($i = 0; $i < count($image_names); $i++) { ?>
                        <span class="dot <?php if ($i === 0) echo 'active'; ?>" data-index="<?php echo $i; ?>" data-product-id="<?php echo $fetch_products['product_id']; ?>"></span>
                    <?php } ?>
                </div>
            </div>
            



            </div>
            
            <?php
                    }
                }else{
                    echo'
                        <div class="empty">
                            <p>Chưa có đơn hàng nào được đặt!</p>
                        </div>
                    ';            
                }
            ?>
        </div>
    </div>
    
    <div class="line"></div>
    <script src="https://www.gstatic.com/dialogflow-console/fast/messenger/bootstrap.js?v=1"></script>
<df-messenger
  intent="WELCOME"
  chat-title="Chatbot"
  agent-id="9b3c9d53-e2a3-42da-a61c-e036c32c8aa2"
  language-code="en"
></df-messenger>
    <?php include '../user/footer.php'?>

<!-- Đoạn mã JavaScript -->
<script>
$(document).ready(function(){
    // Khi hình ảnh được click
    $(".img_order").click(function(){
        // Lấy ID của đơn hàng từ thuộc tính data
        var orderId = $(this).data("order-id");
        // Chuyển hướng đến trang hiển thị chi tiết của đơn hàng
        window.location.href = 'view_order.php?order_id=' + orderId;
    });
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var dots = document.querySelectorAll('.dot');

        // Lặp qua từng dấu chấm và gắn sự kiện click
        dots.forEach(function(dot) {
            dot.addEventListener('click', function() {
                // Lấy chỉ số của dấu chấm
                var index = parseInt(dot.getAttribute('data-index'));

                // Lấy tất cả các ảnh của sản phẩm
                var container = dot.closest('.box');
                var images = container.querySelectorAll('.img_order');

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
    });

    
</script>

<script>
    // Lắng nghe sự kiện click vào nút "Mua Lại"
    document.querySelectorAll("[name='buy-again-btn']").forEach(function(btn) {
        btn.addEventListener("click", function() {
            // Lấy order_id từ thuộc tính data
            var orderId = this.getAttribute("data-order-id");
            // Chuyển hướng đến trang checkout_order.php với order_id tương ứng
            window.location.href = "checkout_order.php?order_id=" + orderId;
        });
    });
</script>



    
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script> -->
    <script type="text/javascript" src="../js/script2.js"></script>
</body>
</html>