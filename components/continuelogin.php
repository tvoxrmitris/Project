<?php
include '../connection/connection.php';
session_start();

if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit();
}

if (isset($_SESSION['user_email'])) {
    $email = $_SESSION['user_email'];

    // Truy vấn để lấy thông tin từ CSDL dựa trên email
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_name = $row['user_name'];
        $user_password_hash = $row['user_password'];
    } else {
        $message[] = 'Không tìm thấy người dùng.';
        exit();
    }
} else {
    echo 'Không tìm thấy email trong session';
    $message[] = 'Tài khoản không tồn tại.';
    exit();
}

if (isset($_POST['login-btn'])) {
    $entered_password = $_POST['password'];

    // Kiểm tra mật khẩu
    if (password_verify($entered_password, $user_password_hash)) {
        $_SESSION['user_name'] = $user_name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_id'] = $row['user_id'];

        // Lưu product_id từ URL nếu có
        if (isset($_GET['product_id'])) {
            $_SESSION['product_id'] = intval($_GET['product_id']);
        }

        // Điều hướng người dùng
        if (isset($_SESSION['redirect_to'])) {
            $redirect_to = $_SESSION['redirect_to'];
            if ($redirect_to == 'checkout') {
                // Truyền product_id vào URL khi điều hướng tới checkout.php
                $product_id = isset($_SESSION['product_id']) ? $_SESSION['product_id'] : null;
                if ($product_id !== null) {
                    header("Location: ../user/checkout.php?product_id=$product_id");
                } else {
                    header('Location: ../user/checkout.php?sessioncart');
                }
            } else {
                unset($_SESSION['redirect_to']); // Xóa redirect nếu không phải checkout
                header('Location: ../user/index.php');
            }
            exit();
        } else {
            header('Location: ../user/index.php');
            exit();
        }
    } else {
        $message[] = 'Mật khẩu không chính xác!';
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
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js" crossorigin="anonymous"
        referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" type="text/css" href="../CSS/main.css?v=1.1 <?php echo time(); ?>">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <!-- <?php include '../guest/header_guest.php'; ?> -->

    <section class="login">
        <div class="header_guest">
            <div class="header-main">
                <h1>Xin chào, <?php echo htmlspecialchars($user_name); ?>!</h1>

                <div class="emailname">
                    Bạn đang đăng nhập với <?php echo htmlspecialchars($email); ?>
                </div>

                <!-- Form để nhập mật khẩu sau khi đã nhập email -->
                <form method="post">
                    <div class="loginpassword">
                        <input type="password" name="password" placeholder="Nhập mật khẩu*" required>
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
                        <button type="submit" name="login-btn">Đăng nhập</button>
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
<?php include '../guest/footer.php'; ?>

</html>