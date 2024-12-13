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

// Thêm danh mục
if (isset($_POST['add_category'])) {
    $category_name = mysqli_real_escape_string($conn, $_POST['name']);
    $category_image = $_FILES['image']['name'];
    $category_image_tmp_name = $_FILES['image']['tmp_name'];
    $category_image_folder = '../image/' . $category_image;
    $category_id = mysqli_real_escape_string($conn, $_POST['subcategory']);

    $select_category_name = mysqli_query($conn, "SELECT subcategory_name FROM subcategory WHERE subcategory_name = '$category_name'") or die('Truy vấn thất bại');

    if (mysqli_num_rows($select_category_name) > 0) {
        $message[] = 'Tên thương hiệu đã tồn tại';
    } else {
        $insert_category = mysqli_query($conn, "INSERT INTO subcategory (subcategory_name, subcategory_image, category_id) VALUES ('$category_name', '$category_image', '$category_id')") or die('Truy vấn thất bại');

        if ($insert_category) {
            if (move_uploaded_file($category_image_tmp_name, $category_image_folder)) {
                $message[] = 'Danh mục đã được thêm thành công';
            } else {
                $message[] = 'Không thể tải lên hình ảnh thương hiệu';
            }
        } else {
            $message[] = 'Không thể thêm danh mục: ' . mysqli_error($conn);
        }
    }
}

// Xóa danh mục
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $select_delete_image = mysqli_query($conn, "SELECT subcategory_image FROM subcategory WHERE subcategory_id='$delete_id'") or die('Truy vấn thất bại');
    $fetch_delete_image = mysqli_fetch_assoc($select_delete_image);
    $delete_image = '../image/' . $fetch_delete_image['subcategory_image'];

    if (mysqli_query($conn, "DELETE FROM subcategory WHERE subcategory_id = '$delete_id'")) {
        if (file_exists($delete_image)) {
            unlink($delete_image);
        }
        header('location:../admin/admin_subcategory.php');
        exit();
    } else {
        die('Truy vấn thất bại');
    }
}

// Cập nhật danh mục
if (isset($_POST['update_subcategory'])) {
    if (isset($_POST['update_subid']) && isset($_POST['update_name']) && isset($_POST['update_category'])) {
        $update_id = $_POST['update_subid'];
        $update_name = mysqli_real_escape_string($conn, $_POST['update_name']);
        $update_category_id = mysqli_real_escape_string($conn, $_POST['update_category']);

        if (isset($_FILES['update_image']['name']) && $_FILES['update_image']['name'] != '') {
            $update_image = $_FILES['update_image']['name'];
            $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
            $update_image_folder = '../image/' . $update_image;

            $update_query = mysqli_query($conn, "UPDATE subcategory SET subcategory_name = '$update_name', subcategory_image = '$update_image', category_id = '$update_category_id' WHERE subcategory_id = '$update_id'") or die('Cập nhật không thành công');

            if ($update_query) {
                move_uploaded_file($update_image_tmp_name, $update_image_folder);
                echo "Cập nhật thành công!";
                header('location:../admin/admin_subcategory.php');
                exit();
            } else {
                echo "Cập nhật thất bại!";
            }
        } else {
            $update_query = mysqli_query($conn, "UPDATE subcategory SET subcategory_name = '$update_name', category_id = '$update_category_id' WHERE subcategory_id = '$update_id'") or die('Cập nhật không thành công');

            if ($update_query) {
                echo "Cập nhật thành công!";
                header('location:../admin/admin_subcategory.php');
                exit();
            } else {
                echo "Cập nhật thất bại!";
            }
        }
    }
}

