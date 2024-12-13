<?php
include '../connection/connection.php';
// Đảm bảo không có khoảng trắng, ký tự hoặc output nào trước khi gọi session_start()
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





if (isset($_POST['add_product'])) {
    // Lấy thông tin từ form và thoát các ký tự đặc biệt để tránh lỗi SQL injection
    $product_name = mysqli_real_escape_string($conn, $_POST['name']);
    $code_color = mysqli_real_escape_string($conn, $_POST['code_color']);
    $product_quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $product_price = mysqli_real_escape_string($conn, $_POST['price']);
    $product_detail = mysqli_real_escape_string($conn, $_POST['detail']);
    $product_brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $product_category = mysqli_real_escape_string($conn, $_POST['category']);
    $product_subcategory = mysqli_real_escape_string($conn, $_POST['subcategory']);
    $selected_tags = explode(',', $_POST['selected_tags']);
    $capacity = mysqli_real_escape_string($conn, $_POST['capacityy']);
    $collection = mysqli_real_escape_string($conn, $_POST['collection']);

    // Xử lý hình ảnh
    $image_paths = [];
    if (!empty($_FILES['image']['name'][0])) {
        $total_images = count($_FILES['image']['name']);
        for ($i = 0; $i < $total_images; $i++) {
            $image_name = mysqli_real_escape_string($conn, $_FILES['image']['name'][$i]);
            $image_paths[] = $image_name;
        }
    }
    $image_paths_str = implode(',', $image_paths);

    // Kiểm tra nếu có tệp ảnh màu sắc được tải lên
    if (!empty($_FILES['color_image']['name'])) {
        $color_image_name = mysqli_real_escape_string($conn, $_FILES['color_image']['name']);
    } else {
        $color_image_name = null;
    }

    // Bắt đầu giao dịch
    mysqli_begin_transaction($conn);

    try {
        // Kiểm tra tồn tại trong bảng inventory_entries
        $check_inventory = mysqli_query($conn, "SELECT inventory_id, quantity_stock FROM inventory_entries WHERE product_name = '$product_name' AND code_color = '$code_color' AND capacity = '$capacity' LIMIT 1");

        if (!$check_inventory) {
            throw new Exception('Lỗi khi truy vấn bảng inventory_entries: ' . mysqli_error($conn));
        }

        $inventory_entry = mysqli_fetch_assoc($check_inventory);

        if ($inventory_entry) {
            $inventory_id = $inventory_entry['inventory_id'];
            $quantity_stock = $inventory_entry['quantity_stock'];

            if ($product_quantity > $quantity_stock) {
                throw new Exception('Số lượng nhập vượt quá số lượng tồn kho hiện có.');
            }

            // Kiểm tra sản phẩm trong bảng products
            $check_product = mysqli_query($conn, "SELECT product_id FROM products WHERE product_id = '$inventory_id' LIMIT 1");
            $existing_product = mysqli_fetch_assoc($check_product);

            if ($existing_product) {
                // Cập nhật sản phẩm
                $update_product_query = "UPDATE products SET 
                    quantity_in_stock = quantity_in_stock + '$product_quantity',
                    product_price = '$product_price', 
                    product_detail = '$product_detail', 
                    brand_name = '$product_brand', 
                    category_name = '$product_category', 
                    product_subcategory = '$product_subcategory',
                    color_image = '$color_image_name' ";

                if (!empty($collection)) {
                    $update_product_query .= ", collection = '" . mysqli_real_escape_string($conn, $collection) . "' ";
                }

                $update_product_query .= "WHERE product_id = '$inventory_id'";

                $update_product = mysqli_query($conn, $update_product_query);

                if (!$update_product) {
                    throw new Exception('Không thể cập nhật sản phẩm: ' . mysqli_error($conn));
                }
            } else {
                // Thêm sản phẩm mới
                $insert_product_query = "INSERT INTO products 
                    (product_id, product_name, product_price, color_name, color_image, capacity, quantity_in_stock, product_detail, product_image, brand_name, category_name, product_subcategory, collection, create_at) 
                    VALUES ('$inventory_id', '$product_name', '$product_price', '$code_color', '$color_image_name', '$capacity', '$product_quantity', '$product_detail', '$image_paths_str', '$product_brand', '$product_category', '$product_subcategory', '" . mysqli_real_escape_string($conn, $collection) . "', NOW())";

                $insert_product = mysqli_query($conn, $insert_product_query);

                if (!$insert_product) {
                    throw new Exception('Không thể thêm sản phẩm: ' . mysqli_error($conn));
                }
            }

            // Lấy tên nhân viên từ bảng employees
            $employee_id = $_SESSION['employee_id'];
            $sql_get_employee_name = "SELECT employee_name FROM employees WHERE employee_id = '$employee_id'";
            $result_employee = $conn->query($sql_get_employee_name);

            if ($result_employee && $result_employee->num_rows > 0) {
                $row_employee = $result_employee->fetch_assoc();
                $employee_name = $row_employee['employee_name'];
            } else {
                $employee_name = "Unknown";
            }

            // Chèn vào bảng detail_import_product
            $sql_detail_import = "INSERT INTO detail_import_product 
                (importer, product_id, product_name, category_name, import_price, subcategory_name, code_color, capacity, quantity_stock, date_received)
                VALUES ('$employee_name', '$inventory_id', '$product_name', '$product_category', '$product_price', '$product_subcategory', '$code_color', '$capacity', '$product_quantity', NOW())";

            $result_detail_import = mysqli_query($conn, $sql_detail_import);

            if (!$result_detail_import) {
                throw new Exception('Không thể thêm dữ liệu vào bảng detail_import_product: ' . mysqli_error($conn));
            }

            // Cập nhật bảng inventory_entries
            $update_inventory = mysqli_query($conn, "UPDATE inventory_entries SET quantity_stock = quantity_stock - '$product_quantity' WHERE inventory_id = '$inventory_id'");

            if (!$update_inventory) {
                throw new Exception('Không thể cập nhật số lượng tồn kho: ' . mysqli_error($conn));
            }

            // Cập nhật detail_color và color_name
            $get_color_code = mysqli_query($conn, "SELECT color_name, code_color FROM inventory_entries WHERE inventory_id='$inventory_id' LIMIT 1");

            if (!$get_color_code) {
                throw new Exception('Lỗi khi lấy color_name và code_color từ bảng inventory_entries: ' . mysqli_error($conn));
            }

            $color_code_entry = mysqli_fetch_assoc($get_color_code);

            if ($color_code_entry) {
                $color_name_for_detail = mysqli_real_escape_string($conn, $color_code_entry['color_name']);
                $code_color_for_detail = mysqli_real_escape_string($conn, $color_code_entry['code_color']);

                $update_color_code = mysqli_query($conn, "UPDATE products SET 
                    detail_color = '$color_name_for_detail', 
                    color_name = '$code_color_for_detail' 
                    WHERE product_id = '$inventory_id'");

                if (!$update_color_code) {
                    throw new Exception('Không thể cập nhật detail_color và color_name trong bảng products: ' . mysqli_error($conn));
                }
            }

            // Xử lý tags
            foreach ($selected_tags as $tag_id) {
                $query_tag_name = mysqli_query($conn, "SELECT tag_name FROM tags WHERE tag_id = '$tag_id' LIMIT 1");

                if (!$query_tag_name) {
                    throw new Exception('Lỗi khi truy vấn bảng tags: ' . mysqli_error($conn));
                }

                $tag_data = mysqli_fetch_assoc($query_tag_name);
                $tag_name = $tag_data['tag_name'];

                $insert_tag = mysqli_query($conn, "INSERT INTO product_tags (product_id, color_name, tag_id, tag_name) VALUES ('$inventory_id', '$code_color', '$tag_id', '$tag_name')");

                if (!$insert_tag) {
                    throw new Exception('Không thể thêm tag vào bảng product_tags: ' . mysqli_error($conn));
                }
            }
        }
        // Thêm thông báo thành công với JavaScript
        echo '<script>
document.addEventListener("DOMContentLoaded", function() {
    const successBox = document.getElementById("success-box");
    successBox.style.display = "block";
    
    setTimeout(function() {
        successBox.style.display = "none";
    }, 3000);
});
</script>';

        // Commit giao dịch
        mysqli_commit($conn);
    } catch (Exception $e) {
        // Rollback nếu có lỗi
        mysqli_rollback($conn);
        echo 'Lỗi: ' . $e->getMessage();
    }
}




$query = "
    SELECT product_name, code_color, capacity, quantity_stock, inventory_id, CONCAT(capacity, code_color, '.jpg') AS color_image
    FROM inventory_entries
    WHERE quantity_stock > 0
    AND inventory_id NOT IN (SELECT product_id FROM products)
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
    <title>Seraph Beauty - Thêm Sản Phẩm</title>
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
    <div id="success-box" class="notification-box">
        <p>Nhập kho thành công!</p>
    </div>

    <!-- CSS để thiết kế box thông báo -->
    <style>
    .notification-box {
        display: none;
        position: fixed;
        bottom: 20px;
        left: 20px;
        padding: 15px 30px;
        background-color: #000;
        /* Màu xanh thành công */
        color: #fff;
        font-size: 16px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        z-index: 1000;
    }

    .add-products .request_product .no-product-message {
        background-color: #000;
        /* Nền đen */
        color: #fff;
        /* Chữ trắng */
        padding: 20px;
        font-size: 1.2rem;
        font-weight: bold;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        margin-top: 30px;
    }
    </style>


    <div id="content-wrapper">
        <div class="title">
            <h2 style="font-size:50px;">Thêm sản phẩm</h2>
        </div>
        <div class="line2"></div>
        <section class="add-products form-container">
            <form method="POST" action="" enctype="multipart/form-data">
                <a style="font-size: 25px;" href="NVNK_pannel.php" class="back-arrow">&#8592;</a>



                <!-- Trường chọn sản phẩm -->
                <div class="input-field">
                    <label>Tên sản phẩm<span>*</span></label>
                    <select id="productSelect" name="name" onchange="updateStockQuantity()">
                        <option value="">Chọn sản phẩm</option>
                        <?php while ($row = mysqli_fetch_assoc($missing_info_products)): ?>
                        <?php
                            $product_display = htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8');
                            if (!empty($row['code_color'])) {
                                $product_display .= ' - ' . htmlspecialchars($row['code_color'], ENT_QUOTES, 'UTF-8');
                            }
                            if (!empty($row['capacity'])) {
                                $product_display .= ' - ' . htmlspecialchars($row['capacity'], ENT_QUOTES, 'UTF-8');
                            }
                            if (!empty($row['quantity_stock'])) {
                                $product_display .= ' - Số lượng: ' . htmlspecialchars($row['quantity_stock'], ENT_QUOTES, 'UTF-8');
                            }
                            ?>
                        <option value="<?= htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-color="<?= htmlspecialchars($row['code_color'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-capacity="<?= htmlspecialchars($row['capacity'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-quantity="<?= htmlspecialchars($row['quantity_stock'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?= $product_display; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Các trường ẩn -->
                <input type="hidden" name="code_color" id="colorNameInput">
                <input type="hidden" name="capacity" id="capacityInput">

                <!-- Các trường nhập liệu -->
                <div class="input-field">
                    <label>Số lượng <span>*</span></label>
                    <input type="number" min="1" name="quantity" required>

                </div>


                <div class="input-field">
                    <label>Dung tích (Nếu có)</label>
                    <input type="text" name="capacityy">
                </div>

                <div class="input-field">
                    <label>Bộ sưu tập (Nếu có)</label>
                    <input type="text" name="collection">
                </div>


                <input type="hidden" name="inventoryId" id="inventoryIdInput" value="">


                <script>
                // Ẩn hộp gợi ý khi nhấp ra ngoài
                document.addEventListener("click", function(event) {
                    const suggestionsBox = document.getElementById("suggestions");
                    if (!event.target.closest(".search-field")) {
                        suggestionsBox.style.display = "none";
                    }
                });

                // Hàm lấy gợi ý từ API PHP
                async function getSuggestions() {
                    const input = document.getElementById("searchProduct").value.trim();
                    const suggestionsBox = document.getElementById("suggestions");
                    suggestionsBox.innerHTML = ""; // Xóa gợi ý cũ

                    if (input !== "") {
                        try {
                            const response = await fetch(
                                `../admin/search_products.php?query=${encodeURIComponent(input)}`);
                            const products = await response.json();

                            if (products.length > 0) {
                                suggestionsBox.style.display = "block";
                                products.forEach(product => {
                                    const suggestionItem = document.createElement("div");
                                    suggestionItem.classList.add("suggestion-item");
                                    suggestionItem.dataset.productId = product.product_id;

                                    // Tạo chuỗi gợi ý
                                    let suggestionText = `${product.product_name} - ${product.color_name}`;
                                    if (product.capacity) {
                                        suggestionText += ` - ${product.capacity}`;
                                    }

                                    suggestionItem.textContent = suggestionText;
                                    suggestionItem.onclick = () => selectProduct(product.product_id);
                                    suggestionsBox.appendChild(suggestionItem);
                                });
                            } else {
                                suggestionsBox.style.display = "block";
                                suggestionsBox.innerHTML =
                                    "<div class='no-suggestions'>Không tìm thấy sản phẩm phù hợp</div>";
                            }
                        } catch (error) {
                            console.error("Lỗi khi tìm kiếm sản phẩm:", error);
                        }
                    } else {
                        suggestionsBox.style.display = "none";
                    }
                }

                async function selectProduct(productId) {
                    const suggestionsBox = document.getElementById("suggestions");
                    suggestionsBox.style.display = "none";

                    try {
                        const response = await fetch(`../admin/get_product_info.php?product_id=${productId}`);
                        const product = await response.json();

                        if (product) {
                            document.getElementById("searchProduct").value = product.product_name;
                            document.getElementById("stockQuantity").textContent =
                                `Hiện tại trong kho còn: ${product.quantity_in_stock}`;
                            document.querySelector('input[name="price"]').value = product.product_price || '';
                            document.querySelector('textarea[name="detail"]').value = product.product_detail || '';

                            displayProductImage(product.product_image);
                            displayColorImage(product.color_image);
                            updateSelectedTags(product.tags);

                            // Hiển thị danh mục phụ phù hợp
                            const subCategorySelect = document.getElementById("subCategory");
                            subCategorySelect.innerHTML = ""; // Xóa các option hiện tại

                            const productSubcategory = product.product_subcategory;

                            // Thêm danh mục phụ phù hợp lên đầu
                            if (productSubcategory) {
                                const matchedOption = document.createElement("option");
                                matchedOption.value = productSubcategory;
                                matchedOption.textContent = productSubcategory;
                                subCategorySelect.appendChild(matchedOption);
                            }

                            // Thêm các danh mục phụ khác
                            product.subcategories.forEach(subcategory => {
                                if (subcategory.subcategory_name !== productSubcategory) {
                                    const option = document.createElement("option");
                                    option.value = subcategory.subcategory_id;
                                    option.textContent = subcategory.subcategory_name;
                                    subCategorySelect.appendChild(option);
                                }
                            });

                            // Đặt danh mục phụ đầu tiên làm lựa chọn mặc định
                            subCategorySelect.value = productSubcategory || subCategorySelect.options[0].value;

                            // Hiển thị danh mục chính
                            const mainCategorySelect = document.getElementById("mainCategory");
                            if (product.category_id) {
                                const options = Array.from(mainCategorySelect.options);
                                const matchedOption = options.find(option => option.value === product.category_id
                                    .toString());
                                if (matchedOption) {
                                    mainCategorySelect.value = matchedOption.value;
                                }
                            }
                            const brandSelect = document.querySelector('select[name="brand"]');
                            if (product.brand_name) {
                                const brandOptions = Array.from(brandSelect.options);
                                const matchedBrand = brandOptions.find(option => option.text === product
                                    .brand_name);

                                if (matchedBrand) {
                                    // Di chuyển thương hiệu phù hợp lên đầu
                                    matchedBrand.parentNode.prepend(matchedBrand);
                                    brandSelect.value = matchedBrand.value; // Đặt làm lựa chọn mặc định
                                }
                            }

                        }
                    } catch (error) {
                        console.error("Lỗi khi lấy thông tin sản phẩm:", error);
                    }
                }




                // Hàm hiển thị tất cả ảnh sản phẩm từ chuỗi tên ảnh
                function displayProductImage(imageList) {
                    const previewContainer = document.getElementById('productImagePreview');
                    previewContainer.innerHTML = '';

                    const images = imageList.split(',');

                    images.forEach(image => {
                        if (image) {
                            const img = document.createElement('img');
                            img.src = `../image/product/${image.trim()}`;
                            previewContainer.appendChild(img);
                        }
                    });

                    const addIcon = document.createElement('div');
                    addIcon.classList.add('add-image-placeholder');
                    addIcon.innerHTML = '<div class="add-icon">+</div>';
                    previewContainer.appendChild(addIcon);
                }

                // Hàm hiển thị ảnh màu sắc
                function displayColorImage(imageName) {
                    const previewContainer = document.getElementById('colorImagePreview');
                    previewContainer.innerHTML = '';

                    if (imageName) {
                        const img = document.createElement('img');
                        img.src = `../image/colorimage/${imageName.trim()}`;
                        previewContainer.appendChild(img);
                    }
                }

                // Cập nhật tag với lớp selected
                function updateSelectedTags(tags) {
                    document.querySelectorAll('.tag-item').forEach(item => {
                        item.classList.remove('selected');
                    });

                    tags.forEach(tag => {
                        const tagItem = document.querySelector(`.tag-item[data-tag-id="${tag.tag_id}"]`);
                        if (tagItem) {
                            tagItem.classList.add('selected');
                        }
                    });
                }

                function showSuggestions() {
                    const suggestionsBox = document.getElementById('suggestions');
                    suggestionsBox.innerHTML = ''; // Xóa nội dung cũ trước khi thêm mới
                    getSuggestions();
                }


                document.getElementById("searchProduct").addEventListener("input", function() {
                    showSuggestions();
                    getSuggestions();
                });

                // Script chọn và bỏ chọn tags
                document.addEventListener('DOMContentLoaded', function() {
                    let selectedTags = [];

                    document.querySelectorAll('.tag-item').forEach(function(item) {
                        item.addEventListener('click', function() {
                            const tagId = this.getAttribute('data-tag-id');

                            if (selectedTags.includes(tagId)) {
                                selectedTags = selectedTags.filter(id => id !== tagId);
                                this.classList.remove('selected');
                            } else {
                                selectedTags.push(tagId);
                                this.classList.add('selected');
                            }

                            document.getElementById('selected_tags').value = selectedTags.join(
                                ',');
                        });
                    });
                });
                </script>




                <div class="input-field">
                    <label>Giá Bán<span>*</span></label>
                    <input type="number" name="price" required>
                </div>




                <div class="input-field">
                    <label>Chi tiết sản phẩm<span>*</span></label>
                    <textarea name="detail" required></textarea>
                </div>

                <?php
                // Lấy tất cả tags từ bảng `tags`
                $query = "SELECT * FROM tags";
                $result = mysqli_query($conn, $query);
                $tags = array();
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $tags[] = $row;
                    }
                }
                ?>
                <div class="input-field">
                    <label>Tags<span>*</span></label>
                    <div class="tags-container">
                        <?php foreach ($tags as $tag): ?>
                        <span class="tag-item" data-tag-id="<?= $tag['tag_id']; ?>">
                            <?= htmlspecialchars($tag['tag_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Hidden field to store selected tag IDs -->
                <input type="hidden" name="selected_tags" id="selected_tags">

                <script>
                // Script for selecting and deselecting tags
                document.addEventListener('DOMContentLoaded', function() {
                    let selectedTags = [];

                    document.querySelectorAll('.tag-item').forEach(function(item) {
                        item.addEventListener('click', function() {
                            const tagId = this.getAttribute('data-tag-id');

                            if (selectedTags.includes(tagId)) {
                                // Deselect tag
                                selectedTags = selectedTags.filter(id => id !== tagId);
                                this.classList.remove('selected');
                            } else {
                                // Select tag
                                selectedTags.push(tagId);
                                this.classList.add('selected');
                            }

                            // Update hidden input with selected tag IDs
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
                    <input type="file" name="image[]" id="productImages" multiple required
                        onchange="previewProductImages()">
                </div>
                <div id="productImagePreview" class="image-preview"></div>

                <div class="input-field">
                    <label>Ảnh màu sắc (Nếu có)<span>*</span></label>
                    <input type="file" name="color_image" id="colorImage" onchange="previewColorImage()">
                </div>
                <div id="colorImagePreview" class="image-preview"></div>

                <script>
                function previewProductImages() {
                    var previewContainer = document.getElementById('productImagePreview');
                    var files = document.getElementById('productImages').files;

                    previewContainer.innerHTML = ''; // Xóa toàn bộ nội dung hiện tại

                    if (files.length > 0) {
                        Array.from(files).forEach(function(file) {
                            var reader = new FileReader();
                            reader.onload = function(e) {
                                var container = document.createElement('div');
                                container.classList.add('image-preview');

                                var img = document.createElement('img');
                                img.src = e.target.result;

                                var removeBtn = document.createElement('button');
                                removeBtn.innerHTML = 'X';
                                removeBtn.classList.add('remove-image');
                                removeBtn.onclick = function() {
                                    container.remove(); // Xóa hình ảnh khi nhấn nút "X"
                                    if (!previewContainer.hasChildNodes()) {
                                        // Nếu không còn hình ảnh nào, hiện lại hình vuông dấu cộng
                                        previewContainer.innerHTML =
                                            '<div class="add-image-placeholder"><div class="add-icon">+</div></div>';
                                    }
                                }

                                container.appendChild(img);
                                container.appendChild(removeBtn);
                                previewContainer.appendChild(container);
                            }
                            reader.readAsDataURL(file);
                        });

                        // Thêm ô dấu cộng ở cuối sau các ảnh đã chọn
                        var addIcon = document.createElement('div');
                        addIcon.classList.add('add-image-placeholder');
                        addIcon.innerHTML = '<div class="add-icon">+</div>';
                        previewContainer.appendChild(addIcon);
                    } else {
                        // Nếu không có ảnh nào, hiện lại hình vuông dấu cộng
                        previewContainer.innerHTML =
                            '<div class="add-image-placeholder"><div class="add-icon">+</div></div>';
                    }
                }
                </script>



                <?php
                // Lấy tất cả danh mục chính
                $query = "SELECT * FROM categories";
                $result = mysqli_query($conn, $query);
                $categories = array();
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $categories[] = $row;
                    }
                }

                // Lấy tất cả danh mục phụ
                $query = "SELECT * FROM subcategory";
                $result = mysqli_query($conn, $query);
                $subcategories = array();
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $subcategories[] = $row;
                    }
                }
                ?>

                <div class="input-field">
                    <label>Danh mục chính<span>*</span></label>
                    <select id="mainCategory" name="category">
                        <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id']; ?>" style="text-transform: capitalize;">
                            <?= $category['category_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="input-field">
                    <label>Danh mục phụ<span>*</span></label>
                    <select id="subCategory" name="subcategory">
                        <!-- Danh mục phụ sẽ được cập nhật dựa trên danh mục chính được chọn -->
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
                        <option value="<?= $brand['brand_name']; ?>" style="text-transform: capitalize;">
                            <?= $brand['brand_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>


                <input type="submit" name="add_product" value="Thêm sản phẩm">
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

    // Cập nhật giá trị cho input hidden
    document.getElementById('colorNameInput').value = colorName;

    // Thực hiện các thao tác tiếp theo như cập nhật giao diện hoặc gửi dữ liệu đến server
    console.log('Tên sản phẩm:', productName);
    console.log('Màu sắc:', colorName);
    console.log('Số lượng:', quantity);
}
</script>

</html>