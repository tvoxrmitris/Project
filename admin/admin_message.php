<?php
include '../connection/connection.php';
session_start();
if (!isset($_SESSION['employee_email']) || $_SESSION['employee_type'] !== 'super admin') {
    header('location:../components/admin_login.php');
    exit;
}



// Xử lý đăng xuất
if (isset($_POST['logout'])) {
    session_destroy();
    header('location:../components/admin_login.php');
    exit;
}



//delete product from database
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    mysqli_query($conn, "DELETE FROM `message` WHERE message_id = $delete_id") or die('query failed');

    header('location:../admin/admin_message.php');
}

?>
<style type="text/css">
    <?php include '../CSS/style.css';
    ?>
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
    <title>Seraph Beauty - Tin Nhắn</title>
</head>

<body>
    <?php include '../admin/admin_header.php'; ?>
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
    <h1 class="title">Tin nhắn chưa đọc</h1>
    <section class="message-container">

        <div class="box-container">
            <?php
            $select_message = mysqli_query($conn, "SELECT * FROM `message`") or die('query failed');
            if (mysqli_num_rows($select_message) > 0) {
                while ($fetch_message = mysqli_fetch_assoc($select_message)) {

            ?>
                    <div class="box">
                        <p>Id người dùng: <span><?php echo $fetch_message['user_id']; ?></span></p>
                        <p>Tên: <span><?php echo $fetch_message['user_name']; ?></span></p>
                        <p>Email: <span><?php echo $fetch_message['user_email']; ?></span></p>
                        <p><?php echo $fetch_message['message']; ?></p>
                        <div class="delete">
                            <a class="btn-delete"
                                href="../admin/admin_message.php?delete=<?php echo $fetch_message['message_id']; ?>"
                                onclick="return confirm('Xóa tin nhắn này'); ">Xóa</a>

                        </div>

                    </div>
            <?php
                }
            } else {
                echo '
                        <div class="empty">
                            <p>Chưa có tin nhắn nào!</p>
                        </div>
                    ';
            }
            ?>
        </div>
    </section>
    <div class="line"></div>
    <script src="../js/script.js"></script>
</body>

</html>