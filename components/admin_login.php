<?php
include '../connection/connection.php';
session_start();

// Kiểm tra xem nút submit đã được nhấn chưa
if (isset($_POST['submit-btn'])) {
    // Lấy employee_id hoặc email và mật khẩu từ form
    $identifier = mysqli_real_escape_string($conn, $_POST['identifier']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Truy vấn bảng employees để kiểm tra employee_id hoặc email
    $query = "SELECT * FROM employees WHERE (employee_id = '$identifier' OR employee_email = '$identifier') LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $employee = mysqli_fetch_assoc($result);

        // Kiểm tra mật khẩu
        if (password_verify($password, $employee['employee_password'])) {
            // Lấy employee_type và lưu vào session
            $employee_type = $employee['employee_type'];
            $_SESSION['employee_email'] = $employee['employee_email'];
            $_SESSION['employee_type'] = $employee_type;
            $_SESSION['employee_id'] = $employee['employee_id'];

            // Kiểm tra employee_type và chuyển hướng
            try {
                if ($employee_type == 'super admin') {
                    header('Location: ../admin/admin_pannel.php');
                    exit;
                } elseif ($employee_type == 'NVNK') {
                    header('Location: ../NVNK/NVNK_pannel.php');
                    exit;
                } elseif ($employee_type == 'staff') {
                    header('Location: ../staff/staff_pannel.php');
                    exit;
                } else {
                    throw new Exception('Bạn không có quyền truy cập vào trang này!');
                }
            } catch (Exception $e) {
                echo 'Lỗi chuyển hướng: ' . $e->getMessage();
            }
        } else {
            $message[] = 'Mật khẩu không chính xác!';
        }
    } else {
        $message[] = 'Tài khoản không tồn tại';
    }
}
?>




<style type="text/css">
    <?php include '../CSS/style.css';

    ?>.login {
        padding: 40px 0;
        background-color: #fff;
        /* Nền trắng cho phần login */
        text-align: center;
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        /* Bóng đổ nhẹ */
        max-width: 50%;
        margin: 0 auto;
    }

    h1 {
        font-size: 36px;
        font-family: 'Georgia', serif;
        color: #000;
        /* Tiêu đề màu đen */
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    /* Nút đăng nhập */
    button {
        background-color: #000;
        /* Màu nền đen cho nút */
        color: #fff;
        /* Chữ trắng */
        border: none;
        padding: 12px 24px;
        border-radius: 30px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    /* Hiệu ứng khi hover lên nút */
    button:hover {
        background-color: #444;
        /* Đổi thành màu xám khi hover */
        transform: scale(1.05);
    }

    /* Phần login email */
    .loginemail input {
        width: 60%;
        padding: 15px;
        margin: 10px 0;
        border: 2px solid #ccc;
        /* Đường viền xám nhạt */
        border-radius: 5px;
        font-size: 16px;
        background-color: #f4f4f4;
        /* Nền xám nhạt */
    }

    /* Thông báo lỗi và thành công */
    .message {
        background-color: #f8d7da;
        /* Màu thông báo lỗi */
        color: #721c24;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #f5c6cb;
        border-radius: 5px;
    }

    /* Các biểu tượng đóng thông báo */
    .message i {
        cursor: pointer;
    }

    /* Đường viền và hiệu ứng các phần tử khác */
    input[type="text"]:focus {
        border-color: #000;
        /* Đổi màu đường viền khi focus */
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.8);
        /* Hiệu ứng bóng đổ */
    }

    a {
        color: #000;
        /* Liên kết màu đen */
        text-decoration: none;
        font-weight: bold;
    }

    a:hover {
        color: #444;
        /* Màu xám khi hover */
    }

    /* Định dạng cho phần đăng ký */
    .register p {
        font-size: 14px;
        margin-top: 10px;
    }

    .or {
        font-size: 16px;
        font-weight: bold;
        color: #000;
    }

    .continue {
        font-size: 18px;
        color: #333;
        font-weight: 600;
    }

    /* Thêm các phong cách cho nút đăng nhập loại tài khoản */
    .account-type {
        margin: 20px 0;
    }

    .account-btn {
        background-color: #000;
        /* Màu nền đen cho nút */
        color: #fff;
        /* Chữ trắng */
        border: none;
        padding: 12px 24px;
        border-radius: 30px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        width: 100%;
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    .account-btn:hover {
        background-color: #444;
        /* Đổi màu khi hover */
        transform: scale(1.05);
    }
</style>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" type="text/css" href="../CSS/style.css?v=1.1 <?php echo time(); ?>">
    <title>Seraph Beauty - Đăng Nhập</title>
</head>

<body>



    <header class="header">
        <div class="flex">
            <img src="../image/seraph.png" width="100">
        </div>
    </header>
    <div class="line"></div>
    <section class="login">
        <div class="header_guest">
            <div class="header-main">
                <h1>Tài Khoản Quản Trị - Seraph Beauty</h1>





                <!-- Form đăng nhập qua email -->
                <form id="loginForm" method="post">
                    <div class="loginemail">
                        <input type="text" name="identifier" placeholder="Mã số nhân viên*" required>
                        <input type="password" name="password" placeholder="Mật khẩu*" required>
                    </div>
                    <?php
                    if (isset($message)) {
                        foreach ($message as $message) {
                            echo '
            <div class="message">
                <span>' . $message . '</span>
                <i class="bi bi-x-circle" onclick="this.parentElement.remove()"></i>
            </div>
            ';
                        }
                    }
                    ?>
                    <div class="goon">
                        <button type="submit" name="submit-btn">Đăng nhập</button>
                    </div>

                </form>



            </div>
        </div>
    </section>
    <div class="line"></div>
</body>

</html>