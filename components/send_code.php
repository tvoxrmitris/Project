<?php

include '../connection/connection.php';
session_start();
$message = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kiểm tra nếu người dùng nhấn nút gửi mã
    if (isset($_POST['send-btn'])) {
        $email = $_SESSION['searched_email'];

        // Kiểm tra tài khoản
        $sql = "SELECT * FROM users WHERE user_email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $_SESSION['user_name'] = $row['user_name'];

            // Tạo mã xác minh ngẫu nhiên
            $verification_code = rand(100000, 999999);
            $verification_code_time = time(); // Lấy thời gian hiện tại

            // Cập nhật mã xác minh và thời gian vào cơ sở dữ liệu
            $update_sql = "UPDATE users SET verification_code = '$verification_code', verification_code_time = '$verification_code_time' WHERE user_email = '$email'";
            $conn->query($update_sql);

            // Gửi email
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
            $mail->Username = "seraphbeauty22@gmail.com";
            $mail->Password = "einsonpyjjyxepyr"; // Hãy sử dụng biến môi trường cho mật khẩu
            $mail->SetFrom("seraphbeauty22@gmail.com", "Seraph Beauty");
            $mail->Subject = "RESET PASSWORD";
            $mail->Body = "Xin chào " . $row['user_name'] . ",<br><br>Mã xác thực của bạn là: <strong>$verification_code</strong>";
            $mail->AddAddress($email);

            if (!$mail->send()) {
                echo "Mailer error" . $mail->ErrorInfo;
            } else {

                $message[] = "Mã code đã được gửi thành công!";
            }

            $message[] = "Mã code đã được gửi thành công!";

            header("Location: send_code.php");
            exit();
        } else {
            $message[] = "Không tìm thấy tài khoản của bạn. Vui lòng thử lại với thông tin khác.";
        }
    }

    // Kiểm tra nếu người dùng nhấn nút xác nhận mã
    if (isset($_POST['confirm-btn'])) {
        $code = $_POST['code'];

        if (isset($_SESSION['searched_email'])) {
            $email = $_SESSION['searched_email'];

            // Kiểm tra mã xác minh
            $sql = "SELECT * FROM users WHERE user_email = '$email' AND verification_code = '$code'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $verification_code_time = $row['verification_code_time']; // Lấy thời gian mã xác minh

                // Kiểm tra thời gian mã xác minh có còn hiệu lực không
                if ((time() - $verification_code_time) <= 180) { // 10 giây
                    header("Location: reset_password.php");
                    exit();
                } else {
                    $message[] = "Mã code đã hết hạn. Vui lòng yêu cầu mã mới.";

                    $redirect = $_SERVER['PHP_SELF'];
                }
            } else {
                $message[] = "Mã code không đúng. Vui lòng kiểm tra lại.";

                $redirect = $_SERVER['PHP_SELF'];
            }
        } else {
            $message[] = "Có lỗi xảy ra. Vui lòng thử lại sau.";

            $redirect = $_SERVER['PHP_SELF'];
        }

        header("Location: $redirect");
        exit();
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
            <div class="header-main">
                <h1>Xin chào, <?php if (isset($_SESSION['user_name'])) {
                                    echo htmlspecialchars($_SESSION['user_name']);
                                } ?>!</h1>





                <!-- Form để nhập mật khẩu sau khi đã nhập email -->
                <form method="post">
                    <div class="loginpassword">
                        <input type="text" name="code" placeholder="Vui lòng nhập mã code*">
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
                    <div class="gooncf">

                        <button type="submit" name="confirm-btn">Xác nhận</button>
                        <button type="submit" name="send-btn">Gửi</button>
                    </div>
                </form>

            </div>
        </div>
    </section>
    <div class="line3"></div>
</body>


<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('input[name="confirm-btn"]').addEventListener('click', function(event) {

        var codeValue = document.querySelector('input[name="code"]').value;


        if (codeValue.trim() === '') {

            event.preventDefault();

            alert('Vui lòng nhập mã code');
        }
    });
});
</script>

<?php include '../guest/footer.php' ?>

<script src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons.js"></script>

</html>