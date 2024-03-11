<?php 
    include 'connection.php';

    if(isset($_POST['submit-btn'])){
        $filter_name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $name = mysqli_real_escape_string($conn, $filter_name);

        $filter_email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
        $email = mysqli_real_escape_string($conn, $filter_email);

        $filter_password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
        $password = mysqli_real_escape_string($conn, $filter_password);

        $filter_cpassword = filter_var($_POST['cpassword'], FILTER_SANITIZE_STRING);
        $cpassword = mysqli_real_escape_string($conn, $filter_cpassword);

        $select_user = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'") or die('querry failed');

        if(mysqli_num_rows($select_user)>0){
            $message[] = 'Người dùng đã tồn tại';
        }else{
            if($password != $cpassword){
                $message[] = 'Sai mật khẩu vui lòng thử lại';
            }else{
                mysqli_query($conn, "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')") or die('querry failed');
                $message[] = 'Đăng ký thành công';
                header('location:login.php');
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
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" type="text/css" href="style.css?v=1.1 <?php echo time();?>">
    <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">
    <title>Trang đăng ký</title>
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
            <h1>Trang đăng ký</h1>
            <input type="text" name="name" placeholder="Vui lòng nhập tên của bạn" required>
            <input type="email" name="email" placeholder="Vui lòng nhập địa chỉ Email" required>
            <input type="password" name="password" placeholder="Hãy nhập mật khẩu" required>
            <input type="password" name="cpassword" placeholder="Xác nhận lại mật khẩu" required>
            <input type="submit" name="submit-btn" value="Đăng ký ngay" class="btn">   
            <p>Bạn đã có tài khoản?><a href="login.php">Đăng nhập ngay</a></p>  

        </form>
    </section>
    
</body>
</html>