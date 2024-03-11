<?php
    include 'connection.php';
    session_start();

    if (isset($_POST['submit-btn'])) {

        $filter_email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
        $email = mysqli_real_escape_string($conn, $filter_email);

        $filter_password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
        $password = mysqli_real_escape_string($conn, $filter_password);

        $select_user = mysqli_query($conn, "SELECT * FROM `users` WHERE email='$email'") or die('query failed');

        if (mysqli_num_rows($select_user) > 0) {
            $row = mysqli_fetch_assoc($select_user);

            if ($row['password'] == $password && $row['user_type'] == 'admin') {
                $_SESSION['admin_name'] = $row['name'];
                $_SESSION['admin_email'] = $row['email'];
                $_SESSION['admin_id'] = $row['id'];
                header('location:admin_pannel.php');
            } else if($row['password'] == $password && $row['user_type'] == 'user'){
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['user_email'] = $row['email'];
                $_SESSION['user_id'] = $row['id'];
                header('location:index.php');
            }else{
                $message[] = 'Email hoặc mặt khẩu không chính xác';
            }
        } 
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- box icon link -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="style.css?v=1.1 <?php echo time();?>">
    <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">

    <title>Trang đăng nhập</title>
</head>
<body>

    <section class="form-container">
        <?php
            if(isset($message)){
                foreach($message as $message){
                    echo '
                        <div class="message">
                            <span>'.$message.'</span>
                            <i class="bi bi-x-circle" onclick="this.parentElement.remove()"></i>
                        </div>
                    ';
                }
            }
        ?>
        <form method="post">
            <h1>Đăng nhập ngay</h1>
            <div class = "input-field">
                <label>Email</label><br>
                <input type="email" name="email" placeholder="Vui lòng nhập địa chỉ email" required>
            </div>
            <div class = "input-field">
                <label>Mật khẩu</label><br>
                <input type="password" name="password" placeholder="Vui lòng nhập mật khẩu" required>
            </div>
            <input type="submit" name="submit-btn" value="Đăng nhập ngay" class="btn">   
            <p>Bạn chưa có tài khoản?><a href="register.php">Đăng ký ngay</a></p>  

        </form>
    </section>
    
</body>
</html>