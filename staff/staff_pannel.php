<?php
include '../connection/connection.php';
session_start();

if (!isset($_SESSION['employee_id']) || $_SESSION['employee_type'] !== 'staff') {
    header('location:../components/admin_login.php');
    exit;
}



// Xử lý đăng xuất
if (isset($_POST['logout'])) {
    session_destroy();
    header('location:../components/admin_login.php');
    exit;
}
?>

<style type="text/css">
<?php include '../CSS/style.css'
?>
</style>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap');

/* Áp dụng font Poppins cho toàn bộ trang */
body {
    font-family: 'Gucci Sans:', sans-serif;
}

.blur {
    filter: blur(5px);
    transition: filter 0.3s ease;
}

.box p,
.box h3 {
    color: black;
    /* Màu đen */
}

body {
    background-color: #f9f1f1;
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
    <title>Seraph Beauty - Trang Chủ</title>
</head>

<body>
    <?php include '../staff/staff_header.php'; ?>
    <?php
    if (isset($_POST[''])) {
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






    <div id="content-wrapper">




        <section class="dashboard">
            <div class="box-container">











                <a href="order.php" style="text-decoration: none;">
                    <div class="box" style="cursor: pointer;">
                        <?php
                        $select_order = mysqli_query($conn, "SELECT * FROM orders") or die('query failed');
                        $num_of_order = mysqli_num_rows($select_order);
                        ?>
                        <img src="../image/icons/order.png" alt="" style="width: 40px; height: 40px;">

                        <p>Đơn hàng</p>
                        <h3><?php echo $num_of_order; ?></h3>
                    </div>
                </a>












            </div>

        </section>
    </div>




    <script>
    document.getElementById("myForm").onsubmit = function() {
        window.location = this.action;
        return false;
    };
    </script>
    <script type="text/javascript" src="../js/script.js"></script>
</body>

</html>