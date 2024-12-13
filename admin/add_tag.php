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

if (isset($_POST['add_tag'])) {

    // Lấy giá trị từ form
    $tag_name = $_POST['name'];
    $tag_image = null;

    // Kiểm tra xem có file hình ảnh không
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['image']['name'];
        $tag_image = $file_name; // Lưu tên file hình ảnh
    }

    // Xây dựng truy vấn SQL
    if ($tag_image !== null) {
        // Nếu có hình ảnh
        $sql = "INSERT INTO tags (tag_name, tag_image) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $tag_name, $tag_image);
    } else {
        // Nếu không có hình ảnh
        $sql = "INSERT INTO tags (tag_name) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $tag_name);
    }

    // Thực hiện truy vấn
    if ($stmt->execute()) {
        echo "Thêm tag thành công.";
    } else {
        echo "Lỗi: " . $stmt->error;
    }
}



// Kiểm tra nếu người dùng yêu cầu xóa tag
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    // Truy vấn để xóa tag từ bảng `tags`
    $delete_query = mysqli_query($conn, "DELETE FROM `tags` WHERE tag_id = '$delete_id'") or die('query failed');

    // Kiểm tra nếu truy vấn xóa thành công
    if ($delete_query) {
        $message[] = 'Nhãn đã được xóa thành công!';
        echo "<script>window.location.href = 'add_tag.php';</script>"; // Redirect về trang quản lý nhãn
    } else {
        $message[] = 'Xóa nhãn thất bại!';
    }
}

// Update product
if (isset($_POST['update_tag'])) {
    $update_id = $_POST['update_id'];
    $update_name = $_POST['update_name'];
    $update_image = $_FILES['update_image']['name']; // Vẫn giữ tên ảnh

    // Cập nhật thông tin vào cơ sở dữ liệu mà không cần di chuyển file
    $update_query = mysqli_query($conn, "UPDATE `tags` SET tag_name = '" . mysqli_real_escape_string($conn, $update_name) . "', tag_image = '$update_image' WHERE tag_id = '$update_id'") or die('query failed');

    if ($update_query) {
        // Không cần di chuyển file
        // Hiển thị thông báo xác nhận
        echo "Cập nhật thành công!";
        // Chuyển hướng về trang
        header('location:../admin/add_tag.php');
        exit(); // Dừng thực thi script
    } else {
        echo "Cập nhật thất bại!";
    }
}

if (isset($_POST['cancel-form'])) {
    header('location:../admin/add_tag.php');
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
    <title>Seraph Beauty - Thêm Nhãn</title>
</head>

<body>
    <?php include '../admin/admin_header.php'; ?>



    <?php
    if (isset($_GET['edit'])) {
        $edit_id = $_GET['edit'];
        $edit_query = mysqli_query($conn, "SELECT * FROM tags WHERE tag_id='$edit_id'") or die('query failed');
        if (mysqli_num_rows($edit_query) > 0) {
            while ($fetch_edit = mysqli_fetch_assoc($edit_query)) {
    ?>

                <section class="update_tag form-container">
                    <div class="title">
                        <h2 style="font-size:50px;">Chỉnh sửa Nhãn</h2>
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
                    <form method="POST" enctype="multipart/form-data" class="form-content">
                        <input type="hidden" name="update_id" value="<?php echo $fetch_edit['tag_id']; ?>">
                        <img style="width:40%; border:2px solid black; margin-bottom: 25px;"
                            src="../image/tags/<?php echo $fetch_edit['tag_image']; ?>">

                        <div class="input-field">
                            <label>Tên nhãn<span>*</span><br></label>
                            <input type="text" name="update_name" value="<?php echo $fetch_edit['tag_name']; ?>" required>
                        </div>
                        <div class="input-field">
                            <label>Hình ảnh(nếu có)<span>*</span></label>
                            <input type="file" name="update_image" accept="image/jpg, image/jpeg, image/png, image/webp">
                        </div>
                        <div class="input-group">
                            <input type="submit" name="update_tag" value="Cập nhật" class="edit">
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
            <h2 style="font-size:50px;">Thêm nhãn</h2>
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
        <section class="add-products form-container">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="input-field">
                    <label>Tên nhãn<span>*</span><br></label>
                    <input type="text" name="name" required>
                </div>
                <div class="input-field">
                    <label>Hình ảnh(Nếu có)<span>*</span></label>
                    <input type="file" name="image" accept="image/jpg, image/jpeg, image/png, image/webp">
                </div>
                <input type="submit" name="add_tag" class="add_tag" value="Thêm" class="btn">
            </form>
        </section>



        <section class="show-products">
            <div class="title">
                <h1>Nhãn đã thêm</h1>
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
            <div class="line2"></div>
            <div class="box-container">
                <?php
                $select_tags = mysqli_query($conn, "SELECT * FROM `tags`") or die('query failed');
                if (mysqli_num_rows($select_tags) > 0) {
                    while ($fetch_tag = mysqli_fetch_assoc($select_tags)) {
                ?>
                        <div class="box">
                            <a href="../admin/view_product_tag.php?tag_id=<?php echo $fetch_tag['tag_id']; ?>">
                                <img src="../image/tags/<?php echo $fetch_tag['tag_image']; ?>"
                                    alt="<?php echo $fetch_tag['tag_name']; ?>">
                            </a>
                            <h4 class="tag_name"><?php echo $fetch_tag['tag_name']; ?></h4><br><br>


                            <div class="button-container">
                                <a href="../admin/add_tag.php?edit=<?php echo $fetch_tag['tag_id']; ?>" class="edit">Sửa</a>
                                <a href="../admin/add_tag.php?delete=<?php echo $fetch_tag['tag_id']; ?>" class="delete"
                                    onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này');">Xóa</a>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo '<div class="empty"><p>Chưa có nhãn được thêm!</p></div>';
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
            window.location.href = '../admin/add_tag.php';
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