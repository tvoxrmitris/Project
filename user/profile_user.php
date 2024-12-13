<?php
    include '../connection/connection.php';
    session_start();
    $admin_id = $_SESSION['user_name'];
    $user_id = $_SESSION['user_id'];

    if(!isset($admin_id)){
        header('location:../components/login.php');
    }

    if(isset($_POST['logout'])){
        session_destroy();
        header('location:../components/login.php');
    }


    



   
?>

<style type="text/css">
<?php include 'main.css'
?>
</style>

<style>
.hidden {
    display: none;
}
</style>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css"
        integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link class="logoo" rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css"
        integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js"
        integrity="sha512-HGOnQO9+SP1V92SrtZfjqxxtLmVzqZpjFFekvzZVWoiASSQgSr4cw9Kqd2+l8Llp4Gm0G8GIFJ4ddwZilcdb8A=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.js"
        integrity="sha512-eP8DK17a+MOcKHXC5Yrqzd8WI5WKh6F1TIk5QZ/8Lbv+8ssblcz7oGC8ZmQ/ZSAPa7ZmsCU4e/hcovqR8jfJqA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" type="text/css" href="../CSS/main.css?v=1.1 <?php echo time();?>">
    <!-- <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon"> -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Home</title>
</head>

<body>
    <!-- <div class="line3"></div> -->
    <?php include '../user/header.php'?>

    <div class="line2"></div>



    <div class="about-us">
        <div class="row">
            <?php
            // Mảng chứa các tên hình ảnh
            $image_names = array("bannermakeup.jpg", "../image/banner/arcane.jpg", "../image/product/traceoutliplinerBrownOuta2.jpg","../image/product/universalluminizerGlassSlipa2.jpg");
            $select_user = mysqli_query($conn, "SELECT * FROM users WHERE user_id='$user_id'") or die('Query failed');
            if(mysqli_num_rows($select_user) > 0){
                while($fetch_user = mysqli_fetch_assoc($select_user)){

                    ?>
            <div class="img-about" style="position: relative;">
                <!-- Đặt position là relative -->
                <?php foreach ($image_names as $index => $image_name) { ?>
                <img class="imgshop <?php if ($index !== 0) echo 'hidden'; ?>" src="../image/<?php echo $image_name; ?>"
                    data-index="<?php echo $index; ?>">
                <?php } ?>

                <!-- Di chuyển dấu chấm container vào bên trong .img-about -->
                <div class="dot-about">
                    <?php for ($i = 0; $i < count($image_names); $i++) { ?>
                    <span class="dot <?php if ($i === 0) echo 'active'; ?>" data-index="<?php echo $i; ?>"
                        data-product-id="<?php echo $fetch_products['product_id']; ?>"></span>
                    <?php } ?>
                </div>
            </div>

            <div class="user-box">
                <div class="detail">
                    <p><strong style="font-size: 30px; font-weight: bold;">Hồ Sơ</strong></p>
                </div>
                <p>Tên tài khoản: <span><?php echo $fetch_user['user_name']; ?></span></p>
                <p>Email: <span><?php echo $fetch_user['user_email']; ?></span></p>
                <p>Số điện thoại: 0<span><?php echo $fetch_user['user_number']; ?></span></p>
                <p>Điểm tích lũy: <span><?php echo $fetch_user['point']; ?></span></p>
                <?php 
    // Tính hạng hiện tại và điểm cần để đạt hạng tiếp theo
    $point = $fetch_user['point'];
    $rank = 'Thành viên thường'; // Hạng mặc định
    $next_rank = 'Bạc'; // Hạng tiếp theo mặc định
    $next_point = 500; // Điểm cần để đạt hạng tiếp theo mặc định

    if ($point >= 5000) {
        $rank = 'Kim Cương';
        $next_rank = null; // Không có hạng tiếp theo
    } elseif ($point >= 2000) {
        $rank = 'Bạch Kim';
        $next_rank = 'Kim Cương';
        $next_point = 5000;
    } elseif ($point >= 1000) {
        $rank = 'Vàng';
        $next_rank = 'Bạch Kim';
        $next_point = 2000;
    } elseif ($point >= 500) {
        $rank = 'Bạc';
        $next_rank = 'Vàng';
        $next_point = 1000;
    }

    // Tính điểm cần thêm để đạt hạng tiếp theo
    $points_needed = $next_point - $point;
    ?>
                <p>Hạng thành viên: <span style="color: #d4af37; font-weight: bold;"><?php echo $rank; ?></span></p>

                <form method="post" action="reset_profile.php" class="btn-container">
                    <button type="submit" name="update" class="update-btn">Chỉnh sửa</button>
                </form>
                <div class="rank-note" style="margin-top: 20px; text-align: center; font-style: italic; color: #555;">
                    <p>
                        Hạng hiện tại: <strong><?php echo $rank; ?></strong>.
                        <?php
        // Tính mức giảm giá theo hạng
        $discount = 0; // Mặc định là không giảm giá
        if ($rank === 'Bạc') {
            $discount = 2;
        } elseif ($rank === 'Vàng') {
            $discount = 5;
        } elseif ($rank === 'Bạch Kim') {
            $discount = 8;
        } elseif ($rank === 'Kim Cương') {
            $discount = 10;
        }
        ?>
                        <?php if ($discount > 0): ?>
                        Với hạng này, bạn sẽ được giảm <strong><?php echo $discount; ?>%</strong> khi mua sắm.
                        <?php else: ?>
                        Hạng này không áp dụng giảm giá.
                        <?php endif; ?>
                    </p>

                    <?php if ($next_rank): ?>
                    <p>
                        Bạn cần tích thêm <strong><?php echo $points_needed; ?> điểm</strong> để đạt hạng
                        <strong><?php echo $next_rank; ?></strong>.
                    </p>
                    <?php else: ?>
                    <p>
                        Bạn đã đạt hạng cao nhất: <strong>Kim Cương</strong>. Hãy tiếp tục duy trì!
                    </p>
                    <?php endif; ?>
                </div>


            </div>
        </div>
        <?php
            }
        }
    ?>
    </div>





    <script>
    $('.img-about').slick({
        // dots: true,
        infinite: true,
        autoplay: true,
        autoplaySpeed: 2000,
        lazyLoad: 'ondemand',
        // speed: 300,
        slidesToShow: 1,
        adaptiveHeight: true
    });
    </script>






    <script src="https://www.gstatic.com/dialogflow-console/fast/messenger/bootstrap.js?v=1"></script>
    <df-messenger chat-title="Seraph Beauty" agent-id="9b3c9d53-e2a3-42da-a61c-e036c32c8aa2" language-code="en"
        chat-icon="../image/seraphh.png">
    </df-messenger>

    <style>
    /* Giao diện chat theo phong cách Gucci - Trắng đen */
    df-messenger {
        --df-messenger-chat-icon: url('../image/seraphh.png');
        /* Logo Gucci */
        --df-messenger-chat-icon-width: 60px;
        --df-messenger-chat-icon-height: 60px;
        --df-messenger-button-titlebar-color: #F2F2F2;
        /* Màu nền header */
        --df-messenger-bot-message: #f9f9f9;
        /* Màu tin nhắn của bot */
        --df-messenger-user-message: #000;
        /* Màu tin nhắn của người dùng */
        --df-messenger-send-icon: #fff;
        /* Màu biểu tượng gửi */
        --df-messenger-font-color: #000;
        /* Màu chữ */
    }

    /* Bo tròn khung chat */
    df-messenger .df-messenger-wrapper {
        border-radius: 15px;
        border: 1px solid #000;
        /* Viền đen */
    }

    /* Thay đổi màu nền khung chat */
    df-messenger .df-messenger-wrapper {
        background-color: #fff;
        /* Nền trắng */
    }

    /* Tùy chỉnh tiêu đề khung chat */
    df-messenger .df-messenger-titlebar {
        font-weight: bold;
        font-size: 16px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #000;
        /* Màu chữ đen */
        background-color: #F2F2F2;
        /* Màu nền header nhẹ */
    }

    /* Tùy chỉnh các tin nhắn từ người dùng */
    df-messenger .df-messenger-user-message {
        background-color: #000;
        color: #fff;
        border-radius: 15px;
        padding: 8px;
        margin-bottom: 10px;
    }

    /* Tùy chỉnh các tin nhắn từ bot */
    df-messenger .df-messenger-bot-message {
        background-color: #f9f9f9;
        color: #000;
        border-radius: 15px;
        padding: 8px;
        margin-bottom: 10px;
    }

    /* Tùy chỉnh nút gửi */
    df-messenger .df-messenger-send-button {
        background-color: #000;
        color: #fff;
        border-radius: 50%;
        padding: 10px;
    }

    /* Thêm hiệu ứng hover cho nút gửi */
    df-messenger .df-messenger-send-button:hover {
        background-color: #333;
    }
    </style>





    </script>

    <?php include '../user/footer.php'?>



    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script> -->
    <script type="text/javascript" src="../js/script2.js"></script>
</body>

</html>