<?php
include '../connection/connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $_SESSION['searched_email'] = $email;

    $stmt = $conn->prepare("SELECT * FROM users WHERE user_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['valid_email'] = true;
        header("Location: send_code.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Không tìm thấy tài khoản của bạn. Vui lòng thử lại với thông tin khác.";
        header("Location: error_page.php");
        exit();
    }
}
?>
<!DOCTYPE HTML>
<html lang="vi">

<head>
    <title>Seraph Beauty - Quên Mật Khẩu</title>
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
    <?php include '../guest/header_guest.php'; ?>
    <section class="login">
        <div class="header_guest">
            <div class="header-main">
                <h1>Tìm tài khoản của bạn</h1>
                <div class="emailname">Vui lòng nhập email của bạn</div>

                <form method="post">
                    <div class="loginpassword">
                        <input type="text" name="email" placeholder="Email*" required>
                    </div>
                    <div class="goonfg">
                        <button type="button" name="cancel-btn" onclick="cancel()">Hủy</button>
                        <button type="submit" name="find-btn">Tìm kiếm</button>
                    </div>
                </form>
                <div class="register">
                    <p><a href="forget_password.php">Quên mật khẩu?</a></p>
                </div>
            </div>
        </div>
    </section>
    <div class="line3"></div>
</body>
<script>
function cancel() {
    window.location.href = 'login.php';
}
</script>
<?php include '../guest/footer.php' ?>

</html>