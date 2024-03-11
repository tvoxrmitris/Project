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

    $sql = "SELECT * FROM province";
    $result = mysqli_query($conn, $sql);

    if (isset($_POST['add_sale'])) {
        echo "<pre>";
        print_r($_POST);
        die();
    }

    if(isset($_POST['order-btn'])){
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $number = mysqli_real_escape_string($conn, $_POST['number']);
        $method = mysqli_real_escape_string($conn, $_POST['method']);
        $flat = mysqli_real_escape_string($conn, $_POST['flat']);
        $province = mysqli_real_escape_string($conn, $_POST['province']);
        $district = mysqli_real_escape_string($conn, $_POST['district']);
        $wards = mysqli_real_escape_string($conn, $_POST['wards']);
        $full_address = $flat . ', ' . $wards . ', ' . $district . ', ' . $province;
        
        
        // Kiểm tra giá trị của các biến trước khi chèn vào câu lệnh SQL

        


        
        

        // mysqli_query($conn, "INSERT INTO orders  (full_address) VALUES ('$full_address')");

        // $address = $_POST['flat']. ', ' .$_POST['province']. ', ' .$_POST['district']. ', ' .$_POST['wards'];

        $placed_on =date('d-M-Y');
        $cart_total=0;
        $cart_product[]='';
        $cart_query=mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id='$user_id'") or die('query failed');

        if(mysqli_num_rows($cart_query)>0){
            while($cart_item=mysqli_fetch_assoc($cart_query)){
                $cart_product[]=$cart_item['name'].' ('.$cart_item['quantity'].')';
                $sub_total=($cart_item['price']) * $cart_item['quantity'];
                $cart_total+=$sub_total;
                }
            }
            $total_products=implode(',', $cart_product);
            mysqli_query($conn, "INSERT INTO orders (user_id, name, number, email, method, address, total_products, total_price, placed_on) VALUES ('$user_id', '$name', '$number', '$email', '$method', '$full_address', '$total_products', '$cart_total', '$placed_on')");

            

            mysqli_query($conn, "DELETE FROM `cart` WHERE user_id='$user_id'");
            $message[]='Đã hoàn tất việc đặt hàng';
            header('location:checkout.php');
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
    <script src="https://code.jquery.com/jquery-3.6.4.js"></script>
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
            <h1>Đặt hàng</h1>
            <p>Đôi mắt là ngôn từ của trái tim.</p>
            <a href="index.php">Trang chủ</a><span>/Đặt hàng</span>
        </div>
    </div>
    <div class="line3"></div>
    <div class="checkout-form">
        <h1 class="title">Thanh toán đang xử lí</h1>
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
        <div class="display-order">
            <div class="box-container">
                <?php
                    $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id='$user_id'") or die('query failed');
                    $total=0;
                    $grand_total=0;
                    if(mysqli_num_rows($select_cart)>0){
                        while($fetch_cart=mysqli_fetch_assoc($select_cart)){
                            $total_price = ($fetch_cart['price'] * $fetch_cart['quantity']);
                            $grand_total = $total+=$total_price;

                ?>
                    <div class="box">
                        <img src="image/<?php echo $fetch_cart['image']; ?>">
                        <span><?= $fetch_cart['name']; ?>(<?=$fetch_cart['quantity'];?>)</span>
                    </div>
                <?php
                        }
                    }
                ?>
            </div>
            <span class="grand-total">Tổng số tiền phải trả: <?php echo number_format($grand_total, 0, '.', '.') ?>VND</span>
        </div>
        <form method="post">
                <div class="input-field">
                    <label>Tên của bạn<span>*</span></label>
                    <input type="text" name="name" placeholder="Hãy nhập tên của bạn">
                </div>
                <div class="input-field">
                    <label>Số điện thoại của bạn<span>*</span></label>
                    <input type="number" name="number" placeholder="Hãy nhập số điện thoại của bạn">
                </div>
                <div class="input-field">
                    <label>Email của bạn<span>*</span></label>
                    <input type="text" name="email" placeholder="Hãy nhập email của bạn">
                </div>
                <div class="input-field">
                    <label>Vui lòng chọn phương thức thanh toán<span>*</span></label>
                    <select name="method">
                        <option selected disabled>Chọn phương thức thanh toán</option>
                        <option value="Thanh toán khi nhận hàng">Thanh toán khi nhận hàng</option>
                        <option value="credit card">credit card</option>
                        <option value="paytm">paytm</option>
                    </select>
                </div>
                <!-- <div class="input-field">
                    <label>địa chỉ 1<span>*</span></label>
                    <input type="text" name="flate" placeholder="">
                </div>

                <div class="input-field">
                    <label>địa chỉ 2<span>*</span></label>
                    <input type="text" name="street" placeholder="">
                </div>

                <div class="input-field">
                    <label>thành phố<span>*</span></label>
                    <input type="text" name="city" placeholder="">
                </div>

                <div class="input-field">
                    <label>Bang<span>*</span></label>
                    <input type="text" name="state" placeholder="">
                </div>

                <div class="input-field">
                    <label>Đất nước<span>*</span></label>
                    <input type="text" name="country" placeholder="">
                </div>

                <div class="input-field">
                    <label>Pin code<span>*</span></label>
                    <input type="text" name="pin" placeholder="">
                </div> -->



                <div class="input-field">
                    <label>Tên đường, tòa nhà, số nhà<span>*</span></label>
                    <input type="text" id="flat" name="flat" placeholder="Nhập tên đường, tòa nhà, số nhà">
                </div>
                <div class="input-field">
                    <label for="province">Tỉnh/Thành phố<span>*</span></label>
                    <select id="province" name="province" class="form-control">
                        <option value="">Chọn một tỉnh</option>

                        <?php
                            while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                            <option value="<?php echo $row['province_id'] ?>"><?php echo $row['name'] ?></option>
                        <?php
                            }
                            ?>
                    </select>
                </div> 
                <div class="input-field">
                    <label for="district">Quận/Huyện<span>*</span></label>
                    <select id="district" name="district" class="form-control">
                        <option value="">Chọn một quận/huyện</option>
                    </select>
                </div>
                <div class="input-field">
                    <label for="wards">Phường/Xã<span>*</span></label>
                    <select id="wards" name="wards" class="form-control">
                        <option value="">Chọn một xã</option>
                    </select>
                </div>
                <input type="submit" name="order-btn" class="btn" value="Đặt ngay">
        </form>

    </div>

    
    <div class="line"></div>

    <?php include 'footer.php'?>
    
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script> -->
    <script type="text/javascript" src="script2.js"></script>
    <script type="text/javascript" src="js/app.js"></script>
</body>
</html>