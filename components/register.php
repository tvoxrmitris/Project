<?php
include '../connection/connection.php';
session_start();

$message = []; // Khai báo mảng để chứa thông điệp
$user_id = null; // Khởi tạo biến user_id
if (isset($_POST['submit-btn'])) {

    $filter_name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $name = mysqli_real_escape_string($conn, $filter_name);

    $filter_number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
    $number = mysqli_real_escape_string($conn, $filter_number);

    $filter_email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
    $email = mysqli_real_escape_string($conn, $filter_email);

    $filter_password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
    $password = mysqli_real_escape_string($conn, $filter_password);

    $filter_cpassword = filter_var($_POST['cpassword'], FILTER_SANITIZE_STRING);
    $cpassword = mysqli_real_escape_string($conn, $filter_cpassword);

    // Kiểm tra điều kiện mật khẩu
    if ($password != $cpassword) {
        $message[] = 'Mật khẩu không khớp vui lòng thử lại';
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.{8,})/', $password)) {
        $message[] = 'Mật khẩu phải có ít nhất 8 ký tự, 1 chữ cái viết hoa và 1 ký tự đặc biệt';
    } else {
        // Kiểm tra xem email đã tồn tại trong cơ sở dữ liệu chưa
        $query = "SELECT * FROM users WHERE user_email = '$email' LIMIT 1"; // Giả sử bảng của bạn là users và cột email là user_email
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $message[] = 'Email đã tồn tại, vui lòng thử với email khác';
        } else {
            // Thay vì thêm người dùng vào cơ sở dữ liệu, thực hiện việc gửi email
            $verification_code = rand(100000, 999999);

            // Lưu mã xác minh và thời gian tạo mã vào phiên
            $_SESSION['verification_code'] = $verification_code;
            $_SESSION['verification_code_time'] = time(); // Thời gian hiện tại
            $_SESSION['searched_email'] = $email; // Lưu email để sử dụng sau này nếu cần

            // Lưu thêm các thông tin vào session
            $_SESSION['name'] = $name;
            $_SESSION['number'] = $number;
            $_SESSION['email'] = $email;
            $_SESSION['password'] = $password;
            $_SESSION['cpassword'] = $cpassword;

            require "../mail/PHPMailer/src/PHPMailer.php";
            require "../mail/PHPMailer/src/SMTP.php";
            require "../mail/PHPMailer/src/Exception.php";

            $mail = new PHPMailer\PHPMailer\PHPMailer();
            $mail->IsSMTP();

            $mail->SMTPDebug = 1;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl';
            $mail->Host = "smtp.gmail.com";
            $mail->Port = 465;
            $mail->IsHTML(true);

            // Đảm bảo email hỗ trợ UTF-8
            $mail->CharSet = 'UTF-8';
            $mail->Username = "seraphbeauty22@gmail.com";
            $mail->Password = "einsonpyjjyxepyr"; // Hãy sử dụng biến môi trường cho mật khẩu
            $mail->SetFrom("seraphbeauty22@gmail.com", "Seraph Beauty");
            $mail->Subject = "XÁC THỰC MẬT KHẨU";

            // Nội dung email có thể chứa ký tự tiếng Việt
            $mail->Body = "Xin chào, " . $name . ",<br><br>Bạn đang tạo tài khoản Seraph Beauty bằng tài khoản email $email.<br> Mã xác thực của bạn là: <strong>$verification_code</strong><br>Mã này sẽ hết hạn trong 3 phút. Nếu bạn không yêu cầu email này, bạn có thể bỏ qua.";
            $mail->AddAddress("$email");

            if (!$mail->send()) {
                echo "Mailer error: " . $mail->ErrorInfo;
            } else {
                $_SESSION['success_message'] = "Mã code đã được gửi thành công!";
                header("Location: cf_code_register.php");
                exit();
            }
        }
    }
}
?>

<?php

if (isset($_POST['verify-btn'])) {
    $user_code = $_POST['verification_code'];

    if (isset($_SESSION['verification_code']) && isset($_SESSION['verification_code_time'])) {
        // Kiểm tra thời gian hết hạn mã xác minh
        $code_time = $_SESSION['verification_code_time'];
        $current_time = time();

        if (($current_time - $code_time) > 180) { // 180 giây tương đương với 3 phút
            unset($_SESSION['verification_code']);
            unset($_SESSION['verification_code_time']);
            $message[] = "Mã xác minh đã hết hạn. Vui lòng yêu cầu mã mới.";
        } elseif ($user_code == $_SESSION['verification_code']) {
            $message[] = "Xác minh thành công!";
            // Xóa mã sau khi sử dụng
            unset($_SESSION['verification_code']);
            unset($_SESSION['verification_code_time']);
        } else {
            $message[] = "Mã xác minh không đúng. Vui lòng thử lại.";
        }
    } else {
        $message[] = "Không có mã xác minh nào được tìm thấy. Vui lòng yêu cầu mã mới.";
    }
}
?>
<style>
html,
body {
    height: 100%;
}

.footer {
    position: relative;
    bottom: 0;
    width: 100%;
    background-color: #f1f1f1;
    text-align: center;
    padding: 10px 0;
}
</style>

<!DOCTYPE HTML>
<html lang="vi">

<head>
    <title>Seraph Beauty - Đăng Ký</title>
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
                <h1>Đăng ký tài khoản Seraph Beauty</h1>



                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="registerr">
                        <input class="nameuser" type="text" name="name" placeholder="Tên người dùng" required>
                        <input class="numberuser" type="text" name="number" placeholder="Số điện thoại người dùng"
                            required>
                        <input class="emailuser" type="text" placeholder="Email" name="email" onfocus="this.value = '';"
                            onblur="if (this.value == '') {this.value = 'Email';}" />
                        <input class="password" type="password" placeholder="Hãy nhập mật khẩu" name="password"
                            onfocus="this.value = '';"
                            onblur="if (this.value == '') {this.value = 'Vui lòng nhập mật khẩu';}" />
                        <input class="cpassword" type="password" placeholder="Xác nhận lại mật khẩu" name="cpassword"
                            onfocus="this.value = '';" onblur="if (this.value == '') {this.value = 'Mật khẩu';}" />
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
                        <input type="submit" name="submit-btn" value="Đăng ký" class="btn">
                    </div>
                </form>
            </div>
        </div>
    </section>
    <div class="line3"></div>
    <?php include '../guest/footer.php' ?>
</body>

</html>