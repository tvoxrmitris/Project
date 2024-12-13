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

// Lấy email người dùng từ session
$current_user_type = $_SESSION['employee_type'];



// Kiểm tra người dùng có quyền logout hay không
if (isset($_POST['logout'])) {
    session_destroy();
    header('location:../components/admin_login.php');
    exit();
}


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

// Kiểm tra quyền trước khi xóa tài khoản
if (isset($_GET['delete'])) {
    $delete_user_id = $_GET['delete'];

    // Chỉ cho phép super admin thực hiện xóa tài khoản
    if ($current_user_type == 'super admin') {
        mysqli_query($conn, "DELETE FROM employees WHERE user_id = $delete_user_id") or die('query failed');
        $message[] = 'Tài khoản đã được xóa thành công';
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

    /* Khung cho bảng */
    table {
        width: 100%;
        border-collapse: collapse;
        /* Kết hợp viền để không bị khoảng cách giữa các ô */
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        /* Thêm bóng đổ cho bảng */
    }

    th,
    td {
        padding: 10px;
        text-align: left;
        border: 1px solid #ddd;
        /* Viền cho các ô dữ liệu */
    }

    th {
        background-color: #f2f2f2;
        /* Màu nền cho tiêu đề */
        border-bottom: 2px solid #ddd;
        /* Viền dày hơn cho tiêu đề */
    }

    /* Khung cho box-container */
    .box-container {
        border: 1px solid #ccc;
        /* Khung cho toàn bộ bảng */
        border-radius: 8px;
        /* Bo góc */
        overflow: hidden;
        /* Để các góc bo không bị lộ */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        /* Đổ bóng cho khung */
    }

    .empty {
        text-align: center;
        padding: 20px;
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
    <title>Seraph Beauty - Người Dùng</title>
    <style type="text/css">
        <?php include '../CSS/style.css';

        ?>.empty {
            text-align: center;
            padding: 20px;
        }

        .btn-delete {
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
            width: 40px;
        }

        th:nth-child(2),
        td:nth-child(2) {
            width: 105px;
        }

        th:nth-child(3),
        td:nth-child(3) {
            width: 120px;
        }

        th:nth-child(4),
        td:nth-child(4) {
            width: 150px;
        }

        th:nth-child(5),
        td:nth-child(5) {
            width: 120px;
        }

        th:nth-child(6),
        td:nth-child(6) {
            width: 250px;
        }

        th:nth-child(7),
        td:nth-child(7) {
            width: 220px;
        }

        /* Đặt phần chứa các nút và select thành flex */
        td select[name="employee_type"],
        .btn-delete,
        .btn-remove-admin,
        .btn-admin {
            display: inline-block;
            vertical-align: middle;
            margin-right: 10px;
            /* Khoảng cách giữa các phần tử */
        }

        td .btn-admin,
        td .btn-remove-admin,
        td .btn-delete {
            margin-top: 5px;
            /* Căn chỉnh để trông đều hơn */
        }

        /* Container để căn chỉnh mũi tên tùy chỉnh */
        .select-container {
            position: relative;
            display: inline-block;
        }

        /* CSS cho thẻ <select> */
        select[name="employee_type"] {
            padding: 12px 15px;
            font-size: 16px;
            font-family: "Times New Roman", Times, serif;
            /* Font phong cách cổ điển */
            color: #000;
            /* Màu chữ đen */
            background-color: #fff;
            /* Nền trắng */
            border: 2px solid #000;
            /* Viền đen đậm */
            border-radius: 4px;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            font-weight: bold;
            transition: background-color 0.3s ease, border-color 0.3s ease;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            /* Hiệu ứng bóng đổ mạnh hơn */
        }

        /* Hiệu ứng khi hover */
        select[name="employee_type"]:hover {
            background-color: #e5e5e5;
            /* Nền xám nhạt khi hover */
        }

        /* Hiệu ứng khi focus */
        select[name="employee_type"]:focus {
            outline: none;
            border-color: #666;
            /* Viền màu xám đậm hơn khi focus */
            background-color: #f2f2f2;
            /* Nền xám rất nhạt khi focus */
        }

        /* Mũi tên tùy chỉnh */
        .select-container::after {
            content: "▼";
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #000;
            /* Màu đen cho mũi tên */
            pointer-events: none;
            font-size: 14px;
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
    <section class="message-container">
        <div class="box-container">
            <?php
            // Truy vấn tất cả dữ liệu từ bảng users mà không kết hợp với bảng employees
            $select_users = mysqli_query($conn, "SELECT * FROM `users`") or die('query failed');

            if (mysqli_num_rows($select_users) > 0) {
            ?>
                <table>
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stt = 1; // Khởi tạo biến đếm STT
                        while ($fetch_users = mysqli_fetch_assoc($select_users)) {
                        ?>
                            <tr>
                                <td><?php echo $stt++; ?></td>
                                <td><?php echo $fetch_users['user_name']; ?></td>
                                <td><?php echo $fetch_users['user_email']; ?></td>
                                <td>
                                    <!-- Các hành động thực hiện trên tài khoản -->
                                    <form action="update_user.php" method="POST">
                                        <input type="hidden" name="user_id" value="<?php echo $fetch_users['user_id']; ?>">

                                        <a class="btn-remove"
                                            href="../admin/admin_user.php?delete_user=<?php echo $fetch_users['user_id']; ?>"
                                            onclick="return confirm('Bạn có chắc muốn xóa tài khoản này?');">Xóa</a>
                                    </form>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            <?php
            } else {
                echo '
                <div class="empty">
                    <p>Chưa có người dùng được tạo</p>
                </div>
            ';
            }
            ?>
        </div>
    </section>









    <script src="../js/script.js"></script>
</body>

</html>