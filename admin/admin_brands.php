<?php
include '../connection/connection.php';
// Đảm bảo không có khoảng trắng, ký tự hoặc output nào trước khi gọi session_start()
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

//adding product to database
if (isset($_POST['add_brands'])) {
    $brand_name = mysqli_real_escape_string($conn, $_POST['name']);
    $brand_image = $_FILES['image']['name'];
    $brand_image_tmp_name = $_FILES['image']['tmp_name'];
    $brand_image_folder = 'image/' . $brand_image;

    // Kiểm tra xem tên thương hiệu đã tồn tại hay chưa
    $select_brand_name = mysqli_query($conn, "SELECT brand_name FROM brands WHERE brand_name = '$brand_name'") or die('query failed');
    if (mysqli_num_rows($select_brand_name) > 0) {
        $message[] = 'Tên thương hiệu đã tồn tại';
    } else {
        // Thêm thương hiệu mới vào cơ sở dữ liệu
        $insert_brand = mysqli_query($conn, "INSERT INTO brands(brand_name, brand_image) VALUES('$brand_name', '$brand_image')") or die('query failed');

        // Nếu thêm thương hiệu thành công, thì tải lên hình ảnh thương hiệu
        if ($insert_brand) {
            if (move_uploaded_file($brand_image_tmp_name, $brand_image_folder)) {
                $message[] = 'Thương hiệu đã được thêm thành công';
            } else {
                $message[] = 'Không thể tải lên hình ảnh thương hiệu';
            }
        } else {
            $message[] = 'Không thể thêm thương hiệu: ' . mysqli_error($conn);
        }
    }
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $select_delete_image = mysqli_query($conn, "SELECT brand_image FROM `brands` WHERE brand_id='$delete_id'") or die('query failed');
    $fetch_delete_image = mysqli_fetch_assoc($select_delete_image);
    mysqli_query($conn, "DELETE FROM `brands` WHERE brand_id = '$delete_id'") or die('query faild');
    header('location:../admin/admin_brands.php');
}

// Update product
if (isset($_POST['update_brand'])) {
    $update_id = $_POST['update_id'];
    $update_name = $_POST['update_name'];
    $update_image = $_FILES['update_image']['name']; // Vẫn giữ tên ảnh

    // Cập nhật thông tin vào cơ sở dữ liệu mà không cần di chuyển file
    $update_query = mysqli_query($conn, "UPDATE `brands` SET brand_name = '$update_name', brand_image = '$update_image' WHERE brand_id = '$update_id'") or die('query failed');

    if ($update_query) {
        // Không cần di chuyển file
        header('location:../admin/admin_brands.php');
        exit(); // Đảm bảo kết thúc script sau khi chuyển hướng
    }
}

if (isset($_POST['cancel-form'])) {
    header('location:../admin/admin_brands.php');
    exit();
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
    <title>Seraph Beauty - Thương Hiệu</title>
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


    <?php
    if (isset($_GET['edit'])) {
        $edit_id = $_GET['edit'];
        $edit_query = mysqli_query($conn, "SELECT * FROM brands WHERE brand_id='$edit_id'") or die('query failed');
        if (mysqli_num_rows($edit_query) > 0) {
            while ($fetch_edit = mysqli_fetch_assoc($edit_query)) {
    ?>

                <section class="update_brand form-container">
                    <div class="title">
                        <h2 style="font-size:50px;">Chỉnh sửa thương hiệu</h2>
                    </div>
                    <form method="POST" enctype="multipart/form-data" class="form-content">
                        <input type="hidden" name="update_id" value="<?php echo $fetch_edit['brand_id']; ?>">
                        <img style="width:40%; border:2px solid black; margin-bottom: 25px;"
                            src="../image/<?php echo $fetch_edit['brand_image']; ?>">

                        <div class="input-field">
                            <label>Tên thương hiệu<span>*</span><br></label>
                            <input type="text" name="update_name" value="<?php echo $fetch_edit['brand_name']; ?>" required>
                        </div>
                        <div class="input-field">
                            <label>Hình ảnh thương hiệu<span>*</span></label>
                            <input type="file" name="update_image" accept="image/jpg, image/jpeg, image/png, image/webp">
                        </div>
                        <div class="input-group">
                            <input type="submit" name="update_brand" value="Cập nhật" class="edit">
                            <input type="button" value="Hủy" class="cancel" id="cancel-form">
                        </div>
                    </form>
                </section>



        <?php
            }
        }
    } else {
        ?>
        <div class="title">
            <h2 style="font-size:50px;">Thêm thương hiệu</h2>
        </div>
        <section class="add-products form-container">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="input-field">
                    <label>Tên thương hiệu<span>*</span><br></label>
                    <input type="text" name="name" required>
                </div>
                <div class="input-field">
                    <label>Hình ảnh thương hiệu<span>*</span></label>
                    <input type="file" name="image" accept="image/jpg, image/jpeg, image/png, image/webp" required>
                </div>
                <input type="submit" name="add_brands" class="add_brands" value="Thêm thương hiệu" class="btn">
            </form>
        </section>



        <section class="show-products">
            <div class="title">
                <h1>Thương hiệu đã thêm</h1>
            </div>
            <div class="line2"></div>
            <div class="box-container">
                <?php
                $select_brands = mysqli_query($conn, "SELECT * FROM `brands`") or die('query failed');
                if (mysqli_num_rows($select_brands) > 0) {
                    while ($fetch_brands = mysqli_fetch_assoc($select_brands)) {
                ?>
                        <div class="box">
                            <a href="../admin/view_product_brand.php?brand_id=<?php echo $fetch_brands['brand_id']; ?>">
                                <img src="../image/<?php echo $fetch_brands['brand_image']; ?>"
                                    alt="<?php echo $fetch_brands['brand_name']; ?>">
                            </a>
                            <h4 class="brand_name"><?php echo $fetch_brands['brand_name']; ?></h4><br><br>


                            <div class="button-container">
                                <a href="../admin/admin_brands.php?edit=<?php echo $fetch_brands['brand_id']; ?>"
                                    class="edit">Sửa</a>
                                <a href="../admin/admin_brands.php?delete=<?php echo $fetch_brands['brand_id']; ?>" class="delete"
                                    onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này');">Xóa</a>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo '<div class="empty"><p>Chưa có thương hiệu được thêm!</p></div>';
                }
                ?>
            </div>
        </section>


    <?php
    }
    ?>

    <div class="line"></div>

    <script type="text/javascript">
        document.getElementById('cancel-form').addEventListener('click', function() {
            window.location.href = '../admin/admin_brands.php';
        });
    </script>

    <script>
        // JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            var boxes = document.querySelectorAll('.box');

            boxes.forEach(function(box) {
                box.addEventListener('click', function() {
                    // Thêm lớp clicked khi hộp được nhấp
                    this.classList.toggle('clicked');

                    // Xóa lớp clicked khỏi các hộp khác nếu có
                    boxes.forEach(function(otherBox) {
                        if (otherBox !== box) {
                            otherBox.classList.remove('clicked');
                        }
                    });
                });
            });
        });
    </script>
    <script type="text/javascript" src="../js/script.js"></script>
</body>

</html>