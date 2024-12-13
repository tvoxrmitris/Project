<?php
include '../connection/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nhấn nút gửi lại mã
    if (isset($_POST['send-btn'])) {
        // Tạo mã xác minh mới
        $verification_code = rand(100000, 999999);

        // Lưu mã xác minh và thời gian tạo mã vào phiên
        $_SESSION['verification_code'] = $verification_code;
        $_SESSION['verification_code_time'] = time(); // Thời gian hiện tại

        // Gửi email bằng PHPMailer
        require "../mail/PHPMailer/src/PHPMailer.php";
        require "../mail/PHPMailer/src/SMTP.php";
        require "../mail/PHPMailer/src/Exception.php";

        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $mail->IsSMTP();

        $mail->SMTPDebug = 0; // Thay đổi thành 0 để tắt debug trong môi trường sản xuất
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = "smtp.gmail.com";
        $mail->Port = 465;
        $mail->IsHTML(true);
        $mail->Username = "seraphbeauty22@gmail.com";
        $mail->Password = "einsonpyjjyxepyr"; // Hãy sử dụng biến môi trường cho mật khẩu
        $mail->SetFrom("seraphbeauty22@gmail.com", "Seraph Beauty");
        $mail->Subject = "RESET PASSWORD";
        $mail->Body = "Xin chào, <br>Bạn đã yêu cầu gửi lại mã xác thực cho tài khoản của bạn.<br>Mã xác thực của bạn là: <strong>$verification_code</strong><br>Mã này sẽ hết hạn trong 3 phút.";
        $mail->AddAddress($_SESSION['searched_email']);

        if (!$mail->send()) {
            echo "Mailer error: " . $mail->ErrorInfo;
        } else {
            $_SESSION['success_message'] = "Mã code đã được gửi thành công!";
            header("Location: cf_code_register.php"); // Điều hướng lại đến trang xác thực
            exit();
        }
    }

    // Nhấn nút xác nhận

}
if (isset($_POST['confirm-btn'])) {
    // Lấy mã người dùng nhập
    $entered_code = $_POST['code'];

    // Kiểm tra mã đã nhập có khớp với mã trong session không
    if ($entered_code == $_SESSION['verification_code']) {
        // Mã xác minh đúng, thực hiện insert vào bảng users

        // Băm mật khẩu trước khi lưu vào cơ sở dữ liệu
        $hashed_password = password_hash($_SESSION['password'], PASSWORD_DEFAULT);

        // Câu lệnh SQL để insert dữ liệu vào bảng users
        $sql = "INSERT INTO users (user_name, user_number, user_email, user_password) 
                VALUES ('{$_SESSION['name']}', '{$_SESSION['number']}', '{$_SESSION['email']}', '$hashed_password')";

        // Thực hiện truy vấn
        if (mysqli_query($conn, $sql)) {
            // Nếu insert thành công
            $message[] = 'Đăng ký thành công!';
            $discount_code = "newseraphbeauty"; // Mã giảm giá của bạn

            require "../mail/PHPMailer/src/PHPMailer.php";
            require "../mail/PHPMailer/src/SMTP.php";
            require "../mail/PHPMailer/src/Exception.php";

            $mail = new PHPMailer\PHPMailer\PHPMailer();
            $mail->IsSMTP();
            $mail->SMTPDebug = 0; // Thay đổi thành 0 để tắt debug trong môi trường sản xuất
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl';
            $mail->Host = "smtp.gmail.com";
            $mail->Port = 465;
            $mail->IsHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Username = "seraphbeauty22@gmail.com";
            $mail->Password = "einsonpyjjyxepyr"; // Hãy sử dụng biến môi trường cho mật khẩu
            $mail->SetFrom("seraphbeauty22@gmail.com", "Seraph Beauty");
            $mail->Subject = "Chào mừng bạn đến với Seraph Beauty!";

            // Nội dung email kèm mã giảm giá
            $mail->Body = "
            Xin chào, <br>
            Cảm ơn bạn đã đăng ký tài khoản tại Seraph Beauty!<br>

        ";
            $mail->AddAddress($_SESSION['email']);

            if (!$mail->send()) {
                echo "Mailer error: " . $mail->ErrorInfo;
            } else {
                $_SESSION['success_message'] = "Mã code đã được gửi thành công!";
            }
            // Hủy session để tránh insert lại
            session_unset();
            session_destroy();

            // Chuyển hướng người dùng đến trang đăng nhập hoặc trang khác
            header('Location: login.php');
            exit();
        } else {
            // Nếu có lỗi trong quá trình insert
            $message[] = 'Có lỗi xảy ra. Vui lòng thử lại.';
        }
    } else {
        // Nếu mã xác minh không khớp
        $message[] = 'Mã xác minh không đúng. Vui lòng thử lại.';
    }
}



// Kiểm tra thời gian hết hạn mã
$verification_duration = 180; // 180 giây (3 phút)

