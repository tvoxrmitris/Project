<?php
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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enter'])) {
    // Kết nối cơ sở dữ liệu
    // Thay thế $conn bằng biến kết nối của bạn

    // Lấy dữ liệu từ form
    $suplier_name = mysqli_real_escape_string($conn, $_POST['suplier']);
    $number_suplier = mysqli_real_escape_string($conn, $_POST['number']);
    $address_suplier = mysqli_real_escape_string($conn, $_POST['address']);
    $date_received = mysqli_real_escape_string($conn, $_POST['date']);
    $date_received = date('Y-m-d H:i:s', strtotime($date_received));

    $category_ids = array_map(function ($category) use ($conn) {
        return mysqli_real_escape_string($conn, $category);
    }, $_POST['category']);

    $subcategory_names = array_map(function ($subcategory) use ($conn) {
        return mysqli_real_escape_string($conn, $subcategory);
    }, $_POST['subcategory']);

    // Kiểm tra sự tồn tại của nhà cung cấp
    $sql_check = "SELECT suplier_id FROM suplier 
                  WHERE suplier_name = '$suplier_name' 
                    AND number_suplier = '$number_suplier' 
                    AND address_suplier = '$address_suplier'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        $row = $result_check->fetch_assoc();
        $suplier_id = $row['suplier_id'];
    } else {
        $sql = "INSERT INTO suplier (suplier_name, number_suplier, address_suplier) 
                VALUES ('$suplier_name', '$number_suplier', '$address_suplier')";
        if ($conn->query($sql) === TRUE) {
            $suplier_id = $conn->insert_id;
        } else {
            echo "Lỗi: " . $sql . "<br>" . $conn->error;
        }
    }

    foreach ($_POST['name'] as $product_index => $product_name) {
        $product_name = mysqli_real_escape_string($conn, $product_name);
        $quantity = isset($_POST['quantity'][$product_index]) ? intval($_POST['quantity'][$product_index]) : 0;
        $product_price = isset($_POST['product_price'][$product_index]) ? floatval($_POST['product_price'][$product_index]) : 0.0;

        $sql_check_product = "SELECT inventory_id, quantity_stock FROM inventory 
                              WHERE product_name = '$product_name'";
        $result_check_product = $conn->query($sql_check_product);

        if ($result_check_product->num_rows > 0) {
            $row_product = $result_check_product->fetch_assoc();
            $new_quantity = intval($row_product['quantity_stock']) + $quantity;
            $inventory_id = $row_product['inventory_id'];

            // Kiểm tra sự tồn tại trong bảng low_quantity_stock và xóa nếu có
            $sql_check_low_quantity = "SELECT * FROM low_quantity_stock WHERE inventory_id = '$inventory_id'";
            $result_check_low_quantity = $conn->query($sql_check_low_quantity);
            if ($result_check_low_quantity->num_rows > 0) {
                $sql_delete_low_quantity = "DELETE FROM low_quantity_stock WHERE inventory_id = '$inventory_id'";
                if ($conn->query($sql_delete_low_quantity) !== TRUE) {
                    echo "Lỗi: " . $sql_delete_low_quantity . "<br>" . $conn->error;
                }
            }

            $sql_update = "UPDATE inventory 
                           SET quantity_stock = '$new_quantity', purchase_price = '$product_price' 
                           WHERE inventory_id = '$inventory_id'";
            if ($conn->query($sql_update) !== TRUE) {
                echo "Lỗi: " . $sql_update . "<br>" . $conn->error;
            }
        } else {
            $sql_product = "INSERT INTO inventory (product_name, suplier_id, quantity_stock, date_received, purchase_price) 
                            VALUES ('$product_name', '$suplier_id', '$quantity', '$date_received', '$product_price')";
            if ($conn->query($sql_product) === TRUE) {
                $inventory_id = $conn->insert_id;
            } else {
                echo "Lỗi: " . $sql_product . "<br>" . $conn->error;
            }
        }

        if (isset($_POST['color_name'][$product_index]) && isset($_POST['color_quantity'][$product_index]) && isset($_POST['capacity'][$product_index])) {
            foreach ($_POST['color_name'][$product_index] as $color_index => $color_name) {
                $color_quantity = isset($_POST['color_quantity'][$product_index][$color_index]) ? intval($_POST['color_quantity'][$product_index][$color_index]) : $quantity;
                $capacity = isset($_POST['capacity'][$product_index][$color_index]) ? mysqli_real_escape_string($conn, $_POST['capacity'][$product_index][$color_index]) : NULL;
                $pricee = isset($_POST['pricee'][$product_index][$color_index]) ? mysqli_real_escape_string($conn, $_POST['pricee'][$product_index][$color_index]) : "";
                $code_color = isset($_POST['code_color'][$product_index][$color_index]) ? mysqli_real_escape_string($conn, $_POST['code_color'][$product_index][$color_index]) : "";

                $color_name = mysqli_real_escape_string($conn, $color_name);

                // Kiểm tra sự tồn tại trong bảng inventory_entries
                if ($capacity === NULL) {
                    // Trường hợp capacity là NULL, sử dụng IS NULL trong câu truy vấn
                    $sql_check_entry = "SELECT inventory_id FROM inventory_entries 
                                        WHERE product_id = '$inventory_id' 
                                        AND color_name = '$color_name' 
                                        AND capacity IS NULL 
                                        AND code_color = '$code_color'";
                } else {
                    // Trường hợp capacity có giá trị, sử dụng so sánh bình thường
                    $sql_check_entry = "SELECT inventory_id FROM inventory_entries 
                                        WHERE product_id = '$inventory_id' 
                                        AND color_name = '$color_name' 
                                        AND capacity = '$capacity' 
                                        AND code_color = '$code_color'";
                }

                $result_check_entry = $conn->query($sql_check_entry);

                if ($result_check_entry->num_rows > 0) {
                    $row_entry = $result_check_entry->fetch_assoc();
                    $inventory_id_entry = $row_entry['inventory_id'];

                    // Kiểm tra tồn tại trong bảng low_quantity_stock và xóa nếu có
                    $sql_check_low_quantity = "SELECT * FROM low_quantity_stock WHERE inventory_id = '$inventory_id_entry'";
                    $result_check_low_quantity = $conn->query($sql_check_low_quantity);
                    if ($result_check_low_quantity->num_rows > 0) {
                        $sql_delete_low_quantity = "DELETE FROM low_quantity_stock WHERE inventory_id = '$inventory_id_entry'";
                        if ($conn->query($sql_delete_low_quantity) !== TRUE) {
                            echo "Lỗi: " . $sql_delete_low_quantity . "<br>" . $conn->error;
                        }
                    }

                    $sql_update_entry = "UPDATE inventory_entries 
                    SET quantity_stock = quantity_stock + '$color_quantity', import_price = '$pricee' 
                    WHERE product_id = '$inventory_id' 
                    AND color_name = '$color_name' 
                    AND capacity = '$capacity' 
                    AND code_color = '$code_color'";

                    if ($conn->query($sql_update_entry) !== TRUE) {
                        echo "Lỗi: " . $sql_update_entry . "<br>" . $conn->error;
                    }
                } else {
                    $sql_inventory_entries = "INSERT INTO inventory_entries (product_id, product_name, category_name, import_price, subcategory_name, code_color, color_name, capacity, suplier_id, quantity_stock, date_received)
                                              VALUES ('$inventory_id', '$product_name', '{$category_ids[$product_index]}', '$pricee', '{$subcategory_names[$product_index]}', '$code_color', '$color_name', '$capacity', '$suplier_id', '$color_quantity', '$date_received')";
                    if ($conn->query($sql_inventory_entries) !== TRUE) {
                        echo "Lỗi: " . $sql_inventory_entries . "<br>" . $conn->error;
                    }
                }

                // Lấy tên nhân viên từ bảng employees
                $employee_id = $_SESSION['employee_id'];
                $sql_get_employee_name = "SELECT employee_name FROM employees WHERE employee_id = '$employee_id'";
                $result_employee = $conn->query($sql_get_employee_name);

                if ($result_employee->num_rows > 0) {
                    $row_employee = $result_employee->fetch_assoc();
                    $employee_name = $row_employee['employee_name'];
                } else {
                    $employee_name = "Unknown";
                }

                // Chèn dữ liệu vào bảng detail_import
                $sql_detail_import = "INSERT INTO detail_import (importer, product_id, product_name, category_name, import_price, subcategory_name, code_color, color_name, capacity, suplier_id, quantity_stock, date_received)
                                    VALUES ('$employee_name', '$inventory_id', '$product_name', '{$category_ids[$product_index]}', '$pricee', '{$subcategory_names[$product_index]}', '$code_color', '$color_name', '$capacity', '$suplier_id', '$color_quantity', '$date_received')";
                if ($conn->query($sql_detail_import) !== TRUE) {
                    echo "Lỗi: " . $sql_detail_import . "<br>" . $conn->error;
                }
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
}










//delete product from database
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $select_delete_image = mysqli_query($conn, "SELECT product_image FROM `products` WHERE product_id='$delete_id'") or
        die('query failed');
    $fetch_delete_image = mysqli_fetch_assoc($select_delete_image);
    // unlink('image/'.$fetch_delete_image['image']);
    mysqli_query($conn, "DELETE FROM `products` WHERE product_id = '$delete_id'") or die('query faild');
    mysqli_query($conn, "DELETE FROM `cart` WHERE pid = '$delete_id'") or die('query faild');
    mysqli_query($conn, "DELETE FROM `wishlist` WHERE pid = '$delete_id'") or die('query failed');

    header('location:../admin/admin_product.php');
}








?>
<style>
#add-item {
    background-color: #666666;
    /* Màu đen sang trọng */
    color: #FFFFFF;
    /* Màu chữ trắng */

    /* Phông chữ cổ điển, thanh lịch */
    font-size: 14px;
    font-weight: bold;
    text-transform: uppercase;
    /* Chữ hoa để tạo sự mạnh mẽ */
    padding: 12px 20px;
    border: 2px solid #B5A36C;
    /* Viền vàng nhạt (gợi nhắc đến logo Gucci) */
    border-radius: 5px;
    /* Góc bo tròn nhẹ nhàng */
    cursor: pointer;
    transition: all 0.3s ease;
    /* Hiệu ứng chuyển động mượt mà */
}

