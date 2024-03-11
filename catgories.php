<?php
    include 'connection.php';
    session_start();
    $admin_id = $_SESSION['user_name'];
    $user_id = $_SESSION['user_id'];

    if(!isset($admin_id)){
        header('location:login.php');
    }

    if(isset($_POST['logout'])){
        session_destroy();
        header('location:login.php');
    }
    ?>

<style type="text/css">
    <?php
        include 'main.css'
    ?>
</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" type="text/css" href="main.css?v = <?php echo time(); ?>">
</head>
<body>
    <?php include 'header.php'?>
    <!-- slider section star -->
    
    <div class="products">
    <?php 
        $category_id = $_GET['id'];
        $sql = "SELECT name FROM categories WHERE id = $category_id";
        $result = mysqli_query($conn, $sql);
        $category_name = mysqli_fetch_assoc($result)['name'];
    ?>
    <div class="heading">
        <h1 style="text-transform: uppercase;"><?= $category_name; ?></h1>
    </div>
    <div class="box-container" style="margin-top: -1rem;">
    <?php 
        include 'connection.php'; // Kết nối cơ sở dữ liệu

        $category_id = $_GET['id'];

        $select_categories = $conn->prepare("SELECT name FROM categories WHERE id = ?");
        $select_categories->bind_param("i", $category_id);
        $select_categories->execute();
        $result_categories = $select_categories->get_result();

        if ($result_categories->num_rows > 0) {
            $category_name = $result_categories->fetch_assoc()['name'];

            $status = 'Đang hoạt động';
            $select_products = $conn->prepare("SELECT * FROM `products` WHERE BINARY categories = ? AND status = ?");
            $select_products->bind_param("ss", $category_name, $status);
            $select_products->execute();

            $result_products = $select_products->get_result();

            if ($result_products->num_rows > 0) {
                while ($fetch_products = $result_products->fetch_assoc()) {
                    // Your code logic here
                }
            }
        }
    ?>
</div>

    ?>
</div>

</div>

    

    <script src="http://cdnjs.cloudflare.com/ajax.libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script type="text/javascript" src="script2.js"></script>
    <?php include 'footer.php'?>
</body>
</html>