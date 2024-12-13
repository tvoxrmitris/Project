<?php
// Kết nối cơ sở dữ liệu và bắt đầu phiên
include '../connection/connection.php';
session_start();
if (!isset($_SESSION['employee_id']) || $_SESSION['employee_type'] !== 'NVNK') {
    header('location:../components/admin_login.php');
    exit;
}




// Xử lý đăng xuất
if (isset($_POST['logout'])) {
    session_destroy();
    header('location:../components/admin_login.php');
    exit;
}






if (isset($_POST['edit_product'])) {
    // Lấy thông tin từ form
    $product_name = mysqli_real_escape_string($conn, $_POST['name']);
    $color_name = mysqli_real_escape_string($conn, $_POST['color']);
    $product_quantity = (int)$_POST['quantity'];
    $product_price = mysqli_real_escape_string($conn, $_POST['price']);
    $product_detail = mysqli_real_escape_string($conn, $_POST['detail']);
    $product_brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $product_capacity = !empty($_POST['capacity']) ? mysqli_real_escape_string($conn, $_POST['capacity']) : NULL;

    $product_category = mysqli_real_escape_string($conn, $_POST['category']);
    $product_subcategory = mysqli_real_escape_string($conn, $_POST['subcategory']);
    $selected_tags = explode(',', $_POST['selected_tags']);
    $deleted_image = $_POST['deleted_color_image'] ?? '0';
    $updated_images = explode(',', $_POST['updated_images']);
    $deleted_images = explode(',', $_POST['deleted_images']);

    $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

    // Kiểm tra sản phẩm có tồn tại trong bảng products hay không
    $check_product_query = "SELECT * FROM products WHERE product_id = '$product_id' LIMIT 1";
    $product_result = mysqli_query($conn, $check_product_query);

    if (!$product_result || mysqli_num_rows($product_result) === 0) {
        $message[] = 'Không tìm thấy sản phẩm trong bảng products!';
        return;
    }

    // Bắt đầu giao dịch
    mysqli_begin_transaction($conn);

    try {
        // Lấy số lượng tồn kho
        $check_quantity_inventory = "SELECT quantity_stock FROM inventory_entries 
            WHERE inventory_id = '$product_id' LIMIT 1";
        $result = mysqli_query($conn, $check_quantity_inventory);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $quantity_stock = (int)$row['quantity_stock'];
        } else {
            throw new Exception("Không tìm thấy sản phẩm trong kho.");
        }

        // Kiểm tra nếu số lượng nhập lớn hơn số lượng tồn kho
        if ($product_quantity > $quantity_stock) {
            throw new Exception("Số lượng nhập lớn hơn số lượng tồn kho hiện có.");
        }

        // Cập nhật số lượng tồn kho trong bảng `products`
        if (!empty($product_quantity)) {
            $update_quantity_in_stock_query = "UPDATE products 
                SET quantity_in_stock = quantity_in_stock + '$product_quantity' 
                WHERE product_id = '$product_id'";

            $update_quantity_in_stock = mysqli_query($conn, $update_quantity_in_stock_query);
            if (!$update_quantity_in_stock) {
                throw new Exception('Không thể cập nhật quantity_in_stock trong bảng products: ' . mysqli_error($conn));
            }
        }

        // Xử lý xóa và cập nhật ảnh
        if (!empty($deleted_images)) {
            foreach ($deleted_images as $image_to_delete) {
                $updated_images = array_diff($updated_images, [$image_to_delete]);
            }
        }

        $updated_images = array_map(function ($image) {
            return basename($image);
        }, $updated_images);

        if (!empty($_FILES['image']['name'][0])) {
            $image_count = count($_FILES['image']['name']);
            for ($i = 0; $i < $image_count; $i++) {
                $image_name = $_FILES['image']['name'][$i];
                $updated_images[] = $image_name;
            }
        }

        $updated_images_string = implode(',', $updated_images);
        $update_product_images_query = mysqli_query(
            $conn,
            "UPDATE products SET product_image = '$updated_images_string' WHERE product_id = '$product_id'"
        );
        if (!$update_product_images_query) {
            throw new Exception('Không thể cập nhật ảnh sản phẩm: ' . mysqli_error($conn));
        }

        // Cập nhật các thông tin sản phẩm
        $update_product_query = "UPDATE products SET 
                product_price = '$product_price',
                product_detail = '$product_detail',
                color_name = '$color_name',
                brand_name = '$product_brand',
                category_name = '$product_category',
                product_subcategory = '$product_subcategory'";

        if (isset($_POST['deleted_color_image']) && $_POST['deleted_color_image'] == '1') {
            $update_product_query .= ", color_image = ''";
        } elseif (!empty($_FILES['color_image']['name'])) {
            $color_image_name = mysqli_real_escape_string($conn, $_FILES['color_image']['name']);
            $update_product_query .= ", color_image = '$color_image_name'";
        }

        $update_product_query .= " WHERE product_id = '$product_id'";

        $update_product = mysqli_query($conn, $update_product_query);
        if (!$update_product) {
            throw new Exception('Không thể cập nhật sản phẩm: ' . mysqli_error($conn));
        }

        // Cập nhật tồn kho trong `inventory_entries`
        $update_inventory_entries_query = "UPDATE inventory_entries 
                SET quantity_stock = quantity_stock - '$product_quantity' 
                WHERE inventory_id = '$product_id'";

        $update_inventory_entries = mysqli_query($conn, $update_inventory_entries_query);
        if (!$update_inventory_entries) {
            throw new Exception('Không thể cập nhật quantity_stock trong inventory_entries: ' . mysqli_error($conn));
        }

        // Kiểm tra và xóa sản phẩm khỏi bảng `low_stock_requests`
        $delete_low_stock_query = "DELETE FROM low_stock_requests WHERE product_id = '$product_id'";
        $delete_low_stock_result = mysqli_query($conn, $delete_low_stock_query);

        if (!$delete_low_stock_result) {
            throw new Exception('Không thể xóa sản phẩm khỏi bảng low_stock_requests: ' . mysqli_error($conn));
        }

        // Commit giao dịch
        mysqli_commit($conn);
        $message[] = 'Sản phẩm và hình ảnh đã được cập nhật thành công!';
    } catch (Exception $e) {
        // Rollback nếu có lỗi
        mysqli_rollback($conn);
        $message[] = 'Lỗi: ' . $e->getMessage();
    }
}