#add-item:hover {
    background-color: #B5A36C;
    /* Màu nền khi hover */
    color: #000000;
    /* Màu chữ khi hover */
    border-color: #000000;
    /* Đổi màu viền khi hover */
}

#add-item:active {
    transform: scale(0.98);
    /* Hiệu ứng nhấn nút */
}
</style>
<style type="text/css">
<?php include '../CSS/style.css';
?>

/* Style cho các trường nhập liệu */
input[type="text"],
input[type="number"],
input[type="date"],
select {
    width: 100%;
    /* Đảm bảo trường nhập liệu chiếm toàn bộ chiều rộng của container */
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
}

th:nth-child(1),
td:nth-child(1) {
    width: 65px;
}

th:nth-child(2),
td:nth-child(2) {
    width: 350px;
}



/* Đảm bảo giao diện không vỡ trên thiết bị nhỏ */
@media (max-width: 768px) {
    .row {
        flex-direction: column;
        /* Chuyển thành cột trên màn hình nhỏ */
    }
}



.request_product table {
    border-collapse: separate !important;
    /* Đảm bảo mỗi ô có viền riêng biệt */
    border-spacing: 0 !important;
    /* Không có khoảng cách giữa các ô */
}

/* Các ô được highlight trong bảng */
.highlight-td {
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
.highlight-td:hover {
    transform: scale(1.02);
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
}

/* Hiệu ứng toàn dòng */
.highlight-row {
    background-color: #ffffcc !important;
    /* Nền vàng nhạt cho dòng */
    transition: all 0.3s ease;
}


.highlight-suggested {
    background-color: #ffffcc !important;
    /* Màu nền vàng nhạt cho highlight */
}

.request-product-row {
    cursor: pointer;
}
</style>
<style>
.suggestions-box {
    position: absolute;
    background-color: white;
    border: 1px solid #ddd;
    max-height: 150px;
    overflow-y: auto;
    z-index: 1000;
    width: 90%;
}

.suggestions-box div {
    padding: 10px;
    cursor: pointer;
}

.suggestions-box div:hover {
    background-color: #f0f0f0;
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
    <!-- <link rel="stylesheet" type="text/css" href="../CSS/main.css?v=1.1 <?php echo time(); ?>"> -->

    <title>Seraph Beauty - Nhập Kho</title>
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
            <h2 style="font-size:50px;">Nhập hàng</h2>
        </div>
        <div class="line2"></div>
        <section class="add-products form-container">
            <div class="request_product">
                <span>Sản phẩm ưu tiên nhập</span>
                <?php
                // Lấy danh sách product_id từ bảng low_quantity_stock
                $query = "SELECT inventory_id FROM low_quantity_stock";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    echo "<table border='1' cellspacing='0' cellpadding='10'>";
                    echo "<tr>
                    <th>STT</th>
                    <th>Thông tin sản phẩm</th>
                  </tr>";

                    $stt = 1;
                    while ($row = $result->fetch_assoc()) {
                        $inventory_id = $row['inventory_id'];

                        // Lấy thông tin sản phẩm từ bảng products dựa trên product_id
                        $product_query = "
                    SELECT product_name, code_color, color_name, capacity 
                    FROM inventory_entries 
                    WHERE inventory_id = '$inventory_id'
                ";
                        $product_result = $conn->query($product_query);

                        if ($product_result->num_rows > 0) {
                            while ($product = $product_result->fetch_assoc()) {
                                $capacity_display = $product['capacity'] ? " - " . $product['capacity'] : "";

                                // Thêm thuộc tính dữ liệu sản phẩm để JavaScript lấy thông tin
                                echo "<tr class='request-product-row' 
                                  data-product-name='{$product['product_name']}' 
                                  data-code-color='{$product['code_color']}' 
                                  data-color-name='{$product['color_name']}' 
                                  data-capacity='{$product['capacity']}'>";
                                echo "<td>" . $stt++ . "</td>";
                                echo "<td style='color: #666; font-weight: bold;'>" . $product['product_name'] . " - " . $product['code_color'] . " - " . $product['color_name'] . $capacity_display . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2'>Không tìm thấy sản phẩm cho product_id: $inventory_id</td></tr>";
                        }
                    }
                    echo "</table>";
                } else {
                    echo "<div class='no-product-message'>Không có sản phẩm cần ưu tiên</div>";
                }
                ?>
            </div>




            <form method="POST" action="" enctype="multipart/form-data">
                <a style="font-size: 25px;" href="NVNK_pannel.php" class="back-arrow">&#8592;</a>
                <!-- Thêm dấu mũi tên -->
                <!-- Thông tin nhà cung cấp -->
                <div class="input-field">
                    <label>Nhà cung cấp<span>*</span></label>
                    <input type="text" name="suplier" required>
                </div>
                <div class="input-field">
                    <label>Số điện thoại nhà cung cấp<span>*</span></label>
                    <input type="number" name="number" required>
                </div>
                <div class="input-field">
                    <label>Địa chỉ nhà cung cấp<span>*</span></label>
                    <input type="text" name="address" required>
                </div>
                <div class="input-field">
                    <label>Ngày nhập hàng<span>*</span></label>
                    <input type="datetime-local" name="date" id="dateReceived" required>
                </div>

                <script>
                // Lấy ngày giờ hiện tại
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0'); // Tháng bắt đầu từ 0
                const day = String(now.getDate()).padStart(2, '0');
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');

                // Định dạng theo kiểu 'YYYY-MM-DDTHH:MM'
                const currentDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;

                // Gán giá trị vào trường datetime-local
                document.getElementById('dateReceived').value = currentDateTime;
                </script>


                <div id="products-wrapper">
                    <div class="product-container">
                        <div class="product-item">
                            <!-- Tìm sản phẩm nằm trên một hàng riêng -->
                            <div class="input-field search-field">
                                <label>Tìm sản phẩm</label>
                                <input type="text" id="searchProduct" placeholder="Nhập tên sản phẩm...">
                                <div id="suggestions" class="suggestions-box"></div>
                            </div>

                            <!-- Tên sản phẩm, số lượng, và tổng giá sản phẩm nằm trên cùng một hàng -->
                            <div class="product-info">
                                <div class="input-field">
                                    <label>Tên sản phẩm<span>*</span></label>
                                    <input type="text" name="name[]" required>
                                </div>
                                <div class="input-field">
                                    <label>Tổng số lượng<span>*</span></label>
                                    <input type="number" min="1" name="quantity[]" required>
                                </div>
                                <div class="input-field">
                                    <label>Tổng giá sản phẩm<span>*</span></label>
                                    <input type="number" min="0" step="0.01" name="product_price[]" required>
                                </div>
                            </div>
                        </div>


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

                        <!-- HTML code -->
                        <div class="row">
                            <div class="input-field">
                                <label>Danh mục chính<span>*</span></label>
                                <select class="mainCategory" name="category[]" id="mainCategory" required>
                                    <option value="">Chọn danh mục chính</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['category_id']; ?>" style="text-transform: capitalize;"
                                        <?= ($category['category_id'] == 2) ? 'selected' : ''; ?>>
                                        <?= $category['category_name']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="input-field">
                                <label>Danh mục phụ<span>*</span></label>
                                <select class="subCategory" name="subcategory[]" id="subCategory" required>
                                    <option value="">Chọn danh mục phụ</option>
                                </select>
                            </div>
                        </div>

                        <script>
                        document.getElementById('mainCategory').addEventListener('change', function() {
                            var categoryId = this.value; // Lấy giá trị category_id đã chọn

                            // Gửi yêu cầu AJAX để lấy danh mục phụ
                            var xhr = new XMLHttpRequest();
                            xhr.open('POST', 'get_subcategories.php', true);
                            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                            xhr.onload = function() {
                                if (xhr.status === 200) {
                                    var subcategories = JSON.parse(xhr
                                        .responseText); // Chuyển dữ liệu JSON thành mảng

                                    // Xóa tất cả các option cũ trong dropdown subcategory
                                    var subCategorySelect = document.getElementById('subCategory');
                                    subCategorySelect.innerHTML =
                                        '<option value="">Chọn danh mục phụ</option>'; // Đặt lại option mặc định

                                    // Nếu có subcategories, thêm vào dropdown
                                    if (subcategories.length > 0) {
                                        subcategories.forEach(function(subcategory) {
                                            var option = document.createElement('option');
                                            option.value = subcategory
                                                .subcategory_id; // Lấy ID của subcategory
                                            option.textContent = subcategory
                                                .subcategory_name; // Hiển thị tên của subcategory
                                            subCategorySelect.appendChild(option);
                                        });
                                    }
                                }
                            };
                            xhr.send('category_id=' + categoryId); // Gửi category_id trong body của yêu cầu
                        });

                        // Kích hoạt sự kiện change ngay khi trang tải để tự động hiển thị danh mục phụ
                        document.addEventListener('DOMContentLoaded', function() {
                            var mainCategory = document.getElementById('mainCategory');
                            // Nếu danh mục chính đã có giá trị mặc định, kích hoạt sự kiện change
                            if (mainCategory.value) {
                                mainCategory.dispatchEvent(new Event('change'));
                            }
                        });
                        </script>



                        <!-- Container cho màu sắc -->
                        <div class="color-container">
                            <div class="color-item">
                                <div class="input-field">
                                    <label>Mã Màu<span>*</span></label>
                                    <input type="text" name="code_color[0][]">
                                </div>
                                <div class="input-field">
                                    <label>Màu sắc<span>*</span></label>
                                    <input type="text" name="color_name[0][]">
                                </div>
                                <div class="input-field">
                                    <label>Thể tích<span>*</span></label>
                                    <input type="text" name="capacity[0][]">
                                </div>
                                <div class="input-field">
                                    <label>Số lượng<span>*</span></label>
                                    <input type="number" min="1" name="color_quantity[0][]">
                                </div>
                                <div class="input-field">
                                    <label>Giá<span>*</span></label>
                                    <input type="number" min="1" name="pricee[0][]">
                                </div>
                            </div>
                        </div>

                        <!-- Nút để thêm màu sắc -->
                        <div class="detail" style="margin-bottom: 2rem; margin-right: 30rem; width: 50%;">
                            <span class="add-color"
                                style="color: #666; cursor: pointer; text-decoration: underline;">Nếu sản phẩm có thêm
                                màu sắc, thể tích hãy nhấn vào đây</span>
                        </div>
                    </div>
                </div>


                <button style="margin-bottom: 2rem;" type="button" id="add-item">Thêm sản phẩm</button>
                <input type="submit" name="enter" class="enter" value="Nhập">
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    let productCount = 0;

                    // Hàm tạo HTML cho các trường nhập màu sắc
                    function createColorFieldHTML(codeColor = '', colorName = '', capacity = '') {
                        return `
            <div class="color-item">
                <div class="input-field">
                    <label>Mã Màu<span>*</span></label>
                    <input type="text" name="code_color[${productCount}][]" value="${codeColor}">
                </div>
                <div class="input-field">
                    <label>Tên Màu sắc<span>*</span></label>
                    <input type="text" name="color_name[${productCount}][]" value="${colorName}">
                </div>
                <div class="input-field">
                    <label>Thể tích<span>*</span></label>
                    <input type="text" name="capacity[${productCount}][]" value="${capacity}">
                </div>
                <div class="input-field">
                    <label>Số lượng<span>*</span></label>
                    <input type="number" min="1" name="color_quantity[${productCount}][]">
                </div>
                <div class="input-field">
                    <label>Giá<span>*</span></label>
                    <input type="number" min="1" name="pricee[${productCount}][]">
                </div>
            </div>
        `;
                    }

                    // Lắng nghe sự kiện click vào các phần tử có class "add-color" để thêm các trường màu mới
                    // Điều chỉnh event listener của nút "add-color" để kích hoạt highlight
                    document.addEventListener('click', function(event) {
                        if (event.target.classList.contains('add-color')) {
                            const productContainer = event.target.closest('.product-container');
                            const colorContainer = productContainer.querySelector('.color-container');
                            colorContainer.insertAdjacentHTML('beforeend', createColorFieldHTML());

                            // Gọi hàm highlight sau khi thêm trường màu mới
                            autoHighlightProducts();
                        }
                    });


                    // Lắng nghe sự kiện click cho nút "Thêm sản phẩm"
                    document.getElementById('add-item').addEventListener('click', function() {
                        productCount++;
                        const newProductContainer = document.createElement('div');
                        newProductContainer.classList.add('product-container');
                        newProductContainer.innerHTML = `
            <div class="product-item">
                <div class="input-field search-field">
                    <label>Tìm sản phẩm</label>
                    <input type="text" id="searchProduct" placeholder="Nhập tên sản phẩm...">
                    <div id="suggestions" class="suggestions-box"></div>
                </div>
                <div class="product-info">
                    <div class="input-field">
                        <label>Tên sản phẩm<span>*</span></label>
                        <input type="text" name="name[]" required>
                    </div>
                    <div class="input-field">
                        <label>Tổng số lượng<span>*</span></label>
                        <input type="number" min="1" name="quantity[]" required>
                    </div>
                    <div class="input-field">
                        <label>Tổng giá sản phẩm<span>*</span></label>
                        <input type="number" min="0" step="0.01" name="product_price[]" required>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field">
                <label>Danh mục chính<span>*</span></label>
                <select class="mainCategory" name="category[]" required>
                    <option value="">Chọn danh mục chính</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id']; ?>"><?= $category['category_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-field">
                <label>Danh mục phụ<span>*</span></label>
                <select class="subCategory" name="subcategory[]" required>
                    <option value="">Chọn danh mục phụ</option>
                </select>
            </div>
        </div>
                        <div class="color-container">
                    ${createColorFieldHTML()}
                </div>
                <div class="detail" style="margin-bottom: 2rem; margin-right: 30rem; width: 50%;">
                    <span class="add-color" style="color: #666; cursor: pointer; text-decoration: underline;">
                        Nếu sản phẩm có thêm màu sắc, thể tích hãy nhấn vào đây
                    </span>
                </div>
    `;
                        document.getElementById('products-wrapper').appendChild(newProductContainer);

                        // Gắn sự kiện change cho mainCategory mới
                        const mainCategorySelect = newProductContainer.querySelector('.mainCategory');
                        const subCategorySelect = newProductContainer.querySelector('.subCategory');

                        mainCategorySelect.addEventListener('change', function() {
                            const categoryId = this.value;

                            // Gửi yêu cầu AJAX để lấy danh mục phụ
                            fetch('get_subcategories.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: 'category_id=' + categoryId
                                })
                                .then(response => response.json())
                                .then(subcategories => {
                                    subCategorySelect.innerHTML =
                                        '<option value="">Chọn danh mục phụ</option>';
                                    subcategories.forEach(subcategory => {
                                        const option = document.createElement(
                                            'option');
                                        option.value = subcategory.subcategory_id;
                                        option.textContent = subcategory
                                            .subcategory_name;
                                        subCategorySelect.appendChild(option);
                                    });
                                })
                                .catch(error => console.error('Error fetching subcategories:',
                                    error));
                        });
                    });

                    // Lắng nghe sự kiện input để tìm sản phẩm từ suggest-box
                    document.addEventListener("input", function(e) {
                        if (e.target.matches(".search-field input")) {
                            const query = e.target.value;
                            const suggestionsBox = e.target.nextElementSibling;

                            if (query.length > 0) {
                                fetch(`../admin/suggest_product.php?query=${query}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        suggestionsBox.innerHTML = "";

                                        if (data.length > 0) {
                                            data.forEach(item => {
                                                let suggestionItem = document.createElement(
                                                    "div");
                                                suggestionItem.classList.add(
                                                    "suggestion-item");

                                                let displayText =
                                                    `<strong>${item.product_name}</strong> - ${item.code_color}`;
                                                if (item.capacity) displayText +=
                                                    ` - ${item.capacity}`;

                                                suggestionItem.innerHTML = displayText;
                                                suggestionItem.dataset.product = JSON
                                                    .stringify(item);
                                                suggestionsBox.appendChild(suggestionItem);
                                            });
                                            suggestionsBox.style.display = "block";
                                        } else {
                                            suggestionsBox.style.display = "none";
                                        }
                                    })
                                    .catch(error => console.error("Error fetching suggestions:",
                                        error));
                            } else {
                                suggestionsBox.style.display = "none";
                            }
                        }
                    });

                    // Lắng nghe sự kiện click vào suggestion-item và highlight matching products
                    document.addEventListener("click", function(e) {
                        if (e.target.classList.contains("suggestion-item")) {
                            const productData = JSON.parse(e.target.dataset.product);
                            const productsWrapper = document.getElementById("products-wrapper");
                            let lastProductContainer = productsWrapper.lastElementChild;

                            const productNameField = lastProductContainer.querySelector(
                                "input[name='name[]']");

                            if (productNameField && productNameField.value === '') {
                                productNameField.value = productData.product_name;
                                lastProductContainer.querySelector("input[name^='code_color']").value =
                                    productData.code_color;
                                lastProductContainer.querySelector("input[name^='color_name']").value =
                                    productData.color_name;
                                lastProductContainer.querySelector("input[name^='capacity']").value =
                                    productData.capacity;
                            } else if (productNameField && productNameField.value === productData
                                .product_name) {
                                const colorContainer = lastProductContainer.querySelector(
                                    '.color-container');
                                colorContainer.insertAdjacentHTML('beforeend', createColorFieldHTML(
                                    productData.code_color, productData.color_name, productData
                                    .capacity));
                            } else {
                                document.getElementById('add-item').click();
                                setTimeout(() => {
                                    lastProductContainer = productsWrapper.lastElementChild;
                                    const newProductNameField = lastProductContainer
                                        .querySelector("input[name='name[]']");
                                    if (newProductNameField) newProductNameField.value =
                                        productData.product_name;
                                    lastProductContainer.querySelector(
                                            "input[name^='code_color']").value = productData
                                        .code_color;
                                    lastProductContainer.querySelector(
                                            "input[name^='color_name']").value = productData
                                        .color_name;
                                    lastProductContainer.querySelector(
                                            "input[name^='capacity']").value = productData
                                        .capacity;
                                }, 0);
                            }

                            e.target.closest('.suggestions-box').style.display = "none";

                            highlightMatchingProducts(productData.product_name, productData.code_color,
                                productData.color_name, productData.capacity);
                        }
                    });

                    // Hàm highlight các sản phẩm trùng khớp trong bảng request-product và product-wrapper
                    function highlightMatchingProducts(productName, codeColor, colorName, capacity) {
                        const requestProductTable = document.querySelector(".request_product table");

                        if (requestProductTable) {
                            const rows = requestProductTable.querySelectorAll(".request-product-row");

                            rows.forEach(row => {
                                const productInfoCell = row.querySelector("td:nth-child(2)");

                                if (productInfoCell) {
                                    const matchProduct = productInfoCell.textContent.includes(
                                            productName) &&
                                        productInfoCell.textContent.includes(codeColor);
                                    const matchCapacity = !capacity || productInfoCell.textContent
                                        .includes(capacity);

                                    if (matchProduct && matchCapacity) {
                                        row.querySelectorAll("td").forEach(td => {
                                            td.classList.add("highlight-td");
                                        });
                                    }
                                }
                            });
                        }

                        const productRows = document.querySelectorAll('#products-wrapper .product-container');
                        productRows.forEach(row => {
                            const nameInput = row.querySelector("input[name='name[]']");
                            const codeColorInput = row.querySelector("input[name^='code_color']");
                            const colorNameInput = row.querySelector("input[name^='color_name']");
                            const capacityInput = row.querySelector("input[name^='capacity']");

                            if (nameInput && codeColorInput && colorNameInput && capacityInput) {
                                if (nameInput.value === productName && codeColorInput.value ===
                                    codeColor && capacityInput.value === capacity) {
                                    row.classList.add("highlight-product");
                                } else {
                                    row.classList.remove("highlight-product");
                                }
                            }
                        });
                    }

                    function autoHighlightProducts() {
                        const requestProductTable = document.querySelector(".request_product table");

                        if (requestProductTable) {
                            const requestRows = requestProductTable.querySelectorAll(".request-product-row");

                            requestRows.forEach(requestRow => {
                                const requestProductInfo = requestRow.querySelector("td:nth-child(2)")
                                    .textContent;

                                const productRows = document.querySelectorAll(
                                    '#products-wrapper .product-container');
                                productRows.forEach(row => {
                                    const nameInput = row.querySelector("input[name='name[]']");
                                    const codeColorInput = row.querySelector(
                                        "input[name^='code_color']");
                                    const capacityInput = row.querySelector(
                                        "input[name^='capacity']");

                                    if (nameInput && codeColorInput && capacityInput) {
                                        const matchProduct = requestProductInfo.includes(
                                                nameInput.value) &&
                                            requestProductInfo.includes(codeColorInput.value);
                                        const matchCapacity = !capacityInput.value ||
                                            requestProductInfo.includes(capacityInput.value);

                                        if (matchProduct && matchCapacity) {
                                            row.classList.add("highlight-product");
                                        } else {
                                            row.classList.remove("highlight-product");
                                        }
                                    }
                                });
                            });
                        }
                    }


                    // Kiểm tra sản phẩm khi thêm hoặc khi nhập vào trường
                    document.addEventListener('input', function(event) {
                        if (event.target.matches("input[name='name[]']") || event.target.matches(
                                "input[name^='code_color']") || event.target.matches(
                                "input[name^='capacity']")) {
                            autoHighlightProducts();
                        }
                    });
                });
                </script>








</html>