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
    $product_quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $product_price = mysqli_real_escape_string($conn, $_POST['price']);
    $product_detail = mysqli_real_escape_string($conn, $_POST['detail']);
    $product_brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $product_category = mysqli_real_escape_string($conn, $_POST['category']);
    $product_subcategory = mysqli_real_escape_string($conn, $_POST['subcategory']);
    $selected_tags = explode(',', $_POST['selected_tags']);
    $deleted_image = $_POST['deleted_color_image'] ?? '0';
    $updated_images = explode(',', $_POST['updated_images']);
    $deleted_images = explode(',', $_POST['deleted_images']);

    $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

    // Bắt đầu giao dịch
    mysqli_begin_transaction($conn);

    try {
        // 1. Kiểm tra nếu quantity lớn hơn quantity_stock trong bảng inventory_entries
        if (!empty($product_quantity)) {
            $check_quantity_query = mysqli_query(
                $conn,
                "SELECT quantity_stock FROM inventory_entries 
                     WHERE product_id = '$product_id' AND color_name = '$color_name' LIMIT 1"
            );
            if (!$check_quantity_query) {
                throw new Exception('Lỗi khi kiểm tra số lượng tồn kho: ' . mysqli_error($conn));
            }

            $inventory_entry = mysqli_fetch_assoc($check_quantity_query);
            $current_quantity_stock = $inventory_entry['quantity_stock'];

            // Nếu số lượng yêu cầu lớn hơn số lượng tồn kho, dừng cập nhật
            if ($product_quantity > $current_quantity_stock) {
                throw new Exception('Số lượng yêu cầu lớn hơn số lượng tồn kho hiện có!');
            }
        }

        // 2. Cộng thêm quantity vào quantity_in_stock của bảng products
        if (!empty($product_quantity)) {
            $update_quantity_in_stock_query = "UPDATE products 
                    SET quantity_in_stock = quantity_in_stock + '$product_quantity' 
                    WHERE product_id = '$product_id' AND color_name = '$color_name'";

            $update_quantity_in_stock = mysqli_query($conn, $update_quantity_in_stock_query);
            if (!$update_quantity_in_stock) {
                throw new Exception('Không thể cập nhật quantity_in_stock trong bảng products: ' . mysqli_error($conn));
            }
        }

        // 3. Xử lý xóa các ảnh trong danh sách đã xóa
        if (!empty($deleted_images)) {
            foreach ($deleted_images as $image_to_delete) {
                $updated_images = array_diff($updated_images, [$image_to_delete]);
            }
        }

        // 4. Loại bỏ đường dẫn từ các ảnh cũ (nếu có)
        $updated_images = array_map(function ($image) {
            return basename($image); // Lấy phần tên file, bỏ đường dẫn
        }, $updated_images);

        // 5. Xử lý thêm tên ảnh mới vào danh sách (không bao gồm đường dẫn)
        if (!empty($_FILES['image']['name'][0])) {
            $image_count = count($_FILES['image']['name']);
            for ($i = 0; $i < $image_count; $i++) {
                $image_name = $_FILES['image']['name'][$i];
                $updated_images[] = $image_name;
            }
        }

        // 6. Cập nhật cột product_image với danh sách tên ảnh
        $updated_images_string = implode(',', $updated_images);
        $update_product_images_query = mysqli_query(
            $conn,
            "UPDATE products SET product_image = '$updated_images_string' WHERE product_id = '$product_id'"
        );
        if (!$update_product_images_query) {
            throw new Exception('Không thể cập nhật ảnh sản phẩm: ' . mysqli_error($conn));
        }

        // 7. Cập nhật các thông tin sản phẩm khác, bao gồm xử lý ảnh màu sắc
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

        // 8. Cập nhật quantity_stock trong bảng inventory_entries
        $update_inventory_entries_query = "UPDATE inventory_entries 
                SET quantity_stock = quantity_stock - '$product_quantity' 
                WHERE product_id = '$product_id' AND color_name = '$color_name'";

        $update_inventory_entries = mysqli_query($conn, $update_inventory_entries_query);
        if (!$update_inventory_entries) {
            throw new Exception('Không thể cập nhật quantity_stock trong inventory_entries: ' . mysqli_error($conn));
        }

        // 9. Xử lý tags
        // Lấy danh sách tag hiện tại của sản phẩm
        $current_tag_ids = [];
        $current_tags_query = mysqli_query(
            $conn,
            "SELECT tag_id FROM product_tags WHERE product_id = '$product_id' AND color_name = '$color_name'"
        );
        while ($row = mysqli_fetch_assoc($current_tags_query)) {
            $current_tag_ids[] = $row['tag_id'];
        }

        // Loại bỏ các tag đã không còn được chọn
        foreach ($current_tag_ids as $tag_id) {
            if (!in_array($tag_id, $selected_tags)) {
                // Nếu tag không có trong danh sách đã chọn, xóa tag khỏi bảng product_tags
                $delete_tag_query = mysqli_query(
                    $conn,
                    "DELETE FROM product_tags WHERE product_id = '$product_id' AND tag_id = '$tag_id' AND color_name = '$color_name'"
                );
                if (!$delete_tag_query) {
                    throw new Exception('Không thể xóa tag: ' . mysqli_error($conn));
                }
            }
        }

        // Thêm các tag mới nếu chúng không tồn tại
        foreach ($selected_tags as $tag_id) {
            if (!in_array($tag_id, $current_tag_ids)) {
                $check_tag_query = mysqli_query(
                    $conn,
                    "SELECT tag_name FROM tags WHERE tag_id = '$tag_id' LIMIT 1"
                );
                if (!$check_tag_query) {
                    throw new Exception('Lỗi khi truy vấn bảng tags: ' . mysqli_error($conn));
                }
                $tag_data = mysqli_fetch_assoc($check_tag_query);
                $tag_name = $tag_data['tag_name'];

                $insert_tag = mysqli_query(
                    $conn,
                    "INSERT INTO product_tags (product_id, color_name, tag_id, tag_name) 
                        VALUES ('$product_id', '$color_name', '$tag_id', '$tag_name')"
                );
                if (!$insert_tag) {
                    throw new Exception('Không thể thêm tag: ' . mysqli_error($conn));
                }
            }
        }

        // Commit giao dịch nếu tất cả đều thành công
        mysqli_commit($conn);
        $message[] = 'Sản phẩm và hình ảnh đã được cập nhật thành công!';
    } catch (Exception $e) {
        // Rollback nếu có lỗi
        mysqli_rollback($conn);
        $message[] = 'Lỗi: ' . $e->getMessage();
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
    <?php include '../NVNK/NVNK_header.php'; ?>
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
        <div class="line2"></div>




        <section class="add-products form-container">
            <form method="POST" action="" enctype="multipart/form-data">
                <a style="font-size: 25px;" href="view_product_added.php" class="back-arrow">&#8592;</a>
                <span class="span-title">Lưu ý: Bạn không có quyền chỉnh sửa sản phẩm</span>
                <?php
                $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

                // Truy vấn để lấy tên sản phẩm, màu sắc, số lượng tồn kho, giá sản phẩm, chi tiết sản phẩm, ảnh màu sắc và thương hiệu
                $query = "SELECT product_name, color_name, quantity_in_stock, product_price, product_detail, product_image, color_image, brand_name FROM products WHERE product_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();

                // Khởi tạo biến để lưu thông tin sản phẩm
                $product_display = '';
                $color_name = ''; // Lưu màu sắc của sản phẩm
                $stock_quantity = 0; // Giá trị mặc định
                $product_price = 0; // Giá mặc định
                $product_detail = ''; // Chi tiết sản phẩm mặc định
                $brand_name = ''; // Biến lưu thương hiệu sản phẩm
                $existing_images = array(); // Khởi tạo mảng để lưu ảnh sản phẩm
                $color_image_path = ''; // Biến lưu đường dẫn ảnh màu sắc

                // Kiểm tra xem có sản phẩm nào không
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $product_display = htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8');
                    $color_name = htmlspecialchars($row['color_name'], ENT_QUOTES, 'UTF-8'); // Lấy màu sắc
                    $stock_quantity = intval($row['quantity_in_stock']); // Lấy số lượng tồn kho
                    $product_price = floatval($row['product_price']); // Lấy giá sản phẩm
                    $product_detail = htmlspecialchars($row['product_detail'], ENT_QUOTES, 'UTF-8'); // Lấy chi tiết sản phẩm
                    $brand_name = htmlspecialchars($row['brand_name'], ENT_QUOTES, 'UTF-8'); // Lấy thương hiệu sản phẩm

                    // Giả sử sản phẩm có nhiều ảnh lưu trữ trong trường product_image dưới dạng chuỗi phân cách bằng dấu phẩy
                    if (!empty($row['product_image'])) {
                        $images = explode(',', $row['product_image']); // Tách các ảnh thành mảng
                        foreach ($images as $image) {
                            $existing_images[] = '../image/product/' . htmlspecialchars(trim($image), ENT_QUOTES, 'UTF-8');
                        }
                    }

                    if (!empty($row['color_image'])) {
                        // Mã hóa tên file để tránh lỗi ký tự đặc biệt như #
                        $encoded_color_image = rawurlencode($row['color_image']);
                        $color_image_path = '../image/colorimage/' . $encoded_color_image;
                    }
                }
                ?>



                <div class="input-field">
                    <label>Tên sản phẩm<span>*</span></label>
                    <input type="text" id="productName" name="name" value="<?= $product_display; ?>" required>
                </div>

                <div class="input-field">
                    <label>Màu sắc<span>*</span></label>
                    <input type="text" id="colorName" name="color" value="<?= $color_name; ?>">
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




                <!-- <div class="action-buttons">
                    <input type="submit" name="edit_product" value="Chỉnh sửa sản phẩm">
                    <button type="button" onclick="handleCancelButton()" class="cancel-button">Hủy</button>
                </div> -->

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