// Hiển thị thông báo nếu có
if (isset($message) && !empty($message)) {
    foreach ($message as $msg) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    showToast('$msg');
                });
              </script>";
    }
}








// Truy vấn để lấy sản phẩm có tồn kho mà chưa có trong bảng products
$query = "
    SELECT product_name, color_name, quantity_stock, CONCAT(color_name, '.jpg') AS color_image
    FROM inventory_entries
    WHERE quantity_stock > 0
    AND (product_name, color_name) NOT IN (SELECT product_name, color_name FROM products)
    ";
$missing_info_products = mysqli_query($conn, $query);












?>

<style type="text/css">
<?php include '../CSS/style.css';

?>th:nth-child(1),
td:nth-child(1) {
    width: 65px;
}

th:nth-child(2),
td:nth-child(2) {
    width: 350px;
}



/* Hiệu ứng toàn dòng */
.highlight-row {
    background-color: #ffffcc !important;
    /* Nền vàng nhạt cho dòng */
    transition: all 0.3s ease;
    font-weight: bold;
}

/* Các ô được highlight trong bảng */
.highlight-row .highlight-td {
    background-color: #eaeaea !important;
    color: #000 !important;
    font-weight: bold;
    font-family: 'Futura', sans-serif;
    border: 1px solid #000000 !important;
    /* Viền đen bao quanh */
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1) !important;
    padding: 12px;
    /* Tăng padding để dễ nhìn hơn */
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    font-size: 1.1em;
    /* Tăng kích thước chữ */
}

