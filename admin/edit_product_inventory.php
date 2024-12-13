<?php
// Kết nối cơ sở dữ liệu và bắt đầu phiên
include '../connection/connection.php';
session_start();

// Kiểm tra quyền truy cập
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_product'])) {
    // Lấy dữ liệu từ form
    $inventory_id = isset($_POST['inventory_id']) ? intval($_POST['inventory_id']) : 0;
    $name = htmlspecialchars($_POST['name']);
    $code_color = $_POST['code_color'];
    $color = htmlspecialchars($_POST['color']);
    $import_price = floatval($_POST['price']);
    $category = intval($_POST['category']);
    $subcategory_id = intval($_POST['subcategory']); // Sử dụng subcategory_id
    $capacity = htmlspecialchars($_POST['capacity']);

    if ($inventory_id > 0) {
        // Cập nhật dữ liệu vào bảng inventory_entries
        $update_query = "UPDATE inventory_entries SET product_name = ?, import_price = ?, code_color = ?, color_name = ?, capacity = ?, category_name = ?, subcategory_name = ? WHERE inventory_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssssssii", $name, $import_price, $code_color, $color, $capacity, $category, $subcategory_id, $inventory_id);

        if ($update_stmt->execute()) {
            $message[] = 'Cập nhật sản phẩm thành công.';
            echo "<script>var notificationMessage = 'Cập nhật sản phẩm thành công.'; var isError = false;</script>";
        } else {
            $message[] = "Lỗi khi cập nhật sản phẩm: " . $conn->error;
            echo "<script>var notificationMessage = 'Lỗi khi cập nhật sản phẩm.'; var isError = true;</script>";
        }
        $update_stmt->close();
    } else {
        $message[] = "ID sản phẩm không hợp lệ.";
        echo "<script>var notificationMessage = 'ID sản phẩm không hợp lệ.'; var isError = true;</script>";
    }
}

// Lấy danh sách sản phẩm chưa có trong bảng `products`
$query = "
    SELECT product_name, color_name, import_price, quantity_stock, CONCAT(color_name, '.jpg') AS color_image
    FROM inventory_entries
    WHERE quantity_stock > 0
    AND (product_name, color_name) NOT IN (SELECT product_name, color_name FROM products)
";
$missing_info_products = mysqli_query($conn, $query);
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
    <link rel="stylesheet" type="text/css" href="../CSS/style.css?v=<?= time(); ?>">
    <title>Seraph Beauty - Sản Phẩm</title>
    <style>
    .notification-box {
        position: fixed;
        bottom: -40px;
        /* Ban đầu ẩn bên dưới màn hình */
        left: 20px;
        background-color: #000;
        /* Nền đen */
        color: #fff;
        /* Chữ trắng */
        border: 1px solid #fff;
        /* Viền trắng */
        padding: 10px 15px;
        /* Thu nhỏ khoảng cách bên trong */
        border-radius: 8px;
        /* Bo góc mềm hơn */
        box-shadow: 0 3px 15px rgba(0, 0, 0, 0.4);
        /* Bóng nhỏ hơn */
        font-size: 14px;
        /* Giảm kích thước chữ */

        z-index: 1000;
        opacity: 0;
        /* Ban đầu ẩn */
        transform: translateY(40px);
        /* Di chuyển xuống dưới ít hơn */
        transition: opacity 0.3s ease, transform 0.3s ease;
        /* Hiệu ứng mượt */
    }

    .notification-box.error {
        background-color: #fff;
        /* Nền trắng */
        color: #000;
        /* Chữ đen */
        border-color: #000;
        /* Viền đen */
    }



    /* Khi hiển thị thông báo */
    .notification-box.show {
        opacity: 1;
        /* Hiển thị */
        transform: translateY(0);
        /* Di chuyển về vị trí ban đầu */
        bottom: 20px;
        /* Đặt vị trí dưới cách cạnh màn hình */
    }
    </style>
</head>

