<?php
include '../connection/connection.php';
session_start();
$admin_id = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];

if(!isset($admin_id)){
    header('location:../components/login.php');
}

if(isset($_POST['logout'])){
    session_destroy();
    header('location:../components/login.php');
}

if(isset($_POST['update-edit'])) {
    // Lấy giá trị mới từ form
    $new_name = $_POST['name'];
    $new_email = $_POST['email'];
    $new_number = $_POST['number'];
    $old_password = $_POST['oldpassword'];
    $new_password = $_POST['newpassword'];
    $confirm_new_password = $_POST['cnewpassword'];

    // Kiểm tra mật khẩu cũ có khớp với mật khẩu trong cơ sở dữ liệu không
    $query_check_password = "SELECT user_password FROM users WHERE user_id = ?";
    $stmt_check_password = $conn->prepare($query_check_password);
    $stmt_check_password->bind_param("i", $user_id);
    $stmt_check_password->execute();
    $result_check_password = $stmt_check_password->get_result();
    $row_password = $result_check_password->fetch_assoc();
    $stored_password = $row_password['user_password'];

    // Kiểm tra mật khẩu cũ
    if (password_verify($old_password, $stored_password)) {
        // Mật khẩu cũ khớp, tiếp tục kiểm tra mật khẩu mới
        if ($new_password === $confirm_new_password) {
            // Kiểm tra mật khẩu mới: ít nhất 8 ký tự và có ít nhất một chữ cái in hoa
            if (preg_match('/^(?=.*[A-Z]).{8,}$/', $new_password)) {
                // Mật khẩu mới hợp lệ, thực hiện cập nhật dữ liệu
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Cập nhật thông tin người dùng (bao gồm cả mật khẩu nếu có)
                $query_update = "UPDATE users SET user_name = ?, user_email = ?, user_number = ?, user_password = ? WHERE user_id = ?";
                $stmt_update = $conn->prepare($query_update);
                $stmt_update->bind_param("ssisi", $new_name, $new_email, $new_number, $hashed_password, $user_id);

                if ($stmt_update->execute()) {
                    $message[] = "Cập nhật thành công!";
                } else {
                    $message[] = "Cập nhật không thành công!";
                }
            } else {
                // Mật khẩu mới không hợp lệ (không đủ ký tự hoặc không có chữ cái in hoa)
                $message[] = "Mật khẩu mới phải chứa ít nhất 8 ký tự và một chữ cái in hoa!";
            }
        } else {
            // Mật khẩu mới không khớp
            $message[] = "Xác nhận mật khẩu mới không chính xác!";
        }
    } else {
        // Mật khẩu cũ không khớp
        $message[] = "Mật khẩu không trùng khớp!";
    }
}
?>

<style type="text/css">
<?php include 'main.css'
?>
</style>



</style>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css"
        integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link class="logoo" rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css"
        integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js"
        integrity="sha512-HGOnQO9+SP1V92SrtZfjqxxtLmVzqZpjFFekvzZVWoiASSQgSr4cw9Kqd2+l8Llp4Gm0G8GIFJ4ddwZilcdb8A=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.js"
        integrity="sha512-eP8DK17a+MOcKHXC5Yrqzd8WI5WKh6F1TIk5QZ/8Lbv+8ssblcz7oGC8ZmQ/ZSAPa7ZmsCU4e/hcovqR8jfJqA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" type="text/css" href="../CSS/main.css?v=1.1 <?php echo time();?>">
    <!-- <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon"> -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Home</title>
</head>

<body>
    <!-- <div class="line3"></div> -->
    <?php include '../user/header.php'?>

    <div class="line"></div>




    <div class="edit-profile">
        <div class="row">
            <?php
        $image_names = array("bannermakeup.jpg", "../image/banner/arcane.jpg", "../image/product/traceoutliplinerBrownOuta2.jpg","../image/product/universalluminizerGlassSlipa2.jpg");
        $select_user = mysqli_query($conn, "SELECT * FROM users WHERE user_id='$user_id'") or die('Query failed');
        if(mysqli_num_rows($select_user) > 0){
            while($fetch_user = mysqli_fetch_assoc($select_user)){
        ?>
            <div class="img-about" style="position: relative;">
                <?php foreach ($image_names as $index => $image_name) { ?>
                <img class="imgshop <?php if ($index !== 0) echo 'hidden'; ?>" src="../image/<?php echo $image_name; ?>"
                    data-index="<?php echo $index; ?>">
                <?php } ?>
                <div class="dot-about">
                    <?php for ($i = 0; $i < count($image_names); $i++) { ?>
                    <span class="dot <?php if ($i === 0) echo 'active'; ?>" data-index="<?php echo $i; ?>"
                        data-product-id="<?php echo $fetch_products['product_id']; ?>"></span>
                    <?php } ?>
                </div>
            </div>

            <form method="post">
                <?php
    if(isset($message)){
        foreach($message as $message){
            echo '
                <div class="message" style="padding: 5px 0; color: #000; margin-bottom: 1rem; background-color: #ffcccc; border: 1px solid #990000;">
                    <span>' . $message . '</span>
                    <i class="bi bi-x-circle" onclick="this.parentElement.remove()"></i>
                </div>
            ';
        }
    }
    ?>
                <div class="input-field">
                    <label>Tên của bạn<span>*</span></label>
                    <input type="text" name="name" placeholder="Nhập tên của bạn"
                        value="<?php echo $fetch_user['user_name']; ?>" required>
                </div>
                <div class="input-field">
                    <label>Email của bạn<span>*</span></label>
                    <input type="text" name="email" placeholder="Hãy nhập email của bạn"
                        value="<?php echo $fetch_user['user_email']; ?>" required>
                </div>
                <div class="input-field">
                    <label>Số điện thoại của bạn<span>*</span></label>
                    <input type="number" name="number" placeholder="Nhập số điện thoại của bạn"
                        value="0<?php echo $fetch_user['user_number']; ?>" required>
                </div>
                <div class="input-field">
                    <label>Mật khẩu cũ<span>*</span></label>
                    <input type="password" name="oldpassword" placeholder="Nhập mật khẩu hiện tại" required>
                </div>
                <div class="input-field">
                    <label>Mật khẩu mới<span>*</span></label>
                    <input type="password" name="newpassword" placeholder="Nhập mật khẩu mới" required>
                </div>
                <div class="input-field">
                    <label>Xác nhận mật khẩu mới<span>*</span></label>
                    <input type="password" name="cnewpassword" placeholder="Xác nhận mật khẩu mới" required>
                </div>
                <form method="post" class="btn-container">
                    <button type="submit" name="update-edit" class="update-btn">Cập nhật</button>
                </form>
            </form>
        </div>
        <?php
            }
        }
    ?>
    </div>



    <script>
    $('.img-about').slick({
        // dots: true,
        infinite: true,
        autoplay: true,
        autoplaySpeed: 2000,
        lazyLoad: 'ondemand',
        // speed: 300,
        slidesToShow: 1,
        adaptiveHeight: true
    });
    </script>








    <?php include '../user/footer.php'?>



    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script> -->
    <script type="text/javascript" src="../js/script2.js"></script>
</body>

</html>