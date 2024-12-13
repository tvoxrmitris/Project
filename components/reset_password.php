<?php

include '../connection/connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy giá trị nhập từ form
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    // Kiểm tra xem password và cpassword có giống nhau không
    if ($password === $cpassword) {
        // Kiểm tra điều kiện mật khẩu: ít nhất 8 ký tự và có ít nhất 1 ký tự hoa
        if (strlen($password) >= 8 && preg_match('/[A-Z]/', $password)) {
            // Lấy email từ session để xác định người dùng
            $email = $_SESSION['searched_email'];

            // Mã hóa mật khẩu trước khi lưu vào cơ sở dữ liệu (đảm bảo mã hóa an toàn như bcrypt)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Chuẩn bị truy vấn SQL để cập nhật mật khẩu trong cơ sở dữ liệu
            $sql_update_password = "UPDATE users SET user_password = '$hashed_password' WHERE user_email = '$email'";
            $conn->query($sql_update_password);

            // Điều hướng người dùng đến trang thông báo thành công hoặc trang đăng nhập, tùy thuộc vào yêu cầu
            header("Location: login.php?success=1");
            exit();
        } else {
            // Nếu mật khẩu không đáp ứng điều kiện, hiển thị thông báo lỗi
            $message[] = "Mật khẩu phải có ít nhất 8 ký tự và chứa ít nhất một ký tự hoa.";
            $_SESSION['error_message'] = $message;
        }
    } else {
        // Nếu password và cpassword không trùng khớp, hiển thị thông báo lỗi
        $message[] = "Mật khẩu không trùng khớp. Vui lòng nhập lại.";
        $_SESSION['error_message'] = $message;
    }
}


?>


<!DOCTYPE HTML>
<html lang="vi">

<head>
    <title>Seraph Beauty - Đăng Nhập</title>
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
            <div class="header-mainn">
                <h1>Xin chào, <?php if (isset($_SESSION['user_name'])) {
                                    echo htmlspecialchars($_SESSION['user_name']);
                                } ?>!</h1>



                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="reser_pw">
                        <input class="password" type="password" placeholder="Nhập mật khẩu mới" name="password"
                            onfocus="this.value = '';"
                            onblur="if (this.value == '') {this.value = 'Vui lòng nhập mật khẩu';}" />
                        <input class="cpassword" type="password" placeholder="Xác nhận lại mật khẩu" name="cpassword"
                            onfocus="this.value = '';" onblur="if (this.value == '') {this.value = 'Mật khẩu';}" />
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


                        <input class="confirm" type="submit" name="confirm-btn" value="Xác nhận" class="btn">
                    </div>

                </form>



            </div>
        </div>
    </section>
    <div class="line3"></div>
</body>



<?php include '../guest/footer.php' ?>


<script src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons.js"></script>

</html>