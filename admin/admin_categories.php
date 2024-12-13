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
//adding product to database
if (isset($_POST['add_category'])) {
    $category_name = mysqli_real_escape_string($conn, $_POST['name']);
    $category_image = $_FILES['image']['name'];
    $category_image_tmp_name = $_FILES['image']['tmp_name'];
    $category_image_folder = 'image/' . $category_image;

    // Kiểm tra xem tên thương hiệu đã tồn tại hay chưa
    $select_category_name = mysqli_query($conn, "SELECT category_name FROM categories WHERE category_name = '$category_name'") or die('query failed');
    if (mysqli_num_rows($select_category_name) > 0) {
        $message[] = 'Tên thương hiệu đã tồn tại';
    } else {
        // Thêm thương hiệu mới vào cơ sở dữ liệu
        $insert_category = mysqli_query($conn, "INSERT INTO categories(category_name, category_image) VALUES('$category_name', '$category_image')") or die('query failed');

        // Nếu thêm thương hiệu thành công, thì tải lên hình ảnh thương hiệu
        if ($insert_category) {
            if (move_uploaded_file($category_image_tmp_name, $category_image_folder)) {
                $message[] = 'Danh mục đã được thêm thành công';
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
    $select_delete_image = mysqli_query($conn, "SELECT category_image FROM `categories` WHERE category_id='$delete_id'") or die('query failed');
    $fetch_delete_image = mysqli_fetch_assoc($select_delete_image);
    mysqli_query($conn, "DELETE FROM `categories` WHERE category_id = '$delete_id'") or die('query faild');
    header('location:../admin/admin_categories.php');
}

// Update product
if (isset($_POST['update_category'])) {
    $update_id = $_POST['update_id'];
    $update_name = $_POST['update_name'];
    $update_image = $_FILES['update_image']['name']; // Vẫn giữ tên ảnh

    // Cập nhật thông tin vào cơ sở dữ liệu mà không cần di chuyển file
    $update_query = mysqli_query($conn, "UPDATE `categories` SET category_name = '" . mysqli_real_escape_string($conn, $update_name) . "', category_image = '$update_image' WHERE category_id = '$update_id'") or die('query failed');

    if ($update_query) {
        // Không cần di chuyển file
        // Hiển thị thông báo xác nhận
        echo "Cập nhật thành công!";
        // Chuyển hướng về trang
        header('location:../admin/admin_categories.php');
        exit(); // Dừng thực thi script
    } else {
        echo "Cập nhật thất bại!";
    }
}

if (isset($_POST['cancel-form'])) {
    header('location:../admin/admin_categories.php');
    exit();
}




if (isset($_POST['cancel-form'])) {
    header('location:../admin/admin_categories.php');
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
    <title>Seraph Beauty - Danh Mục</title>
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
        $edit_query = mysqli_query($conn, "SELECT * FROM categories WHERE category_id='$edit_id'") or die('query failed');
        if (mysqli_num_rows($edit_query) > 0) {
            while ($fetch_edit = mysqli_fetch_assoc($edit_query)) {
    ?>

    <section class="update_category form-container">
        <form method="POST" enctype="multipart/form-data" class="form-content">
            <input type="hidden" name="update_id" value="<?php echo $fetch_edit['category_id']; ?>">
            <img style="width:40%; border:2px solid black; margin-bottom: 25px;"
                src="../image/categories/<?php echo $fetch_edit['category_image']; ?>">

            <div class="input-field">
                <label>Tên danh mục<span>*</span><br></label>
                <input type="text" name="update_name" value="<?php echo $fetch_edit['category_name']; ?>" required>
            </div>
            <div class="input-field">
                <label>Hình ảnh danh mục<span>*</span></label>
                <input type="file" name="update_image" accept="image/jpg, image/jpeg, image/png, image/webp">
            </div>
            <div class="input-group">
                <input type="submit" name="update_category" value="Cập nhật" class="edit">
                <input type="button" value="Hủy" class="option-btn btn" id="cancel-form">
            </div>
        </form>
    </section>



    <?php
            }
        }
    } else {
        ?>
    <div class="title">
        <h2 style="font-size:50px;">Thêm danh mục</h2>
    </div>

    <section class="add-products form-container">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="input-field">
                <label>Tên danh mục<span>*</span><br></label>
                <input type="text" name="name" required>
            </div>
            <div class="input-field">
                <label>Hình ảnh danh mục<span>*</span></label>
                <input type="file" name="image" accept="image/jpg, image/jpeg, image/png, image/webp" required>
            </div>
            <input type="submit" name="add_category" class="add_category" value="Thêm danh mục" class="btn">
        </form>
    </section>



    <section class="show-products">
        <div class="title">
            <h2 style="font-size:50px;">Danh mục chính đã thêm</h2>
        </div>
        <div class="box-container">
            <?php
                $select_category = mysqli_query($conn, "SELECT * FROM `categories`") or die('query failed');
                if (mysqli_num_rows($select_category) > 0) {
                    while ($fetch_category = mysqli_fetch_assoc($select_category)) {
                ?>
            <div class="box">
                <a href="../admin/category.php?category_id=<?php echo $fetch_brands['category_id']; ?>">
                    <img src="../image/categories/<?php echo $fetch_category['category_image']; ?>">
                    <h4><?php echo $fetch_category['category_name']; ?></h4>
                    <div class="button-container">
                        <a href="../admin/admin_categories.php?edit=<?php echo $fetch_category['category_id']; ?>"
                            class="edit">Sửa</a>
                        <a href="../admin/admin_categories.php?delete=<?php echo $fetch_category['category_id']; ?>"
                            class="delete" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này');">Xóa</a>
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
        window.location.href = '../admin/admin_categories.php';
    });
    </script>
    <script type="text/javascript" src="../js/script.js"></script>
</body>

</html>