if (isset($_POST['cancel-form'])) {
    header('location:../admin/admin_subcategory.php');
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
    $category_query = mysqli_query($conn, "SELECT * FROM categories") or die('Truy vấn thất bại');
    $categories = [];
    if (mysqli_num_rows($category_query) > 0) {
        while ($row = mysqli_fetch_assoc($category_query)) {
            $categories[] = $row;
        }
    }

    if (isset($_GET['edit'])) {
        $edit_id = $_GET['edit'];
        $edit_query = mysqli_query($conn, "SELECT * FROM subcategory WHERE subcategory_id='$edit_id'") or die('Truy vấn thất bại');
        if (mysqli_num_rows($edit_query) > 0) {
            while ($fetch_edit = mysqli_fetch_assoc($edit_query)) {
    ?>

    <section class="update_category form-container">
        <form method="POST" enctype="multipart/form-data" class="form-content">
            <input type="hidden" name="update_subid" value="<?php echo $fetch_edit['subcategory_id']; ?>">
            <img style="width:40%; border:2px solid black; margin-bottom: 25px;"
                src="../image/subcategory/<?php echo $fetch_edit['subcategory_image']; ?>">

            <div class="input-field">
                <label>Tên danh mục<span>*</span><br></label>
                <input type="text" name="update_name" value="<?php echo $fetch_edit['subcategory_name']; ?>" required>
            </div>
            <div class="input-field">
                <label>Hình ảnh danh mục<span>*</span></label>
                <input type="file" name="update_image" accept="image/jpg, image/jpeg, image/png, image/webp">
            </div>

            <div class="input-field">
                <label>Danh mục<span>*</span></label>
                <select name="update_category" required>
                    <?php foreach ($categories as $category) { ?>
                    <option value="<?php echo $category['category_id']; ?>"
                        <?php echo ($fetch_edit['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                        <?php echo $category['category_name']; ?>
                    </option>
                    <?php } ?>
                </select>
            </div>

            <div class="input-group">
                <input type="submit" name="update_subcategory" value="Cập nhật" class="edit">
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
        <h2 style="font-size:50px;">Thêm danh mục phụ</h2>
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
            <div class="input-field">
                <label>Danh mục<span>*</span></label>
                <select name="subcategory">
                    <?php
                        // Truy vấn lấy danh sách các danh mục từ bảng categories
                        $select_subcategory_query = mysqli_query($conn, "SELECT category_id, category_name FROM categories") or die('Query failed');

                        // Kiểm tra kết quả
                        if (mysqli_num_rows($select_subcategory_query) > 0) {
                            // Lặp qua các kết quả và tạo tùy chọn cho trường chọn
                            while ($subcategory_row = mysqli_fetch_assoc($select_subcategory_query)) {
                                $category_id = $subcategory_row['category_id'];
                                $subcategory_name = $subcategory_row['category_name'];
                                echo '<option value="' . $category_id . '" style="text-transform: capitalize;">' . $subcategory_name . '</option>';
                            }
                        } else {
                            echo '<option value="" disabled>Không có danh mục nào.</option>';
                        }
                        ?>
                </select>
            </div>
            <input type="submit" name="add_category" class="add_category" value="Thêm danh mục" class="btn">
        </form>
    </section>

    <section class="show-products">
        <div class="title">
            <h2 style="font-size:50px;">Danh mục phụ đã thêm</h2>
        </div>
        <div class="box-container">
            <?php
                $select_category = mysqli_query($conn, "SELECT * FROM `subcategory`") or die('query failed');
                if (mysqli_num_rows($select_category) > 0) {
                    while ($fetch_category = mysqli_fetch_assoc($select_category)) {
                ?>
            <div class="box">
                <a href="../admin/subcategory.php?subcategory_id=<?php echo $fetch_subcategory['subcategory_id']; ?>">
                    <img src="../image/subcategory/<?php echo $fetch_category['subcategory_image']; ?>">
                    <h4><?php echo $fetch_category['subcategory_name']; ?></h4>

                    <div class="button-container">
                        <a href="../admin/admin_subcategory.php?edit=<?php echo $fetch_category['subcategory_id']; ?>"
                            class="edit">Sửa</a>
                        <a href="../admin/admin_subcategory.php?delete=<?php echo $fetch_category['subcategory_id']; ?>"
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
        window.location.href = '../admin/admin_subcategory.php';
    });
    </script>
    <script type="text/javascript" src="../js/script.js"></script>
</body>

</html>