if (isset($_SESSION['verification_code_time'])) {
    $code_time = $_SESSION['verification_code_time'];
    $current_time = time();
    $time_elapsed = $current_time - $code_time;
    $time_remaining = $verification_duration - $time_elapsed;

    // Nếu thời gian còn lại dưới 0, mã đã hết hạn
    if ($time_remaining <= 0) {
        $time_remaining = 0;
        unset($_SESSION['verification_code']); // Xóa mã
        unset($_SESSION['verification_code_time']); // Xóa thời gian
        $message[] = "Mã xác minh đã hết hạn. Vui lòng yêu cầu mã mới.";
    }

    // Lưu thời gian còn lại vào session để dùng cho lần tải lại tiếp theo
    $_SESSION['time_remaining'] = $time_remaining;
}
?>

<style>
.login .header img {
    width: 100px;
    /* Điều chỉnh chiều rộng hình ảnh theo nhu cầu */
    height: auto;
    /* Đảm bảo tỉ lệ hình ảnh không bị bóp méo */
    max-width: 100%;
    /* Đảm bảo hình ảnh không lớn hơn chiều rộng container */
    display: block;
    margin: 0 auto;
    /* Canh giữa hình ảnh */
}

.going-cf button {
    background-color: gray;
    /* Màu nền mặc định khi thời gian còn lại */
    color: white;
    /* Màu chữ */
    border: none;
    padding: 10px 15px;
    cursor: not-allowed;
    /* Con trỏ chuột cho nút không khả dụng */
}

.going-cf button.enabled {
    background-color: black;
    /* Màu nền khi nút khả dụng */
    cursor: pointer;
    /* Con trỏ chuột cho nút có thể nhấn */
}

.tii {
    text-align: center;
    font-size: 1rem;
    text-transform: uppercase;
    margin: 1.5rem 0;
}

span {
    text-align: center;
    font-size: 1.1rem;
    font-weight: 500;
    letter-spacing: -.04em;
    line-height: 1.5rem;
    padding-bottom: 1rem;
}
</style>

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <?php include '../guest/header_guest.php'; ?>

    <section class="login">
        <div class="header_guest">
            <div class="header-main">
                <div>
                    <img style="width: 50px" src="../image/codecf.png" alt="Logo">
                </div>
                <div class="tii">Hãy nhập mã code 6 ký tự được gửi qua email của bạn!</div>
                <!-- Form để nhập mã xác minh -->
                <form method="post">
                    <div class="loginpassword">
                        <input type="text" name="code" placeholder="Vui lòng nhập mã code*">
                        <!-- Giữ thuộc tính required -->
                    </div>
                    <!-- Nơi hiển thị đếm ngược -->
                    <div class="timer">
                        <span id="countdown"></span>
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
                        <button type="submit" name="send-btn">Gửi lại mã</button> <!-- Gửi lại mã không cần nhập -->
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script>
    // Lấy thời gian còn lại từ PHP lưu trong session
    let timeLeft = <?= $_SESSION['time_remaining'] ?? 0 ?>; // Khởi tạo timeLeft
    const countdownElement = document.getElementById('countdown'); // Lấy phần tử hiển thị đếm ngược
    const timerDiv = document.querySelector('.timer'); // Lấy div chứa timer
    const expiredMessage = document.createElement('div'); // Tạo phần tử thông báo mã đã hết hạn

    // Lấy phần tử nút gửi lại mã
    const resendButton = document.querySelector('button[name="send-btn"]');

    // Tạo hàm đếm ngược
    const timerInterval = setInterval(() => {
        // Cập nhật phần tử hiển thị đếm ngược với tổng số giây còn lại
        countdownElement.textContent = `Mã sẽ hết hạn sau ${timeLeft} giây`;

        // Giảm thời gian
        timeLeft--;

        // Kiểm tra nếu thời gian đã hết
        if (timeLeft < 0) {
            clearInterval(timerInterval);

            // Ẩn phần tử .timer khi mã hết hạn
            timerDiv.style.display = 'none';

            // Hiển thị thông báo mã đã hết hạn
            expiredMessage.textContent = "Mã đã hết hạn!";
            expiredMessage.style.color = 'red'; // Thay đổi màu sắc cho thông báo
            timerDiv.parentNode.insertBefore(expiredMessage, timerDiv.nextSibling); // Thêm thông báo vào DOM

            // Enable lại nút gửi lại mã
            resendButton.disabled = false;

            // Thêm lớp expired để thay đổi màu sắc
            countdownElement.classList.add('expired');
        } else {
            // Disable nút gửi lại mã trong khi đếm ngược
            resendButton.disabled = true;
        }
    }, 1000); // Cập nhật mỗi giây (1000 ms)
    </script>




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

<div class="line3"></div>
<?php include '../guest/footer.php' ?>

<script src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons.js"></script>

</html>