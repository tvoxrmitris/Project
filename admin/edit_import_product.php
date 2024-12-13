<?php
// Kết nối cơ sở dữ liệu và bắt đầu phiên
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



if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_product'])) {
    $detail_import_id = isset($_GET['detail_import_id']) ? intval($_GET['detail_import_id']) : 0;
    if ($detail_import_id <= 0) {
        echo "ID sản phẩm không hợp lệ.";
        exit;
    }

    $name = $_POST['name'];
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $code_color = $_POST['code_color'];
    $color = $_POST['color'];
    $capacity = $_POST['capacity'];
    $category = $_POST['category'];
    $subcategory_id = $_POST['subcategory'];

    // Thực hiện cập nhật trong cơ sở dữ liệu
    $update_query = "UPDATE detail_import 
                     SET product_name = ?, 
                         quantity_stock = ?, 
                         import_price = ?, 
                         code_color = ?, 
                         color_name = ?, 
                         capacity = ?, 
                         category_name = ?, 
                         subcategory_name = ? 
                     WHERE detail_import_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("siisssssi", $name, $quantity, $price, $code_color, $color, $capacity, $category, $subcategory_id, $detail_import_id);

    if ($update_stmt->execute()) {
        echo "Cập nhật sản phẩm thành công.";
    } else {
        echo "Lỗi khi cập nhật sản phẩm: " . $update_stmt->error;
    }
    $update_stmt->close();
}






