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

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['product_id']) && isset($_GET['color_name'])) {
    $productId = $_GET['product_id'];
    $colorName = $_GET['color_name'];

    // Kiểm tra nếu sản phẩm tồn tại trong bảng code_color
    $checkQuery = "
        SELECT * 
        FROM code_color c
        JOIN products p ON c.color_name = p.color_name
        WHERE p.product_id = ? AND p.color_name = ?
    ";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param('is', $productId, $colorName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Nếu tồn tại, thực hiện xóa
        $deleteQuery = "DELETE FROM code_color WHERE color_name = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param('s', $colorName);
        if ($deleteStmt->execute()) {
            $message = "Xóa mã màu thành công.";
        } else {
            $message = "Lỗi: Không thể xóa mã màu.";
        }
    } else {
        $message = "Không tìm thấy mã màu tương ứng để xóa.";
    }
}

if (isset($_POST['add_color'])) {
    $color_name = $conn->real_escape_string($_POST['color_name']); // Lấy giá trị từ dropdown
    $code_color = $conn->real_escape_string($_POST['code_color']);

    $message = []; // Khởi tạo mảng thông báo

    if (empty($color_name)) {
        $message[] = 'Vui lòng chọn sản phẩm!';
    } elseif (empty($code_color)) {
        $message[] = 'Vui lòng nhập mã màu tương ứng!';
    } else {
        // Lấy product_id từ bảng products dựa trên color_name
        $product_query = "SELECT product_id FROM products WHERE color_name = '$color_name' LIMIT 1";
        $product_result = $conn->query($product_query);

        if ($product_result->num_rows > 0) {
            $product_row = $product_result->fetch_assoc();
            $product_id = $product_row['product_id'];

            // Kiểm tra sự tồn tại của color_name
            $check_query = "SELECT * FROM code_color WHERE color_name = '$color_name'";
            $check_result = $conn->query($check_query);

            if ($check_result->num_rows > 0) {
                // Cập nhật mã màu nếu đã tồn tại
                $update_query = "UPDATE code_color SET color_code = '$code_color', product_id = '$product_id' WHERE color_name = '$color_name'";
                if ($conn->query($update_query)) {
                    $message[] = 'Cập nhật mã màu thành công!';
                } else {
                    $message[] = 'Lỗi khi cập nhật mã màu: ' . $conn->error;
                }
            } else {
                // Thêm mới mã màu và product_id nếu chưa tồn tại
                $insert_query = "INSERT INTO code_color (color_name, color_code, product_id) VALUES ('$color_name', '$code_color', '$product_id')";
                if ($conn->query($insert_query)) {
                    $message[] = 'Thêm mã màu thành công!';
                } else {
                    $message[] = 'Lỗi khi thêm mã màu: ' . $conn->error;
                }
            }
        } else {
            $message[] = 'Không tìm thấy sản phẩm tương ứng!';
        }
    }
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
    <title>Seraph Beauty - Thêm Mã Màu</title>
</head>

<body>
    <?php include '../admin/admin_header.php'; ?>

    <div class="title">
        <h2 style="font-size:50px;">Thêm mã màu sắc tương ứng</h2>
    </div>
    <div class="line2"></div>
    <section class="add-products form-container">
        <form method="POST" action="" enctype="multipart/form-data">
            <a style="font-size: 25px;" href="admin_pannel.php" class="back-arrow">&#8592;</a>

            <div class="input-field">
                <label>Chọn sản phẩm</label>
                <select name="product_id">
                    <option value="">-- Tên sản phẩm và màu sắc --</option>
                    <?php
        // Truy vấn để lấy product_name, color_name và product_id nếu product_id không tồn tại trong bảng code_color
        $query = "
        SELECT DISTINCT p.product_id, p.product_name, p.color_name
        FROM products p
        LEFT JOIN code_color c ON p.product_id = c.product_id
        WHERE c.product_id IS NULL
        ";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Hiển thị định dạng product_name - color_name
                $product_name = htmlspecialchars($row['product_name']);
                $color_name = htmlspecialchars($row['color_name']);
                $product_id = htmlspecialchars($row['product_id']);
                echo '<option value="' . $product_id . '">' . $product_name . ' - ' . $color_name . '</option>';
            }
        } else {
            echo '<option value="">Không có sản phẩm khả dụng</option>';
        }
        ?>


                </select>
            </div>

            <div class="input-field">
                <label>Mã màu tương ứng<span>*</span></label>
                <input type="text" min="1" name="code_color" required>
            </div>

            <input type="submit" name="add_color" value="Thêm mã màu">
        </form>
    </section>


    <section class="product-colors-table">
        <title>
            <h1>Danh sách sản phẩm và mã màu</h1>
        </title>

        <!-- Thông báo kết quả -->
        <?php if (isset($message)): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên sản phẩm</th>
                    <th>Màu sắc</th>
                    <th>Mã màu</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Truy vấn để lấy danh sách sản phẩm, màu sắc và mã màu
                $query = "
                SELECT p.product_id, p.product_name, p.color_name, c.color_code, c.code_color_id
                FROM products p
                JOIN code_color c ON p.color_name = c.color_name
                ";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    $stt = 1; // Số thứ tự
                    while ($row = $result->fetch_assoc()) {
                        $productId = htmlspecialchars($row['product_id']);
                        $colorName = htmlspecialchars($row['color_name']);
                        $colorCode = htmlspecialchars($row['color_code']);
                        echo '<tr>';
                        echo '<td>' . $stt++ . '</td>';
                        echo '<td>' . htmlspecialchars($row['product_name']) . '</td>';
                        echo '<td>' . $colorName . '</td>';
                        echo '<td>';
                        echo '<span class="color-box" style="background-color: ' . $colorCode . ';"></span>';
                        echo ' ' . $colorCode;
                        echo '</td>';
                        echo '<td>';
                        echo '<a href="?action=delete&product_id=' . $productId . '&color_name=' . $colorName . '" 
                                onclick="return confirm(\'Bạn có chắc muốn xóa mã màu này?\')">Xóa  </a>';
                                echo '<a href="edit_color.php?code_color_id=' . $row['code_color_id'] . '">Sửa</a> ';

                                
                        echo '</td>';
                        
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="5">Không có dữ liệu</td></tr>';
                }
             ?>
            </tbody>
        </table>
    </section>




</body>





<style>
.color-box {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 1px solid #ccc;
    margin-right: 5px;
    vertical-align: middle;
}
</style>



</html>