<?php
include '../connection/connection.php';
session_start();

if (isset($_POST['submit-btn'])) {
    $filter_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $email = mysqli_real_escape_string($conn, $filter_email);

    // Truy vấn kiểm tra email có tồn tại trong bảng không
    $query = "SELECT * FROM users WHERE user_email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // Email tồn tại
        $_SESSION['user_email'] = $email;

        // Kiểm tra nếu có tham số redirect từ URL
        if (isset($_GET['redirect'])) {
            $_SESSION['redirect_to'] = $_GET['redirect'];
        }

        // Kiểm tra xem có tham số product_id trong URL không
        if (isset($_GET['product_id'])) {
            $product_id = intval($_GET['product_id']); // Chuyển đổi sang số nguyên
            $_SESSION['product_id'] = $product_id; // Lưu product_id vào session
        }

        header('Location: continuelogin.php');
        exit();
    } else {
        // Email không tồn tại
        $message[] = 'Tài khoản không tồn tại.';
    }
}



?>

<!DOCTYPE HTML>
<html lang="vi">

<head>
    <title>Seraph Beauty - Đăng nhập</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css"
        integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css"
        integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js"
        integrity="sha512-HGOnQO9+SP1V92SrtZfjqxxtLmVzqZpjFFekvzZVWoiASSQgSr4cw9Kqd2+l8Llp4Gm0G8GIFJ4ddwZilcdb8A=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.js"
        integrity="sha512-eP8DK17a+MOcKHXC5Yrqzd8WI5WKh6F1TIk5QZ/8Lbv+8ssblcz7oGC8ZmQ/ZSAPa7ZmsCU4e/hcovqR8jfJqA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" type="text/css" href="../CSS/main.css?v=1.1 <?php echo time(); ?>">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />


    <!-- <link rel="shortcut icon" href="../image/logo.png" type="image/vnd.microsoft.icon"> -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>

<body>
    <?php include '../guest/header_guest.php' ?>
    <!-- Form Đăng nhập -->

    <section class="login">
        <div class="header_guest">
            <div class="header-main">
                <h1>Tài khoản Seraph Beauty</h1>

                <!-- Nút đăng nhập bằng Google -->
                <div id="g_id_onload"
                    data-client_id="723556964262-digcs4vo4682hc3rjvlecjjjuj8rfn4i.apps.googleusercontent.com"
                    data-login_uri="http://localhost/NLCSN/components/login.php" data-auto_prompt="false">
                </div>

                <!-- <div class="g_id_signin google-login" data-type="standard" data-shape="rectangular" data-theme="outline"
                    data-text="signin_with" data-size="large" data-logo_alignment="left">
                </div>

                <div class="or">Hoặc</div> -->
                <div class="continue">Đăng nhập bằng email</div>
                <div class="detail">
                    Đăng nhâp bằng tài khoản email và mật khẩu của bản hoặc tạo tài khoản nếu bạn là thành viên mới.
                </div>

                <!-- Form đăng nhập qua email -->
                <form id="loginForm" method="post">
                    <div class="loginemail">
                        <input type="text" name="email" placeholder="Email*" required>
                    </div>
                    <?php
                    // Hiển thị thông báo nếu có
                    if (!empty($message)) {
                        foreach ($message as $msg) {
                            echo '
                                <div class="message">
                                    <span>' . $msg . '</span>
                                    <i class="bi bi-x-circle" onclick="this.parentElement.remove()"></i>
                                </div>
                            ';
                        }
                    }
                    ?>
                    <div class="goon">
                        <button type="submit" name="submit-btn">Tiếp tục</button>
                    </div>
                </form>
                <div>
                    <a href="register.php">Đăng ký</a> tài khoản để nhận ngay ưu đãi mới nhất.
                </div>



            </div>
        </div>
    </section>

    <div class="line3"></div>

    <?php include '../guest/footer.php' ?>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</body>

</html>