// Truy vấn để lấy sản phẩm có tồn kho mà chưa có trong bảng products
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
    <link rel="stylesheet" type="text/css" href="../CSS/style.css?v=1.1 <?php echo time(); ?>">
    <title>Seraph Beauty - Sản Phẩm</title>
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
    <div id="content-wrapper">
        <div class="title">
            <h2 style="font-size:50px;">Chỉnh sửa sản phẩm</h2>
        </div>
        <style>
        .success-box {
            position: fixed;
            bottom: 20px;
            left: 20px;
            padding: 15px 20px;
            background-color: #28a745;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .success-box.show {
            opacity: 1;
            transform: translateY(0);
        }

        .success-box.hidden {
            display: none;
        }
        </style>

        <div id="success-box" class="success-box hidden">
            Cập nhật sản phẩm thành công!
        </div>

        <script>
        function showSuccessBox(message) {
            const successBox = document.getElementById('success-box');
            successBox.textContent = message; // Cập nhật nội dung thông báo
            successBox.classList.remove('hidden'); // Hiển thị hộp thông báo
            successBox.classList.add('show'); // Thêm hiệu ứng

            // Ẩn hộp thông báo sau 3 giây
            setTimeout(() => {
                successBox.classList.remove('show');
                setTimeout(() => {
                    successBox.classList.add('hidden');
                }, 300); // Đợi hiệu ứng kết thúc trước khi ẩn
            }, 3000);
        }
        </script>


        <div class="line2"></div>

        <section class="add-products form-container">
            <form method="POST" action="" enctype="multipart/form-data">
                <a style="font-size: 25px;" href="detail_import.php" class="back-arrow">&#8592;</a>
                <?php


                // Lấy detail_import_id từ URL
                $detail_import_id = isset($_GET['detail_import_id']) ? intval($_GET['detail_import_id']) : 0;

                // Khởi tạo biến
                $product_name = '';
                $import_price = '';
                $quantity_stock = '';
                $code_color = '';
                $capacity = '';
                $suplier_name = '';
                $color_name = '';
                $category_name = '';
                $subcategory_name = '';
                $subcategory_id = '';

                if ($detail_import_id > 0) {
                    // Lấy thông tin sản phẩm từ bảng detail_import
                    $query = "SELECT product_name, import_price, quantity_stock, code_color, capacity, suplier_id, color_name, category_name, subcategory_name 
                      FROM detail_import 
                      WHERE detail_import_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $detail_import_id);
                    $stmt->execute();
                    $stmt->bind_result($product_name, $import_price, $quantity_stock, $code_color, $capacity, $suplier_id, $color_name, $category_name, $subcategory_name);
                    $stmt->fetch();
                    $stmt->close();

                    // Lấy tên nhà cung cấp
                    if ($suplier_id > 0) {
                        $query = "SELECT suplier_name FROM suplier WHERE suplier_id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $suplier_id);
                        $stmt->execute();
                        $stmt->bind_result($suplier_name);
                        $stmt->fetch();
                        $stmt->close();
                    }
                }






                // Lấy danh mục chính
                $query = "SELECT * FROM categories";
                $result = mysqli_query($conn, $query);
                $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);

                // Lấy tất cả danh mục phụ
                $query = "SELECT * FROM subcategory";
                $result = mysqli_query($conn, $query);
                $subcategories = mysqli_fetch_all($result, MYSQLI_ASSOC);
                ?>

                <!-- Hiển thị tên nhà cung cấp -->
                <div class="input-field">
                    <label>Nhà cung cấp<span>*</span></label>
                    <input type="text" id="supplierName" name="suplier" value="<?= htmlspecialchars($suplier_name); ?>"
                        readonly>
                </div>

                <!-- Các trường thông tin -->
                <div class="input-field">
                    <label>Tên sản phẩm<span>*</span></label>
                    <input type="text" id="productName" name="name" value="<?= htmlspecialchars($product_name); ?>"
                        required>
                </div>

                <div class="input-field">
                    <label for="quantity">Số lượng <span>*</span></label>
                    <input id="quantity" type="number" min="1" name="quantity"
                        value="<?= htmlspecialchars($quantity_stock); ?>" required>
                </div>

                <div class="input-field">
                    <label>Giá nhập<span>*</span></label>
                    <input type="text" id="productPrice" name="price" value="<?= htmlspecialchars($import_price); ?>"
                        required>
                </div>

                <div class="input-field">
                    <label>Mã Màu<span>*</span></label>
                    <input type="text" id="codeColor" name="code_color" value="<?= htmlspecialchars($code_color); ?>"
                        required>
                </div>

                <div class="input-field">
                    <label>Mô tả màu sắc<span>*</span></label>
                    <input type="text" id="colorName" name="color" value="<?= htmlspecialchars($color_name); ?>"
                        required>
                </div>

                <div class="input-field">
                    <label>Thể tích<span>*</span></label>
                    <input type="text" id="capacity" name="capacity" value="<?= htmlspecialchars($capacity); ?>">
                </div>
                <!-- Danh mục chính -->
                <div class="input-field">
                    <label>Danh mục chính<span>*</span></label>
                    <select id="mainCategory" name="category">
                        <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id']; ?>"
                            <?= $category['category_id'] == $category_name ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($category['category_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>


                <!-- Danh mục phụ -->
                <div class="input-field">
                    <label>Danh mục phụ<span>*</span></label>
                    <select id="subCategory" name="subcategory" required>
                        <?php if (!empty($subcategory_name)): ?>
                        <!-- Hiển thị danh mục phụ từ bảng detail_import -->
                        <option value="<?= htmlspecialchars($subcategory_name); ?>" selected>
                            <?php
                                // Lấy tên danh mục phụ từ bảng subcategory
                                $query = "SELECT subcategory_name FROM subcategory WHERE subcategory_id = ?";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("i", $subcategory_name);
                                $stmt->execute();
                                $stmt->bind_result($subcategory_real_name);
                                $stmt->fetch();
                                $stmt->close();
                                echo htmlspecialchars($subcategory_real_name);
                                ?>
                        </option>
                        <?php endif; ?>
                        <option value="" disabled>Chọn danh mục phụ</option>
                    </select>
                </div>




                <!-- Nút hành động -->
                <div class="action-buttons">
                    <input type="submit" name="edit_product" value="Chỉnh sửa sản phẩm" class="edit-button">
                </div>
            </form>
        </section>



        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainCategorySelect = document.getElementById('mainCategory');
            const subCategorySelect = document.getElementById('subCategory');

            function updateSubcategories() {
                const selectedCategoryId = mainCategorySelect.value;

                // Nếu chưa chọn danh mục chính, không làm gì
                if (!selectedCategoryId) return;

                // Xóa tất cả tùy chọn hiện tại
                subCategorySelect.innerHTML = '<option value="" disabled>Đang tải...</option>';

                // Gửi yêu cầu Ajax để lấy danh mục phụ
                fetch('get_subcategories.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'category_id=' + encodeURIComponent(selectedCategoryId),
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Xóa tùy chọn "Đang tải..."
                        subCategorySelect.innerHTML = '';

                        // Thêm danh mục phụ đầu tiên làm tùy chọn đầu tiên và tự động chọn
                        if (data.length > 0) {
                            const firstOption = document.createElement('option');
                            firstOption.value = data[0].subcategory_id;
                            firstOption.textContent = data[0].subcategory_name;
                            firstOption.selected = true; // Đặt làm tùy chọn mặc định
                            subCategorySelect.appendChild(firstOption);

                            // Thêm các tùy chọn danh mục phụ còn lại
                            data.slice(1).forEach(subcategory => {
                                const option = document.createElement('option');
                                option.value = subcategory.subcategory_id;
                                option.textContent = subcategory.subcategory_name;
                                subCategorySelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Lỗi khi tải danh mục phụ:', error);
                    });
            }

            // Gọi hàm khi danh mục chính thay đổi
            mainCategorySelect.addEventListener('change', updateSubcategories);

            // Gọi hàm khi trang được tải nếu danh mục chính đã được chọn
            if (mainCategorySelect.value) {
                updateSubcategories();
            }
        });
        </script>




    </div>

</body>








</html>