/* Hiệu ứng khi hover */
.highlight-row .highlight-td:hover {
    transform: scale(1.02);
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
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
    <title>Seraph Beauty - Sản Phẩm</title>
</head>

<body>
    <?php include '../NVNK/NVNK_header.php'; ?>

    <style>
    .toast-box {
        position: fixed;
        bottom: 20px;
        left: 20px;
        background-color: #000;
        /* Tông đen sang trọng */
        color: #fff;
        /* Chữ trắng để nổi bật */
        padding: 15px 25px;
        /* Khoảng cách thoải mái */
        border-radius: 8px;
        /* Các góc bo nhẹ */
        font-size: 16px;

        /* Phông chữ tối giản */
        font-weight: bold;
        /* Chữ đậm cho cảm giác cao cấp */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        /* Bóng đổ nhẹ, tạo chiều sâu */
        border: 2px solid #fff;
        /* Viền trắng nổi bật */
        opacity: 0;
        /* Hiệu ứng mờ khi bắt đầu */
        transform: translateY(20px);
        transition: opacity 0.3s ease, transform 0.3s ease;
        z-index: 9999;
    }

    .toast-box.show {
        opacity: 1;
        transform: translateY(0);
    }

    .no-product-message {
        background-color: #000;
        color: #fff;
        padding: 20px;
        font-size: 1.2rem;
        font-weight: bold;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        margin-top: 30px;
    }
    </style>
    <script>
    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast-box';
        toast.innerText = message;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }
    </script>
    <div id="content-wrapper">
        <div class="title">
            <h2 style="font-size:50px;">Chỉnh sửa sản phẩm</h2>
        </div>
        <div class="line2"></div>




        <section class="add-products form-container">
            <div class="request_product">
                <span>Sản phẩm ưu tiên nhập</span>
                <?php
                // Lấy product_id từ URL và đảm bảo loại bỏ khoảng trắng
                $current_product_id = isset($_GET['product_id']) ? trim($_GET['product_id']) : null;

                // Lấy danh sách product_id từ bảng low_stock_requests
                $query = "SELECT product_id FROM low_stock_requests";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    echo "<table border='1' cellspacing='0' cellpadding='10'>";
                    echo "<tr>
                    <th>STT</th>
                    <th>Thông tin sản phẩm</th>
                  </tr>";

                    $stt = 1;
                    while ($row = $result->fetch_assoc()) {
                        $product_id = trim($row['product_id']);

                        // Lấy thông tin sản phẩm từ bảng products dựa trên product_id
                        $product_query = "
                    SELECT product_name, color_name, detail_color, capacity 
                    FROM products 
                    WHERE product_id = '$product_id'
                ";
                        $product_result = $conn->query($product_query);

                        if ($product_result->num_rows > 0) {
                            while ($product = $product_result->fetch_assoc()) {
                                $capacity_display = $product['capacity'] ? " - " . $product['capacity'] : "";

                                // Kiểm tra nếu product_id trùng với product_id trên URL
                                $highlight_class = ($product_id === $current_product_id) ? 'highlight-row' : '';

                                echo "<tr class='request-product-row $highlight_class' 
                              data-product-name='{$product['product_name']}' 
                              data-color-name='{$product['color_name']}' 
                              data-detail-color='{$product['detail_color']}' 
                              data-capacity='{$product['capacity']}'>";
                                echo "<td class='highlight-td'>" . $stt++ . "</td>";
                                echo "<td class='highlight-td'>" . $product['product_name'] . " - " . $product['color_name'] . " - " . $product['detail_color'] . $capacity_display . "</td>";

                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2'>Không tìm thấy sản phẩm cho product_id: $product_id</td></tr>";
                        }
                    }
                    echo "</table>";
                } else {
                    echo "<div class='no-product-message'>Không có sản phẩm cần ưu tiên</div>";
                }
                ?>
            </div>



            <form method="POST" action="" enctype="multipart/form-data">
                <?php
                // Lấy product_id từ URL
                $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

                // Truy vấn để lấy tên sản phẩm, màu sắc, số lượng tồn kho, giá sản phẩm, chi tiết sản phẩm, ảnh màu sắc và thương hiệu
                $query = "SELECT product_name, color_name, quantity_in_stock, product_price, product_detail, product_image, color_image, brand_name, capacity FROM products WHERE product_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();

                // Khởi tạo các biến để lưu thông tin sản phẩm
                $product_display = '';
                $color_name = '';
                $stock_quantity = 0;
                $product_price = 0;
                $product_detail = '';
                $brand_name = '';
                $existing_images = array();
                $color_image_path = '';
                $capacity = ''; // Biến lưu thể tích sản phẩm

                // Kiểm tra nếu có sản phẩm
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $product_display = htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8');
                    $color_name = htmlspecialchars($row['color_name'], ENT_QUOTES, 'UTF-8');
                    $stock_quantity = intval($row['quantity_in_stock']);
                    $product_price = floatval($row['product_price']);
                    $product_detail = htmlspecialchars($row['product_detail'], ENT_QUOTES, 'UTF-8');
                    $brand_name = htmlspecialchars($row['brand_name'], ENT_QUOTES, 'UTF-8');
                    $capacity = htmlspecialchars($row['capacity'], ENT_QUOTES, 'UTF-8'); // Lấy thể tích sản phẩm

                    // Giả sử sản phẩm có nhiều ảnh
                    if (!empty($row['product_image'])) {
                        $images = explode(',', $row['product_image']);
                        foreach ($images as $image) {
                            $existing_images[] = '../image/product/' . htmlspecialchars(trim($image), ENT_QUOTES, 'UTF-8');
                        }
                    }

                    // Lấy đường dẫn ảnh màu sắc
                    if (!empty($row['color_image'])) {
                        $encoded_color_image = rawurlencode($row['color_image']);
                        $color_image_path = '../image/colorimage/' . $encoded_color_image;
                    }
                }
                ?>

                <div class="input-field">
                    <label for="productName">Tên sản phẩm<span>*</span></label>
                    <select id="productName" name="name" required>
                        <option value="">Chọn sản phẩm</option>
                        <?php
                        // Truy vấn lấy tất cả sản phẩm
                        $query = "SELECT product_id, product_name, color_name, capacity FROM products";
                        $result = $conn->query($query);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $productDisplay = $row['product_name'] . ' - ' . $row['color_name'] . (!empty($row['capacity']) ? ' - ' . $row['capacity'] : '');
                                $selected = ($product_id == $row['product_id']) ? ' selected' : '';
                                echo '<option value="' . $row['product_id'] . '"' . $selected . '>' . htmlspecialchars($productDisplay) . '</option>';
                            }
                        } else {
                            echo '<option value="">Không có sản phẩm nào</option>';
                        }
                        ?>
                    </select>
                </div>

                <script>
                document.getElementById('productName').addEventListener('change', function() {
                    const selectedProductId = this.value;
                    if (selectedProductId) {
                        const url = new URL(window.location.href);
                        url.searchParams.set('product_id', selectedProductId);
                        window.location.href = url; // Chuyển hướng tới URL mới
                    }
                });
                </script>

                <div class="input-field">
                    <label>Màu sắc<span>*</span></label>
                    <input type="text" id="colorName" name="color" value="<?= $color_name; ?>" readonly>
                </div>

                <div class="input-field">
                    <label>Thể tích<span>*</span></label>
                    <input type="text" name="capacity" value="<?= $capacity; ?>" readonly>
                </div>

                <div class="input-field">
                    <label>Ảnh màu sắc (Nếu có)<span>*</span></label>
                    <input type="file" name="color_image" id="colorImage"
                        onchange="previewImages('colorImage', 'colorImagePreview')">
                </div>



                <div id="colorImagePreview" class="image-preview">
                    <?php if (!empty($color_image_path)): ?>
                    <div class="image-container" id="colorImageContainer">
                        <img src="<?= $color_image_path; ?>" alt="Color Image" style="max-width: 100px; margin: 5px;"
                            class="color-image">
                        <span class="close-button" onclick="removeColor(this)">x</span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Trường ẩn để đánh dấu ảnh đã bị xóa -->
                <input type="hidden" name="deleted_color_image" id="deletedColorImage" value="0">

                <script>
                function previewImages(inputId, previewId) {
                    const input = document.getElementById(inputId);
                    const preview = document.getElementById(previewId);

                    preview.innerHTML = ''; // Xóa ảnh trước đó nếu có

                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const container = document.createElement('div');
                            container.className = 'image-container';
                            container.innerHTML = `
                    <img src="${e.target.result}" alt="Color Image" style="max-width: 100px; margin: 5px;" class="color-image">
                    <span class="close-button" onclick="removeColor(this)">x</span>
                `;
                            preview.appendChild(container);
                        };
                        reader.readAsDataURL(input.files[0]);
                    }
                }

                function removeColor(button) {
                    const container = button.parentElement;
                    const input = document.getElementById('colorImage');

                    container.remove(); // Xóa ảnh khỏi giao diện
                    input.value = ''; // Reset giá trị của input file

                    // Đánh dấu ảnh đã bị xóa
                    document.getElementById('deletedColorImage').value = '1';
                }
                </script>


                <div class="input-field">
                    <label for="quantity">Số lượng <span>*</span></label>
                    <span id="pre-message">Số lượng sẽ được cộng vào sản phẩm trong cửa hàng</span>
                    <input id="quantity" placeholder="Số lượng sẽ cộng vào số lượng hiện tại" type="number" min="1"
                        name="quantity">
                    <span id="stockQuantity">Hiện tại trong cửa hàng còn: <?= $stock_quantity; ?></span>
                </div>

                <div class="input-field">
                    <label>Giá Bán<span>*</span></label>
                    <input type="number" name="price" value="<?= $product_price; ?>" required>
                </div>

                <div class="input-field">
                    <label>Chi tiết sản phẩm<span>*</span></label>
                    <textarea name="detail" required><?= $product_detail; ?></textarea>
                </div>

                <?php
                // Phần code để lấy tất cả tags từ bảng `tags`
                $query = "SELECT * FROM tags";
                $result = mysqli_query($conn, $query);
                $tags = [];
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $tags[] = $row;
                    }
                }

                // Lấy danh sách tag_id đã được chọn cho sản phẩm
                $query_selected_tags = "SELECT tag_id FROM product_tags WHERE product_id = ?";
                $stmt = $conn->prepare($query_selected_tags);
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result_selected = $stmt->get_result();
                $selected_tag_ids = [];
                while ($row = $result_selected->fetch_assoc()) {
                    $selected_tag_ids[] = $row['tag_id'];
                }
                ?>

                <div class="input-field">
                    <label>Tags<span>*</span></label>
                    <div class="tags-container">
                        <?php foreach ($tags as $tag): ?>
                        <span class="tag-item <?= in_array($tag['tag_id'], $selected_tag_ids) ? 'selected' : ''; ?>"
                            data-tag-id="<?= htmlspecialchars($tag['tag_id'], ENT_QUOTES, 'UTF-8'); ?>" style="background-color: <?= in_array($tag['tag_id'], $selected_tag_ids) ? 'black' : 'transparent'; ?>; 
                    color: <?= in_array($tag['tag_id'], $selected_tag_ids) ? 'white' : 'inherit'; ?>;">
                            <?= htmlspecialchars($tag['tag_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Hidden field to store selected tag IDs -->
                <input type="hidden" name="selected_tags" id="selected_tags"
                    value="<?= implode(',', $selected_tag_ids); ?>">

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    let selectedTags = document.getElementById('selected_tags').value ? document.getElementById(
                        'selected_tags').value.split(',') : [];

                    document.querySelectorAll('.tag-item').forEach(function(item) {
                        item.addEventListener('click', function() {
                            const tagId = this.getAttribute('data-tag-id');
                            if (selectedTags.includes(tagId)) {
                                selectedTags = selectedTags.filter(id => id !== tagId);
                                this.classList.remove('selected');
                                this.style.backgroundColor = 'transparent';
                                this.style.color = 'inherit';
                            } else {
                                selectedTags.push(tagId);
                                this.classList.add('selected');
                                this.style.backgroundColor = 'black';
                                this.style.color = 'white';
                            }
                            document.getElementById('selected_tags').value = selectedTags.join(
                                ',');
                        });
                    });
                });
                </script>

                <style>
                /* Optional styling for selected tags */
                .tag-item {
                    display: inline-block;
                    padding: 5px 10px;
                    border: 1px solid #ccc;
                    margin: 2px;
                    cursor: pointer;
                }

                .tag-item.selected {
                    background-color: #000;
                    color: white;
                }
                </style>

                <div class="input-field">
                    <label>Ảnh sản phẩm<span>*</span></label>
                    <input type="file" name="image[]" id="productImages" multiple
                        onchange="previewImages('productImages', 'productImagePreview')">
                </div>

                <div id="productImagePreview" class="image-preview">
                    <!-- Hiển thị ảnh cũ -->
                    <?php if (!empty($existing_images)): ?>
                    <?php foreach ($existing_images as $index => $image_name): ?>
                    <div class="image-container" style="position: relative; display: inline-block;"
                        data-index="<?= $index; ?>" draggable="true" ondragstart="drag(event)"
                        ondragover="allowDrop(event)" ondrop="drop(event)">
                        <img src="<?= $image_name; ?>" alt="Product Image" style="max-width: 100px; margin: 5px;">
                        <span class="close-button" onclick="removeImage(this, <?= $index; ?>)">x</span>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Hidden field to store updated image list -->
                <input type="hidden" id="updatedImages" name="updated_images"
                    value="<?= implode(',', $existing_images); ?>">

                <!-- Hidden field để lưu danh sách các ảnh cần xóa khỏi cơ sở dữ liệu (không xóa khỏi thư mục) -->
                <input type="hidden" id="deletedImages" name="deleted_images" value="">


                <script>
                // Hàm preview ảnh khi chọn thêm ảnh mới
                function previewImages(inputId, previewContainerId) {
                    const input = document.getElementById(inputId);
                    const previewContainer = document.getElementById(previewContainerId);

                    if (input.files) {
                        // Hiển thị ảnh mới
                        Array.from(input.files).forEach((file, index) => {
                            const reader = new FileReader();

                            reader.onload = function(e) {
                                const imageElement = document.createElement('div');
                                imageElement.className = 'image-container';
                                imageElement.style.position = 'relative';
                                imageElement.style.display = 'inline-block';

                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.alt = 'New Product Image';
                                img.style.maxWidth = '100px';
                                img.style.margin = '5px';

                                const closeButton = document.createElement('span');
                                closeButton.className = 'close-button';
                                closeButton.textContent = 'x';
                                closeButton.style.position = 'absolute';
                                closeButton.style.top = '0';
                                closeButton.style.right = '0';
                                closeButton.onclick = function() {
                                    imageElement.remove(); // Xóa ảnh khỏi preview
                                };

                                imageElement.appendChild(img);
                                imageElement.appendChild(closeButton);
                                previewContainer.appendChild(imageElement);
                            };

                            reader.readAsDataURL(file);
                        });
                    }
                }

                // Hàm xóa ảnh cũ
                function removeImage(button, index) {
                    const imageContainer = button.parentElement;
                    imageContainer.remove();

                    let existingImages = document.getElementById('updatedImages').value.split(',');

                    if (index >= 0 && index < existingImages.length) {
                        let deletedImages = document.getElementById('deletedImages').value.split(',');
                        deletedImages.push(existingImages[index]);

                        document.getElementById('deletedImages').value = deletedImages.join(',');
                        existingImages.splice(index, 1);
                    }

                    document.getElementById('updatedImages').value = existingImages.join(',');

                    console.log('Updated images:', existingImages); // Debug xem mảng đã được cập nhật chưa
                    console.log('Deleted images:', deletedImages); // Debug xem danh sách ảnh đã bị xóa chưa
                }
                </script>







                <?php
                // Lấy thông tin sản phẩm hiện tại dựa trên product_id
                $query = "SELECT p.product_id, p.product_name, p.category_name, p.product_subcategory, c.category_name AS current_category_name
                FROM products p
                LEFT JOIN categories c ON p.category_name = c.category_id
                WHERE p.product_id = $product_id";
                $result = mysqli_query($conn, $query);


                // Kiểm tra kết quả truy vấn
                if (mysqli_num_rows($result) > 0) {
                    $current_product = mysqli_fetch_assoc($result);
                } else {
                    echo "Không tìm thấy sản phẩm!";
                    exit;
                }

                // Lấy tất cả danh mục chính
                $query = "SELECT * FROM categories";
                $result = mysqli_query($conn, $query);
                $categories = array();
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $categories[] = $row;
                    }
                }

                // Lấy tất cả danh mục phụ (nếu cần)
                $query = "SELECT * FROM subcategory";
                $result = mysqli_query($conn, $query);
                $subcategories = array();
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $subcategories[] = $row;
                    }
                }


                ?>

                <!-- HTML hiển thị dropdown -->
                <div class="input-field">
                    <label>Danh mục chính<span>*</span></label>
                    <select id="mainCategory" name="category">
                        <?php
                        // Hiển thị category_name của sản phẩm hiện tại trước
                        if (isset($current_product['category_name'])) {
                            echo '<option value="' . $current_product['category_name'] . '" selected style="text-transform: capitalize;">'
                                . $current_product['current_category_name'] . '</option>';
                        }
                        ?>
                        <!-- Hiển thị tất cả các danh mục -->
                        <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id']; ?>" style="text-transform: capitalize;">
                            <?= $category['category_name']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>


                <div class="input-field">
                    <label>Danh mục phụ<span>*</span></label>
                    <select id="subCategory" name="subcategory">
                        <?php
                        // Hiển thị danh mục phụ của sản phẩm hiện tại nếu có
                        if (isset($current_product['product_subcategory'])) {
                            echo '<option value="' . $current_product['product_subcategory'] . '" selected style="text-transform: capitalize;">'
                                . $current_product['product_subcategory'] . '</option>';
                        }
                        ?>
                        <!-- Danh mục phụ sẽ được cập nhật dựa trên danh mục chính được chọn thông qua AJAX -->
                    </select>
                </div>



                <?php
                $query = "SELECT * FROM brands";
                $result = mysqli_query($conn, $query);
                $brands = array();
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $brands[] = $row;
                    }
                }
                ?>
                <div class="input-field">
                    <label>Thương hiệu<span>*</span></label>
                    <select name="brand">
                        <?php foreach ($brands as $brand): ?>
                        <option value="<?= htmlspecialchars($brand['brand_name'], ENT_QUOTES, 'UTF-8'); ?>"
                            <?= ($brand['brand_name'] === $brand_name) ? 'selected' : ''; ?>
                            style="text-transform: capitalize;">
                            <?= htmlspecialchars($brand['brand_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>




                <div class="action-buttons">
                    <input type="submit" name="edit_product" value="Chỉnh sửa sản phẩm">

                </div>

                <script>
                function handleCancelButton() {
                    if (document.referrer.includes('outstock.php')) {
                        window.location.href = 'outstock.php';
                    } else {
                        window.location.href = 'view_product_added.php';
                    }
                }
                </script>

            </form>
        </section>
    </div>

</body>






<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainCategorySelect = document.getElementById('mainCategory');
    const subCategorySelect = document.getElementById('subCategory');

    // Chuyển mảng PHP sang đối tượng JavaScript
    const subcategories = <?php echo json_encode($subcategories); ?>;
    const currentSubcategory =
        "<?php echo isset($current_product['product_subcategory']) ? $current_product['product_subcategory'] : ''; ?>";

    function updateSubcategories() {
        const selectedCategoryId = mainCategorySelect.value;

        // Xóa các tùy chọn hiện có
        subCategorySelect.innerHTML = '';

        // Lọc các danh mục phụ dựa trên danh mục chính được chọn
        const filteredSubcategories = subcategories.filter(subcategory => subcategory.category_id ==
            selectedCategoryId);

        // Điền vào dropdown danh mục phụ
        filteredSubcategories.forEach(subcategory => {
            const option = document.createElement('option');
            option.value = subcategory.subcategory_name;
            option.textContent = subcategory.subcategory_name;
            option.style.textTransform = 'capitalize';

            // Kiểm tra xem danh mục phụ hiện tại có khớp với danh mục trong cơ sở dữ liệu không
            if (subcategory.subcategory_name === currentSubcategory) {
                option.selected = true; // Chọn danh mục phụ hiện tại
            }

            subCategorySelect.appendChild(option);
        });
    }

    // Cập nhật danh mục phụ khi danh mục chính thay đổi
    mainCategorySelect.addEventListener('change', updateSubcategories);

    // Khởi tạo danh mục phụ khi trang được tải
    updateSubcategories();
});

function updateStockQuantity() {
    const select = document.getElementById('productSelect');
    const selectedOption = select.options[select.selectedIndex];

    // Lấy tên sản phẩm
    const productName = selectedOption.value;

    // Lấy color_name
    const colorName = selectedOption.getAttribute('data-color');

    // Lấy số lượng tồn kho
    const quantity = selectedOption.getAttribute('data-quantity');

    // Lấy product_id
    const productId = selectedOption.getAttribute('data-product-id');

    // Cập nhật giá trị cho input hidden
    document.getElementById('colorNameInput').value = colorName;

    // Thực hiện các thao tác tiếp theo như cập nhật giao diện hoặc gửi dữ liệu đến server
    console.log('Tên sản phẩm:', productName);
    console.log('Màu sắc:', colorName);
    console.log('Số lượng:', quantity);
    console.log('Product ID:', productId);
}
</script>

</html>