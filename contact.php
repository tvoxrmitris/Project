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
            <h1>Liên hệ</h1>
            <p>Đôi mắt là ngôn từ của trái tim.</p>
            <a href="index.php">Trang chủ</a><span>/Liên hệ</span>
        </div>
    </div>
    <div class="line3"></div>

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
    <div class="line3"></div>

    <div class="form-container">
        <h1 class="title">Để lại lời nhắn</h1>
        <form method="post">
            <div class="input-field">
                <label>Tên của bạn</label><br>
                <input type="text" name="name">
            </div>
            <div class="input-field">
                <label>Email của bạn</label><br>
                <input type="text" name="email">
            </div>
            <div class="input-field">
                <label>Số điện thoại</label><br>
                <input type="number" name="number">
            </div>
            <div class="input-field">
                <label>Lời nhắn của bạn</label><br>
                <textarea name="message"></textarea>
            </div>
            <button type="submit" name="submit-btn">Gửi lời nhắn</button>


        </form>
    </div>

    <div class="line2"></div>

    <div class="address">
        <h1 class="title">Liên hệ của chúng tôi</h1>
        <div class="row">
            <div class="box">
                <i class="bi bi-map"></i>
                <div>
                    <h4>Địa chỉ</h4>
                    <p>Ấp Nam Chánh, xã Lịch Hội Thượng, huyện Trần Đề, tỉnh Sóc Trăng</p>
                </div>
            </div>
            <div class="box">
                <i class="bi bi-telephone"></i>
                <div>
                    <h4>Số điện thoại</h4>
                    <p>0706990348</p>
                </div>
            </div>
            <div class="box">
                <i class="bi bi-envelope"></i>
                <div>
                    <h4>Email</h4>
                    <p>triminhvo0404@gmail.com</p>
                </div>
            </div>
        </div>
    </div>
    <div class="line"></div>

    <?php include 'footer.php'?>
    
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script> -->
    <script type="text/javascript" src="script2.js"></script>
</body>
</html>