<body>
    <?php include '../admin/admin_header.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof notificationMessage !== 'undefined') {
            const notificationBox = document.createElement('div');
            notificationBox.className = 'notification-box';
            if (isError) {
                notificationBox.classList.add('error');
            }
            notificationBox.textContent = notificationMessage;

            document.body.appendChild(notificationBox);

            // Hiển thị box với hiệu ứng
            setTimeout(() => {
                notificationBox.classList.add('show');
            }, 10); // Trễ nhỏ để hiệu ứng hoạt động

            // Tự động ẩn sau 3 giây
            setTimeout(() => {
                notificationBox.classList.remove('show');
                setTimeout(() => {
                    notificationBox.remove();
                }, 300); // Chờ hiệu ứng hoàn tất trước khi xóa
            }, 3000);
        }
    });
    </script>

    <div id="content-wrapper">
        <div class="title">
            <h2 style="font-size:50px;">Chỉnh sửa sản phẩm</h2>
        </div>
        <div class="line2"></div>

        <section class="add-products form-container">
            <form method="POST" action="">
                <a style="font-size: 25px;" href="product_inventory.php" class="back-arrow">&#8592;</a>
                <?php
                $inventory_id = isset($_GET['inventory_id']) ? intval($_GET['inventory_id']) : 0;
                $product_name = $color_name = $capacity = $subcategory_name = $code_color = "";
                $import_price = $stock_quantity = $category_id = 0;

                if ($inventory_id > 0) {
                    $query = "SELECT product_name, code_color, import_price, color_name, capacity, quantity_stock, category_name, subcategory_name 
                              FROM inventory_entries WHERE inventory_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $inventory_id);
                    $stmt->execute();
                    $stmt->bind_result($product_name, $code_color, $import_price, $color_name, $capacity, $stock_quantity, $category_id, $subcategory_name);
                    $stmt->fetch();
                    $stmt->close();
                }
                ?>
                <input type="hidden" name="inventory_id" value="<?= $inventory_id; ?>">

                <div class="input-field">
                    <label>Tên sản phẩm<span>*</span></label>
                    <input type="text" name="name" value="<?= htmlspecialchars($product_name); ?>" required>
                </div>
                <div class="input-field">
                    <label>Giá nhập<span>*</span></label>
                    <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($import_price); ?>"
                        required>
                </div>
                <div class="input-field">
                    <label>Mã màu<span>*</span></label>
                    <input type="text" name="code_color" value="<?= htmlspecialchars($code_color); ?>" required>
                </div>
                <div class="input-field">
                    <label>Màu sắc<span>*</span></label>
                    <input type="text" name="color" value="<?= htmlspecialchars($color_name); ?>" required>
                </div>
                <div class="input-field">
                    <label>Thể tích<span>*</span></label>
                    <input type="text" name="capacity" value="<?= htmlspecialchars($capacity); ?>">
                </div>

                <?php
                $categories = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM categories"), MYSQLI_ASSOC);
                $subcategories = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM subcategory"), MYSQLI_ASSOC);
                ?>

                <div class="input-field">
                    <label>Danh mục chính<span>*</span></label>
                    <select name="category" id="mainCategory">
                        <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id']; ?>"
                            <?= $category['category_id'] == $category_id ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($category['category_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-field">
                    <label>Danh mục phụ<span>*</span></label>
                    <select name="subcategory" id="subCategory">
                        <option value="<?= $subcategory_id; ?>" selected>
                            <?= htmlspecialchars($subcategory_name); ?></option>
                        <!-- Subcategories will be populated by JavaScript -->
                    </select>
                </div>

                <div class="action-buttons">
                    <input type="submit" name="edit_product" value="Chỉnh sửa sản phẩm" class="edit-button">
                </div>
            </form>
        </section>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const mainCategory = document.querySelector('select[name="category"]');
        const subCategory = document.querySelector('select[name="subcategory"]');
        const subcategories = <?= json_encode($subcategories); ?>;

        function filterSubcategories() {
            const selectedCategoryId = mainCategory.value;
            subCategory.innerHTML = ''; // Clear current subcategories

            const filteredSubcategories = subcategories.filter(subcat => subcat.category_id ==
                selectedCategoryId);

            filteredSubcategories.forEach(subcat => {
                const option = document.createElement('option');
                option.value = subcat.subcategory_id; // Sử dụng subcategory_id
                option.textContent = subcat.subcategory_name;
                subCategory.appendChild(option);
            });
        }

        mainCategory.addEventListener('change', filterSubcategories);
        filterSubcategories();

        // Hiển thị thông báo sau khi cập nhật
        if (typeof notificationMessage !== 'undefined') {
            const notificationBox = document.createElement('div');
            notificationBox.className = 'notification-box';
            if (isError) {
                notificationBox.classList.add('error');
            }
            notificationBox.textContent = notificationMessage;

            document.body.appendChild(notificationBox);
            notificationBox.style.display = 'block';

            setTimeout(() => {
                notificationBox.style.opacity = 0;
                setTimeout(() => {
                    notificationBox.remove();
                }, 500); // Thời gian chờ để xóa
            }, 3000);
        }
    });
    </script>
</body>

</html>