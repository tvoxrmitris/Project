<?php
include '../connection/connection.php';
session_start(); // Bắt đầu session

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

$employee_email = $_SESSION['employee_email'];
$current_user_type = $_SESSION['employee_type'];




// Kiểm tra quyền trước khi loại bỏ quyền admin
if (isset($_GET['remove_admin'])) {
    $remove_admin_id = $_GET['remove_admin'];

    // Lấy thông tin loại người dùng mà bạn muốn thay đổi quyền
    $result = mysqli_query($conn, "SELECT employee_type FROM employees WHERE user_id = $remove_admin_id") or die('query failed');
    $remove_admin_user = mysqli_fetch_assoc($result);
    $remove_admin_user_type = $remove_admin_user['employee_type'];

    if ($current_user_type == 'super admin' && $remove_admin_user_type == 'admin') {
        // Chỉ super admin có thể loại bỏ quyền admin và chuyển về user
        mysqli_query($conn, "UPDATE employees SET employee_type = 'user' WHERE user_id = $remove_admin_id") or die('query failed');
        $message[] = 'Đã loại bỏ quyền admin và chuyển thành user';
    } else {
        $message[] = 'Bạn không có quyền thực hiện thao tác này';
    }

    header('location: ../admin/admin_user.php');
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
    <title>Seraph Beauty - Người Dùng</title>
    <style type="text/css">
    <?php include '../CSS/style.css';

    ?>.btn-delete {
        display: inline-block;
        color: white;
        padding: 0.1px 10px;
        text-decoration: none;
        border-radius: 4px;
    }

    .btn-delete:hover {
        background-color: #ccc;
        color: #000;
    }

    .btn-admin {
        display: inline-block;
        background-color: #28a745;
        color: white;
        padding: 0.1px 10px;
        text-decoration: none;
        border-radius: 4px;
        margin-left: 10px;
    }

    .btn-admin:hover {
        background-color: #218838;
    }

    /* Thiết lập chiều rộng tối thiểu cho các cột nếu cần */
    th:nth-child(1),
    td:nth-child(1) {
        width: 65px;
    }

    th:nth-child(2),
    td:nth-child(2) {
        width: 130px;
    }

    th:nth-child(3),
    td:nth-child(3) {
        width: 120px;
    }

    th:nth-child(4),
    td:nth-child(4) {
        width: 230px;
    }

    th:nth-child(5),
    td:nth-child(5) {
        width: 350px;
    }

    th:nth-child(6),
    td:nth-child(6) {
        width: 250px;
    }

    th:nth-child(7),
    td:nth-child(7) {
        width: 300px;
    }
    </style>
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
    <h1 class="title">Tổng số tài khoản</h1>
    <section class="shop">
        <div class="border-wrapper" style="margin-bottom: 12rem;">
            <a href="manage_user.php" class="back-arrow"
                style="text-decoration: none; font-size: 1.5rem; margin-right: 1rem;">&#8592;</a>
            <div class="total-quantity">
                <?php
            $sql = "SELECT COUNT(*) AS total_employees FROM employees";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            $total_employees = $row['total_employees'];
            ?>
                <p style="margin-left: 3rem;"><?php echo number_format($total_employees, 0, '.', '.'); ?> Nhân viên</p>
            </div>

            <div class="sort-by">
                <form method="get" action="">
                    <select name="employee_type" id="employee_type" style="left: 10rem;" onchange="this.form.submit()">
                        <option value="">Tất cả nhân viên</option>
                        <option value="staff"
                            <?php echo isset($_GET['employee_type']) && $_GET['employee_type'] == 'staff' ? 'selected' : ''; ?>>
                            Nhân viên bán hàng</option>
                        <option value="NVNK"
                            <?php echo isset($_GET['employee_type']) && $_GET['employee_type'] == 'NVNK' ? 'selected' : ''; ?>>
                            Nhân viên nhập kho</option>
                        <option value="admin"
                            <?php echo isset($_GET['employee_type']) && $_GET['employee_type'] == 'admin' ? 'selected' : ''; ?>>
                            Admin</option>
                    </select>
                </form>
            </div>
        </div>
        <div class="line3"></div>
        <div class="message-container">
            <div class="box-container">
                <?php
            $employee_type_filter = isset($_GET['employee_type']) ? $_GET['employee_type'] : '';

            if ($current_user_type == 'super admin') {
                $sql = "SELECT e.* FROM `employees` e";
                if ($employee_type_filter) {
                    $sql .= " WHERE e.employee_type = '$employee_type_filter' AND e.employee_type != 'super admin'";
                } else {
                    $sql .= " WHERE e.employee_type != 'super admin'";
                }
            } elseif ($current_user_type == 'admin') {
                $sql = "SELECT e.* FROM `employees` e WHERE e.user_id = (SELECT user_id FROM users WHERE user_email = '$user_email')";
                if ($employee_type_filter) {
                    $sql .= " AND e.employee_type = '$employee_type_filter'";
                }
            }

            $select_users = mysqli_query($conn, $sql) or die('query failed');

            if (mysqli_num_rows($select_users) > 0) {
            ?>
                <table>
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>MSNV</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>Địa chỉ</th>
                            <th>Chức vụ</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                    $stt = 1;
                    while ($fetch_users = mysqli_fetch_assoc($select_users)) {
                    ?>
                        <tr>
                            <td><?php echo $stt++; ?></td>
                            <td><?php echo $fetch_users['employee_id']; ?></td>
                            <td><?php echo $fetch_users['employee_name']; ?></td>
                            <td><?php echo $fetch_users['employee_email']; ?></td>
                            <td><?php echo $fetch_users['employee_address']; ?></td>
                            <td
                                style="color: <?php if ($fetch_users['employee_type'] == 'super admin') { echo 'orange'; } ?>">
                                <?php
                            if ($fetch_users['employee_type'] == 'staff') {
                                echo 'Nhân viên bán hàng';
                            } elseif ($fetch_users['employee_type'] == 'NVNK') {
                                echo 'Nhân viên nhập kho';
                            } else {
                                echo $fetch_users['employee_type'];
                            }
                            ?>
                            </td>
                            <td>
                                <!-- Form cập nhật loại nhân viên -->
                                <form action="" method="POST">
                                    <div class="select-container">
                                        <select name="new_employee_type" onchange="this.form.submit()">
                                            <option value="staff"
                                                <?php if ($fetch_users['employee_type'] == 'staff') echo 'selected'; ?>>
                                                Nhân viên bán hàng</option>
                                            <option value="NVNK"
                                                <?php if ($fetch_users['employee_type'] == 'NVNK') echo 'selected'; ?>>
                                                Nhân viên nhập kho</option>
                                            <option value="admin"
                                                <?php if ($fetch_users['employee_type'] == 'admin') echo 'selected'; ?>>
                                                Admin</option>
                                        </select>
                                    </div>
                                    <input type="hidden" name="employee_id"
                                        value="<?php echo $fetch_users['employee_id']; ?>">
                                    <input type="hidden" name="update_employee_type" value="1">
                                </form>

                                <!-- Form xóa nhân viên -->
                                <?php if ($fetch_users['employee_type'] !== 'super admin'): ?>
                                <form action="" method="POST">
                                    <input type="hidden" name="delete_user_email"
                                        value="<?php echo $fetch_users['employee_email']; ?>">
                                    <button type="submit" name="delete" class="btn-delete"
                                        onclick="return confirm('Bạn có chắc muốn xóa tài khoản này?');">Xóa</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
                <?php
            } else {
                echo '<div class="empty"><p>Chưa có người dùng được tạo</p></div>';
            }

            // Xử lý cập nhật loại nhân viên và xóa nhân viên
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                if (isset($_POST['new_employee_type'], $_POST['employee_id'], $_POST['update_employee_type'])) {
                    $new_employee_type = $_POST['new_employee_type'];
                    $employee_id = $_POST['employee_id'];

                    $update_query = "UPDATE `employees` SET `employee_type` = '$new_employee_type' WHERE `employee_id` = '$employee_id'";
                    mysqli_query($conn, $update_query) or die('Cập nhật thất bại');
                    echo "<script>alert('Cập nhật thành công!'); window.location.href=window.location.href;</script>";
                }

                if (isset($_POST['delete_user_email'], $_POST['delete'])) {
                    $delete_user_email = $_POST['delete_user_email'];

                    $delete_query = "DELETE FROM `employees` WHERE `employee_email` = '$delete_user_email'";
                    mysqli_query($conn, $delete_query) or die('Xóa nhân viên thất bại');
                    echo "<script>alert('Nhân viên đã được xóa thành công!'); window.location.href=window.location.href;</script>";
                }
            }
            ?>
            </div>
        </div>
    </section>




    <script>
    // Lưu vị trí cuộn trước khi gửi form
    document.querySelectorAll('select[name="employee_type"]').forEach(select => {
        select.addEventListener('change', () => {
            localStorage.setItem('scrollPosition', window.scrollY);
        });
    });

    // Khôi phục vị trí cuộn khi tải lại trang
    window.addEventListener('load', () => {
        const scrollPosition = localStorage.getItem('scrollPosition');
        if (scrollPosition) {
            window.scrollTo(0, parseInt(scrollPosition));
            localStorage.removeItem('scrollPosition'); // Xóa dữ liệu sau khi khôi phục
        }
    });
    </script>






    <script src="../js/script.js"></script>
</body>

</html>